(() => {
  const guestbookEl = document.getElementById('guestbook');
  if (!guestbookEl) return;

  const form = document.getElementById('messageForm');
  const notesEl = document.getElementById('notes');

  const countEl = document.getElementById('count');
  const noteCountEl = document.getElementById('noteCount');
  const statusText = document.getElementById('statusText');
  const submitBtn = document.getElementById('submitBtn');
  const refreshBtn = document.getElementById('refreshBtn');

  const boardEl = document.getElementById('board');
  const topbarEl = document.getElementById('boardTopbar');
  const pagerEl = document.getElementById('pager');

  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const pageNumEl = document.getElementById('pageNum');
  const pageTotalEl = document.getElementById('pageTotal');

  const panelCollapseBtn = document.getElementById('panelCollapseBtn');
  const peekTab = document.getElementById('peekTab');
  const mobileHandle = document.getElementById('mobileHandle');

  const msgInput = document.getElementById('message');
  const nameInput = document.getElementById('name');
  const anonInput = document.getElementById('anonymous');
  const hpInput = document.getElementById('website');

  if (!form || !notesEl || !countEl || !noteCountEl || !statusText || !submitBtn || !refreshBtn ||
      !prevBtn || !nextBtn || !pageNumEl || !pageTotalEl || !panelCollapseBtn || !peekTab ||
      !mobileHandle || !msgInput || !nameInput || !anonInput || !hpInput ||
      !boardEl || !topbarEl || !pagerEl) {
    return;
  }

  const API_GET = 'api/get_messages.php';
  const API_ADD = 'api/add_message.php';

  let page = 1;
  let totalCount = 0;
  let totalPages = 1;

  const mqMobile = window.matchMedia('(max-width: 900px)');

  // -------- NEW: stable limit cache --------
  let cachedLimit = null;
  let cachedLayoutKey = '';
  // ----------------------------------------

  function formatTime(ts) {
    const d = new Date(ts);
    return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
  }

  function randomRotation() {
    return (Math.random() * 7 - 3.5).toFixed(2) + 'deg';
  }

  function createTape(cls) {
    const t = document.createElement('span');
    t.className = `tape ${cls}`;
    return t;
  }

  function polaroidCard(n) {
    const card = document.createElement('article');
    card.className = 'polaroid';
    card.style.setProperty('--rot', n.rot || randomRotation());

    const msg = document.createElement('p');
    msg.className = 'polaroid__message';
    msg.textContent = n.message;

    const footer = document.createElement('div');
    footer.className = 'polaroid__footer';

    const name = document.createElement('span');
    name.className = 'polaroid__name';
    name.textContent = n.anonymous ? 'Anonymous' : (n.name?.trim() || 'Anonymous');

    const time = document.createElement('span');
    time.className = 'polaroid__time';
    time.textContent = formatTime(n.time);

    footer.appendChild(name);
    footer.appendChild(time);

    card.appendChild(createTape('tape--bl'));
    card.appendChild(createTape('tape--br'));
    card.appendChild(msg);
    card.appendChild(footer);

    return card;
  }

  function parsePx(value) {
    const parsed = Number.parseFloat(value);
    return Number.isFinite(parsed) ? parsed : 0;
  }

  function getGridCols() {
    const style = window.getComputedStyle(notesEl);
    const cols = style.gridTemplateColumns.split(' ').filter(Boolean).length;
    return Math.max(1, cols || 1);
  }

  // -------- UPDATED: always measure a temp card (stable), sized like a real column --------
  function getCardHeight() {
    const cols = getGridCols();

    const notesStyle = window.getComputedStyle(notesEl);
    const colGap = parsePx(notesStyle.columnGap || notesStyle.gap);
    const totalGap = colGap * (cols - 1);
    const colWidth = Math.max(140, (notesEl.clientWidth - totalGap) / cols);

    const tempCard = polaroidCard({
      name: '',
      anonymous: true,
      message: 'Sample message\nSample message',
      time: Date.now(),
      rot: '0deg',
    });

    tempCard.style.position = 'absolute';
    tempCard.style.left = '-9999px';
    tempCard.style.top = '0';
    tempCard.style.width = `${colWidth}px`;
    tempCard.style.transform = 'none';
    tempCard.style.visibility = 'hidden';
    tempCard.style.pointerEvents = 'none';

    notesEl.appendChild(tempCard);
    const height = tempCard.getBoundingClientRect().height || 1;
    tempCard.remove();

    return height;
  }
  // -----------------------------------------------------------------------

  function computeLimit() {
    const cols = getGridCols();

    const boardStyle = window.getComputedStyle(boardEl);
    const boardGap = parsePx(boardStyle.rowGap || boardStyle.gap);
    const paddingTop = parsePx(boardStyle.paddingTop);
    const paddingBottom = parsePx(boardStyle.paddingBottom);

    const boardHeight = boardEl.getBoundingClientRect().height;
    const topbarHeight = topbarEl.getBoundingClientRect().height;
    const pagerHeight = pagerEl.getBoundingClientRect().height;

    const availableHeight = Math.max(
      0,
      boardHeight - topbarHeight - pagerHeight - paddingTop - paddingBottom - (boardGap * 2)
    );

    const notesStyle = window.getComputedStyle(notesEl);
    const notesGap = parsePx(notesStyle.rowGap || notesStyle.gap);

    const cardHeight = getCardHeight();
    const rows = Math.max(1, Math.floor(availableHeight / (cardHeight + notesGap)));

    const limit = rows * cols;
    return Math.max(6, Math.min(30, limit));
  }

  // -------- NEW: stable limit per layout --------
  function getLayoutKey() {
    const cols = getGridCols();
    const boardH = Math.round(boardEl.getBoundingClientRect().height);

    const collapsedDesktop = guestbookEl.classList.contains('is-panel-collapsed') ? 1 : 0;
    const collapsedMobile = guestbookEl.classList.contains('is-mobile-form-collapsed') ? 1 : 0;
    const isMobile = mqMobile.matches ? 1 : 0;

    return `${cols}|${boardH}|${collapsedDesktop}|${collapsedMobile}|${isMobile}`;
  }

  function getStableLimit() {
    const key = getLayoutKey();
    if (key !== cachedLayoutKey || !cachedLimit) {
      cachedLayoutKey = key;
      cachedLimit = computeLimit();
    }
    return cachedLimit;
  }

  function invalidateLimit() {
    cachedLimit = null;
    cachedLayoutKey = '';
  }
  // --------------------------------------------

  function setPagingUI() {
    pageNumEl.textContent = String(page);
    pageTotalEl.textContent = String(totalPages);
    noteCountEl.textContent = String(totalCount);

    prevBtn.disabled = page <= 1;
    nextBtn.disabled = page >= totalPages;
  }

  async function loadNotes() {
    statusText.textContent = 'Loading notes…';

    const limit = getStableLimit();

    try {
      const url = new URL(API_GET, window.location.href);
      url.searchParams.set('page', String(page));
      url.searchParams.set('limit', String(limit));

      const res = await fetch(url.toString(), { cache: 'no-store' });
      const data = await res.json();

      if (!data.ok) throw new Error(data.error || 'Failed to load.');

      const notes = Array.isArray(data.notes) ? data.notes : [];
      totalCount = Number(data.total ?? 0) || 0;

      totalPages = Math.max(1, Math.ceil(totalCount / limit));
      if (page > totalPages) page = totalPages;

      notesEl.innerHTML = '';
      notes.forEach((n) => notesEl.appendChild(polaroidCard(n)));

      setPagingUI();
      statusText.textContent = '';
    } catch {
      statusText.textContent = 'Could not load notes. Check server + file permissions.';
    }
  }

  async function addNote(payload) {
    submitBtn.disabled = true;
    submitBtn.style.opacity = '0.7';
    statusText.textContent = 'Posting…';

    try {
      const res = await fetch(API_ADD, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      const data = await res.json();
      if (!data.ok) throw new Error(data.error || 'Submit failed.');

      page = 1;
      await loadNotes();

      statusText.textContent = 'Posted! 💛';
      setTimeout(() => (statusText.textContent = ''), 1600);
    } catch {
      statusText.textContent = 'Could not submit. Please try again.';
    } finally {
      submitBtn.disabled = false;
      submitBtn.style.opacity = '';
    }
  }

  msgInput.addEventListener('input', () => {
    countEl.textContent = String(msgInput.value.length);
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const payload = {
      name: nameInput.value.trim(),
      anonymous: anonInput.checked,
      message: msgInput.value.trim(),
      website: hpInput.value.trim(),
    };

    if (!payload.message) return;

    await addNote(payload);

    msgInput.value = '';
    nameInput.value = '';
    anonInput.checked = false;
    hpInput.value = '';
    countEl.textContent = '0';
  });

  prevBtn.addEventListener('click', async () => {
    if (page <= 1) return;
    page -= 1;
    await loadNotes();
  });

  nextBtn.addEventListener('click', async () => {
    if (page >= totalPages) return;
    page += 1;
    await loadNotes();
  });

  refreshBtn.addEventListener('click', async () => {
    page = 1;
    await loadNotes();
  });

  function toggleDesktopPanel(forceExpand = false) {
    const currentlyCollapsed = guestbookEl.classList.contains('is-panel-collapsed');
    const nextCollapsed = forceExpand ? false : !currentlyCollapsed;

    guestbookEl.classList.toggle('is-panel-collapsed', nextCollapsed);

    panelCollapseBtn.textContent = nextCollapsed ? 'Expand' : 'Collapse';
    panelCollapseBtn.setAttribute('aria-expanded', nextCollapsed ? 'false' : 'true');

    page = 1;
    invalidateLimit();
    requestAnimationFrame(() => requestAnimationFrame(loadNotes));
  }

  panelCollapseBtn.addEventListener('click', () => toggleDesktopPanel(false));
  peekTab.addEventListener('click', () => toggleDesktopPanel(true));

  mobileHandle.addEventListener('click', () => {
    const collapsed = guestbookEl.classList.toggle('is-mobile-form-collapsed');
    mobileHandle.textContent = collapsed ? 'Show form' : 'Hide form';
    mobileHandle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

    page = 1;
    invalidateLimit();
    requestAnimationFrame(() => requestAnimationFrame(loadNotes));
  });

  mqMobile.addEventListener('change', () => {
    page = 1;
    invalidateLimit();
    requestAnimationFrame(() => requestAnimationFrame(loadNotes));
  });

  let resizeTimer = null;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      page = 1;
      invalidateLimit();
      loadNotes();
    }, 120);
  });

  countEl.textContent = '0';
  requestAnimationFrame(() => requestAnimationFrame(loadNotes));
})();
