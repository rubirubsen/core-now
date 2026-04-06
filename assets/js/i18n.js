/* ── i18n / setLang ── */
let currentLang = 'de';

function setLang(lang) {
  if (!T[lang]) return;
  currentLang = lang;
  document.documentElement.lang = lang;
  document.getElementById('langField').value = lang;

  // Buttons
  document.querySelectorAll('.lbtn,.mob-lang button').forEach(b => {
    b.classList.toggle('active', b.getAttribute('onclick').includes(`'${lang}'`));
  });

  // Text
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const v = T[lang][el.dataset.i18n];
    if (v !== undefined) el.textContent = v;
  });
  // HTML
  document.querySelectorAll('[data-i18n-html]').forEach(el => {
    const v = T[lang][el.dataset.i18nHtml];
    if (v !== undefined) el.innerHTML = v;
  });
  // Placeholders
  document.querySelectorAll('[data-i18n-ph]').forEach(el => {
    const v = T[lang][el.dataset.i18nPh];
    if (v !== undefined) el.placeholder = v;
  });
  // Select options
  const sel = document.getElementById('serviceSelect');
  if (sel) {
    const keys = ['f-service-ph','opt-cms','opt-web','opt-app','opt-ai','opt-local','opt-full','opt-consulting'];
    Array.from(sel.options).forEach((o, i) => { if (T[lang][keys[i]]) o.text = T[lang][keys[i]]; });
  }
  try { localStorage.setItem('cnLang', lang); } catch(e) {}
}

/* ── Auto-detect ── */
(function() {
  let lang = 'de';
  try { lang = localStorage.getItem('cnLang') || lang; } catch(e){}
  if (!T[lang]) {
    const b = navigator.language.slice(0,2);
    lang = T[b] ? b : 'de';
  }
  setLang(lang);
})();
