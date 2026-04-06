/* ── Contact form: AJAX to mail.php ── */
document.getElementById('ctForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const btn = this.querySelector('.f-submit');
  btn.disabled = true;
  btn.textContent = '…';
  fetch('mail.php', {method:'POST', body: new FormData(this)})
    .then(r => r.ok ? r.text() : Promise.reject())
    .then(() => {
      const ok = document.getElementById('fOk');
      ok.textContent = T[currentLang]['f-success'];
      ok.style.display = 'block';
      btn.style.display = 'none';
    })
    .catch(() => { btn.disabled = false; btn.textContent = T[currentLang]['f-submit']; });
});
