<?php
/**
 * Volunteer Connect - Dashboard Page
 * PHP/MySQL Implementation
 */

// Prevent direct access to this file
if (!defined('VOLUNTEER_CONNECT')) {
    define('VOLUNTEER_CONNECT', true);
}

require_once 'config.php';

// Check if required classes exist before requiring them
if (file_exists('classes/Auth.php')) {
    require_once 'classes/Auth.php';
} else {
    die("Required class files not found. Please ensure all classes are properly uploaded.");
}

if (file_exists('classes/User.php')) {
    require_once 'classes/User.php';
} else {
    die("Required class files not found. Please ensure all classes are properly uploaded.");
}

if (file_exists('classes/Opportunity.php')) {
    require_once 'classes/Opportunity.php';
} else {
    die("Required class files not found. Please ensure all classes are properly uploaded.");
}

if (file_exists('classes/Message.php')) {
    require_once 'classes/Message.php';
} else {
    die("Required class files not found. Please ensure all classes are properly uploaded.");
}

// Require login
Utils::requireLogin();

// Initialize classes with error handling
try {
    $auth = new Auth();
    $userClass = new User();
    $opportunityClass = new Opportunity();
    $messageClass = new Message();
} catch (Exception $e) {
    error_log("Class initialization error: " . $e->getMessage());
    die("System initialization failed. Please try again later.");
}

// Get current user with error handling
try {
    $currentUser = Utils::getCurrentUser();
    if (!$currentUser) {
        Utils::redirect('login.php');
    }
    $userStats = $userClass->getUserStats($currentUser['id']);
    $fullProfile = $userClass->getById($currentUser['id']);
} catch (Exception $e) {
    error_log("User data retrieval error: " . $e->getMessage());
    die("Unable to load user data. Please try again later.");
}

// Initialize variables to prevent undefined variable errors
$error = '';
$success = '';
$applications = [];
$recommendedOpportunities = [];
$opportunities = ['opportunities' => [], 'pagination' => ['total' => 0]];
$recentApplications = [];
$unreadMessages = 0;
$notifications = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        switch ($_POST['action'] ?? '') {
            case 'update_profile':
                try {
                    $result = $userClass->updateProfile($currentUser['id'], $_POST);
                    if ($result['success']) {
                        $success = "Profile updated successfully!";
                        // Refresh user data
                        $currentUser = Utils::getCurrentUser();
                        $fullProfile = $userClass->getById($currentUser['id']);
                    } else {
                        $error = $result['message'];
                    }
                } catch (Exception $e) {
                    error_log("Profile update error: " . $e->getMessage());
                    $error = "Failed to update profile. Please try again.";
                }
                break;
                
            case 'update_volunteer_data':
                if ($currentUser['user_type'] === 'volunteer') {
                    try {
                        $result = $userClass->updateVolunteerData($currentUser['id'], $_POST);
                        if ($result['success']) {
                            $success = "Volunteer information updated successfully!";
                        } else {
                            $error = $result['message'];
                        }
                    } catch (Exception $e) {
                        error_log("Volunteer data update error: " . $e->getMessage());
                        $error = "Failed to update volunteer information. Please try again.";
                    }
                }
                break;
                
            case 'update_organization_data':
                if ($currentUser['user_type'] === 'organization') {
                    try {
                        $result = $userClass->updateOrganizationData($currentUser['id'], $_POST);
                        if ($result['success']) {
                            $success = "Organization information updated successfully!";
                        } else {
                            $error = $result['message'];
                        }
                    } catch (Exception $e) {
                        error_log("Organization data update error: " . $e->getMessage());
                        $error = "Failed to update organization information. Please try again.";
                    }
                }
                break;
        }
    }
}

// Get dashboard data based on user type with error handling
try {
    $db = Database::getInstance();
    
    if ($currentUser['user_type'] === 'volunteer') {
        // Get volunteer's applications
        $applications = $db->fetchAll(
            "SELECT a.*, o.title, o.description, u.full_name as organization_name
             FROM applications a 
             JOIN opportunities o ON a.opportunity_id = o.id 
             JOIN users u ON o.organization_id = u.id 
             WHERE a.volunteer_id = ? 
             ORDER BY a.applied_at DESC 
             LIMIT 5",
            [$currentUser['id']]
        );
        
        // Get recommended opportunities
        $recommendedOpportunities = $opportunityClass->getRecommended($currentUser['id'], 3);
        
    } else {
        // Get organization's opportunities
        $opportunities = $opportunityClass->getByOrganization($currentUser['id'], 1, 5);
        
        // Get recent applications
        $recentApplications = $db->fetchAll(
            "SELECT a.*, o.title, v.full_name as volunteer_name, v.email as volunteer_email
             FROM applications a 
             JOIN opportunities o ON a.opportunity_id = o.id 
             JOIN users v ON a.volunteer_id = v.id 
             WHERE o.organization_id = ? 
             ORDER BY a.applied_at DESC 
             LIMIT 5",
            [$currentUser['id']]
        );
    }
    
    // Get unread messages count
    $unreadMessages = $messageClass->getUnreadCount($currentUser['id']);
    
    // Get recent notifications
    $notifications = $db->fetchAll(
        "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
        [$currentUser['id']]
    );
    
} catch (Exception $e) {
    error_log("Dashboard data loading error: " . $e->getMessage());
    $error = "Unable to load dashboard data. Please try again.";
}

// Handle welcome message
$welcome = isset($_GET['welcome']) && $_GET['welcome'] == '1';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Volunteer Connect</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="container">
            <a href="index.php" class="logo">
                <i class="fas fa-hands-helping"></i> Volunteer Connect
            </a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="opportunities.php">Opportunities</a></li>
                <li><a href="volunteers.php">Volunteers</a></li>
                <li>
                    <a href="messages.php">
                        Messages 
                        <?php if ($unreadMessages > 0): ?>
                            <span class="badge"><?php echo $unreadMessages; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
            </ul>
            <div class="nav-actions">
                <div class="user-menu">
                    <img src="assets/images/default-avatar.png" alt="Avatar" class="avatar-small">
                    <span><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                    <a href="logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <section class="dashboard-section">
            <div class="container">
                <?php if ($welcome): ?>
                    <div class="message message-success" style="margin-bottom: 2rem;">
                        <i class="fas fa-check-circle"></i>
                        Welcome to Volunteer Connect, <?php echo htmlspecialchars($currentUser['full_name']); ?>! Your account has been created successfully.
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="message message-success" style="margin-bottom: 2rem;">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="message message-error" style="margin-bottom: 2rem;">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Dashboard Header -->
                <div class="dashboard-header">
                    <div class="welcome-section">
                        <h1>Welcome back, <?php echo htmlspecialchars($currentUser['full_name']); ?>!</h1>
                        <p><?php echo $currentUser['user_type'] === 'volunteer' ? 
                            'Ready to make a difference? Check out your applications and new opportunities below.' : 
                            'Manage your opportunities and connect with passionate volunteers.'; ?></p>
                    </div>
                    <div class="quick-actions">
                        <?php if ($currentUser['user_type'] === 'volunteer'): ?>
                            <a href="opportunities.php" class="btn btn-primary">
                                <i class="fas fa-search"></i> Find Opportunities
                            </a>
                        <?php else: ?>
                            <a href="create-opportunity.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Post Opportunity
                            </a>
                        <?php endif; ?>
                        <a href="messages.php" class="btn btn-secondary">
                            <i class="fas fa-envelope"></i> Messages
                            <?php if ($unreadMessages > 0): ?>
                                <span class="badge"><?php echo $unreadMessages; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>

                <!-- Statistics Dashboard -->
                <div class="dashboard-stats">
                    <?php if ($currentUser['user_type'] === 'volunteer'): ?>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $userStats['applications']; ?></div>
                            <div class="stat-label">Applications</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $userStats['accepted_applications']; ?></div>
                            <div class="stat-label">Accepted</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $userStats['total_hours']; ?></div>
                            <div class="stat-label">Hours Volunteered</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($recommendedOpportunities); ?></div>
                            <div class="stat-label">Recommended</div>
                        </div>
                    <?php else: ?>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $userStats['opportunities']; ?></div>
                            <div class="stat-label">Opportunities</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $userStats['applications_received']; ?></div>
                            <div class="stat-label">Applications</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $userStats['active_volunteers']; ?></div>
                            <div class="stat-label">Active Volunteers</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $unreadMessages; ?></div>
                            <div class="stat-label">Unread Messages</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="dashboard-content">
                    <div class="dashboard-main">
                        <!-- Tabs -->
                        <div class="tabs">
                            <button class="tab active" onclick="switchTab('overview')">Overview</button>
                            <button class="tab" onclick="switchTab('profile')">Profile</button>
                            <?php if ($currentUser['user_type'] === 'volunteer'): ?>
                                <button class="tab" onclick="switchTab('applications')">Applications</button>
                            <?php else: ?>
                                <button class="tab" onclick="switchTab('opportunities')">Opportunities</button>
                            <?php endif; ?>
                            <button class="tab" onclick="switchTab('notifications')">Notifications</button>
                        </div>

                        <!-- Overview Tab -->
                        <div id="overviewTab" class="tab-content active">
                            <div class="card">
                                <div class="card-header">
                                    <h3>Recent Activity</h3>
                                </div>
                                
                                <?php if ($currentUser['user_type'] === 'volunteer'): ?>
                                    <?php if (!empty($applications)): ?>
                                        <div class="activity-list">
                                            <?php foreach ($applications as $app): ?>
                                                <div class="activity-item">
                                                    <div class="activity-icon">
                                                        <i class="fas fa-paper-plane" style="color: var(--primary-color);"></i>
                                                    </div>
                                                    <div class="activity-content">
                                                        <h4>Applied to: <?php echo htmlspecialchars($app['title']); ?></h4>
                                                        <p><?php echo htmlspecialchars($app['organization_name']); ?></p>
                                                        <span class="activity-time"><?php echo Utils::timeAgo($app['applied_at']); ?></span>
                                                        <span class="badge badge-<?php echo $app['status']; ?>"><?php echo ucfirst($app['status']); ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p>You haven't applied to any opportunities yet. <a href="opportunities.php">Start browsing</a> to find meaningful ways to contribute!</p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($recommendedOpportunities)): ?>
                                        <h4 style="margin-top: 2rem;">Recommended Opportunities</h4>
                                        <div class="recommended-opportunities">
                                            <?php foreach ($recommendedOpportunities as $opp): ?>
                                                <div class="opportunity-card compact">
                                                    <h5><a href="opportunity-details.php?id=<?php echo $opp['id']; ?>"><?php echo htmlspecialchars($opp['title']); ?></a></h5>
                                                    <p><?php echo htmlspecialchars($opp['organization_name']); ?></p>
                                                    <div class="opportunity-meta">
                                                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($opp['location']); ?></span>
                                                        <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($opp['time_commitment']); ?></span>
                                                    </div>
                                                    <a href="apply-opportunity.php?id=<?php echo $opp['id']; ?>" class="btn btn-primary btn-small">Apply</a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                <?php else: ?>
                                    <!-- Organization Overview -->
                                    <?php if (!empty($recentApplications)): ?>
                                        <div class="activity-list">
                                            <?php foreach ($recentApplications as $app): ?>
                                                <div class="activity-item">
                                                    <div class="activity-icon">
                                                        <i class="fas fa-user-plus" style="color: var(--secondary-color);"></i>
                                                    </div>
                                                    <div class="activity-content">
                                                        <h4>New Application: <?php echo htmlspecialchars($app['volunteer_name']); ?></h4>
                                                        <p>For: <?php echo htmlspecialchars($app['title']); ?></p>
                                                        <span class="activity-time"><?php echo Utils::timeAgo($app['applied_at']); ?></span>
                                                        <span class="badge badge-<?php echo $app['status']; ?>"><?php echo ucfirst($app['status']); ?></span>
                                                        <div style="margin-top: 0.5rem;">
                                                            <a href="view-application.php?id=<?php echo $app['id']; ?>" class="btn btn-primary btn-small">Review</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p>No applications yet. <a href="create-opportunity.php">Post an opportunity</a> to start receiving applications from passionate volunteers!</p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($opportunities['opportunities'])): ?>
                                        <h4 style="margin-top: 2rem;">Your Opportunities</h4>
                                        <div class="your-opportunities">
                                            <?php foreach ($opportunities['opportunities'] as $opp): ?>
                                                <div class="opportunity-card compact">
                                                    <h5><a href="opportunity-details.php?id=<?php echo $opp['id']; ?>"><?php echo htmlspecialchars($opp['title']); ?></a></h5>
                                                    <p><?php echo $opp['applications_count']; ?> applications • <?php echo $opp['accepted_count']; ?> accepted</p>
                                                    <div class="opportunity-actions">
                                                        <a href="edit-opportunity.php?id=<?php echo $opp['id']; ?>" class="btn btn-outline btn-small">Edit</a>
                                                        <a href="opportunity-applications.php?id=<?php echo $opp['id']; ?>" class="btn btn-primary btn-small">View Apps</a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Profile Tab -->
                        <div id="profileTab" class="tab-content">
                            <?php include 'includes/dashboard-profile.php'; ?>
                        </div>

                        <!-- Applications/Opportunities Tab -->
                        <div id="applicationsTab" class="tab-content" style="display: none;">
                            <?php if ($currentUser['user_type'] === 'volunteer'): ?>
                                <?php include 'includes/dashboard-applications.php'; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div id="opportunitiesTab" class="tab-content" style="display: none;">
                            <?php if ($currentUser['user_type'] === 'organization'): ?>
                                <?php include 'includes/dashboard-opportunities.php'; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Notifications Tab -->
                        <div id="notificationsTab" class="tab-content" style="display: none;">
                            <?php include 'includes/dashboard-notifications.php'; ?>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="dashboard-sidebar">
                        <!-- Quick Stats -->
                        <div class="card">
                            <div class="card-header">
                                <h4>Quick Stats</h4>
                            </div>
                            <div class="quick-stats">
                                <?php if ($currentUser['user_type'] === 'volunteer'): ?>
                                    <div class="quick-stat">
                                        <span class="stat-label">Profile Completion</span>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: 85%;"></div>
                                        </div>
                                        <span class="stat-value">85%</span>
                                    </div>
                                    <div class="quick-stat">
                                        <span class="stat-label">Response Rate</span>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: 100%;"></div>
                                        </div>
                                        <span class="stat-value">100%</span>
                                    </div>
                                <?php else: ?>
                                    <div class="quick-stat">
                                        <span class="stat-label">Active Opportunities</span>
                                        <span class="stat-value"><?php echo $userStats['opportunities']; ?></span>
                                    </div>
                                    <div class="quick-stat">
                                        <span class="stat-label">Pending Applications</span>
                                        <span class="stat-value"><?php echo $userStats['applications_received']; ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tips -->
                        <div class="card">
                            <div class="card-header">
                                <h4>Tips</h4>
                            </div>
                            <div class="tips-list">
                                <?php if ($currentUser['user_type'] === 'volunteer'): ?>
                                    <div class="tip">
                                        <i class="fas fa-lightbulb" style="color: var(--accent-color);"></i>
                                        <p>Complete your profile to get better opportunity recommendations</p>
                                    </div>
                                    <div class="tip">
                                        <i class="fas fa-clock" style="color: var(--accent-color);"></i>
                                        <p>Respond quickly to organization messages for better chances</p>
                                    </div>
                                    <div class="tip">
                                        <i class="fas fa-star" style="color: var(--accent-color);"></i>
                                        <p>Ask for recommendations after completing volunteer work</p>
                                    </div>
                                <?php else: ?>
                                    <div class="tip">
                                        <i class="fas fa-bullhorn" style="color: var(--accent-color);"></i>
                                        <p>Provide detailed descriptions to attract qualified volunteers</p>
                                    </div>
                                    <div class="tip">
                                        <i class="fas fa-clock" style="color: var(--accent-color);"></i>
                                        <p>Review applications within 48 hours for best results</p>
                                    </div>
                                    <div class="tip">
                                        <i class="fas fa-trophy" style="color: var(--accent-color);"></i>
                                        <p>Recognize outstanding volunteers with recommendations</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Resources -->
                        <div class="card">
                            <div class="card-header">
                                <h4>Resources</h4>
                            </div>
                            <div class="resource-links">
                                <a href="help.php" class="resource-link">
                                    <i class="fas fa-question-circle"></i>
                                    <span>Help Center</span>
                                </a>
                                <a href="blog.php" class="resource-link">
                                    <i class="fas fa-blog"></i>
                                    <span>Volunteer Blog</span>
                                </a>
                                <a href="training.php" class="resource-link">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span>Training Resources</span>
                                </a>
                                <a href="contact.php" class="resource-link">
                                    <i class="fas fa-envelope"></i>
                                    <span>Contact Support</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div style="text-align: center;">
                <p>&copy; <?php echo date('Y'); ?> Volunteer Connect. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/dashboard.js"></script>
</body>
</html>