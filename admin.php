<?php
// admin.php
require_once __DIR__ . "/db.php";
session_start();

/* =========================
   SIMPLE PASSWORD PROTECTION
   Change this password!
========================= */
$ADMIN_PASSWORD = "keem_kate";
$ADMIN_API_KEY = "superadmin123";

/* ===== Handle logout ===== */
if (isset($_GET["logout"])) {
  session_destroy();
  header("Location: admin.php");
  exit;
}

/* ===== Handle login ===== */
if (isset($_POST["admin_password"])) {
  if (hash_equals($ADMIN_PASSWORD, (string)$_POST["admin_password"])) {
    $_SESSION["is_admin"] = true;
    header("Location: admin.php");
    exit;
  } else {
    $login_error = "Wrong password.";
  }
}

$is_admin = !empty($_SESSION["is_admin"]);
if (!$is_admin) :
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Login</title>
  <style>
    :root{
      --hero-desktop: url("assets/hero.webp");
      --hero-mobile:  url("assets/hero-mobile.webp");
    }

    *{ box-sizing: border-box; }

    body{
      margin:0;
      min-height:100vh;
      display:grid;
      place-items:center;
      padding:24px;

      font-family: Arial, Helvetica, sans-serif;
      color:#fff;

      background-image: var(--hero-desktop);
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;

      position: relative;
      overflow: hidden;
    }

    body::before{
      content:"";
      position:absolute;
      inset:0;
      background: rgba(0,0,0,0.58);
      z-index:0;
    }

    body::after{
      content:"";
      position:absolute;
      inset:-40px;
      background: radial-gradient(circle at center, transparent 35%, rgba(0,0,0,0.55) 100%);
      z-index:0;
      pointer-events:none;
    }

    .card{
      position: relative;
      z-index: 1;

      width:min(420px,100%);
      background: rgba(255,255,255,0.92);
      color:#222;

      border: 1px solid rgba(255,255,255,0.18);
      border-radius: 18px;
      padding: 22px 20px;

      box-shadow: 0 24px 60px rgba(0,0,0,0.38);
      text-align: center;
      backdrop-filter: blur(2px);
    }

    h1{
      margin:0 0 8px;
      font-family: Georgia, "Times New Roman", Times, serif;
      font-size: 22px;
      letter-spacing: .10em;
      text-transform: uppercase;
      color:#2f2f2a;
    }

    p{
      margin:0 0 16px;
      opacity:.8;
      font-size:14px;
    }

    input{
      width:100%;
      padding:14px 14px;
      border-radius:14px;
      border:1px solid rgba(0,0,0,.16);
      background:#fff;
      color:#111;
      outline:none;
      font-size:15px;
    }

    input:focus{
      border-color: rgba(63,63,53,0.45);
      box-shadow: 0 0 0 4px rgba(63,63,53,0.12);
    }

    button{
      margin-top:12px;
      width:100%;
      padding:14px 18px;
      border-radius:999px;
      border:0;

      background:#3f3f35;
      color:#fff;

      font-weight:700;
      letter-spacing:.10em;
      text-transform:uppercase;
      cursor:pointer;

      box-shadow: 0 10px 26px rgba(0,0,0,0.22);
      transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
    }

    button:hover{
      transform: translateY(-1px);
      box-shadow: 0 14px 32px rgba(0,0,0,0.30);
    }

    button:active{
      transform: translateY(0);
      opacity: .95;
    }

    .err{
      margin-top:12px;
      background: rgba(220,80,80,.12);
      border: 1px solid rgba(220,80,80,.25);
      padding: 10px 12px;
      border-radius: 12px;
      color: rgba(120,20,20,.95);
      font-size: 14px;
    }

    @media (max-width: 768px){
      body{
        background-image: var(--hero-mobile);
        padding: 16px;
      }
      .card{
        padding: 20px 16px;
      }
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>Keem & Kate</h1>
    <p>See the full list of attendees.</p>

    <form method="POST" action="admin.php">
      <input type="password" name="admin_password" placeholder="Admin password" required autofocus />
      <button type="submit">Login</button>
      <?php if (!empty($login_error)): ?>
        <div class="err"><?php echo htmlspecialchars($login_error); ?></div>
      <?php endif; ?>
    </form>
  </div>
</body>
</html>
<?php
exit;
endif;

/* ===== Filters/Search ===== */
$search = trim($_GET["search"] ?? "");
$attendance = strtoupper(trim($_GET["attendance"] ?? "")); // YES/NO/ALL
if (!in_array($attendance, ["YES","NO","ALL",""], true)) $attendance = "";

$where = [];
$params = [];
$types = "";

if ($search !== "") {
  $where[] = "guest_name LIKE ?";
  $params[] = "%" . $search . "%";
  $types .= "s";
}

if ($attendance === "YES" || $attendance === "NO") {
  $where[] = "attendance = ?";
  $params[] = $attendance;
  $types .= "s";
}

$where_sql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

/* ===== Fetch data ===== */
$sql = "SELECT id, guest_name, attendance, ip_address, created_at
        FROM wedding_rsvp
        $where_sql
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($stmt && !empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

/* ===== Counts ===== */
$count_yes = 0; $count_no = 0;
foreach ($rows as $r) {
  if ($r["attendance"] === "YES") $count_yes++;
  if ($r["attendance"] === "NO") $count_no++;
}

$qs = $_GET;
unset($qs["logout"]);
$query_string = http_build_query($qs);
$csv_link = "export_csv.php" . ($query_string ? ("?" . $query_string) : "");

/* ===== Guestbook Messages ===== */
$messages_path = __DIR__ . "/data/messages.json";
$guestbook_messages = [];
if (file_exists($messages_path)) {
  $raw_messages = file_get_contents($messages_path);
  $decoded_messages = json_decode($raw_messages, true);
  if (is_array($decoded_messages)) $guestbook_messages = $decoded_messages;
}
usort($guestbook_messages, function($a, $b){
  return ($b["time"] ?? 0) <=> ($a["time"] ?? 0);
});
$guestbook_total = count($guestbook_messages);

function truncate_message($message, $limit = 120) {
  $message = (string)$message;
  if (mb_strlen($message) <= $limit) return $message;
  return rtrim(mb_substr($message, 0, $limit - 1)) . "…";
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Keem & Kate Guest List</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f7f4ef;color:#222;margin:0}
    .wrap{width:min(1100px,100%);margin:0 auto;padding:24px 18px 60px}
    .top{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;flex-wrap:wrap}
    h1{margin:0;font-size:22px;letter-spacing:.08em;text-transform:uppercase}
    .meta{opacity:.75;font-size:14px}
    .actions{display:flex;gap:10px;flex-wrap:wrap}
    a.btn, button.btn{display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:999px;text-decoration:none;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-size:12px;border:1px solid rgba(0,0,0,.12);background:#fff;color:#111;cursor:pointer}
    a.btn-primary{background:#3f3f35;color:#fff;border-color:rgba(255,255,255,.18)}
    a.btn-ghost{background:transparent}
    .panel{margin-top:18px;background:rgba(255,255,255,.75);border:1px solid rgba(0,0,0,.08);border-radius:16px;padding:14px}
    .filters{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
    input,select{padding:10px 12px;border-radius:12px;border:1px solid rgba(0,0,0,.14);background:#fff;min-width:200px}
    table{width:100%;border-collapse:separate;border-spacing:0;margin-top:14px;background:#fff;border-radius:16px;overflow:hidden;border:1px solid rgba(0,0,0,.08)}
    th,td{padding:12px 12px;border-bottom:1px solid rgba(0,0,0,.06);text-align:left;font-size:14px}
    th{background:#faf8f4;font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:rgba(34,34,34,.7)}
    tr:last-child td{border-bottom:none}
    .pill{display:inline-flex;padding:6px 10px;border-radius:999px;font-weight:700;font-size:12px;letter-spacing:.06em}
    .yes{background:rgba(60,130,70,.12);border:1px solid rgba(60,130,70,.25);color:rgba(20,80,30,.95)}
    .no{background:rgba(220,80,80,.10);border:1px solid rgba(220,80,80,.25);color:rgba(120,20,20,.95)}
    .counts{display:flex;gap:10px;flex-wrap:wrap;margin-top:8px}
    .countbox{background:rgba(255,255,255,.65);border:1px solid rgba(0,0,0,.08);border-radius:14px;padding:10px 12px;font-size:14px}
    .muted{opacity:.7}
    .panel + .panel{margin-top:20px}
    .panel__title{margin:0 0 10px;font-size:18px;letter-spacing:.08em;text-transform:uppercase}
    .table-message{max-width:420px;white-space:normal;word-break:break-word}
    .table-meta{font-size:12px;opacity:.75}
    .status{margin-top:10px;font-size:13px}
    .status.is-ok{color:rgba(20,80,30,.9)}
    .status.is-error{color:rgba(120,20,20,.9)}
    @media (max-width:640px){
      input,select{min-width:0;flex:1}
      th:nth-child(4), td:nth-child(4),
      th:nth-child(5), td:nth-child(5){display:none;}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="top">
      <div>
        <h1>Keem & Kate | Guest List</h1>
        <div class="meta">Manage and export responses</div>
        <div class="counts">
          <div class="countbox"><strong>Total:</strong> <?php echo count($rows); ?></div>
          <div class="countbox"><strong>YES:</strong> <?php echo $count_yes; ?></div>
          <div class="countbox"><strong>NO:</strong> <?php echo $count_no; ?></div>
        </div>
      </div>

      <div class="actions">
        <a class="btn btn-primary" href="<?php echo htmlspecialchars($csv_link); ?>">Download CSV</a>
        <a class="btn btn-ghost" href="admin.php?logout=1">Logout</a>
      </div>
    </div>

    <div class="panel">
      <form class="filters" method="GET" action="admin.php">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search name..." />
        <select name="attendance">
          <option value="ALL" <?php echo ($attendance==="" || $attendance==="ALL") ? "selected" : ""; ?>>All</option>
          <option value="YES" <?php echo ($attendance==="YES") ? "selected" : ""; ?>>YES</option>
          <option value="NO"  <?php echo ($attendance==="NO")  ? "selected" : ""; ?>>NO</option>
        </select>
        <button class="btn" type="submit">Filter</button>
        <a class="btn btn-ghost" href="admin.php">Reset</a>
      </form>

      <table>
        <thead>
          <tr>
            <th style="width:80px;">ID</th>
            <th>Name</th>
            <th style="width:120px;">Response</th>
            <th style="width:180px;">IP</th>
            <th style="width:200px;">Submitted</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr>
              <td colspan="5" class="muted">No results found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?php echo (int)$r["id"]; ?></td>
                <td><?php echo htmlspecialchars($r["guest_name"]); ?></td>
                <td>
                  <?php if ($r["attendance"] === "YES"): ?>
                    <span class="pill yes">YES</span>
                  <?php else: ?>
                    <span class="pill no">NO</span>
                  <?php endif; ?>
                </td>
                <td class="muted"><?php echo htmlspecialchars((string)$r["ip_address"]); ?></td>
                <td class="muted"><?php echo htmlspecialchars((string)$r["created_at"]); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  <div class="panel">
      <h2 class="panel__title">Guestbook Notes (Admin) <span class="meta">(Total: <span data-guestbook-count><?php echo $guestbook_total; ?></span>)</span></h2>
      <div class="meta">Delete messages from the public guestbook feed.</div>

      <table data-guestbook-table>
        <thead>
          <tr>
            <th style="width:140px;">Name</th>
            <th>Message</th>
            <th style="width:120px;">Anonymous</th>
            <th style="width:200px;">Submitted</th>
            <th style="width:110px;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($guestbook_messages)): ?>
            <tr>
              <td colspan="5" class="muted">No guestbook messages yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($guestbook_messages as $message): ?>
              <tr>
                <td><?php echo htmlspecialchars($message["name"] ?: "Anonymous"); ?></td>
                <td class="table-message"><?php echo htmlspecialchars(truncate_message($message["message"] ?? "")); ?></td>
                <td class="table-meta"><?php echo !empty($message["anonymous"]) ? "Yes" : "No"; ?></td>
                <td class="table-meta">
                  <?php
                    $time = isset($message["time"]) ? (int)($message["time"] / 1000) : 0;
                    echo $time ? date("Y-m-d H:i", $time) : "—";
                  ?>
                </td>
                <td>
                  <button class="btn btn-ghost" type="button" data-delete-id="<?php echo htmlspecialchars((string)($message["id"] ?? "")); ?>">Delete</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      <div class="status" data-guestbook-status></div>
    </div>
  </div>

  <script>
    (() => {
      const table = document.querySelector('[data-guestbook-table]');
      if (!table) return;

      const statusEl = document.querySelector('[data-guestbook-status]');
      const countEl = document.querySelector('[data-guestbook-count]');
      const adminKey = <?php echo json_encode($ADMIN_API_KEY); ?>;

      table.addEventListener('click', async (event) => {
        const btn = event.target.closest('[data-delete-id]');
        if (!btn) return;

        const id = btn.getAttribute('data-delete-id');
        if (!id) return;

        const confirmed = window.confirm('Delete this message?');
        if (!confirmed) return;

        btn.disabled = true;

        try {
          const res = await fetch('api/delete_message.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Admin-Key': adminKey,
            },
            body: JSON.stringify({ id }),
          });

          const data = await res.json();
          if (!data.ok) {
            throw new Error(data.error || 'Delete failed.');
          }

          const row = btn.closest('tr');
          if (row) row.remove();

          if (countEl) {
            const next = Math.max(0, Number(countEl.textContent || '0') - 1);
            countEl.textContent = String(next);
          }

          if (statusEl) {
            statusEl.textContent = 'Message deleted.';
            statusEl.classList.remove('is-error');
            statusEl.classList.add('is-ok');
          }
        } catch (error) {
          if (statusEl) {
            statusEl.textContent = error?.message || 'Delete failed.';
            statusEl.classList.remove('is-ok');
            statusEl.classList.add('is-error');
          }
        } finally {
          btn.disabled = false;
          setTimeout(() => {
            if (statusEl) {
              statusEl.textContent = '';
              statusEl.classList.remove('is-ok', 'is-error');
            }
          }, 2200);
        }
      });
    })();
  </script>
</body>
</html>
