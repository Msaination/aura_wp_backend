<?php
/**
 * Front page template for the Aura Spa homepage.
 */

$theme_assets = get_stylesheet_directory_uri() . '/assets';
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('spa-shell'); ?>>
<?php wp_body_open(); ?>

<div class="spa-shell">
    <header class="spa-header">
        <div class="spa-container spa-header-inner">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="spa-brand" aria-label="Aura Spa home">
                <img src="<?php echo esc_url($theme_assets . '/images/AuraLogo.png'); ?>" alt="Aura Spa" class="spa-brand-logo" />
            </a>

            <nav class="spa-nav" aria-label="Main navigation">
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#gift-vouchers">Shop</a></li>
                    <li><a href="/book">Book Appointment</a></li>
                    <li><a href="#gift-vouchers">Gift Cards</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </nav>

            <a href="/book" class="spa-button spa-button-primary spa-button-small">Book Appointment</a>
        </div>
    </header>

    <main>
        <section class="spa-hero" style="background-image: url('<?php echo esc_url($theme_assets . '/images/AuraBG.jpg'); ?>');">
            <div class="spa-light-beam" aria-hidden="true"></div>
            <div class="spa-smoke-layer" aria-hidden="true">
                <span class="spa-smoke smoke-main"></span>
                <span class="spa-smoke smoke-secondary"></span>
                <span class="spa-smoke smoke-tertiary"></span>
            </div>

            <div class="spa-container spa-hero-grid">
                <div class="spa-hero-copy">
                    <p class="spa-eyebrow"># Your Perfect Sanctuary Awaits</p>
                    <h1>Book Your Next Treatment</h1>
                    <p class="spa-hero-subheadline">
                        Relax, recharge, and restore with a luxurious wellness experience designed around you.
                    </p>
                    <div class="spa-button-row">
                        <a href="/book" class="spa-button spa-button-primary">Book Appointment</a>
                        <a href="#gift-vouchers" class="spa-button spa-button-secondary">Buy a Gift Voucher</a>
                        <a href="<?php echo esc_url($theme_assets . '/documents/AuraPolicyv1.1.0.pdf'); ?>" class="spa-button spa-button-policy" target="_blank" rel="noreferrer">Aura Etiquettes and Policy</a>
                    </div>
                </div>

                <div class="spa-hero-visual">
                    <img src="<?php echo esc_url($theme_assets . '/images/AuraBG.jpg'); ?>" alt="Book Your Next Treatment" class="spa-hero-image" />
                    <div class="spa-floating-badge">
                        <strong>Luxury spa treatments</strong>
                        <span>Book online in minutes</span>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="spa-section">
            <div class="spa-container spa-split">
                <div class="spa-story-copy">
                    <p class="spa-eyebrow">The Aura Experience</p>
                    <h2>Slow down. Settle in. Feel restored.</h2>
                    <p>
                        At Aura Spa, every visit is designed to slow the pace, awaken the senses, and restore balance.
                    </p>
                    <p>
                        From the moment you arrive, you are welcomed into a calm, refined environment where thoughtful rituals, expert care, and sensory wellness come together.
                    </p>
                </div>

                <div class="spa-story-media">
                    <img src="<?php echo esc_url($theme_assets . '/images/AuraCerum.jpg'); ?>" alt="The Aura Experience" />
                </div>
            </div>
        </section>

        <section class="spa-section spa-section-soft" id="therapy">
            <div class="spa-container">
                <div class="spa-section-heading">
                    <p class="spa-eyebrow">Sensory Wellness Therapy</p>
                    <h2>Every treatment begins with your preferred aroma.</h2>
                    <p>Every treatment begins with your preferred aroma from our signature collection of therapeutic oils.</p>
                </div>

                <div class="spa-grid-3">
                    <article class="spa-feature-card">
                        <div class="spa-feature-media">
                            <img src="<?php echo esc_url($theme_assets . '/images/47.jpg'); ?>" alt="Atmosphere" />
                        </div>
                        <div class="spa-feature-body">
                            <h3>Atmosphere</h3>
                            <p>A tranquil environment designed to quiet the senses.</p>
                        </div>
                    </article>

                    <article class="spa-feature-card">
                        <div class="spa-feature-media">
                            <img src="<?php echo esc_url($theme_assets . '/images/285.jpg'); ?>" alt="Care" />
                        </div>
                        <div class="spa-feature-body">
                            <h3>Care</h3>
                            <p>Personalised touch, mindful rituals, and exceptional service.</p>
                        </div>
                    </article>

                    <article class="spa-feature-card">
                        <div class="spa-feature-media">
                            <img src="<?php echo esc_url($theme_assets . '/images/41.jpg'); ?>" alt="Outcome" />
                        </div>
                        <div class="spa-feature-body">
                            <h3>Outcome</h3>
                            <p>Refreshed, uplifted, and ready for your next reset.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section id="services" class="spa-section">
            <div class="spa-container">
                <div class="spa-section-heading">
                    <p class="spa-eyebrow">Wellness rituals</p>
                    <h2>Explore Our Services</h2>
                </div>

                <div class="spa-service-grid">
                    <article class="spa-service-card">
                        <div class="spa-service-media"><img src="<?php echo esc_url($theme_assets . '/images/47.jpg'); ?>" alt="Massage" /></div>
                        <div class="spa-service-body">
                            <h3>Massage</h3>
                            <p>Whole-body rituals for tension release, recovery, and deep calm.</p>
                            <a href="/book" class="spa-inline-link">View Services</a>
                        </div>
                    </article>

                    <article class="spa-service-card">
                        <div class="spa-service-media"><img src="<?php echo esc_url($theme_assets . '/images/285.jpg'); ?>" alt="Facials" /></div>
                        <div class="spa-service-body">
                            <h3>Facials</h3>
                            <p>Targeted skin rituals that brighten, smooth, and renew.</p>
                            <a href="/book" class="spa-inline-link">View Services</a>
                        </div>
                    </article>

                    <article class="spa-service-card">
                        <div class="spa-service-media"><img src="<?php echo esc_url($theme_assets . '/images/43.jpg'); ?>" alt="Waxing" /></div>
                        <div class="spa-service-body">
                            <h3>Waxing</h3>
                            <p>Tailored grooming and polish for a confident, refined finish.</p>
                            <a href="/book" class="spa-inline-link">View Services</a>
                        </div>
                    </article>

                    <article class="spa-service-card">
                        <div class="spa-service-media"><img src="<?php echo esc_url($theme_assets . '/images/41.jpg'); ?>" alt="Nails" /></div>
                        <div class="spa-service-body">
                            <h3>Nails</h3>
                            <p>Precision styling, finishing touches, and a glossy elevate.</p>
                            <a href="/book" class="spa-inline-link">View Services</a>
                        </div>
                    </article>

                    <article class="spa-service-card">
                        <div class="spa-service-media"><img src="<?php echo esc_url($theme_assets . '/images/24.jpg'); ?>" alt="Mini Me Collection" /></div>
                        <div class="spa-service-body">
                            <h3>Mini Me Collection</h3>
                            <p>Little moments of luxury designed for children and families.</p>
                            <a href="/book" class="spa-inline-link">View Services</a>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section id="treatments" class="spa-section spa-section-soft">
            <div class="spa-container">
                <div class="spa-section-heading">
                    <p class="spa-eyebrow">Most loved</p>
                    <h2>Popular Treatments</h2>
                </div>

                <div class="spa-treatment-grid">
                    <article class="spa-treatment-card">
                        <div class="spa-treatment-meta">
                            <span class="spa-treatment-topline">60 min</span>
                            <span class="spa-treatment-price">From R890</span>
                        </div>
                        <h3>Swedish Massage</h3>
                        <p>classic full-body relaxation</p>
                        <a href="/book" class="spa-button spa-button-secondary">Book Now</a>
                    </article>

                    <article class="spa-treatment-card">
                        <div class="spa-treatment-meta">
                            <span class="spa-treatment-topline">45–60 min</span>
                            <span class="spa-treatment-price">From R620</span>
                        </div>
                        <h3>Dermaplaning</h3>
                        <p>smoothing and skin renewal</p>
                        <a href="/book" class="spa-button spa-button-secondary">Book Now</a>
                    </article>

                    <article class="spa-treatment-card">
                        <div class="spa-treatment-meta">
                            <span class="spa-treatment-topline">20 min</span>
                            <span class="spa-treatment-price">From R420</span>
                        </div>
                        <h3>Brow + Lip</h3>
                        <p>quick polish and refinement</p>
                        <a href="/book" class="spa-button spa-button-secondary">Book Now</a>
                    </article>

                    <article class="spa-treatment-card">
                        <div class="spa-treatment-meta">
                            <span class="spa-treatment-topline">45 min</span>
                            <span class="spa-treatment-price">From R480</span>
                        </div>
                        <h3>Deluxe Mani</h3>
                        <p>complete polished manicure experience</p>
                        <a href="/book" class="spa-button spa-button-secondary">Book Now</a>
                    </article>
                </div>
            </div>
        </section>

        <section id="why-us" class="spa-section spa-benefits-panel">
            <div class="spa-container">
                <div class="spa-section-heading spa-section-heading-center">
                    <p class="spa-eyebrow">Why Aura</p>
                    <h2>Wellness built for real life</h2>
                </div>

                <div class="spa-benefit-grid">
                    <div class="spa-benefit-item"><span class="spa-check" aria-hidden="true">✓</span><p>Personalised treatment guidance</p></div>
                    <div class="spa-benefit-item"><span class="spa-check" aria-hidden="true">✓</span><p>Thoughtful luxury rituals</p></div>
                    <div class="spa-benefit-item"><span class="spa-check" aria-hidden="true">✓</span><p>Calm, restorative ambience</p></div>
                    <div class="spa-benefit-item"><span class="spa-check" aria-hidden="true">✓</span><p>Premium self-care for every day</p></div>
                </div>
            </div>
        </section>

        <section id="gift-vouchers" class="spa-section spa-section-soft">
            <div class="spa-container spa-gift-grid">
                <div class="spa-gift-visual">
                    <img src="<?php echo esc_url($theme_assets . '/images/92.jpg'); ?>" alt="Thoughtful gifting" />
                </div>
                <div class="spa-gift-copy">
                    <p class="spa-eyebrow">Thoughtful gifting</p>
                    <h2>Give the gift of restoration.</h2>
                    <p>
                        Share a ritual of calm with a beautifully curated gift voucher designed for indulgence and renewal.
                    </p>
                    <a href="/book" class="spa-button spa-button-primary">Purchase Gift Card</a>
                </div>
            </div>
        </section>

        <section id="offers" class="spa-section">
            <div class="spa-container">
                <div class="spa-section-heading">
                    <p class="spa-eyebrow">Special offers</p>
                    <h2>Choose your next ritual</h2>
                </div>

                <div class="spa-offer-list">
                    <div class="spa-offer-item"><span class="spa-offer-index">01</span><p>Signature wellness packages</p></div>
                    <div class="spa-offer-item"><span class="spa-offer-index">02</span><p>Skin renewal bundles</p></div>
                    <div class="spa-offer-item"><span class="spa-offer-index">03</span><p>Gentle self-care rituals</p></div>
                    <div class="spa-offer-item"><span class="spa-offer-index">04</span><p>Thoughtful gift experiences</p></div>
                </div>
            </div>
        </section>

        <section id="wellbeing" class="spa-section spa-section-soft">
            <div class="spa-container spa-corporate-grid">
                <div>
                    <p class="spa-eyebrow">Corporate wellbeing</p>
                    <h2>Restore focus. Rebalance teams.</h2>
                    <p>
                        Curated wellness experiences for teams, corporate retreats, and moments of intentional pause.
                    </p>
                </div>

                <div class="spa-corporate-list">
                    <div class="spa-corporate-item"><span aria-hidden="true">•</span><p>Executive wellness experiences</p></div>
                    <div class="spa-corporate-item"><span aria-hidden="true">•</span><p>Shared reset rituals for teams</p></div>
                    <div class="spa-corporate-item"><span aria-hidden="true">•</span><p>Touchpoints built for longevity</p></div>
                </div>
            </div>
        </section>

        <section id="testimonials" class="spa-section">
            <div class="spa-container">
                <div class="spa-section-heading spa-section-heading-center">
                    <p class="spa-eyebrow">What Our Clients Say</p>
                    <h2>Real experiences. Real calm.</h2>
                </div>

                <div class="spa-testimonial-grid">
                    <article class="spa-testimonial-card">
                        <div class="spa-quote-mark" aria-hidden="true">“</div>
                        <p>Every detail felt intentional. The atmosphere was peaceful and the treatment was deeply restorative.</p>
                        <div class="spa-testimonial-author"><span>Clementine Mariba</span></div>
                    </article>

                    <article class="spa-testimonial-card">
                        <div class="spa-quote-mark" aria-hidden="true">“</div>
                        <p>I left feeling lighter, calmer, and completely renewed. This is the kind of care that stays with you.</p>
                        <div class="spa-testimonial-author"><span>Violet Monareng</span></div>
                    </article>

                    <article class="spa-testimonial-card">
                        <div class="spa-quote-mark" aria-hidden="true">“</div>
                        <p>Beautiful space, thoughtful service, and the most calming experience I have had in a long time.</p>
                        <div class="spa-testimonial-author"><span>Muzi Omphile</span></div>
                    </article>
                </div>
            </div>
        </section>

        <section class="spa-section spa-final-cta">
            <div class="spa-container">
                <div class="spa-cta-banner">
                    <div>
                        <p class="spa-eyebrow">Your next ritual starts here</p>
                        <h2>Ready to feel restored?</h2>
                    </div>
                    <p>Book a treatment or surprise someone with a wellness gift.</p>
                    <a href="/book" class="spa-button spa-button-primary">Book Your Visit</a>
                </div>
            </div>
        </section>
    </main>

    <footer id="contact" class="spa-footer">
        <div class="spa-container spa-footer-grid">
            <div class="spa-footer-brand-block">
                <div class="spa-brand spa-brand-footer">
                    <img src="<?php echo esc_url($theme_assets . '/images/AuraLogo.png'); ?>" alt="Aura Spa" class="spa-brand-logo spa-brand-logo-footer" />
                </div>
                <p>Luxury wellness experiences designed to restore, uplift, and renew.</p>
                <a href="https://www.google.com/maps/search/?api=1&query=King%27s+Palace+Hotel+Donkerhoek+Road+Rustenburg"
                   target="_blank"
                   rel="noreferrer"
                   class="spa-footer-address"
                   aria-label="Open Aura Spa address in Google Maps"
                >
                    <svg class="spa-contact-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s6-5.686 6-11a6 6 0 1 0-12 0c0 5.314 6 11 6 11Zm0-8.5A2.5 2.5 0 1 1 12 7a2.5 2.5 0 0 1 0 5.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>King's Palace Hotel, Donkerhoek Road, Rustenburg</span>
                </a>
            </div>

            <div>
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#gift-vouchers">Shop</a></li>
                    <li><a href="/book">Book Appointment</a></li>
                    <li><a href="#about">About</a></li>
                </ul>
            </div>

            <div>
                <h3>Contact</h3>
                <ul class="spa-contact-list">
                    <li>
                        <a href="mailto:hello@auraretreat.co.za">
                            <span class="spa-contact-icon"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M3 6.75A2.75 2.75 0 0 1 5.75 4h12.5A2.75 2.75 0 0 1 21 6.75v10.5A2.75 2.75 0 0 1 18.25 20H5.75A2.75 2.75 0 0 1 3 17.25V6.75Zm2.2-.75 6.8 5.18 6.8-5.18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                            <span>hello@auraretreat.co.za</span>
                        </a>
                    </li>
                    <li>
                        <a href="tel:+27671832446">
                            <span class="spa-contact-icon"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M7 4.75h2.3a1.3 1.3 0 0 1 1.26 1.01l.56 2.38a1.3 1.3 0 0 1-.8 1.53l-1.26.5a12.42 12.42 0 0 0 6.58 6.58l.5-1.27a1.3 1.3 0 0 1 1.52-.8l2.39.56A1.3 1.3 0 0 1 20.25 15v2.3A1.95 1.95 0 0 1 18.3 19A15.3 15.3 0 0 1 5 5.7 1.95 1.95 0 0 1 7 4.75Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                            <span>+27 67 183 2446</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://wa.me/27671832446" target="_blank" rel="noreferrer">
                            <span class="spa-contact-icon"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M20.25 11.98A8.25 8.25 0 0 1 4.93 16.9L4 20l3.12-1.02A8.25 8.25 0 1 1 20.25 11.98Zm-5.4 1.66c-.16-.08-1.1-.54-1.27-.6-.17-.07-.3-.08-.43.08-.12.15-.48.6-.59.72-.11.12-.22.13-.4.05-.18-.08-.76-.28-1.44-.9-.53-.47-.9-1.05-.99-1.22-.1-.17-.01-.27.08-.35.08-.08.17-.2.25-.3.08-.1.11-.17.17-.28.06-.12.03-.22-.02-.3-.05-.08-.43-1.04-.6-1.42-.15-.35-.31-.3-.43-.3h-.37c-.12 0-.32.05-.49.24-.17.18-.64.62-.64 1.52 0 .9.66 1.76.75 1.88.09.12 1.28 1.96 3.12 2.68.44.19.78.3 1.05.39.45.14.85.12 1.17.07.35-.05 1.1-.45 1.25-.88.16-.44.16-.81.11-.89-.05-.08-.18-.13-.34-.23Z" fill="currentColor"/></svg></span>
                            <span>WhatsApp: +27 671 832 446</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div>
                <h3>Follow Us</h3>
                <ul>
                    <li>
                        <span class="spa-contact-icon"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M7 2.75h10A4.25 4.25 0 0 1 21.25 7v10A4.25 4.25 0 0 1 17 21.25H7A4.25 4.25 0 0 1 2.75 17V7A4.25 4.25 0 0 1 7 2.75Zm4.5 5.5a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm6.25-2.5a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <span>@aura_spa_rustenburg</span>
                    </li>
                </ul>
            </div>
        </div>
    </footer>

    <a class="spa-whatsapp-chat" href="https://wa.me/27671832446" target="_blank" rel="noreferrer" aria-label="Chat on WhatsApp">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M20.25 11.98A8.25 8.25 0 0 1 4.93 16.9L4 20l3.12-1.02A8.25 8.25 0 1 1 20.25 11.98Zm-5.4 1.66c-.16-.08-1.1-.54-1.27-.6-.17-.07-.3-.08-.43.08-.12.15-.48.6-.59.72-.11.12-.22.13-.4.05-.18-.08-.76-.28-1.44-.9-.53-.47-.9-1.05-.99-1.22-.1-.17-.01-.27.08-.35.08-.08.17-.2.25-.3.08-.1.11-.17.17-.28.06-.12.03-.22-.02-.3-.05-.08-.43-1.04-.6-1.42-.15-.35-.31-.3-.43-.3h-.37c-.12 0-.32.05-.49.24-.17.18-.64.62-.64 1.52 0 .9.66 1.76.75 1.88.09.12 1.28 1.96 3.12 2.68.44.19.78.3 1.05.39.45.14.85.12 1.17.07.35-.05 1.1-.45 1.25-.88.16-.44.16-.81.11-.89-.05-.08-.18-.13-.34-.23Z" fill="currentColor"/>
        </svg>
    </a>
</div>

<?php wp_footer(); ?>
</body>
</html>
