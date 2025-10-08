<?php
/** templates/Pages/home.php */
$this->assign('title', 'Curd & Culture - Premium Artisan Cheese');
?>

<!-- ========== HERO ========== -->
<header class="hero" role="banner" aria-labelledby="site-title">
    <div class="hero__content">
        <div class="hero__text">
            <div class="hero__badge">🧀 Premium Artisan Cheese</div>
            <h1 id="site-title">Small-Batch Cheeses,<br>Crafted with Love</h1>
            <p class="hero__lead">From our family farm to your table. Experience the finest handmade cheeses, delivered fresh to your door with temperature-controlled care.</p>

            <div class="hero__features">
                <div class="feature-chip">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span>Family-Run Since 1985</span>
                </div>
                <div class="feature-chip">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M2 12h20"></path></svg>
                    <span>100% Handcrafted</span>
                </div>
                <div class="feature-chip">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <span>Chilled Delivery</span>
                </div>
            </div>

            <div class="hero__cta">
                <?= $this->Html->link('Shop Our Cheeses', ['controller' => 'Products', 'action' => 'index'], ['class' => 'btn btn-hero btn-primary', 'aria-label' => 'Browse our cheese products']) ?>
                <?= $this->Html->link('Contact Us', ['controller' => 'ContactMessages', 'action' => 'add'], ['class' => 'btn btn-hero btn-outline', 'aria-label' => 'Get in touch with us']) ?>
            </div>

            <div class="hero__trust">
                <span class="trust-text">Trusted by 500+ cheese lovers across Australia</span>
            </div>
        </div>

        <div class="hero__media">
            <?= $this->Html->image('cheese-platter.jpg', ['alt' => 'Premium artisan cheese selection', 'class' => 'hero__img']) ?>
            <div class="hero__overlay">
                <div class="stat-card">
                    <div class="stat-number">40+</div>
                    <div class="stat-label">Cheese Varieties</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Natural Ingredients</div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ========== FEATURED PRODUCTS ========== -->
<section class="featured-products" aria-labelledby="featured-title">
    <div class="section-header">
        <h2 id="featured-title">Our Signature Cheeses</h2>
        <p class="section-subtitle">Handcrafted with passion, aged to perfection</p>
    </div>

    <div class="product-grid">
        <div class="product-card">
            <div class="product-image">
                <?= $this->Html->image('cheddar.jpg', ['alt' => 'Aged Cheddar cheese', 'class' => 'product-img']) ?>
                <span class="product-badge bestseller">Bestseller</span>
            </div>
            <div class="product-info">
                <h3 class="product-title">Aged Cheddar</h3>
                <p class="product-desc">Rich, sharp, and beautifully balanced. Our most-loved cheese with traditional aging methods.</p>
                <div class="product-meta">
                    <span class="product-feature">⭐ 4.9/5 Rating</span>
                    <span class="product-feature">🏆 Award Winner</span>
                </div>
            </div>
        </div>

        <div class="product-card">
            <div class="product-image">
                <?= $this->Html->image('cows-meadow.jpg', ['alt' => 'Artisan Brie cheese', 'class' => 'product-img']) ?>
                <span class="product-badge new">New</span>
            </div>
            <div class="product-info">
                <h3 class="product-title">Creamy Brie</h3>
                <p class="product-desc">Silky smooth with a delicate flavor. Perfect for cheese boards and sophisticated entertaining.</p>
                <div class="product-meta">
                    <span class="product-feature">🥛 Fresh Milk</span>
                    <span class="product-feature">🌱 Organic</span>
                </div>
            </div>
        </div>

        <div class="product-card">
            <div class="product-image">
                <?= $this->Html->image('farm.jpg', ['alt' => 'Gourmet Blue cheese', 'class' => 'product-img']) ?>
                <span class="product-badge limited">Limited</span>
            </div>
            <div class="product-info">
                <h3 class="product-title">Gourmet Blue</h3>
                <p class="product-desc">Bold and distinctive with rich marbling. For the adventurous cheese connoisseur.</p>
                <div class="product-meta">
                    <span class="product-feature">👨‍🍳 Chef's Pick</span>
                    <span class="product-feature">⏰ Small Batch</span>
                </div>
            </div>
        </div>
    </div>

    <div class="products-cta">
        <?= $this->Html->link('View All Cheeses →', ['controller' => 'Products', 'action' => 'index'], ['class' => 'btn btn-large btn-primary']) ?>
    </div>
</section>

<!-- ========== TRUST & VALUES ========== -->
<section class="trust-section" aria-labelledby="trust-title">
    <div class="trust-container">
        <div class="trust-badges">
            <div class="trust-badge">
                <div class="trust-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
                <h3>Premium Quality</h3>
                <p>Award-winning cheeses crafted with the finest ingredients</p>
            </div>

            <div class="trust-badge">
                <div class="trust-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                </div>
                <h3>Chilled Delivery</h3>
                <p>Temperature-controlled at 4°C from farm to your door</p>
            </div>

            <div class="trust-badge">
                <div class="trust-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </div>
                <h3>Family Legacy</h3>
                <p>Three generations of cheese-making excellence</p>
            </div>

            <div class="trust-badge">
                <div class="trust-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <h3>Fresh Daily</h3>
                <p>Made in small batches to ensure peak freshness</p>
            </div>
        </div>
    </div>
</section>

<!-- ========== DELIVERY INFO ========== -->
<section class="delivery-section" aria-labelledby="delivery">
    <div class="delivery-content">
        <div class="delivery-text">
            <h2 id="delivery">Freshness First: Refrigerated Delivery</h2>
            <p class="delivery-lead">Your cheese deserves the best care. We maintain optimal temperature (~4°C) throughout the entire journey from our farm to your doorstep.</p>

            <div class="delivery-features">
                <div class="delivery-feature">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                    <div>
                        <strong>Choose Your Delivery Window</strong>
                        <p>Select a convenient time slot that works for your schedule</p>
                    </div>
                </div>
                <div class="delivery-feature">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
                    <div>
                        <strong>Insulated Packaging</strong>
                        <p>Premium insulation keeps everything perfectly chilled for hours</p>
                    </div>
                </div>
                <div class="delivery-feature">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    <div>
                        <strong>Delivery Support</strong>
                        <p>Missed your delivery? We'll reschedule or arrange a safe drop-off</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="delivery-visual">
            <div class="delivery-card highlight">
                <div class="delivery-card-icon">📦</div>
                <div class="delivery-card-content">
                    <h4>Safe Drop-Off Available</h4>
                    <p>Can't be home? We offer secure drop-off options with clear storage instructions.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== WHY CHOOSE US ========== -->
<section class="why-section" aria-labelledby="why-us">
    <div class="why-container">
        <div class="why-image">
            <?= $this->Html->image('cows-meadow.jpg', ['alt' => 'Happy cows grazing in our meadows', 'class' => 'why-img']) ?>
        </div>
        <div class="why-content">
            <h2 id="why-us">Why Choose Curd &amp; Culture?</h2>
            <p class="why-intro">For over 35 years, we've been dedicated to producing the finest artisan cheeses using time-honored techniques and sustainable farming practices.</p>

            <div class="why-list">
                <div class="why-item">
                    <div class="why-number">01</div>
                    <div class="why-text">
                        <h3>Farm-to-Table Excellence</h3>
                        <p>Every cheese starts with milk from our own pasture-raised cows, ensuring complete quality control from start to finish.</p>
                    </div>
                </div>

                <div class="why-item">
                    <div class="why-number">02</div>
                    <div class="why-text">
                        <h3>Traditional Craftsmanship</h3>
                        <p>We use traditional cheese-making methods passed down through three generations, combined with modern food safety standards.</p>
                    </div>
                </div>

                <div class="why-item">
                    <div class="why-number">03</div>
                    <div class="why-text">
                        <h3>Customer-First Service</h3>
                        <p>From our friendly market stall to our secure online shop, we bring the same personal care and attention to every customer.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== HOW IT WORKS ========== -->
<section class="how-section" aria-labelledby="how">
    <div class="section-header centered">
        <h2 id="how">How It Works</h2>
        <p class="section-subtitle">From browsing to enjoying — simple, seamless, and fresh</p>
    </div>

    <div class="steps-grid">
        <div class="step-card">
            <div class="step-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
            </div>
            <div class="step-number">Step 1</div>
            <h3>Browse &amp; Select</h3>
            <p>Explore our selection of artisan cheeses. Each product has detailed descriptions and pairing suggestions.</p>
        </div>

        <div class="step-card">
            <div class="step-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
            </div>
            <div class="step-number">Step 2</div>
            <h3>Choose Delivery</h3>
            <p>Pick a refrigerated delivery window that fits your schedule. We ensure optimal freshness every time.</p>
        </div>

        <div class="step-card">
            <div class="step-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </div>
            <div class="step-number">Step 3</div>
            <h3>Enjoy!</h3>
            <p>Receive your carefully packaged cheese with storage tips. Share, savor, and come back for more!</p>
        </div>
    </div>
</section>

<!-- ========== TESTIMONIALS ========== -->
<section class="testimonials-section" aria-labelledby="testimonials">
    <div class="section-header centered">
        <h2 id="testimonials">What Our Customers Say</h2>
        <p class="section-subtitle">Join hundreds of happy cheese lovers</p>
    </div>

    <div class="testimonials-grid">
        <div class="testimonial-card">
            <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
            <p class="testimonial-text">"The aged cheddar is absolutely divine! You can taste the quality and care in every bite. Best cheese I've ever had."</p>
            <div class="testimonial-author">
                <strong>Sarah M.</strong>
                <span>Melbourne, VIC</span>
            </div>
        </div>

        <div class="testimonial-card">
            <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
            <p class="testimonial-text">"Delivery was impeccable - arrived perfectly chilled and on time. The customer service is as wonderful as the cheese!"</p>
            <div class="testimonial-author">
                <strong>James L.</strong>
                <span>Sydney, NSW</span>
            </div>
        </div>

        <div class="testimonial-card">
            <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
            <p class="testimonial-text">"Love supporting a family business that clearly cares about their craft. The brie is restaurant quality!"</p>
            <div class="testimonial-author">
                <strong>Emma K.</strong>
                <span>Brisbane, QLD</span>
            </div>
        </div>
    </div>
</section>

<!-- ========== FINAL CTA ========== -->
<section class="cta-section" aria-labelledby="cta">
    <div class="cta-container">
        <div class="cta-content">
            <h2 id="cta">Ready to Experience Premium Cheese?</h2>
            <p class="cta-subtitle">Start your artisan cheese journey today. Browse our full collection and taste the difference that quality makes.</p>
            <div class="cta-buttons">
                <?= $this->Html->link('Shop Now', ['controller' => 'Products', 'action' => 'index'], ['class' => 'btn btn-large btn-primary']) ?>
                <?= $this->Html->link('Contact Us', ['controller' => 'ContactMessages', 'action' => 'add'], ['class' => 'btn btn-large btn-outline']) ?>
            </div>
            <p class="cta-note">Have questions? We're here to help! Reach out anytime.</p>
        </div>
    </div>
</section>

<style>
    /* ===== Theme variables for the content area ===== */
    .page {
        --bg: #ffffff;
        --bg-alt: #f8fafc;
        --text: #0f172a;
        --text-muted: #64748b;
        --card: #ffffff;
        --brand: #f59e0b;
        --brand-dark: #d97706;
        --brand-light: #fef3c7;
        --accent: #2563eb;
        --border: #e2e8f0;
        --shadow: rgba(15, 23, 42, 0.08);
        --shadow-lg: rgba(15, 23, 42, 0.12);
        --ring: rgba(245, 158, 11, 0.25);
        color: var(--text);
        background: var(--bg);
        line-height: 1.7;
        font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;
    }

    /* ===== Hero ===== */
    .hero { background:linear-gradient(135deg,#fef3c7 0%,#fef9e8 50%,#ffffff 100%); padding:4rem 0 5rem; margin-bottom:4rem; }
    .hero__content { max-width:1200px; margin:0 auto; padding:0 2rem; display:grid; grid-template-columns:1fr 1fr; gap:4rem; align-items:center; }
    .hero__text { display:flex; flex-direction:column; gap:1.5rem; }
    .hero__badge{display:inline-block;background:var(--brand);color:#fff;padding:.5rem 1.25rem;border-radius:50px;font-size:.875rem;font-weight:600;letter-spacing:.5px;width:fit-content;box-shadow:0 4px 12px var(--shadow)}
    .hero__text h1{font-size:clamp(2.5rem,5vw,3.75rem);font-weight:800;line-height:1.1;color:var(--text);margin:0;letter-spacing:-.02em}
    .hero__lead{font-size:1.125rem;color:var(--text-muted);line-height:1.7;margin:0}
    .hero__features{display:flex;flex-wrap:wrap;gap:1rem}
    .feature-chip{display:flex;align-items:center;gap:.5rem;background:#fff;padding:.75rem 1.25rem;border-radius:12px;border:1px solid var(--border);font-size:.9rem;font-weight:500;color:var(--text);box-shadow:0 2px 8px var(--shadow)}
    .feature-chip svg{color:var(--brand);flex-shrink:0}
    .hero__cta{display:flex;gap:1rem;flex-wrap:wrap}
    .hero__trust{padding-top:.5rem}
    .trust-text{color:var(--text-muted);font-size:.875rem;display:flex;align-items:center;gap:.5rem}
    .trust-text::before{content:'✓';display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;background:#10b981;color:#fff;border-radius:50%;font-size:.75rem;font-weight:700}
    .hero__media{position:relative}
    .hero__img{width:100%;height:500px;object-fit:cover;border-radius:20px;box-shadow:0 20px 60px var(--shadow-lg)}
    .hero__overlay{
        position:absolute;bottom:-20px;left:-20px;display:flex;gap:1rem;
        /* KEY FIX: don't block taps behind it */
        pointer-events:none; z-index:0;
    }
    .stat-card{background:#fff;padding:1.25rem 1.5rem;border-radius:16px;box-shadow:0 10px 30px var(--shadow-lg);border:1px solid var(--border)}
    .stat-number{font-size:2rem;font-weight:800;color:var(--brand);line-height:1}
    .stat-label{font-size:.875rem;color:var(--text-muted);margin-top:.25rem}

    /* ===== Section header ===== */
    .section-header{text-align:center;margin-bottom:3rem}
    .section-header.centered{max-width:700px;margin:0 auto 3rem}
    .section-header h2{font-size:clamp(2rem,4vw,2.75rem);font-weight:800;color:var(--text);margin:0 0 .75rem;letter-spacing:-.01em}
    .section-subtitle{font-size:1.125rem;color:var(--text-muted);margin:0}

    /* ===== Buttons (scoped to content) ===== */
    .page .btn{display:inline-flex;align-items:center;justify-content:center;padding:.875rem 1.75rem;border-radius:12px;border:2px solid transparent;background:var(--border);color:var(--text);text-decoration:none;font-weight:600;font-size:1rem;transition:all .2s ease;cursor:pointer}
    .page .btn-primary{background:var(--brand);color:#fff;border-color:var(--brand)}
    .page .btn-primary:hover{background:var(--brand-dark);border-color:var(--brand-dark);transform:translateY(-2px);box-shadow:0 8px 20px rgba(245,158,11,.3)}
    .page .btn-outline{background:transparent;color:var(--text);border-color:var(--border)}
    .page .btn-outline:hover{border-color:var(--text);background:var(--bg-alt)}
    .page .btn-hero{padding:1rem 2rem;font-size:1.0625rem}
    .page .btn-large{padding:1.125rem 2.25rem;font-size:1.0625rem}
    .page .btn:focus-visible{outline:3px solid var(--ring);outline-offset:2px}

    /* ===== Products ===== */
    .featured-products{max-width:1200px;margin:0 auto 5rem;padding:0 2rem}
    .product-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:2rem;margin-bottom:3rem}
    .product-card{background:#fff;border-radius:20px;overflow:hidden;border:1px solid var(--border);transition:all .3s}
    .product-card:hover{transform:translateY(-8px);box-shadow:0 20px 50px var(--shadow-lg)}
    .product-image{position:relative;overflow:hidden}
    .product-img{width:100%;height:280px;object-fit:cover;transition:transform .3s}
    .product-card:hover .product-img{transform:scale(1.05)}
    .product-badge{position:absolute;top:1rem;right:1rem;padding:.5rem 1rem;border-radius:50px;font-size:.8125rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
    .product-badge.bestseller{background:#10b981;color:#fff}
    .product-badge.new{background:var(--accent);color:#fff}
    .product-badge.limited{background:#f59e0b;color:#fff}
    .product-info{padding:1.75rem}
    .product-title{font-size:1.5rem;font-weight:700;color:var(--text);margin:0 0 .75rem}
    .product-desc{color:var(--text-muted);line-height:1.6;margin:0 0 1rem}
    .product-meta{display:flex;flex-wrap:wrap;gap:.75rem}
    .product-feature{font-size:.875rem;color:var(--text-muted);display:flex;align-items:center;gap:.25rem}
    .products-cta{text-align:center}

    /* ===== Trust ===== */
    .trust-section{background:var(--bg-alt);padding:4rem 0;margin-bottom:4rem}
    .trust-container{max-width:1200px;margin:0 auto;padding:0 2rem}
    .trust-badges{display:grid;grid-template-columns:repeat(4,1fr);gap:2rem}
    .trust-badge{text-align:center;padding:2rem 1.5rem;background:#fff;border-radius:16px;border:1px solid var(--border);transition:all .3s}
    .trust-badge:hover{transform:translateY(-4px);box-shadow:0 12px 30px var(--shadow)}
    .trust-icon{display:flex;align-items:center;justify-content:center;width:64px;height:64px;margin:0 auto 1.25rem;background:var(--brand-light);border-radius:16px;color:var(--brand)}
    .trust-badge h3{font-size:1.125rem;font-weight:700;color:var(--text);margin:0 0 .5rem}
    .trust-badge p{font-size:.9375rem;color:var(--text-muted);line-height:1.5;margin:0}

    /* ===== Delivery ===== */
    .delivery-section{max-width:1200px;margin:0 auto 5rem;padding:0 2rem}
    .delivery-content{display:grid;grid-template-columns:1.5fr 1fr;gap:3rem;align-items:start}
    .delivery-text h2{font-size:2.25rem;font-weight:800;color:var(--text);margin:0 0 1rem;letter-spacing:-.01em}
    .delivery-lead{font-size:1.125rem;color:var(--text-muted);line-height:1.7;margin:0 0 2rem}
    .delivery-features{display:flex;flex-direction:column;gap:1.5rem}
    .delivery-feature{display:flex;gap:1rem;align-items:flex-start}
    .delivery-feature svg{flex-shrink:0;color:var(--brand);margin-top:.25rem}
    .delivery-feature strong{display:block;font-weight:700;color:var(--text);margin-bottom:.25rem}
    .delivery-feature p{color:var(--text-muted);line-height:1.6;margin:0}
    .delivery-card{background:var(--brand-light);border:2px solid var(--brand);border-radius:16px;padding:2rem;text-align:center}
    .delivery-card.highlight{background:linear-gradient(135deg,var(--brand-light) 0%,#ffffff 100%)}
    .delivery-card-icon{font-size:3rem;margin-bottom:1rem}
    .delivery-card h4{font-size:1.25rem;font-weight:700;color:var(--text);margin:0 0 .5rem}
    .delivery-card p{color:var(--text-muted);line-height:1.6;margin:0}

    /* ===== Why ===== */
    .why-section{max-width:1200px;margin:0 auto 5rem;padding:0 2rem}
    .why-container{display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center}
    .why-img{width:100%;height:500px;object-fit:cover;border-radius:20px;box-shadow:0 20px 60px var(--shadow-lg)}
    .why-content h2{font-size:2.25rem;font-weight:800;color:var(--text);margin:0 0 1rem;letter-spacing:-.01em}
    .why-intro{font-size:1.0625rem;color:var(--text-muted);line-height:1.7;margin:0 0 2rem}
    .why-list{display:flex;flex-direction:column;gap:2rem}
    .why-item{display:flex;gap:1.5rem}
    .why-number{flex-shrink:0;width:48px;height:48px;display:flex;align-items:center;justify-content:center;background:var(--brand);color:#fff;border-radius:12px;font-weight:800;font-size:1.125rem}
    .why-text h3{font-size:1.25rem;font-weight:700;color:var(--text);margin:0 0 .5rem}
    .why-text p{color:var(--text-muted);line-height:1.6;margin:0}

    /* ===== How it works ===== */
    .how-section{background:var(--bg-alt);padding:4rem 0;margin-bottom:4rem}
    .steps-grid{max-width:1200px;margin:0 auto;padding:0 2rem;display:grid;grid-template-columns:repeat(3,1fr);gap:2.5rem}
    .step-card{background:#fff;padding:2.5rem 2rem;border-radius:20px;border:1px solid var(--border);text-align:center;transition:all .3s}
    .step-card:hover{transform:translateY(-8px);box-shadow:0 20px 50px var(--shadow-lg);border-color:var(--brand)}
    .step-icon{display:flex;align-items:center;justify-content:center;width:80px;height:80px;margin:0 auto 1.5rem;background:var(--brand-light);border-radius:20px;color:var(--brand)}
    .step-number{display:inline-block;background:var(--brand);color:#fff;padding:.5rem 1.25rem;border-radius:50px;font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:1rem}
    .step-card h3{font-size:1.375rem;font-weight:700;color:var(--text);margin:0 0 .75rem}
    .step-card p{color:var(--text-muted);line-height:1.6;margin:0}

    /* ===== Testimonials ===== */
    .testimonials-section{max-width:1200px;margin:0 auto 5rem;padding:0 2rem}
    .testimonials-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:2rem}
    .testimonial-card{background:#fff;padding:2rem;border-radius:20px;border:1px solid var(--border);transition:all .3s}
    .testimonial-card:hover{transform:translateY(-4px);box-shadow:0 12px 30px var(--shadow)}
    .testimonial-stars{font-size:1.125rem;margin-bottom:1rem}
    .testimonial-text{color:var(--text);font-size:1.0625rem;line-height:1.7;margin:0 0 1.5rem;font-style:italic}
    .testimonial-author{display:flex;flex-direction:column;gap:.25rem;padding-top:1rem;border-top:1px solid var(--border)}
    .testimonial-author strong{color:var(--text);font-weight:700}
    .testimonial-author span{color:var(--text-muted);font-size:.9375rem}

    /* ===== CTA ===== */
    .cta-section{background:linear-gradient(135deg,var(--brand) 0%,var(--brand-dark) 100%);padding:5rem 0;margin-bottom:4rem}
    .cta-container{max-width:900px;margin:0 auto;padding:0 2rem}
    .cta-content{text-align:center;color:#fff}
    .cta-content h2{font-size:clamp(2rem,4vw,3rem);font-weight:800;margin:0 0 1rem;letter-spacing:-.01em}
    .cta-subtitle{font-size:1.25rem;margin:0 0 2.5rem;opacity:.95;line-height:1.6}
    .cta-buttons{display:flex;gap:1.25rem;justify-content:center;flex-wrap:wrap;margin-bottom:1.5rem}
    .cta-section .btn-primary{background:#fff;color:var(--brand);border-color:#fff}
    .cta-section .btn-primary:hover{background:var(--bg-alt);transform:translateY(-2px);box-shadow:0 12px 30px rgba(0,0,0,.2)}
    .cta-section .btn-outline{background:transparent;color:#fff;border-color:#fff}
    .cta-section .btn-outline:hover{background:rgba(255,255,255,.1)}
    .cta-note{font-size:.9375rem;margin:0;opacity:.9}

    /* ===== High-contrast theme for content ===== */
    .page.hc{
        --bg:#0b0f14; --bg-alt:#111827; --text:#f1f5f9; --text-muted:#cbd5e1; --card:#1f2937;
        --brand:#fbbf24; --brand-dark:#f59e0b; --brand-light:#1f2937; --accent:#60a5fa; --border:#374155;
        --shadow:rgba(0,0,0,.3); --shadow-lg:rgba(0,0,0,.5); --ring:rgba(251,191,36,.45);
    }
    .page.hc .hero{background:linear-gradient(135deg,#1f2937 0%,#111827 50%,#0b0f14 100%)}
    .page.hc .product-card,
    .page.hc .trust-badge,
    .page.hc .step-card,
    .page.hc .testimonial-card,
    .page.hc .delivery-card,
    .page.hc .stat-card{background:var(--card);border-color:var(--border)}
    .page.hc .feature-chip{background:var(--card)}
    .page.hc .hero__img,.page.hc .product-img,.page.hc .why-img{opacity:.9}

    /* ===== Responsive ===== */
    @media (max-width:1024px){
        .hero__content{gap:3rem}
        .product-grid,.trust-badges{grid-template-columns:repeat(2,1fr)}
        .steps-grid,.testimonials-grid{grid-template-columns:repeat(2,1fr)}
    }
    @media (max-width:768px){
        .hero{padding:3rem 0 4rem}
        .hero__content{grid-template-columns:1fr;gap:2.5rem}
        .hero__text h1{font-size:2.25rem}
        .hero__img{height:400px}
        .hero__overlay{position:static;margin-top:1.5rem;justify-content:center;pointer-events:none}
        .product-grid,.trust-badges,.steps-grid,.testimonials-grid{grid-template-columns:1fr}
        .delivery-content,.why-container{grid-template-columns:1fr;gap:2rem}
        .why-img{height:350px}
        .trust-section,.how-section,.cta-section{padding:3rem 0}
        .featured-products,.delivery-section,.why-section,.testimonials-section{margin-bottom:3rem}
        .hero__cta{flex-direction:column;align-items:stretch}
        .hero__cta .btn{width:100%}
        .cta-buttons{flex-direction:column;align-items:stretch}
    }
    @media (max-width:480px){
        .hero__text h1{font-size:1.875rem}
        .hero__lead{font-size:1rem}
        .feature-chip{font-size:.8125rem;padding:.625rem 1rem}
        .product-img,.hero__img{height:300px}
        .section-header h2{font-size:1.75rem}
        .delivery-text h2,.why-content h2{font-size:1.75rem}
    }
</style>
