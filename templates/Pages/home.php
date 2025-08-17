<?php
/** templates/Pages/home.php */
$this->assign('title', 'Curd & Culture');
?>

<!-- Hero -->
<header class="hero" role="banner" aria-labelledby="site-title">
    <div class="hero-inner">
        <h1 id="site-title">Curd &amp; Culture</h1>
        <p class="tagline">Small-batch cheeses, crafted with care. From our farm to your table.</p>
    </div>

    <div class="hero-media" aria-hidden="true">
        <?= $this->Html->image('farm.jpg', [
            'alt' => '',
            'class' => 'hero-img',
        ]) ?>
    </div>
</header>

<!-- Best seller -->
<section class="section grid" aria-labelledby="best-seller">
    <div>
        <h2 id="best-seller">Our Bestseller: Aged Cheddar</h2>
        <p>Rich, sharp and beautifully balanced — our most loved cheese among loyal regulars.
            Small batches, traditional methods, premium ingredients.</p>
        <ul>
            <li>Hand-crafted in limited runs</li>
            <li>Perfect for gifting and entertaining</li>
            <li>Available in seasonal specials</li>
        </ul>
    </div>
    <div class="media">
        <?= $this->Html->image('cheddar.jpg', ['alt' => 'Aged cheddar', 'class' => 'card-img']) ?>
    </div>
</section>

<!-- How delivery works -->
<section class="section info" aria-labelledby="delivery">
    <h2 id="delivery">Freshness first: refrigerated delivery</h2>
    <p>
        Dairy travels cold (≈4°C). Choose a delivery window so your order stays perfectly chilled.
        If you might not be home, let us know — we’ll provide insulated packaging and safe-drop options.
    </p>
    <details>
        <summary>What if I miss my delivery?</summary>
        <p>We’ll contact you to reschedule the same day where possible, or arrange a new window.</p>
    </details>
</section>

<!-- Why choose us / brand tone -->
<section class="section grid" aria-labelledby="why-us">
    <div>
        <h2 id="why-us">Why Curd &amp; Culture?</h2>
        <ul class="bullets">
            <li><strong>Family-run &amp; personable:</strong> the same warmth you know from our market stall.</li>
            <li><strong>Secure &amp; trustworthy:</strong> privacy and safety come first in everything we build.</li>
            <li><strong>For all ages:</strong> simple, readable pages and clear guidance every step of the way.</li>
        </ul>
    </div>
    <div class="media">
        <?= $this->Html->image('cows-meadow.jpg', ['alt' => 'Pasture and dairy cows', 'class' => 'card-img']) ?>
    </div>
</section>

<!-- FAQ teaser -->
<section class="section faq-teaser" aria-labelledby="faq">
    <h2 id="faq">Got questions?</h2>
    <p>We’re preparing helpful FAQs about product care, shipping, and choosing cheeses.</p>
    <?= $this->Html->link('Ask us now', ['controller' => 'ContactMessages', 'action' => 'add'], ['class' => 'btn btn-primary']) ?>
</section>

<style>

    .page { --bg:#f7f8fa; --text:#1b1f23; --muted:#6b7280; --brand:#2c7be5; --card:#ffffff; --ring:rgba(44,123,229,.2);
        background:var(--bg); color:var(--text); line-height:1.6; }

    /* Hero */
    .hero { display:grid; grid-template-columns: 1.1fr .9fr; gap:2rem; padding:3.0rem 1rem 1.5rem; align-items:center; }
    .hero-inner { max-width: 720px; margin-inline:auto; text-align:center; }
    .hero h1 { font-size: clamp(1.8rem, 3vw, 2.6rem); margin: .25rem 0 .5rem; letter-spacing:.5px; }
    .tagline { color:var(--muted); margin-bottom:1rem; }
    .hero-media { display:flex; justify-content:center; }
    .hero-img { max-width:480px; width:100%; border-radius:1rem; box-shadow:0 10px 30px rgba(0,0,0,.08); object-fit:cover; }

    /* Sections */
    .section { padding:2.0rem 1rem; max-width:1100px; margin:0 auto; }
    .section h2 { font-size: clamp(1.25rem, 2.2vw, 1.6rem); margin-bottom:.75rem; }
    .grid { display:grid; gap:1.25rem; grid-template-columns: 1fr 1fr; align-items:center; }
    .media { text-align:center; }
    .card-img { max-width:520px; width:100%; border-radius:1rem; box-shadow:0 8px 24px rgba(0,0,0,.06); object-fit:cover; }
    .info { background:#fff; border-radius:1rem; box-shadow:0 10px 30px rgba(0,0,0,.05); }
    .bullets { padding-left:1.1rem; }
    .faq-teaser { text-align:center; }


    .page.hc .info { background:#0f172a; }
    .page.hc .btn { background:#1f2937; color:#fff; border-color:#475569; }
    .page.hc .btn.btn-primary { background:#60a5fa; color:#111; }


    .btn { display:inline-block; padding:.65rem 1.05rem; border-radius:.6rem; text-decoration:none; border:1px solid transparent;
        background:#e5e7eb; color:#111; transition:.15s transform, .15s filter; }
    .btn:hover { transform: translateY(-1px); filter:brightness(.98); }
    .btn:focus-visible { outline: 3px solid var(--ring); outline-offset: 2px; }
    .btn-primary { background:var(--brand); color:#fff; }


    @media (max-width: 900px) {
        .hero { grid-template-columns: 1fr; padding-top:1.5rem; }
        .grid  { grid-template-columns: 1fr; }
        .hero-img, .card-img { max-width: 92vw; }
    }
</style>
