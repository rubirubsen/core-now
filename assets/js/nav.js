/* ── Nav theme ── */
const nav = document.getElementById('nav');

function refreshNav() {
  nav.classList.toggle('scrolled', window.scrollY > 40);
  let theme = 'dark';
  const heroBot = document.getElementById('hero').getBoundingClientRect().bottom;
  if (heroBot > 40) {
    theme = 'dark';
  } else {
    document.querySelectorAll('[data-theme]').forEach(s => {
      const r = s.getBoundingClientRect();
      if (r.top <= 40 && r.bottom > 40) theme = s.dataset.theme;
    });
  }
  nav.className = nav.className.replace(/on-\w+/g,'').trim();
  nav.classList.add('on-' + theme);
  if (window.scrollY > 40) nav.classList.add('scrolled');
}

window.addEventListener('scroll', refreshNav, {passive:true});
refreshNav();

/* ── Mobile menu ── */
document.getElementById('hamBtn').addEventListener('click', () => document.getElementById('mobMenu').classList.add('open'));
document.getElementById('mobClose').addEventListener('click', () => document.getElementById('mobMenu').classList.remove('open'));
document.querySelectorAll('.ml').forEach(a => a.addEventListener('click', () => document.getElementById('mobMenu').classList.remove('open')));
