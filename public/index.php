<?php
// public/index.php
require_once __DIR__ . '/../includes/header.php';
?>

<section class="hero">
  <div class="vc-container hero-inner">
    <div class="hero-left">
      <h1>Connect. Volunteer. <span class="accent">Make a Difference.</span></h1>
      <p class="lead">Join thousands of volunteers and organizations making positive change in communities worldwide.</p>
      <div class="hero-cta">
        <a class="btn btn-cta" href="register.php">Get Started Today</a>
        <a class="btn btn-outline" href="places.php">Browse Opportunities</a>
      </div>

      <ul class="hero-stats">
        <li><strong>10,000+</strong><span>Active Volunteers</span></li>
        <li><strong>500+</strong><span>Partner Organizations</span></li>
        <li><strong>50,000+</strong><span>Hours Contributed</span></li>
        <li><strong>1,200+</strong><span>Active Opportunities</span></li>
      </ul>
    </div>

    <div class="hero-right" aria-hidden="true">
      <!-- Hero visual — using your uploaded image path. Replace if needed. -->
      <div class="hero-card">
        <img src="/mnt/data/050187d2-6952-491a-966e-5c09122a48a1.png" alt="Volunteer Connect hero">
      </div>
    </div>
  </div>
</section>

<section class="how-it-works vc-container">
  <h2 class="section-title">How It Works</h2>

  <div class="cards-grid">
    <article class="info-card">
      <div class="icon">👤</div>
      <h3>Create Your Profile</h3>
      <p>Sign up quickly and tell us what you care about — interests, skills and availability.</p>
    </article>

    <article class="info-card">
      <div class="icon">🔎</div>
      <h3>Find Matches</h3>
      <p>Search for opportunities or volunteers by skill, location, and availability.</p>
    </article>

    <article class="info-card">
      <div class="icon">🤝</div>
      <h3>Connect & Collaborate</h3>
      <p>Apply, chat, and coordinate with organizations to make an impact together.</p>
    </article>
  </div>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>

