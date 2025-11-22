<?php
/**
 * Opportunity Class
 * Handles all opportunity-related operations including CRUD, applications, and search
 */

class Opportunity {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create new opportunity
     */
    public function create($organizationId, $data) {
        try {
            // Validate required fields
            $required = ['title', 'description', 'category', 'location', 'time_commitment', 'duration'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("$field is required");
                }
            }
            
            // Validate organization exists and is organization type
            $org = $this->db->fetchOne(
                "SELECT * FROM users WHERE id = ? AND user_type = 'organization' AND is_active = 1",
                [$organizationId]
            );
            if (!$org) {
                throw new Exception("Invalid organization");
            }
            
            // Sanitize input
            $data = Security::sanitizeInput($data);
            
            // Prepare opportunity data
            $opportunityData = [
                'organization_id' => $organizationId,
                'title' => $data['title'],
                'description' => $data['description'],
                'category' => $data['category'],
                'location' => $data['location'],
                'time_commitment' => $data['time_commitment'],
                'duration' => $data['duration'],
                'skills_required' => $data['skills_required'] ?? '',
                'volunteers_needed' => (int)($data['volunteers_needed'] ?? 1),
                'deadline' => !empty($data['deadline']) ? date('Y-m-d', strtotime($data['deadline'])) : null
            ];
            
            $opportunityId = $this->db->insert('opportunities', $opportunityData);
            
            return ['success' => true, 'opportunity_id' => $opportunityId];
            
        } catch (Exception $e) {
            error_log("Create opportunity error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get opportunity by ID
     */
    public function getById($opportunityId) {
        $sql = "SELECT o.*, u.full_name as organization_name, u.email as organization_email,
                org.org_name, org.org_type, org.mission_statement
                FROM opportunities o 
                JOIN users u ON o.organization_id = u.id 
                LEFT JOIN organizations org ON u.id = org.user_id 
                WHERE o.id = ? AND o.is_active = 1";
        
        $opportunity = $this->db->fetchOne($sql, [$opportunityId]);
        
        if ($opportunity) {
            // Get application count
            $opportunity['applications_count'] = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM applications WHERE opportunity_id = ?",
                [$opportunityId]
            )['count'];
            
            // Get accepted volunteers count
            $opportunity['accepted_count'] = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM applications WHERE opportunity_id = ? AND status = 'accepted'",
                [$opportunityId]
            )['count'];
        }
        
        return $opportunity;
    }
    
    /**
     * Update opportunity
     */
    public function update($opportunityId, $organizationId, $data) {
        try {
            // Validate ownership
            $opportunity = $this->db->fetchOne(
                "SELECT * FROM opportunities WHERE id = ? AND organization_id = ? AND is_active = 1",
                [$opportunityId, $organizationId]
            );
            if (!$opportunity) {
                throw new Exception("Opportunity not found or access denied");
            }
            
            $allowedFields = ['title', 'description', 'category', 'location', 'time_commitment', 
                             'duration', 'skills_required', 'volunteers_needed', 'deadline'];
            $updateData = [];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = Security::sanitizeInput($data[$field]);
                }
            }
            
            if (empty($updateData)) {
                throw new Exception("No valid fields to update");
            }
            
            // Format deadline if provided
            if (isset($updateData['deadline'])) {
                $updateData['deadline'] = !empty($updateData['deadline']) ? 
                    date('Y-m-d', strtotime($updateData['deadline'])) : null;
            }
            
            $this->db->update('opportunities', $updateData, 'id = ?', [$opportunityId]);
            
            return ['success' => true, 'message' => 'Opportunity updated successfully'];
            
        } catch (Exception $e) {
            error_log("Update opportunity error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Delete opportunity (soft delete)
     */
    public function delete($opportunityId, $organizationId) {
        try {
            // Validate ownership
            $opportunity = $this->db->fetchOne(
                "SELECT * FROM opportunities WHERE id = ? AND organization_id = ?",
                [$opportunityId, $organizationId]
            );
            if (!$opportunity) {
                throw new Exception("Opportunity not found or access denied");
            }
            
            // Soft delete
            $this->db->update('opportunities', 
                ['is_active' => false], 
                'id = ?', 
                [$opportunityId]
            );
            
            return ['success' => true, 'message' => 'Opportunity deleted successfully'];
            
        } catch (Exception $e) {
            error_log("Delete opportunity error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Search opportunities with filters
     */
    public function search($filters = [], $page = 1, $perPage = 10) {
        $where = ["o.is_active = 1", "u.is_active = 1"];
        $params = [];
        
        // Search term
        if (!empty($filters['search'])) {
            $where[] = "(o.title LIKE ? OR o.description LIKE ? OR u.full_name LIKE ? OR org.org_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Category filter
        if (!empty($filters['category'])) {
            $where[] = "o.category = ?";
            $params[] = $filters['category'];
        }
        
        // Location filter
        if (!empty($filters['location'])) {
            if ($filters['location'] === 'remote') {
                $where[] = "(o.location LIKE '%remote%' OR o.location LIKE '%online%')";
            } else {
                $where[] = "o.location LIKE ?";
                $params[] = '%' . $filters['location'] . '%';
            }
        }
        
        // Organization type filter
        if (!empty($filters['org_type'])) {
            $where[] = "org.org_type = ?";
            $params[] = $filters['org_type'];
        }
        
        // Date filter (opportunities not expired)
        if (!empty($filters['active_only'])) {
            $where[] = "(o.deadline IS NULL OR o.deadline >= CURDATE())";
        }
        
        // Build query
        $whereClause = implode(' AND ', $where);
        
        // Get total count
        $countSql = "SELECT COUNT(DISTINCT o.id) as total
                     FROM opportunities o 
                     JOIN users u ON o.organization_id = u.id 
                     LEFT JOIN organizations org ON u.id = org.user_id 
                     WHERE $whereClause";
        $total = $this->db->fetchOne($countSql, $params)['total'];
        
        // Get pagination info
        $pagination = Utils::paginate($page, $perPage, $total);
        
        // Get results
        $sql = "SELECT o.*, u.full_name as organization_name, u.email as organization_email,
                org.org_name, org.org_type
                FROM opportunities o 
                JOIN users u ON o.organization_id = u.id 
                LEFT JOIN organizations org ON u.id = org.user_id 
                WHERE $whereClause
                ORDER BY o.created_at DESC
                LIMIT {$pagination['offset']}, {$pagination['perPage']}";
        
        $opportunities = $this->db->fetchAll($sql, $params);
        
        // Add statistics to each opportunity
        foreach ($opportunities as &$opportunity) {
            $opportunity['applications_count'] = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM applications WHERE opportunity_id = ?",
                [$opportunity['id']]
            )['count'];
            
            $opportunity['accepted_count'] = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM applications WHERE opportunity_id = ? AND status = 'accepted'",
                [$opportunity['id']]
            )['count'];
        }
        
        return [
            'opportunities' => $opportunities,
            'pagination' => $pagination
        ];
    }
    
    /**
     * Get opportunities by organization
     */
    public function getByOrganization($organizationId, $page = 1, $perPage = 10) {
        // Get total count
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM opportunities WHERE organization_id = ? AND is_active = 1",
            [$organizationId]
        )['count'];
        
        // Get pagination info
        $pagination = Utils::paginate($page, $perPage, $total);
        
        // Get opportunities
        $sql = "SELECT o.*, 
                       (SELECT COUNT(*) FROM applications WHERE opportunity_id = o.id) as applications_count,
                       (SELECT COUNT(*) FROM applications WHERE opportunity_id = o.id AND status = 'accepted') as accepted_count
                FROM opportunities o 
                WHERE o.organization_id = ? AND o.is_active = 1
                ORDER BY o.created_at DESC
                LIMIT {$pagination['offset']}, {$pagination['perPage']}";
        
        $opportunities = $this->db->fetchAll($sql, [$organizationId]);
        
        return [
            'opportunities' => $opportunities,
            'pagination' => $pagination
        ];
    }
    
    /**
     * Get recommended opportunities for volunteer
     */
    public function getRecommended($volunteerId, $limit = 5) {
        // Get volunteer's skills and interests
        $volunteer = $this->db->fetchOne(
            "SELECT v.*, u.location 
             FROM volunteers v 
             JOIN users u ON v.user_id = u.id 
             WHERE v.user_id = ?",
            [$volunteerId]
        );
        
        if (!$volunteer) {
            return [];
        }
        
        $skills = $this->db->fetchAll(
            "SELECT s.name FROM skills s 
             JOIN user_skills us ON s.id = us.skill_id 
             WHERE us.user_id = ?",
            [$volunteerId]
        );
        
        $interests = $this->db->fetchAll(
            "SELECT i.name FROM interests i 
             JOIN user_interests ui ON i.id = ui.interest_id 
             WHERE ui.user_id = ?",
            [$volunteerId]
        );
        
        $skillNames = array_column($skills, 'name');
        $interestNames = array_column($interests, 'name');
        
        $where = ["o.is_active = 1", "u.is_active = 1"];
        $params = [];
        $orderBy = "o.created_at DESC";
        
        // If volunteer has skills or interests, prioritize matching opportunities
        if (!empty($skillNames) || !empty($interestNames)) {
            $matchTerms = array_merge($skillNames, $interestNames);
            $likeConditions = [];
            
            foreach ($matchTerms as $term) {
                $likeConditions[] = "(o.title LIKE ? OR o.description LIKE ? OR o.skills_required LIKE ?)";
                $likeTerm = '%' . $term . '%';
                $params[] = $likeTerm;
                $params[] = $likeTerm;
                $params[] = $likeTerm;
            }
            
            if (!empty($likeConditions)) {
                $where[] = "(" . implode(" OR ", $likeConditions) . ")";
                $orderBy = "CASE WHEN " . implode(" OR ", $likeConditions) . " THEN 1 ELSE 2 END, o.created_at DESC";
            }
        }
        
        // Filter by location preference (same city or remote)
        if (!empty($volunteer['location'])) {
            $where[] = "(o.location LIKE ? OR o.location LIKE '%remote%' OR o.location LIKE '%online%')";
            $params[] = '%' . $volunteer['location'] . '%';
        }
        
        // Exclude already applied opportunities
        $where[] = "o.id NOT IN (SELECT opportunity_id FROM applications WHERE volunteer_id = ?)";
        $params[] = $volunteerId;
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT o.*, u.full_name as organization_name, org.org_name
                FROM opportunities o 
                JOIN users u ON o.organization_id = u.id 
                LEFT JOIN organizations org ON u.id = org.user_id 
                WHERE $whereClause
                ORDER BY $orderBy
                LIMIT ?";
        
        $params[] = $limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Apply for opportunity
     */
    public function apply($opportunityId, $volunteerId, $message = '') {
        try {
            // Validate opportunity exists and is active
            $opportunity = $this->db->fetchOne(
                "SELECT o.*, u.full_name as organization_name, u.email as organization_email
                 FROM opportunities o 
                 JOIN users u ON o.organization_id = u.id 
                 WHERE o.id = ? AND o.is_active = 1",
                [$opportunityId]
            );
            if (!$opportunity) {
                throw new Exception("Opportunity not found");
            }
            
            // Check if volunteer has already applied
            if ($this->db->exists('applications', 'opportunity_id = ? AND volunteer_id = ?', [$opportunityId, $volunteerId])) {
                throw new Exception("You have already applied for this opportunity");
            }
            
            // Check if deadline has passed
            if ($opportunity['deadline'] && strtotime($opportunity['deadline']) < strtotime('today')) {
                throw new Exception("Application deadline has passed");
            }
            
            // Check if still spots available
            $acceptedCount = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM applications WHERE opportunity_id = ? AND status = 'accepted'",
                [$opportunityId]
            )['count'];
            
            if ($acceptedCount >= $opportunity['volunteers_needed']) {
                throw new Exception("No more spots available for this opportunity");
            }
            
            // Create application
            $applicationId = $this->db->insert('applications', [
                'opportunity_id' => $opportunityId,
                'volunteer_id' => $volunteerId,
                'message' => Security::sanitizeInput($message),
                'status' => 'pending'
            ]);
            
            // Get volunteer details for notification
            $volunteer = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$volunteerId]);
            
            // Send notification to organization
            Email::sendApplicationNotification($opportunity, $volunteer, $opportunity);
            
            // Create notification for organization
            $this->db->insert('notifications', [
                'user_id' => $opportunity['organization_id'],
                'type' => 'application',
                'title' => 'New Application Received',
                'message' => "{$volunteer['full_name']} has applied for your opportunity: {$opportunity['title']}"
            ]);
            
            return ['success' => true, 'application_id' => $applicationId];
            
        } catch (Exception $e) {
            error_log("Apply for opportunity error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get applications for opportunity
     */
    public function getApplications($opportunityId, $organizationId, $status = null) {
        // Validate ownership
        $opportunity = $this->db->fetchOne(
            "SELECT * FROM opportunities WHERE id = ? AND organization_id = ?",
            [$opportunityId, $organizationId]
        );
        if (!$opportunity) {
            return [];
        }
        
        $where = ["a.opportunity_id = ?"];
        $params = [$opportunityId];
        
        if ($status) {
            $where[] = "a.status = ?";
            $params[] = $status;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT a.*, v.full_name, v.email, v.location, v.bio,
                       availability, hours_per_week
                FROM applications a 
                JOIN users v ON a.volunteer_id = v.id 
                LEFT JOIN volunteers vol ON v.id = vol.user_id 
                WHERE $whereClause
                ORDER BY a.applied_at DESC";
        
        $applications = $this->db->fetchAll($sql, $params);
        
        // Add volunteer skills to each application
        foreach ($applications as &$application) {
            $application['skills'] = $this->db->fetchAll(
                "SELECT s.name, us.proficiency_level 
                 FROM skills s 
                 JOIN user_skills us ON s.id = us.skill_id 
                 WHERE us.user_id = ?",
                [$application['volunteer_id']]
            );
        }
        
        return $applications;
    }
    
    /**
     * Update application status
     */
    public function updateApplicationStatus($applicationId, $organizationId, $status, $responseMessage = '') {
        try {
            // Validate application ownership
            $sql = "SELECT a.*, o.organization_id 
                    FROM applications a 
                    JOIN opportunities o ON a.opportunity_id = o.id 
                    WHERE a.id = ? AND o.organization_id = ?";
            $application = $this->db->fetchOne($sql, [$applicationId, $organizationId]);
            
            if (!$application) {
                throw new Exception("Application not found or access denied");
            }
            
            // Update status
            $this->db->update('applications', 
                ['status' => $status], 
                'id = ?', 
                [$applicationId]
            );
            
            // Get opportunity and volunteer details
            $opportunity = $this->db->fetchOne(
                "SELECT * FROM opportunities WHERE id = ?",
                [$application['opportunity_id']]
            );
            $volunteer = $this->db->fetchOne(
                "SELECT * FROM users WHERE id = ?",
                [$application['volunteer_id']]
            );
            
            // Send notification to volunteer
            $statusText = ucfirst($status);
            $subject = "Application Status Update: {$opportunity['title']}";
            $message = "
                <h2>Application Status Update</h2>
                <p>Hello {$volunteer['full_name']},</p>
                <p>Your application for <strong>{$opportunity['title']}</strong> has been <strong>$statusText</strong>.</p>
                " . ($responseMessage ? "<p>Message from organization: $responseMessage</p>" : "") . "
                <p><a href='" . APP_URL . "/dashboard.php'>View in Dashboard</a></p>
                <p>Best regards,<br>The " . APP_NAME . " Team</p>
            ";
            
            Email::send($volunteer['email'], $subject, $message);
            
            // Create notification
            $this->db->insert('notifications', [
                'user_id' => $application['volunteer_id'],
                'type' => 'application',
                'title' => 'Application Status Updated',
                'message' => "Your application for {$opportunity['title']} has been $statusText"
            ]);
            
            return ['success' => true, 'message' => 'Application status updated'];
            
        } catch (Exception $e) {
            error_log("Update application status error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get categories list
     */
    public function getCategories() {
        $categories = $this->db->fetchAll(
            "SELECT DISTINCT category, COUNT(*) as count 
             FROM opportunities 
             WHERE is_active = 1 
             GROUP BY category 
             ORDER BY count DESC"
        );
        
        return $categories;
    }
    
    /**
     * Get locations list
     */
    public function getLocations() {
        $locations = $this->db->fetchAll(
            "SELECT DISTINCT location, COUNT(*) as count 
             FROM opportunities 
             WHERE is_active = 1 
             GROUP BY location 
             ORDER BY count DESC 
             LIMIT 20"
        );
        
        return $locations;
    }
}