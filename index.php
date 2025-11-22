<?php
/**
 * Volunteer Connect - Main Homepage
 * PHP/MySQL Implementation
 */

require_once 'config.php';
require_once 'classes/Auth.php';
require_once 'classes/User.php';
require_once 'classes/Opportunity.php';
require_once 'classes/Message.php';

// Initialize classes
$auth = new Auth();
$userClass = new User();
$opportunityClass = new Opportunity();
$messageClass = new Message();

// Get current user
$currentUser = Utils::getCurrentUser();
$userStats = $currentUser ? $userClass->getUserStats($currentUser['id']) : [];

// Get featured opportunities
$featuredOpportunities = $opportunityClass->search(['active_only' => true], 1, 6);

// Get recent volunteers
$recentVolunteers = $userClass->searchVolunteers([], 1, 6);

// Get organizations count
$organizationsCount = $userClass->searchOrganizations([], 1, 1)['pagination']['total'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'login':
                $result = $auth->login($_POST['email'], $_POST['password']);
                if ($result['success']) {
                    Utils::redirect('dashboard.php');
                } else {
                    $loginError = $result['message'];
                }
                break;
                
            case 'signup':
                $result = $auth->register($_POST);
                if ($result['success']) {
                    Utils::redirect('dashboard.php');
                } else {
                    $signupError = $result['message'];
                }
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Connect - Connect Volunteers with Opportunities</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header Navigation -->
    <header>
        <nav class="container">
            <a href="index.php" class="logo">
                <i class="fas fa-hands-helping"></i> Volunteer Connect
            </a>
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php">Home</a></li>
                <li><a href="opportunities.php">Opportunities</a></li>
                <li><a href="volunteers.php">Volunteers</a></li>
                <?php if ($currentUser): ?>
                    <li><a href="messages.php">
                        Messages 
                        <?php if ($userStats['unread_messages'] > 0): ?>
                            <span class="badge"><?php echo $userStats['unread_messages']; ?></span>
                        <?php endif; ?>
                    </a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                <?php endif; ?>
            </ul>
            <div class="nav-actions">
                <?php if ($currentUser): ?>
                    <div class="user-menu">
                        <img src="assets/images/default-avatar.png" alt="Avatar" class="avatar-small">
                        <span><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                        <a href="logout.php" class="btn btn-outline">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="signup.php" class="btn btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <h1>Connect. Volunteer. Make a Difference.</h1>
                <p>Join thousands of volunteers and organizations making positive change in communities worldwide.</p>
                <div class="hero-actions">
                    <?php if (!$currentUser): ?>
                        <a href="signup.php" class="btn btn-secondary btn-large">
                            <i class="fas fa-user-plus"></i> Get Started Today
                        </a>
                    <?php endif; ?>
                    <a href="opportunities.php" class="btn btn-outline btn-large">
                        <i class="fas fa-search"></i> Browse Opportunities
                    </a>
                </div>
            </div>
        </section>
        
        <!-- Statistics Section -->
        <section class="stats-section">
            <div class="container">
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-number">10,000+</div>
                        <div class="stat-label">Active Volunteers</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo number_format($organizationsCount); ?>+</div>
                        <div class="stat-label">Partner Organizations</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">50,000+</div>
                        <div class="stat-label">Hours Contributed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo number_format($featuredOpportunities['pagination']['total']); ?>+</div>
                        <div class="stat-label">Active Opportunities</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section">
            <div class="container">
                <h2 class="section-title">How It Works</h2>
                <div class="grid grid-3">
                    <div class="card">
                        <div class="card-header">
                            <div class="user-type-icon">👤</div>
                            <h3>Create Your Profile</h3>
                        </div>
                        <p>Sign up and build a detailed profile showcasing your skills, interests, and availability.</p>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <div class="user-type-icon">🔍</div>
                            <h3>Find Matches</h3>
                        </div>
                        <p>Discover opportunities that align with your passions or find volunteers perfect for your cause.</p>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <div class="user-type-icon">🤝</div>
                            <h3>Connect & Collaborate</h3>
                        </div>
                        <p>Message, apply, and start making a real difference in your community.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Opportunities Section -->
        <section class="featured-section">
            <div class="container">
                <h2 class="section-title">Featured Opportunities</h2>
                <div class="grid grid-3">
                    <?php foreach ($featuredOpportunities['opportunities'] as $opportunity): ?>
                        <div class="opportunity-card">
                            <div class="opportunity-header">
                                <div>
                                    <h3 class="opportunity-title">
                                        <a href="opportunity-details.php?id=<?php echo $opportunity['id']; ?>">
                                            <?php echo htmlspecialchars($opportunity['title']); ?>
                                        </a>
                                    </h3>
                                    <p><?php echo htmlspecialchars($opportunity['organization_name']); ?></p>
                                </div>
                                <span class="badge"><?php echo htmlspecialchars($opportunity['category']); ?></span>
                            </div>
                            <p><?php echo Utils::truncateText($opportunity['description'], 150); ?></p>
                            <div class="opportunity-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($opportunity['location']); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($opportunity['time_commitment']); ?></span>
                                <span><i class="fas fa-users"></i> <?php echo $opportunity['applications_count']; ?> applied</span>
                            </div>
                            <div style="margin-top: 1rem;">
                                <?php if ($currentUser && $currentUser['user_type'] === 'volunteer'): ?>
                                    <a href="apply-opportunity.php?id=<?php echo $opportunity['id']; ?>" class="btn btn-primary">Apply Now</a>
                                <?php endif; ?>
                                <a href="opportunity-details.php?id=<?php echo $opportunity['id']; ?>" class="btn btn-outline">Learn More</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="opportunities.php" class="btn btn-secondary">View All Opportunities</a>
                </div>
            </div>
        </section>

        <!-- Recent Volunteers Section -->
        <section class="volunteers-section">
            <div class="container">
                <h2 class="section-title">Recent Volunteers</h2>
                <div class="grid grid-4">
                    <?php foreach ($recentVolunteers['volunteers'] as $volunteer): ?>
                        <div class="card volunteer-card">
                            <div class="volunteer-header">
                                <div class="avatar">
                                    <?php echo strtoupper(substr($volunteer['full_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h4><?php echo htmlspecialchars($volunteer['full_name']); ?></h4>
                                    <p style="color: #6b7280; font-size: 0.875rem;"><?php echo htmlspecialchars($volunteer['location']); ?></p>
                                </div>
                            </div>
                            <p style="font-size: 0.875rem;"><?php echo Utils::truncateText($volunteer['bio'], 80); ?></p>
                            <div class="tags-container" style="margin: 0.5rem 0;">
                                <?php 
                                $skillCount = 0;
                                foreach ($volunteer['skills'] as $skill): 
                                    if ($skillCount >= 3) break;
                                ?>
                                    <span class="tag"><?php echo htmlspecialchars($skill['name']); ?></span>
                                <?php 
                                    $skillCount++;
                                endforeach; 
                                ?>
                            </div>
                            <div style="margin-top: 1rem;">
                                <a href="volunteer-profile.php?id=<?php echo $volunteer['id']; ?>" class="btn btn-outline btn-small">View Profile</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="volunteers.php" class="btn btn-secondary">Browse All Volunteers</a>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials-section">
            <div class="container">
                <h2 class="section-title">Success Stories</h2>
                <div class="grid grid-3">
                    <div class="card testimonial-card">
                        <div class="testimonial-content">
                            <p>"Volunteer Connect helped me find meaningful opportunities that match my skills. I've met amazing people and made a real impact in my community!"</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="avatar">S</div>
                            <div>
                                <strong>Sarah Johnson</strong>
                                <p style="color: #6b7280; font-size: 0.875rem;">Education Volunteer</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card testimonial-card">
                        <div class="testimonial-content">
                            <p>"We found passionate volunteers quickly and efficiently. The platform made it easy to manage applications and communicate with our team."</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="avatar">M</div>
                            <div>
                                <strong>Michael Chen</strong>
                                <p style="color: #6b7280; font-size: 0.875rem;">Nonprofit Director</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card testimonial-card">
                        <div class="testimonial-content">
                            <p>"The messaging system and application tracking made organizing our community events so much simpler. Highly recommend!"</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="avatar">E</div>
                            <div>
                                <strong>Emily Rodriguez</strong>
                                <p style="color: #6b7280; font-size: 0.875rem;">Event Coordinator</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="card" style="text-align: center; background: linear-gradient(135deg, var(--primary-color), #3b82f6); color: white;">
                    <h2>Ready to Make a Difference?</h2>
                    <p style="font-size: 1.1rem; margin-bottom: 2rem;">Join our community of volunteers and organizations today.</p>
                    <div style="display: flex; gap: 1rem; justify-content: center;">
                        <a href="signup.php" class="btn btn-secondary" style="background: white; color: var(--primary-color);">
                            Get Started Now
                        </a>
                        <a href="about.php" class="btn btn-outline" style="border-color: white; color: white;">
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="grid grid-4">
                <div>
                    <h4>Volunteer Connect</h4>
                    <p>Connecting volunteers with opportunities to make a positive impact in communities worldwide.</p>
                </div>
                <div>
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="how-it-works.php">How It Works</a></li>
                        <li><a href="opportunities.php">Opportunities</a></li>
                        <li><a href="volunteers.php">Volunteers</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Support</h4>
                    <ul>
                        <li><a href="help.php">Help Center</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Connect</h4>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                    <p style="margin-top: 1rem;">Subscribe to our newsletter for updates and opportunities.</p>
                    <form class="newsletter-form" action="newsletter.php" method="POST">
                        <input type="email" name="email" placeholder="Your email" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
            <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                <p>&copy; <?php echo date('Y'); ?> Volunteer Connect. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>