/* ── Reveal on scroll ── */
document.documentElement.classList.add('js-loaded');

const obs = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (!e.isIntersecting) return;
    const siblings = [...e.target.parentElement.children].filter(c => c.classList.contains('reveal'));
    e.target.style.transitionDelay = (siblings.indexOf(e.target) * 65) + 'ms';
    e.target.classList.add('visible');
    obs.unobserve(e.target);
  });
}, {threshold:0.1, rootMargin:'0px 0px -28px 0px'});

document.querySelectorAll('.reveal').forEach(el => obs.observe(el));

/* ── Smooth scroll ── */
document.querySelectorAll('a[href^="#"]').forEach(a => a.addEventListener('click', e => {
  const t = document.querySelector(a.getAttribute('href'));
  if (t) { e.preventDefault(); t.scrollIntoView({behavior:'smooth'}); }
}));
