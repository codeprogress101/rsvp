const guestbookEl = document.getElementById("guestbook");

const form = document.getElementById("messageForm");
const notesEl = document.getElementById("notes");

const boardEl = document.getElementById("board");
const topbarEl = document.getElementById("boardTopbar");
const pagerEl = document.getElementById("pager");

const countEl = document.getElementById("count");
const noteCountEl = document.getElementById("noteCount");
const statusText = document.getElementById("statusText");
const submitBtn = document.getElementById("submitBtn");
const refreshBtn = document.getElementById("refreshBtn");

const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");
const pageNumEl = document.getElementById("pageNum");
const pageTotalEl = document.getElementById("pageTotal");

const panelCollapseBtn = document.getElementById("panelCollapseBtn");
const peekTab = document.getElementById("peekTab");
const mobileHandle = document.getElementById("mobileHandle");

const msgInput = document.getElementById("message");
const nameInput = document.getElementById("name");
const anonInput = document.getElementById("anonymous");
const hpInput = document.getElementById("website");

const API_GET = "api/get_messages.php";
const API_ADD = "api/add_message.php";

let page = 1;
let totalCount = 0;
let totalPages = 1;

const mqMobile = window.matchMedia("(max-width: 900px)");

/* ---------- utilities ---------- */

function formatTime(ts) {
  const d = new Date(ts);
  return d.toLocaleDateString(undefined, { month: "short", day: "numeric" });
}

function randomRotation() {
  return (Math.random() * 7 - 3.5).toFixed(2) + "deg";
}

function createTape(cls) {
  const t = document.createElement("span");
  t.className = `tape ${cls}`;
  return t;
}

function polaroidCard(n) {
  const card = document.createElement("article");
  card.className = "polaroid";
  card.style.setProperty("--rot", n.rot || randomRotation());

  const msg = document.createElement("p");
  msg.className = "polaroid__message";
  msg.textContent = n.message;

  const footer = document.createElement("div");
  footer.className = "polaroid__footer";

  const name = document.createElement("span");
  name.className = "polaroid__name";
  name.textContent = n.anonymous ? "Anonymous" : (n.name?.trim() || "Anonymous");

  const time = document.createElement("span");
  time.className = "polaroid__time";
  time.textContent = formatTime(n.time);

  footer.appendChild(name);
  footer.appendChild(time);

  // bottom tapes (top tapes are ::before/::after in CSS)
  card.appendChild(createTape("tape--bl"));
  card.appendChild(createTape("tape--br"));

  card.appendChild(msg);
  card.appendChild(footer);

  return card;
}

/**
 * Read current computed grid columns from .notes
 * Works with "repeat(3, ...)" or "1fr 1fr 1fr"
 */
function getGridCols() {
  const style = window.getComputedStyle(notesEl);
  const cols = style.gridTemplateColumns.split(" ").filter(Boolean).length;
  return Math.max(1, cols || 1);
}

/**
 * Estimate how many cards can fit in the available notes area
 * then return a limit that fills the board.
 */
function computeLimit() {
  const cols = getGridCols();

  // Available height inside the notes area (board grid row 2)
  const notesH = notesEl.getBoundingClientRect().height;

  // Estimate card height by breakpoint
  // (keep this in sync with your CSS card padding/text size)
  let estCardH = 150; // desktop default
  if (mqMobile.matches) estCardH = 140;

  // If form is collapsed on mobile, board grows -> can show more rows naturally
  // We'll still compute from real height so it adapts.
  const rows = Math.max(1, Math.floor(notesH / estCardH));

  // Fill slightly more than exact to avoid empty gaps due to rounding
  const fill = cols * rows;
  const boosted = fill + cols; // add one extra row worth of items

  // Keep sane bounds (avoid huge reads)
  return Math.max(6, Math.min(40, boosted));
}

function setPagingUI() {
  pageNumEl.textContent = String(page);
  pageTotalEl.textContent = String(totalPages);
  noteCountEl.textContent = String(totalCount);

  prevBtn.disabled = page <= 1;
  nextBtn.disabled = page >= totalPages;
}

/* ---------- data ---------- */

async function loadNotes() {
  statusText.textContent = "Loading notes…";

  const limit = computeLimit();

  try {
    const url = new URL(API_GET, window.location.href);
    url.searchParams.set("page", String(page));
    url.searchParams.set("limit", String(limit));

    const res = await fetch(url.toString(), { cache: "no-store" });
    const data = await res.json();

    if (!data.ok) throw new Error(data.error || "Failed to load.");

    const notes = Array.isArray(data.notes) ? data.notes : [];
    totalCount = Number(data.total ?? 0) || 0;

    totalPages = Math.max(1, Math.ceil(totalCount / limit));
    if (page > totalPages) page = totalPages;

    notesEl.innerHTML = "";
    notes.forEach((n) => notesEl.appendChild(polaroidCard(n)));

    setPagingUI();
    statusText.textContent = "";
  } catch {
    statusText.textContent = "Could not load notes. Check server + file permissions.";
  }
}

async function addNote(payload) {
  submitBtn.disabled = true;
  submitBtn.style.opacity = "0.7";
  statusText.textContent = "Posting…";

  try {
    const res = await fetch(API_ADD, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    const data = await res.json();
    if (!data.ok) throw new Error(data.error || "Submit failed.");

    page = 1;
    await loadNotes();

    statusText.textContent = "Posted! 💛";
    setTimeout(() => (statusText.textContent = ""), 1600);
  } catch {
    statusText.textContent = "Could not submit. Please try again.";
  } finally {
    submitBtn.disabled = false;
    submitBtn.style.opacity = "";
  }
}

/* ---------- events ---------- */

msgInput.addEventListener("input", () => {
  countEl.textContent = String(msgInput.value.length);
});

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const payload = {
    name: nameInput.value.trim(),
    anonymous: anonInput.checked,
    message: msgInput.value.trim(),
    website: hpInput.value.trim(),
  };

  if (!payload.message) return;

  await addNote(payload);

  msgInput.value = "";
  nameInput.value = "";
  anonInput.checked = false;
  hpInput.value = "";
  countEl.textContent = "0";
});

prevBtn.addEventListener("click", async () => {
  if (page <= 1) return;
  page -= 1;
  await loadNotes();
});

nextBtn.addEventListener("click", async () => {
  if (page >= totalPages) return;
  page += 1;
  await loadNotes();
});

refreshBtn.addEventListener("click", async () => {
  page = 1;
  await loadNotes();
});

/* Desktop collapse */
function toggleDesktopPanel(forceExpand = false) {
  const currentlyCollapsed = guestbookEl.classList.contains("is-panel-collapsed");
  const nextCollapsed = forceExpand ? false : !currentlyCollapsed;

  guestbookEl.classList.toggle("is-panel-collapsed", nextCollapsed);

  if (panelCollapseBtn) {
    panelCollapseBtn.textContent = nextCollapsed ? "Expand" : "Collapse";
    panelCollapseBtn.setAttribute("aria-expanded", nextCollapsed ? "false" : "true");
  }

  // After layout changes, reload with recomputed limit
  page = 1;
  // wait a tick so CSS layout settles
  requestAnimationFrame(() => requestAnimationFrame(loadNotes));
}

if (panelCollapseBtn) panelCollapseBtn.addEventListener("click", () => toggleDesktopPanel(false));
if (peekTab) peekTab.addEventListener("click", () => toggleDesktopPanel(true));

/* Mobile collapse (down) */
if (mobileHandle) {
  mobileHandle.addEventListener("click", () => {
    const collapsed = guestbookEl.classList.toggle("is-mobile-form-collapsed");
    mobileHandle.textContent = collapsed ? "Show form" : "Hide form";
    mobileHandle.setAttribute("aria-expanded", collapsed ? "false" : "true");

    page = 1;
    requestAnimationFrame(() => requestAnimationFrame(loadNotes));
  });
}

/* Recompute on breakpoint changes */
mqMobile.addEventListener("change", () => {
  page = 1;
  requestAnimationFrame(() => requestAnimationFrame(loadNotes));
});

/* Recompute on resize (throttled) */
let resizeTimer = null;
window.addEventListener("resize", () => {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(() => {
    page = 1;
    loadNotes();
  }, 120);
});

/* Init */
countEl.textContent = "0";
// ensure layout has computed heights before first computeLimit()
requestAnimationFrame(() => requestAnimationFrame(loadNotes));
