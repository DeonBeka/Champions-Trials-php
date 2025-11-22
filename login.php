<?php
/**
 * Volunteer Connect - Login Page
 * PHP/MySQL Implementation
 */

require_once 'config.php';
require_once 'classes/Auth.php';

// Redirect if already logged in
if (Utils::isLoggedIn()) {
    Utils::redirect('dashboard.php');
}

// Initialize auth class
$auth = new Auth();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        $result = $auth->login($_POST['email'], $_POST['password']);
        if ($result['success']) {
            // Check for redirect URL
            $redirect = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
            unset($_SESSION['redirect_after_login']);
            Utils::redirect($redirect);
        } else {
            $error = $result['message'];
        }
    }
}

// Handle welcome message
$welcome = isset($_GET['welcome']) && $_GET['welcome'] == '1';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Volunteer Connect</title>
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
                            <h2 class="card-title">Welcome Back</h2>
                            <p class="card-subtitle">Sign in to your Volunteer Connect account</p>
                        </div>
                        
                        <?php if ($welcome): ?>
                            <div class="message message-success">
                                <i class="fas fa-check-circle"></i>
                                Account created successfully! Please log in to continue.
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="message message-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form id="loginForm" method="POST" action="login.php">
                            <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
                            
                            <div class="form-group">
                                <label class="form-label" for="email">Email Address</label>
                                <div class="input-group">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" class="form-input" id="email" name="email" required
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                           placeholder="Enter your email">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="password">Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" class="form-input" id="password" name="password" required
                                           placeholder="Enter your password">
                                    <button type="button" class="btn-toggle-password" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="passwordToggle"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group-options">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="remember_me" id="rememberMe">
                                    <span class="checkmark"></span>
                                    Remember me
                                </label>
                                <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-full" id="loginBtn">
                                <i class="fas fa-sign-in-alt"></i> Sign In
                            </button>
                        </form>
                        
                        <div class="auth-footer">
                            <p>Don't have an account? <a href="signup.php">Sign up</a></p>
                        </div>
                        
                        <!-- Social Login -->
                        <div class="social-login">
                            <div class="divider">
                                <span>or continue with</span>
                            </div>
                            <div class="social-buttons">
                                <button class="btn btn-social btn-google" onclick="socialLogin('google')">
                                    <i class="fab fa-google"></i> Google
                                </button>
                                <button class="btn btn-social btn-facebook" onclick="socialLogin('facebook')">
                                    <i class="fab fa-facebook-f"></i> Facebook
                                </button>
                                <button class="btn btn-social btn-linkedin" onclick="socialLogin('linkedin')">
                                    <i class="fab fa-linkedin-in"></i> LinkedIn
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Login Benefits -->
                    <div class="benefits-section">
                        <h3>What You Can Do</h3>
                        <div class="feature-list">
                            <div class="feature-item">
                                <i class="fas fa-search"></i>
                                <div>
                                    <h4>Browse Opportunities</h4>
                                    <p>Find volunteer positions that match your skills and interests</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-user-check"></i>
                                <div>
                                    <h4>Apply Instantly</h4>
                                    <p>Send applications with just a few clicks</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-comments"></i>
                                <div>
                                    <h4>Message Organizations</h4>
                                    <p>Communicate directly with opportunity providers</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-chart-line"></i>
                                <div>
                                    <h4>Track Progress</h4>
                                    <p>Monitor your applications and volunteer hours</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="security-note">
                            <i class="fas fa-shield-alt" style="color: var(--secondary-color);"></i>
                            <p>Your information is secure and encrypted. We never share your data with third parties.</p>
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
                    <a href="help.php">Help Center</a>
                </p>
            </div>
        </div>
    </footer>

    <script src="assets/js/login.js"></script>
</body>
</html>