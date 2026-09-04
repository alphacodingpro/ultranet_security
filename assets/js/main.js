/* =============================================
   UltraNet Security – Main JavaScript
   ============================================= */

document.addEventListener('DOMContentLoaded', function () {

  /* ── SCROLL REVEAL ── */
  const revealEls = document.querySelectorAll('.reveal');
  if (revealEls.length) {
    const revealObs = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1 });
    revealEls.forEach(el => revealObs.observe(el));
  }

  /* ── COUNTER ANIMATION ── */
  function animateCounter(el) {
    const target    = parseInt(el.getAttribute('data-target'));
    const em        = el.querySelector('em') ? el.querySelector('em').outerHTML : '';
    let   current   = 0;
    const increment = Math.ceil(target / 60);
    const timer = setInterval(() => {
      current += increment;
      if (current >= target) { current = target; clearInterval(timer); }
      el.innerHTML = current.toLocaleString() + em;
    }, 28);
  }
  const counterEls = document.querySelectorAll('.counter-num[data-target]');
  if (counterEls.length) {
    const cObs = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) { animateCounter(e.target); cObs.unobserve(e.target); }
      });
    }, { threshold: 0.5 });
    counterEls.forEach(el => cObs.observe(el));
  }

  /* ── ACTIVE NAV LINK ON SCROLL ── */
  const sections = document.querySelectorAll('section[id]');
  if (sections.length) {
    window.addEventListener('scroll', () => {
      let current = '';
      sections.forEach(s => { if (window.scrollY >= s.offsetTop - 90) current = s.id; });
      document.querySelectorAll('.nav-link').forEach(a => {
        a.classList.remove('active');
        if (a.getAttribute('href') === '#' + current) a.classList.add('active');
      });
    }, { passive: true });
  }

  /* ── SMOOTH SCROLL ── */
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const href = a.getAttribute('href');
      if (href === '#') return;
      const target = document.querySelector(href);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth' });
        // Close mobile nav
        const navCollapse = document.querySelector('.navbar-collapse');
        if (navCollapse && navCollapse.classList.contains('show')) {
          navCollapse.classList.remove('show');
        }
      }
    });
  });

  /* ── CONTACT FORM (real AJAX submission + email notification) ── */
  const form = document.getElementById('contactForm');
  if (form) {
    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      const btn = document.getElementById('contactSubmitBtn');
      const successMsg = document.getElementById('successMsg');
      const errorMsg = document.getElementById('errorMsg');
      if (errorMsg) { errorMsg.style.display = 'none'; }
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
      }

      try {
        const formData = new FormData(form);
        const res = await fetch(form.getAttribute('data-action') || (window.SITE_URL_BASE || '') + '/contact-submit.php', {
          method: 'POST',
          body: formData,
        });
        const data = await res.json();

        if (data.ok) {
          form.style.display = 'none';
          if (successMsg) successMsg.style.display = 'block';
        } else {
          if (errorMsg) {
            errorMsg.textContent = data.message || 'Something went wrong. Please try again or WhatsApp us directly.';
            errorMsg.style.display = 'block';
          }
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Enquiry';
          }
        }
      } catch (err) {
        if (errorMsg) {
          errorMsg.textContent = 'Network error. Please check your connection and try again, or WhatsApp us directly.';
          errorMsg.style.display = 'block';
        }
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Enquiry';
        }
      }
    });
  }

  /* ── NAVBAR SHADOW ON SCROLL ── */
  const navbar = document.querySelector('.navbar');
  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.style.boxShadow = window.scrollY > 50
        ? '0 4px 28px rgba(10,22,40,0.15)'
        : '0 2px 20px rgba(10,22,40,0.08)';
    }, { passive: true });
  }

});
