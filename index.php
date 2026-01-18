<?php
// index.php (brand-new starter)
// You can adjust paths + sections later as you add more content.
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Keem & Kate — Wedding Invitation</title>
  <meta name="description" content="Keem & Kate are saying I do. Join us for our wedding celebration." />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- Optional: If you want to use a nicer serif later, you can uncomment:
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
  -->

  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <!-- HERO SECTION -->
  <main>
    <section class="hero" aria-label="Wedding invitation hero">
      <!-- If your hero background image already includes the black border/mat,
           you can keep it as-is. If not, the CSS adds a safe frame feel. -->


<div class="hero__clouds" aria-hidden="true">
        <div class="hero__cloud hero__cloud--one">
          <img src="assets/cloud1.png" alt="" aria-hidden="true" />
        </div>
        <div class="hero__cloud hero__cloud--two">
          <img src="assets/cloud2.png" alt="" aria-hidden="true" />
        </div>
        <div class="hero__cloud hero__cloud--three">
          <img src="assets/cloud3.png" alt="" aria-hidden="true" />
        </div>
      </div>

       <img src="assets/couple.png" class="hero__couple" alt="" aria-hidden="true">

      <div class="hero__inner">
        <div class="hero__content">
          <h1 class="hero__title">KEEM &amp; KATE</h1>
          <p class="hero__subtitle">ARE SAYING “I DO”</p>

          <a class="hero__btn" href="#rsvp" aria-label="Join us - RSVP section">
            <span>JOIN US</span>
          </a>
        </div>
      </div>
    </section>

    
    <!-- LOVE STORY SECTION -->
<section id="story" class="story" aria-label="Short love story">
  <div class="container story__wrap">
    <div class="story__text">
      <p class="story__eyebrow">SHORT LOVE STORY</p>
      <h2 class="story__title">Our Story</h2>

      <blockquote class="story__quote">
        <p>
          "Nagsimula sa kilig.<br>
          Natuloy sa seryoso.<br>
          Matatapos sa kasal.<br><br>
          May forever pala, mga bes."
        </p>
      </blockquote>

      <div class="story__actions">
        <a class="btn btn--primary" href="#rsvp">RSVP</a>
        <a class="btn btn--ghost" href="#details">Back to Date</a>
      </div>
    </div>

    <div class="story__media" aria-label="Couple photo">
      <!-- Replace this with your real photo later -->
      <div class="story__photo" role="img" aria-label="Couple photo placeholder"></div>
    </div>
  </div>
</section>


<!-- MESSAGE FOR THE COUPLE / GUESTBOOK SECTION -->
<section class="guestbook" id="guestbook" aria-label="Message for the couple">
  <!-- FORM PANEL -->
  <aside class="panel" id="panel" aria-label="Message form">
    <div class="panel__header">
      <div class="panel__headText">
        <h2 class="panel__title">Message for the Couple</h2>
        <p class="panel__subtitle">Leave a note. Sign it or stay anonymous.</p>
      </div>

      <!-- Desktop only (hidden on mobile) -->
      <button class="panel__collapseBtn" id="panelCollapseBtn" type="button" aria-expanded="true">
        Collapse
      </button>
    </div>

        <form id="messageForm" class="form">
          <div class="form__group">
      <label class="form__label" for="name">Name (optional)</label>
      <input id="name" name="name" class="form__input" type="text" placeholder="e.g., Alex" maxlength="40" />
    </div>

    <div class="form__group">
      <label class="form__label" for="message">Your message</label>
      <textarea id="message" name="message" class="form__textarea"
        placeholder="Write something heartfelt..." maxlength="220" required></textarea>
    </div>

      <div class="form__row">
        <label class="toggle">
          <input id="anonymous" type="checkbox" />
          <span>Post anonymously</span>
        </label>
        <span class="charcount"><span id="count">0</span>/220</span>
      </div>

      <!-- Honeypot -->
      <input type="text" name="website" id="website" class="hp" autocomplete="off" tabindex="-1" />

       <div class="form__actions">
        <button class="btn btn--primary" type="submit" id="submitBtn">Post</button>
        <button class="btn btn--ghost" type="button" id="refreshBtn">Refresh</button>
      </div>

      <p class="form__hint" id="statusText"></p>
    </form>

   

    <!-- Mobile collapse bar button -->
    <button class="panel__mobileHandle" type="button" id="mobileHandle" aria-expanded="true">
      Hide form
    </button>

    <!-- Desktop “peek tab” -->
    <button class="panel__peekTab" type="button" id="peekTab" aria-label="Expand message form">
      Message
    </button>
  </aside>

  <!-- CORKBOARD -->
  <main class="board" id="board" aria-label="Corkboard notes">
    <div class="board__topbar" id="boardTopbar">
      <div class="board__title">Polaroid Wall</div>
      <div class="board__meta">Notes: <span id="noteCount">0</span></div>
    </div>

    <div id="notes" class="notes" aria-live="polite"></div>

    <div class="pager" id="pager" aria-label="Pagination">
      <button class="btn btn--ghost btn--small" id="prevBtn" type="button">Prev</button>
      <span class="pager__text">Page <span id="pageNum">1</span> of <span id="pageTotal">1</span></span>
      <button class="btn btn--ghost btn--small" id="nextBtn" type="button">Next</button>
    </div>
  </main>
</section>

<!-- SAVE THE DATE SECTION -->
<section id="details" class="details" aria-label="Save the date">
  <div class="container">
    <header class="details__header">
      <p class="details__eyebrow">SAVE THE DATE</p>
      <h2 class="details__title">June 18, 2026</h2>
      <p class="details__lead">
        More details will be posted soon.
      </p>
    </header>

    <div class="details__grid">
      <!-- Date card -->
      <article class="card card--highlight">
        <h3 class="card__title">Wedding Date</h3>
        <p class="card__big">June 18, 2026</p>
        <p class="card__sub">Please mark your calendar.</p>
      </article>

      <!-- Placeholder cards (disabled) -->
      <article class="card card--disabled" aria-hidden="true">
        <h3 class="card__title">Time</h3>
        <p class="card__big">To be announced</p>
        <p class="card__sub">Details to follow.</p>
      </article>

      <article class="card card--disabled" aria-hidden="true">
        <h3 class="card__title">Venue</h3>
        <p class="card__big">To be announced</p>
        <p class="card__sub">Details to follow.</p>
      </article>

      <article class="card card--disabled" aria-hidden="true">
        <h3 class="card__title">Reception</h3>
        <p class="card__big">To be announced</p>
        <p class="card__sub">Details to follow.</p>
      </article>
    </div>

    <div class="details__actions">
      <a class="btn btn--primary" href="#rsvp">RSVP</a>
      <a class="btn btn--ghost is-disabled" href="javascript:void(0)" aria-disabled="true">Open Map</a>
      <a class="btn btn--ghost is-disabled" href="javascript:void(0)" aria-disabled="true">Add to Calendar</a>
    </div>
  </div>
</section>

<!-- GALLERY SECTION -->
<section id="gallery" class="gallery" aria-label="Couple photo gallery">
  <div class="container gallery__wrap">
    <header class="gallery__header">
      <p class="gallery__eyebrow">PHOTO GALLERY</p>
      <h2 class="gallery__title">Keem &amp; Kate</h2>
      <p class="gallery__lead">A few moments we want to share with you.</p>
    </header>

    <!-- Desktop: grid | Mobile: swipe carousel -->
    <div class="gallery__track" aria-label="Gallery images">
      <figure class="gallery__item">
        <img src="assets/gallery/image 1.jpg" alt="Couple photo 1" loading="lazy">
      </figure>
      <figure class="gallery__item">
        <img src="assets/gallery/image 2.JPG" alt="Couple photo 2" loading="lazy">
      </figure>
      <figure class="gallery__item">
        <img src="assets/gallery/image 3.jpg" alt="Couple photo 3" loading="lazy">
      </figure>
      <figure class="gallery__item">
        <img src="assets/gallery/image 4.jpg" alt="Couple photo 4" loading="lazy">
      </figure>
      <figure class="gallery__item">
        <img src="assets/gallery/image 5.jpg" alt="Couple photo 5" loading="lazy">
      </figure>
      <figure class="gallery__item">
        <img src="assets/gallery/image 6.jpg" alt="Couple photo 6" loading="lazy">

    </figure>
    </div>

    <div class="gallery__actions">
      <a class="btn btn--ghost" href="#story">Back to Story</a>
      <a class="btn btn--primary" href="#rsvp">RSVP</a>
    </div>
  </div>
</section>

<!-- RSVP SECTION (ENVELOPE STYLE) -->
<section id="rsvp" class="rsvp is-visible" aria-label="RSVP">
  <div class="container rsvp__wrap">
    <header class="rsvp__header">
      <p class="rsvp__eyebrow">RSVP</p>
      <h2 class="rsvp__title">Kindly Respond</h2>
      <p class="rsvp__lead">Your response means a lot to us.</p>

      <!-- +1 NOTE -->
      <p class="rsvp__note">
        Friendly reminder: dahil limited ang seats, the invitation is for named guests only.
        Sana po ay maunawaan ninyo. Salamat! 🤍
      </p>
    </header>

    <div class="envelope" role="group" aria-label="RSVP envelope">
      <div class="envelope__flap" aria-hidden="true"></div>

      <div class="envelope__paper">

      <?php
$rsvp_status = $_GET['rsvp'] ?? '';
$rsvp_msg = $_GET['msg'] ?? '';
?>

<?php if ($rsvp_status === 'error' && $rsvp_msg): ?>
  <div class="rsvp-error" role="alert">
    <?php echo htmlspecialchars($rsvp_msg); ?>
  </div>
<?php endif; ?>

        <?php $rsvp_success = (isset($_GET['rsvp']) && $_GET['rsvp'] === 'success'); ?>

<?php if ($rsvp_success): ?>
  <div class="rsvp-success" role="status" aria-live="polite">
    <h3 class="rsvp-success__title">Thank you for your response!</h3>
    <p class="rsvp-success__text">Your RSVP has been received. See you soon.</p>
    <a class="btn btn--ghost" href="index.php#rsvp">Submit another response</a>
  </div>
<?php endif; ?>

        <form class="rsvp-form <?php echo $rsvp_success ? 'is-hidden' : ''; ?>"
              action="submit.php" method="POST" autocomplete="on">

          <div class="rsvp-form__row">
            <label class="rsvp-form__label" for="guest_name">What is your full name?</label>
            <input
              class="rsvp-form__input"
              type="text"
              id="guest_name"
              name="guest_name"
              placeholder="Type your full name"
              required
              maxlength="120"
            />
          </div>

          <fieldset class="rsvp-form__row rsvp-form__fieldset">
            <legend class="rsvp-form__label">Will you join us in our wedding day?</legend>

            <label class="rsvp-form__choice">
              <input type="radio" name="attendance" value="YES" required />
              <span>Yes, see you there!</span>
            </label>

            <label class="rsvp-form__choice">
              <input type="radio" name="attendance" value="NO" required />
              <span>No, pero with love pa rin!</span>
            </label>
          </fieldset>

          <div class="rsvp-form__actions">
            <button class="btn btn--primary rsvp-form__submit" type="submit">
              Submit RSVP
            </button>
            <p class="rsvp-form__hint">We’ll keep your response private.</p>
          </div>
        </form>
      </div>

      <div class="envelope__base" aria-hidden="true"></div>
    </div>
  </div>
</section>



  </main>
  <script src="script.js"></script>

</body>
</html>
