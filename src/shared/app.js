// ── Navbar scroll effect ──
(function () {
  const header = document.getElementById('main-header');
  if (!header) return;
  const onScroll = () => {
    header.classList.toggle('scrolled', window.scrollY > 8);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();

// ── Mobile menu toggle ──
function toggleMenu() {
  const nav = document.getElementById('main-nav');
  if (!nav) return;
  nav.classList.toggle('open');
}

// ── Profile dropdown ──
function toggleProfileMenu(event) {
  event.stopPropagation();
  const menu = document.getElementById('nav-profile-menu');
  const btn  = event.currentTarget;
  if (!menu) return;
  const isOpen = menu.classList.contains('open');
  menu.classList.toggle('open', !isOpen);
  btn.setAttribute('aria-expanded', String(!isOpen));
}

// Close dropdown when clicking outside
document.addEventListener('click', function (e) {
  const menu = document.getElementById('nav-profile-menu');
  const btn  = document.querySelector('.nav-profile-btn');
  if (!menu) return;
  if (!menu.contains(e.target) && btn && !btn.contains(e.target)) {
    menu.classList.remove('open');
    if (btn) btn.setAttribute('aria-expanded', 'false');
  }
});

// ── Theme Toggle ──
(function () {
  const html = document.documentElement;
  const savedTheme = localStorage.getItem('theme') || 'light';
  if (savedTheme === 'dark') html.classList.add('dark-mode');
  
  window.toggleTheme = function() {
    html.classList.toggle('dark-mode');
    const isDark = html.classList.contains('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    updateThemeIcons();
  };
  
  function updateThemeIcons() {
    const isDark = html.classList.contains('dark-mode');
    const lightIcon = document.querySelector('.theme-icon-light');
    const darkIcon = document.querySelector('.theme-icon-dark');
    const label = document.getElementById('theme-label');
    if (lightIcon) lightIcon.style.display = isDark ? 'none' : 'block';
    if (darkIcon) darkIcon.style.display = isDark ? 'block' : 'none';
    if (label) label.textContent = isDark ? 'Tema chiaro' : 'Tema scuro';
  }
  updateThemeIcons();
})();

// ── Toast Notification System ──
window.showToast = function(msg, type = 'info') {
  const colors = { 
    info: 'var(--brand)', 
    error: 'var(--error)', 
    warning: 'var(--warning)', 
    success: 'var(--success)' 
  };
  const t = document.createElement('div');
  t.style.cssText = `
    position:fixed; bottom:1.5rem; right:1.5rem; z-index:9999; 
    background:var(--white); border:1.5px solid ${colors[type]}; 
    color:${colors[type]}; padding:.85rem 1.25rem; border-radius:12px; 
    font-size:.9rem; font-weight:600; box-shadow:var(--shadow-lg); 
    animation:slideDown .25s ease; font-family:'Inter',sans-serif; 
    max-width:320px; display:flex; align-items:center; gap:.6rem;
    transition: opacity .3s, transform .3s;
  `;
  
  const icon = type === 'success' ? '✓' : (type === 'error' ? '✕' : 'ℹ');
  t.innerHTML = `<span style="font-size:1.1rem">${icon}</span> <span>${msg}</span>`;
  
  document.body.appendChild(t);
  setTimeout(() => { 
    t.style.opacity = '0'; 
    t.style.transform = 'translateY(10px)';
    setTimeout(() => t.remove(), 300); 
  }, 4000);
};

// Auto-show toasts from URL params
(function() {
  const params = new URLSearchParams(window.location.search);
  if (params.has('success_msg')) showToast(params.get('success_msg'), 'success');
  if (params.has('error_msg')) showToast(params.get('error_msg'), 'error');
})();

// ── Animate elements on scroll (Intersection Observer) ──
(function () {
  const items = document.querySelectorAll('[data-animate]');
  if (!items.length) return;
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate-fade-up');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });
  items.forEach(el => observer.observe(el));
})();

// ── Password Visibility Toggle ──
function togglePasswordVisibility(fieldId, event) {
  const field = document.getElementById(fieldId);
  const btn = event.currentTarget;
  if (!field || !btn) return;
  const isPassword = field.type === 'password';
  field.type = isPassword ? 'text' : 'password';
  btn.innerHTML = isPassword ? '<span class="eye-icon">👁️‍🗨️</span>' : '<span class="eye-icon">👁️</span>';
  btn.style.color = isPassword ? 'var(--brand)' : 'var(--muted)';
}
