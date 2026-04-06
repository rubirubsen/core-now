/* ── AI Tabs ── */
document.querySelectorAll('.ai-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    const idx = tab.dataset.tab;
    document.querySelectorAll('.ai-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.ai-panel').forEach(p => p.classList.remove('active'));
    tab.classList.add('active');
    document.querySelector(`.ai-panel[data-panel="${idx}"]`).classList.add('active');
  });
});
