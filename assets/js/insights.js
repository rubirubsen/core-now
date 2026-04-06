/* insights.js — Frontend-Sektion #insights */
(function () {
  const section = document.getElementById('insights');
  if (!section) return;

  const grid = section.querySelector('.insights-grid');
  if (!grid) return;

  function fmt(dateStr) {
    const d = new Date(dateStr);
    const lang = window.currentLang || 'de';
    return d.toLocaleDateString(lang === 'de' ? 'de-DE' : 'en-GB', {
      day: 'numeric', month: 'long', year: 'numeric'
    });
  }

  function teaser(text, len) {
    const t = text.replace(/<[^>]*>/g, '');
    return t.length > len ? t.substring(0, len) + '…' : t;
  }

  function currentLang() {
    return window.currentLang || document.documentElement.lang || 'de';
  }

  function renderCard(post, lang) {
    lang = lang || currentLang();
    const title = (lang !== 'de' && post.title_en) ? post.title_en : post.title_de;
    const body  = (lang !== 'de' && post.body_en)  ? post.body_en  : post.body_de;
    const date  = fmt(post.published_at);
    const short = teaser(body, 160);

    return `<a href="/insights/post.php?slug=${encodeURIComponent(post.slug)}" class="insight-card">
      <div class="insight-meta">
        ${post.category ? `<span class="insight-tag">${post.category}</span>` : ''}
        <span class="insight-date">${date}</span>
      </div>
      <h3 class="insight-card-title">${title}</h3>
      <p class="insight-card-teaser">${short}</p>
      <span class="insight-card-arrow">Weiterlesen →</span>
    </a>`;
  }

  let cachedPosts = null;

  function render(lang) {
    if (!cachedPosts) return;
    grid.innerHTML = cachedPosts.map(p => renderCard(p, lang)).join('');
  }

  // Sprachwechsel abfangen und neu rendern
  const _orig = window.setLang;
  if (typeof _orig === 'function') {
    window.setLang = function(lang) {
      _orig(lang);
      render(lang);
    };
  }

  let loaded = false;

  const observer = new IntersectionObserver((entries) => {
    if (!entries[0].isIntersecting || loaded) return;
    loaded = true;
    observer.disconnect();

    fetch('/api/posts.php?limit=3')
      .then(r => r.ok ? r.json() : Promise.reject())
      .then(posts => {
        if (!posts || !posts.length) return;
        cachedPosts = posts;
        render();
      })
      .catch(() => {});
  }, { threshold: 0.1 });

  observer.observe(section);
})();
