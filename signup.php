<?php
/**
 * Volunteer Connect - Sign Up Page
 * PHP/MySQL Implementation
 */

require_once 'config.php';
require_once 'classes/Auth.php';
require_once 'classes/User.php';

// Redirect if already logged in
if (Utils::isLoggedIn()) {
    Utils::redirect('dashboard.php');
}

// Initialize classes
$auth = new Auth();
$userClass = new User();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        $result = $auth->register($_POST);
        if ($result['success']) {
            // Registration successful, redirect to dashboard
            Utils::redirect('dashboard.php?welcome=1');
        } else {
            $error = $result['message'];
        }
    }
}

// Get available skills and interests from database
$db = Database::getInstance();
$skills = $db->fetchAll("SELECT name FROM skills ORDER BY name");
$interests = $db->fetchAll("SELECT name FROM interests ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Volunteer Connect</title>
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
            </ul>
            <div class="nav-actions">
                <a href="login.php" class="btn btn-outline">Login</a>
                <a href="signup.php" class="btn btn-primary">Sign Up</a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <section class="auth-section">
            <div class="container">
                <div class="auth-container">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Join Volunteer Connect</h2>
                            <p class="card-subtitle">Start making a difference today</p>
                        </div>
                        
                        <?php if (isset($error)): ?>
                            <div class="message message-error">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form id="signupForm" method="POST" action="signup.php">
                            <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
                            
                            <!-- User Type Selection -->
                            <div class="form-group">
                                <label class="form-label">I want to:</label>
                                <div class="user-type-selector">
                                    <div class="user-type-card" onclick="selectUserType('volunteer')">
                                        <input type="radio" name="user_type" value="volunteer" id="volunteerType" required style="display: none;">
                                        <div class="user-type-icon">🤝</div>
                                        <div class="user-type-title">Volunteer</div>
                                        <p>Help organizations and make a difference</p>
                                    </div>
                                    <div class="user-type-card" onclick="selectUserType('organization')">
                                        <input type="radio" name="user_type" value="organization" id="organizationType" required style="display: none;">
                                        <div class="user-type-icon">🏢</div>
                                        <div class="user-type-title">Organization</div>
                                        <p>Find volunteers for your cause</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Basic Information -->
                            <div class="form-group">
                                <label class="form-label" for="fullName">Full Name *</label>
                                <input type="text" class="form-input" id="fullName" name="full_name" required 
                                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="email">Email Address *</label>
                                <input type="email" class="form-input" id="email" name="email" required
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                <small style="color: #6b7280;">We'll never share your email with anyone else.</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="password">Password *</label>
                                <input type="password" class="form-input" id="password" name="password" required 
                                       minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                       title="Must contain at least one number, one uppercase and lowercase letter, and at least 8 characters">
                                <small style="color: #6b7280;">Use 8 or more characters with a mix of letters, numbers & symbols.</small>
                                <div class="password-strength" id="passwordStrength"></div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="confirmPassword">Confirm Password *</label>
                                <input type="password" class="form-input" id="confirmPassword" name="confirm_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="location">Location *</label>
                                <input type="text" class="form-input" id="location" name="location" placeholder="City, Country" required
                                       value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
                                <small style="color: #6b7280;">Help us find opportunities near you.</small>
                            </div>
                            
                            <!-- Volunteer Specific Fields -->
                            <div id="volunteerFields" style="display: none;">
                                <div class="form-group">
                                    <label class="form-label">Skills & Expertise</label>
                                    <small style="color: #6b7280; display: block; margin-bottom: 0.5rem;">Select all that apply</small>
                                    <div class="tags-container">
                                        <?php foreach ($skills as $skill): ?>
                                            <span class="tag clickable-tag" data-skill="<?php echo htmlspecialchars($skill['name']); ?>">
                                                <input type="checkbox" name="skills[]" value="<?php echo htmlspecialchars($skill['name']); ?>" style="display: none;">
                                                <?php echo htmlspecialchars($skill['name']); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Areas of Interest</label>
                                    <small style="color: #6b7280; display: block; margin-bottom: 0.5rem;">Select causes you care about</small>
                                    <div class="tags-container">
                                        <?php foreach ($interests as $interest): ?>
                                            <span class="tag clickable-tag" data-interest="<?php echo htmlspecialchars($interest['name']); ?>">
                                                <input type="checkbox" name="interests[]" value="<?php echo htmlspecialchars($interest['name']); ?>" style="display: none;">
                                                <?php echo htmlspecialchars($interest['name']); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="availability">Availability</label>
                                    <select class="form-select" id="availability" name="availability">
                                        <option value="">Select availability</option>
                                        <option value="weekends">Weekends only</option>
                                        <option value="evenings">Evenings</option>
                                        <option value="weekdays">Weekdays</option>
                                        <option value="flexible">Flexible</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="hoursPerWeek">Hours per week</label>
                                    <input type="number" class="form-input" id="hoursPerWeek" name="hours_per_week" min="1" max="40" placeholder="e.g., 5">
                                    <small style="color: #6b7280;">How many hours can you commit per week?</small>
                                </div>
                            </div>
                            
                            <!-- Organization Specific Fields -->
                            <div id="organizationFields" style="display: none;">
                                <div class="form-group">
                                    <label class="form-label" for="orgName">Organization Name *</label>
                                    <input type="text" class="form-input" id="orgName" name="org_name" required
                                           value="<?php echo isset($_POST['org_name']) ? htmlspecialchars($_POST['org_name']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="orgType">Organization Type *</label>
                                    <select class="form-select" id="orgType" name="org_type" required>
                                        <option value="">Select type</option>
                                        <option value="nonprofit">Non-Profit</option>
                                        <option value="charity">Charity</option>
                                        <option value="school">Educational Institution</option>
                                        <option value="hospital">Healthcare</option>
                                        <option value="government">Government</option>
                                        <option value="community">Community Group</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="missionStatement">Mission Statement</label>
                                    <textarea class="form-textarea" id="missionStatement" name="mission_statement" 
                                              placeholder="Describe your organization's mission..."><?php echo isset($_POST['mission_statement']) ? htmlspecialchars($_POST['mission_statement']) : ''; ?></textarea>
                                    <small style="color: #6b7280;">Tell us about your organization's purpose and goals.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="website">Website</label>
                                    <input type="url" class="form-input" id="website" name="website" placeholder="https://yourwebsite.com"
                                           value="<?php echo isset($_POST['website']) ? htmlspecialchars($_POST['website']) : ''; ?>">
                                    <small style="color: #6b7280;">Optional: Link to your organization's website.</small>
                                </div>
                            </div>
                            
                            <!-- Bio -->
                            <div class="form-group">
                                <label class="form-label" for="bio">Bio/About *</label>
                                <textarea class="form-textarea" id="bio" name="bio" required 
                                          placeholder="<?php echo isset($_POST['user_type']) && $_POST['user_type'] === 'organization' ? 'Tell us about your organization...' : 'Tell us about yourself and why you want to volunteer...'; ?>"><?php echo isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : ''; ?></textarea>
                                <small style="color: #6b7280;">
                                    <?php echo isset($_POST['user_type']) && $_POST['user_type'] === 'organization' ? 
                                        'Share your organization\'s story, impact, and what you\'re looking for in volunteers.' : 
                                        'Help organizations get to know you and what drives your passion for volunteering.'; ?>
                                </small>
                            </div>
                            
                            <!-- Terms and Privacy -->
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="agree_terms" required>
                                    <span class="checkmark"></span>
                                    I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="newsletter" checked>
                                    <span class="checkmark"></span>
                                    Send me occasional updates about new opportunities and volunteer stories
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-full" id="submitBtn">
                                Create Account
                            </button>
                        </form>
                        
                        <div class="auth-footer">
                            <p>Already have an account? <a href="login.php">Log in</a></p>
                        </div>
                    </div>
                    
                    <!-- Benefits Section -->
                    <div class="benefits-section">
                        <h3>Why Join Volunteer Connect?</h3>
                        <div class="benefit-list">
                            <div class="benefit-item">
                                <i class="fas fa-check-circle" style="color: var(--secondary-color);"></i>
                                <span>Connect with meaningful opportunities</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle" style="color: var(--secondary-color);"></i>
                                <span>Build your skills and experience</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle" style="color: var(--secondary-color);"></i>
                                <span>Make a real impact in your community</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle" style="color: var(--secondary-color);"></i>
                                <span>Join a supportive community</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-check-circle" style="color: var(--secondary-color);"></i>
                                <span>Track your volunteer hours and achievements</span>
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
                <p>
                    <a href="terms.php">Terms of Service</a> | 
                    <a href="privacy.php">Privacy Policy</a> | 
                    <a href="contact.php">Contact Us</a>
                </p>
            </div>
        </div>
    </footer>

    <script src="assets/js/signup.js"></script>
</body>
</html>