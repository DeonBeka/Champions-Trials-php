// public/js/app.js
document.addEventListener('DOMContentLoaded', function(){
  // Mobile nav toggle
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.querySelector('.nav');
  if (toggle && nav) {
    toggle.addEventListener('click', function(){
      nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
    });
  }

  // Confirm logout
  var logout = document.querySelector('a[href="logout.php"]');
  if (logout) {
    logout.addEventListener('click', function(e){
      if (!confirm('Are you sure you want to log out?')) e.preventDefault();
    });
  }

  // Smooth reveal on scroll (light)
  var cards = document.querySelectorAll('.info-card, .place, .hero-stats li');
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(en){
        if (en.isIntersecting) {
          en.target.classList.add('enter');
          io.unobserve(en.target);
        }
      });
    }, {threshold: 0.12});
    cards.forEach(c => io.observe(c));
  } else {
    // fallback: just show
    cards.forEach(c => c.classList.add('enter'));
  }
});

