<?php
/**
 * Authentication Class
 * Handles user registration, login, logout, and session management
 */

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Register a new user
     */
    public function register($userData) {
        try {
            // Validate required fields
            $required = ['full_name', 'email', 'password', 'location', 'user_type'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    throw new Exception("$field is required");
                }
            }
            
            // Validate email format
            if (!Security::isValidEmail($userData['email'])) {
                throw new Exception("Invalid email format");
            }
            
            // Check if email already exists
            if ($this->db->exists('users', 'email = ?', [$userData['email']])) {
                throw new Exception("Email already registered");
            }
            
            // Hash password
            $userData['password_hash'] = Security::hashPassword($userData['password']);
            unset($userData['password']);
            
            // Sanitize input
            $userData = Security::sanitizeInput($userData);
            
            // Start transaction
            $this->db->getConnection()->beginTransaction();
            
            // Insert user
            $userId = $this->db->insert('users', [
                'full_name' => $userData['full_name'],
                'email' => $userData['email'],
                'password_hash' => $userData['password_hash'],
                'location' => $userData['location'],
                'user_type' => $userData['user_type'],
                'bio' => $userData['bio'] ?? ''
            ]);
            
            // Insert user type specific data
            if ($userData['user_type'] === 'volunteer') {
                $this->db->insert('volunteers', [
                    'user_id' => $userId,
                    'availability' => $userData['availability'] ?? 'flexible',
                    'hours_per_week' => $userData['hours_per_week'] ?? 1
                ]);
                
                // Add skills and interests
                if (!empty($userData['skills'])) {
                    $this->addUserSkills($userId, $userData['skills']);
                }
                
                if (!empty($userData['interests'])) {
                    $this->addUserInterests($userId, $userData['interests']);
                }
            } else {
                $this->db->insert('organizations', [
                    'user_id' => $userId,
                    'org_name' => $userData['org_name'],
                    'org_type' => $userData['org_type'],
                    'mission_statement' => $userData['mission_statement'] ?? '',
                    'website' => $userData['website'] ?? ''
                ]);
            }
            
            // Commit transaction
            $this->db->getConnection()->commit();
            
            // Get user data for session
            $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
            
            // Create session
            $this->createSession($user);
            
            // Send welcome email
            Email::sendWelcomeEmail($user);
            
            return ['success' => true, 'user' => $user];
            
        } catch (Exception $e) {
            // Rollback transaction
            $this->db->getConnection()->rollBack();
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        try {
            // Check login attempts
            if ($this->isAccountLocked($email)) {
                throw new Exception("Account temporarily locked due to too many failed attempts");
            }
            
            // Get user by email
            $user = $this->db->fetchOne(
                "SELECT * FROM users WHERE email = ? AND is_active = 1", 
                [$email]
            );
            
            if (!$user) {
                $this->recordFailedAttempt($email);
                throw new Exception("Invalid email or password");
            }
            
            // Verify password
            if (!Security::verifyPassword($password, $user['password_hash'])) {
                $this->recordFailedAttempt($email);
                throw new Exception("Invalid email or password");
            }
            
            // Clear failed attempts
            $this->clearFailedAttempts($email);
            
            // Create session
            $this->createSession($user);
            
            // Update last login
            $this->db->update('users', 
                ['last_login' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$user['id']]
            );
            
            return ['success' => true, 'user' => $user];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        // Destroy session
        session_destroy();
        
        // Clear session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        return true;
    }
    
    /**
     * Create user session
     */
    private function createSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Check if account is locked
     */
    private function isAccountLocked($email) {
        $attempts = $this->db->fetchOne(
            "SELECT COUNT(*) as count, MAX(created_at) as last_attempt 
             FROM login_attempts 
             WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)",
            [$email, LOGIN_LOCKOUT_TIME]
        );
        
        return $attempts['count'] >= MAX_LOGIN_ATTEMPTS;
    }
    
    /**
     * Record failed login attempt
     */
    private function recordFailedAttempt($email) {
        $this->db->insert('login_attempts', [
            'email' => $email,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
    
    /**
     * Clear failed login attempts
     */
    private function clearFailedAttempts($email) {
        $this->db->delete('login_attempts', 'email = ?', [$email]);
    }
    
    /**
     * Add skills to user
     */
    private function addUserSkills($userId, $skills) {
        foreach ($skills as $skillName) {
            // Get or create skill
            $skill = $this->db->fetchOne("SELECT id FROM skills WHERE name = ?", [$skillName]);
            if (!$skill) {
                $skillId = $this->db->insert('skills', [
                    'name' => $skillName,
                    'category' => 'general'
                ]);
            } else {
                $skillId = $skill['id'];
            }
            
            // Add user-skill relationship
            $this->db->insert('user_skills', [
                'user_id' => $userId,
                'skill_id' => $skillId
            ]);
        }
    }
    
    /**
     * Add interests to user
     */
    private function addUserInterests($userId, $interests) {
        foreach ($interests as $interestName) {
            // Get or create interest
            $interest = $this->db->fetchOne("SELECT id FROM interests WHERE name = ?", [$interestName]);
            if (!$interest) {
                $interestId = $this->db->insert('interests', [
                    'name' => $interestName,
                    'category' => 'general'
                ]);
            } else {
                $interestId = $interest['id'];
            }
            
            // Add user-interest relationship
            $this->db->insert('user_interests', [
                'user_id' => $userId,
                'interest_id' => $interestId
            ]);
        }
    }
    
    /**
     * Reset password
     */
    public function resetPassword($email) {
        try {
            $user = $this->db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
            if (!$user) {
                throw new Exception("Email not found");
            }
            
            // Generate reset token
            $token = Security::generateRandomString();
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Save reset token
            $this->db->insert('password_resets', [
                'user_id' => $user['id'],
                'token' => $token,
                'expires_at' => $expires
            ]);
            
            // Send reset email
            $resetLink = APP_URL . "/reset-password.php?token=$token";
            $subject = "Password Reset Request";
            $message = "
                <h2>Password Reset Request</h2>
                <p>Hello {$user['full_name']},</p>
                <p>You requested to reset your password. Click the link below to reset it:</p>
                <p><a href='$resetLink'>Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this, please ignore this email.</p>
                <p>Best regards,<br>The " . APP_NAME . " Team</p>
            ";
            
            Email::send($user['email'], $subject, $message);
            
            return ['success' => true, 'message' => 'Password reset link sent to your email'];
            
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Verify reset token
     */
    public function verifyResetToken($token) {
        $reset = $this->db->fetchOne(
            "SELECT pr.*, u.email FROM password_resets pr 
             JOIN users u ON pr.user_id = u.id 
             WHERE pr.token = ? AND pr.expires_at > NOW()",
            [$token]
        );
        
        return $reset !== false;
    }
    
    /**
     * Update password with reset token
     */
    public function updatePasswordWithToken($token, $newPassword) {
        try {
            $reset = $this->db->fetchOne(
                "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()",
                [$token]
            );
            
            if (!$reset) {
                throw new Exception("Invalid or expired reset token");
            }
            
            // Update password
            $passwordHash = Security::hashPassword($newPassword);
            $this->db->update('users', 
                ['password_hash' => $passwordHash], 
                'id = ?', 
                [$reset['user_id']]
            );
            
            // Delete reset token
            $this->db->delete('password_resets', 'token = ?', [$token]);
            
            return ['success' => true, 'message' => 'Password updated successfully'];
            
        } catch (Exception $e) {
            error_log("Password update error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Check if session is valid
     */
    public static function checkSession() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
            return false;
        }
        
        // Check session timeout
        if (time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
            session_destroy();
            return false;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        return true;
    }
}