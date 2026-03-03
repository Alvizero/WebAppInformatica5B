function toggleMenu() {
  const nav = document.getElementById('main-nav');
  if (nav) nav.classList.toggle('open');
}

function toggleProfileMenu(event) {
  event.stopPropagation();
  const menu = document.getElementById('nav-profile-menu');
  if (!menu) return;
  menu.classList.toggle('open');
}

// Chiudi dropdown se clicchi fuori
document.addEventListener('click', function (e) {
  const menu = document.getElementById('nav-profile-menu');
  const btn  = document.querySelector('.nav-profile-btn');
  if (!menu || !btn) return;

  const clickInsideMenu = menu.contains(e.target);
  const clickOnButton   = btn.contains(e.target);

  if (!clickInsideMenu && !clickOnButton) {
    menu.classList.remove('open');
  }
});
