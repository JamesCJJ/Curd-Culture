<?php
/** templates/Pages/home.php */
$this->assign('title', 'Curd & Culture');
?>

<!-- ========== HERO ========== -->
<header class="hero hero--blend" role="banner" aria-labelledby="site-title">
    <div class="hero__text">
        <h1 id="site-title">Curd &amp; Culture</h1>
        <p class="lead">Small-batch cheeses, crafted with care — from our farm to your table.</p>

        <ul class="badges" aria-label="Highlights">
            <li class="badge">Handmade</li>
            <li class="badge">Family-run</li>
            <li class="badge">Chilled Delivery</li>
        </ul>

        <div class="cta-row">
            <?= $this->Html->link('Ask about availability', ['controller' => 'ContactMessages', 'action' => 'add'], ['class' => 'btn btn-primary', 'aria-label' => 'Open contact form']) ?>
            <span class="subtext">Friendly replies, usually within 1 business day.</span>
        </div>
    </div>

    <div class="hero__media" aria-hidden="true">
        <?= $this->Html->image('farm.jpg', ['alt' => '', 'class' => 'hero__img']) ?>
    </div>
</header>

<!-- ========== BESTSELLER ========== -->
<section class="section grid2" aria-labelledby="best-seller">
    <div>
        <h2 id="best-seller">Bestseller · Aged Cheddar</h2>
        <p class="muted">Rich, sharp, beautifully balanced — our most-loved cheese. Small batches, traditional methods, premium ingredients.</p>
        <ul class="list">
            <li>Hand-crafted in limited runs</li>
            <li>Perfect for gifting &amp; entertaining</li>
            <li>Seasonal specials available</li>
        </ul>
    </div>
    <figure class="card-media">
        <?= $this->Html->image('cheddar.jpg', ['alt' => 'Slices of aged cheddar', 'class' => 'img-card']) ?>
    </figure>
</section>

<!-- ========== DELIVERY / TRUST ========== -->
<section class="section card info" aria-labelledby="delivery">
    <div class="split">
        <div>
            <h2 id="delivery">Freshness first: refrigerated delivery</h2>
            <p>Dairy travels cold (~4°C). Choose a delivery window so your order stays perfectly chilled. If you might not be home, let us know — we’ll provide insulated packaging and safe-drop options.</p>
            <details>
                <summary>What if I miss my delivery?</summary>
                <p>We’ll contact you to reschedule the same day where possible, or arrange a new window.</p>
            </details>
        </div>
        <ul class="trust">
            <li>Temperature-controlled transit</li>
            <li>Safe-drop on request</li>
            <li>Clear guidance for storage</li>
        </ul>
    </div>
</section>

<!-- ========== WHY US ========== -->
<section class="section grid2" aria-labelledby="why-us">
    <div>
        <h2 id="why-us">Why Curd &amp; Culture?</h2>
        <ul class="list">
            <li><strong>Family-run &amp; personable</strong> — the same warmth you know from our market stall.</li>
            <li><strong>Secure &amp; trustworthy</strong> — privacy and safety come first in everything we build.</li>
            <li><strong>For all ages</strong> — simple, readable pages and clear guidance every step of the way.</li>
        </ul>
    </div>
    <figure class="card-media">
        <?= $this->Html->image('cows-meadow.jpg', ['alt' => 'Cows at golden hour on meadow', 'class' => 'img-card']) ?>
    </figure>
</section>

<!-- ========== HOW IT WORKS ========== -->
<section class="section steps" aria-labelledby="how">
    <h2 id="how">How it works</h2>
    <ol class="stepper" role="list">
        <li>
            <span class="step-num">1</span>
            <div><strong>Say hello</strong><br>Tell us what you’re after — we’ll suggest cuts &amp; quantities.</div>
        </li>
        <li>
            <span class="step-num">2</span>
            <div><strong>Pick a window</strong><br>Choose a chilled delivery slot that suits your day.</div>
        </li>
        <li>
            <span class="step-num">3</span>
            <div><strong>Enjoy</strong><br>Store with our quick tips, then share &amp; savor.</div>
        </li>
    </ol>
</section>

<!-- ========== FINAL CTA ========== -->
<section class="section final-cta card" aria-labelledby="cta">
    <h2 id="cta">Got questions?</h2>
    <p class="muted">We’re preparing helpful FAQs about product care, shipping, and choosing cheeses.</p>
    <?= $this->Html->link('Ask us now', ['controller' => 'ContactMessages', 'action' => 'add'], ['class' => 'btn btn-primary']) ?>
</section>

<style>
    /* ===== Theme base (inherits .page from layout) ===== */
    .page { --bg:#f6f7f9; --text:#101418; --muted:#5b6470; --card:#ffffff; --brand:#2c7be5; --ring:rgba(44,123,229,.25);
        color:var(--text); background:var(--bg); line-height:1.65; }

    /* ===== Hero ===== */
    .hero{display:grid;grid-template-columns:1.05fr .95fr;gap:2rem;align-items:center;max-width:1100px;margin:0 auto;padding:2.25rem 1rem 1.25rem}
    .hero--blend{background:radial-gradient(1200px 140px at 50% -40px,#eaf2ff 0%,transparent 60%)}
    .hero__text{text-align:left}
    .hero__text h1{font-size:clamp(2rem,3.2vw,2.8rem);margin:0 0 .4rem}
    .lead{color:var(--muted);margin:.25rem 0 1rem}
    .badges{display:flex;gap:.5rem;flex-wrap:wrap;margin:.2rem 0 1rem;padding:0;list-style:none}
    .badge{background:#eef5ff;border:1px solid #d7e7ff;color:#255db2;border-radius:999px;padding:.35rem .65rem;font-weight:600}
    .cta-row{display:flex;gap:.75rem;align-items:center;flex-wrap:wrap}
    .subtext{color:var(--muted);font-size:.95rem}
    .hero__media{display:flex;justify-content:center}
    .hero__img{max-width:520px;width:100%;border-radius:1rem;box-shadow:0 14px 40px rgba(0,0,0,.10);object-fit:cover}

    /* ===== Sections ===== */
    .section{max-width:1100px;margin:0 auto;padding:1.35rem 1rem}
    .grid2{display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;align-items:center}
    .muted{color:var(--muted)}
    .list{padding-left:1.1rem;margin:.6rem 0}
    .card{background:var(--card);border-radius:1rem;box-shadow:0 12px 36px rgba(0,0,0,.08);padding:1.1rem 1rem}
    .card-media{margin:0;text-align:center}
    .img-card{max-width:520px;width:100%;border-radius:1rem;box-shadow:0 10px 30px rgba(0,0,0,.08);object-fit:cover}

    /* Delivery block */
    .info .split{display:grid;grid-template-columns:1.35fr .65fr;gap:1rem;align-items:start}
    .trust{list-style:none;margin:0;padding:0}
    .trust li{background:#f3f6fb;border:1px solid #e2e8f5;border-radius:.75rem;padding:.55rem .7rem;margin:.4rem 0;font-weight:600}

    /* Steps */
    .steps{padding-top:.25rem}
    .steps h2{margin-bottom:.5rem}
    .stepper{list-style:none;padding:0;margin:.5rem 0;display:grid;grid-template-columns:repeat(3,1fr);gap:.9rem}
    .stepper li{display:flex;gap:.6rem;align-items:flex-start;background:var(--card);border-radius:.85rem;padding:.75rem;border:1px solid #eef0f3}
    .step-num{flex:0 0 2rem;height:2rem;display:inline-grid;place-items:center;border-radius:50%;background:#eaf2ff;border:1px solid #d7e7ff;color:#1c4ea0;font-weight:700}

    /* Final CTA */
    .final-cta{text-align:center;margin:1.1rem auto 1.5rem}

    /* Buttons (reuse layout styles) */
    .btn{display:inline-block;padding:.65rem 1.05rem;border-radius:.65rem;border:1px solid transparent;background:#e5e7eb;color:#111;text-decoration:none}
    .btn-primary{background:var(--brand);color:#fff}
    .btn:focus-visible{outline:3px solid var(--ring);outline-offset:2px}
    .btn:hover{filter:brightness(.98);transform:translateY(-1px);transition:.15s}

    /* ========= High-Contrast theme (fix) ========= */


    .page.hc{
        --bg:#0b0f14;
        --text:#f1f5f9;
        --muted:#cbd5e1;
        --card:#0f172a;
        --brand:#60a5fa;
        --ring:rgba(96,165,250,.45);
        color:var(--text);
        background:var(--bg);
    }


    .page.hc .hero--blend{ background:none; }


    .page.hc .badge{ background:#111827; border-color:#334155; color:#e5e7eb; }


    .page.hc .card,
    .page.hc .info,
    .page.hc .stepper li{
        background:var(--card);
        color:var(--text);
        border-color:#334155;
    }


    .page.hc .step-num{
        background:#0b1220;
        border-color:#334155;
        color:#9ec5ff;
    }


    .page.hc .trust li{
        background:#111827;
        border-color:#334155;
        color:#e5e7eb;
    }

    .page.hc .muted{ color:var(--muted); }


    .page.hc details,
    .page.hc details summary{ color:var(--text); }


    .page.hc .hero__img,
    .page.hc .img-card{ box-shadow:none; }

    /* Responsive */
    @media (max-width: 920px){
        .hero{grid-template-columns:1fr}
        .grid2{grid-template-columns:1fr}
        .info .split{grid-template-columns:1fr}
        .stepper{grid-template-columns:1fr}
        .hero__img,.img-card{max-width:92vw}
    }
</style>
