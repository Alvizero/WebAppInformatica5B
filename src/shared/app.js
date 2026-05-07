// ── Navbar scroll effect ──
(function () {
  const h = document.getElementById('main-header');
  if (!h) return;
  const update = () => h.classList.toggle('scrolled', window.scrollY > 8);
  window.addEventListener('scroll', update, { passive: true });
  update();
})();

// ── Mobile menu toggle ──
function toggleMenu() {
  document.getElementById('main-nav')?.classList.toggle('open');
}

// ── Profile dropdown ──
function toggleProfileMenu(e) {
  e.stopPropagation();
  const menu = document.getElementById('nav-profile-menu');
  if (!menu) return;
  const open = menu.classList.toggle('open');
  e.currentTarget.setAttribute('aria-expanded', open);
}

document.addEventListener('click', function (e) {
  const menu = document.getElementById('nav-profile-menu');
  const btn  = document.querySelector('.nav-profile-btn');
  if (!menu || menu.contains(e.target) || btn?.contains(e.target)) return;
  menu.classList.remove('open');
  btn?.setAttribute('aria-expanded', 'false');
});

// ── Theme Toggle ──
(function () {
  const html = document.documentElement;
  if (localStorage.getItem('theme') === 'dark') html.classList.add('dark-mode');

  function syncIcons() {
    const dark = html.classList.contains('dark-mode');
    document.querySelectorAll('.theme-icon-light').forEach(el => el.style.display = dark ? 'none' : 'block');
    document.querySelectorAll('.theme-icon-dark').forEach(el => el.style.display = dark ? 'block' : 'none');
    const lbl = document.getElementById('theme-label');
    if (lbl) lbl.textContent = dark ? 'Tema chiaro' : 'Tema scuro';
  }

  window.toggleTheme = function () {
    const dark = html.classList.toggle('dark-mode');
    localStorage.setItem('theme', dark ? 'dark' : 'light');
    syncIcons();
  };

  document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', syncIcons)
    : syncIcons();
})();

// ── Toast ──
window.showToast = function (msg, type = 'info', duration = 4000) {
  const color = { info: 'var(--brand)', error: 'var(--error)', warning: 'var(--warning)', success: 'var(--success)' }[type];
  const icon  = { success: '✓', error: '✕', warning: '⚠', info: 'ℹ' }[type];
  const t = Object.assign(document.createElement('div'), {
    innerHTML: `<span style="font-size:1.1rem">${icon}</span><span>${msg}</span>`,
  });
  t.style.cssText = `position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;background:var(--white);border:1.5px solid ${color};color:${color};padding:.85rem 1.25rem;border-radius:12px;font-size:.9rem;font-weight:600;box-shadow:var(--shadow-lg);animation:slideDown .25s ease;font-family:'Inter',sans-serif;max-width:320px;display:flex;align-items:center;gap:.6rem;transition:opacity .3s,transform .3s`;
  document.body.appendChild(t);
  setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateY(10px)'; setTimeout(() => t.remove(), 300); }, duration);
};

// Auto-show toasts from URL params
(function () {
  const p = new URLSearchParams(location.search);
  if (p.has('success_msg')) showToast(p.get('success_msg'), 'success');
  if (p.has('error_msg'))   showToast(p.get('error_msg'),   'error');
})();

// ── Scroll-animate elements ──
(function () {
  const items = document.querySelectorAll('[data-animate]');
  if (!items.length) return;
  const io = new IntersectionObserver((entries) => {
    entries.forEach(en => { if (en.isIntersecting) { en.target.classList.add('animate-fade-up'); io.unobserve(en.target); } });
  }, { threshold: 0.1 });
  items.forEach(el => io.observe(el));
})();

// ── Password Visibility Toggle ──
function togglePasswordVisibility(fieldId, e) {
  const field = document.getElementById(fieldId);
  if (!field) return;
  const show = field.type === 'password';
  field.type = show ? 'text' : 'password';
  e.currentTarget.innerHTML = `<span class="eye-icon">${show ? '👁️‍🗨️' : '👁️'}</span>`;
  e.currentTarget.style.color = show ? 'var(--brand)' : 'var(--muted)';
}
