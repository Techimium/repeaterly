<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Repeaterly Overview Admin Screen.
 *
 * Static markup only – no dynamic logic – to keep things fast
 * and compatible with WordPress.org guidelines.
 *
 * @package Repeaterly
 */
?>

<div class="repeaterly-overview wrap">
    <div class="repeaterly-overview__layout">
        <div class="repeaterly-overview__hero">
            <div class="repeaterly-overview__badge-row">
                <span class="repeaterly-badge repeaterly-badge--free"><?php esc_html_e( 'Free Features', 'repeaterly' ); ?></span>
                <span class="repeaterly-badge repeaterly-badge--pro"><?php esc_html_e( 'Pro Upgrade Available', 'repeaterly' ); ?></span>
            </div>

            <h1 class="repeaterly-overview__title">
                <?php esc_html_e( 'Supercharge Elementor with ACF Repeater & Relationship Fields', 'repeaterly' ); ?>
            </h1>

            <p class="repeaterly-overview__subtitle">
                <?php esc_html_e( "Use ACF repeater, nested repeater and relationship fields, plus Dynamic Tags – all inside Elementor, even without Elementor Pro.", 'repeaterly' ); ?>
            </p>

            <ul class="repeaterly-overview__highlights">
                <li><?php esc_html_e( 'No Elementor Pro required – works with free Elementor.', 'repeaterly' ); ?></li>
                <li><?php esc_html_e( 'Dynamic Tags for any Elementor widget (ACF fields, subfields, and post data).', 'repeaterly' ); ?></li>
                <li><?php esc_html_e( 'ACF Repeater & Relationship Loop Builders (Pro) for custom grids and carousels.', 'repeaterly' ); ?></li>
                <li><?php esc_html_e( 'Optimized for performance – avoid duplicated sections and bloated databases.', 'repeaterly' ); ?></li>
            </ul>

            <div class="repeaterly-overview__cta-row">
                <a class="button button-primary button-hero repeaterly-overview__cta-primary" href="<?php echo esc_url( 'https://repeaterly.com/' ); ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e( 'Get Repeaterly Pro', 'repeaterly' ); ?>
                </a>
                <a class="button button-hero repeaterly-overview__cta-secondary" href="<?php echo esc_url( 'https://repeaterly.com/documentation' ); ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e( 'View Plugin Documentation', 'repeaterly' ); ?>
                </a>
            </div>

            <p class="repeaterly-overview__meta">
                <?php esc_html_e( 'Tested with the latest WordPress and Elementor versions. GPLv2 or later.', 'repeaterly' ); ?>
            </p>
        </div>

        <div class="repeaterly-overview__video-card">
            <h2 class="repeaterly-section-title">
                <?php esc_html_e( 'Watch: Repeaterly Overview & Setup', 'repeaterly' ); ?>
            </h2>
            <p class="repeaterly-section-subtitle">
                <?php esc_html_e( 'Prefer to follow along visually? This quick video walks you through using ACF Repeater and Relationship fields with Elementor using Repeaterly.', 'repeaterly' ); ?>
            </p>

            <div class="repeaterly-video-wrapper">
                <iframe
                    width="560"
                    height="315"
                    src="https://www.youtube.com/embed/QU9MhjB3cWs"
                    title="<?php esc_attr_e( 'Repeaterly – ACF Repeater & Relationship Fields for Elementor', 'repeaterly' ); ?>"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen
                ></iframe>
            </div>
        </div>
    </div>

    <hr class="repeaterly-overview__divider" />

    <div class="repeaterly-overview__sections">
        <div class="repeaterly-card">
            <h2 class="repeaterly-section-title">
                <?php esc_html_e( 'Key Free Features', 'repeaterly' ); ?>
            </h2>
            <ul class="repeaterly-feature-list">
                <li>
                    <strong><?php esc_html_e( 'Dynamic Tags for Any Widget', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Use ACF fields, subfields and post data inside any Elementor widget – a capability normally locked behind Elementor Pro.', 'repeaterly' ); ?></span>
                </li>
                <li>
                    <strong><?php esc_html_e( 'ACF Repeater-Based Widgets', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Build dynamic icon lists, accordions and galleries backed by ACF repeater fields without writing PHP.', 'repeaterly' ); ?></span>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Performance-Oriented Workflow', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Create a single layout, then let ACF data populate it – avoid duplicate Elementor sections and keep your database lean.', 'repeaterly' ); ?></span>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Beginner Friendly', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Install, connect your ACF fields, and drag-and-drop your way to dynamic layouts – no custom code required.', 'repeaterly' ); ?></span>
                </li>
            </ul>
        </div>

        <div class="repeaterly-card repeaterly-card--pro">
            <div class="repeaterly-card__header">
                <h2 class="repeaterly-section-title">
                    <?php esc_html_e( 'Unlock Repeaterly Pro', 'repeaterly' ); ?>
                </h2>
                <span class="repeaterly-badge repeaterly-badge--pro">
                    <?php esc_html_e( 'Recommended for complex websites', 'repeaterly' ); ?>
                </span>
            </div>

            <p class="repeaterly-section-subtitle">
                <?php esc_html_e( 'Go beyond basic dynamic content with advanced Loop Builders, nested repeaters and pagination – ideal for serious, content‑heavy sites.', 'repeaterly' ); ?>
            </p>

            <ul class="repeaterly-feature-list repeaterly-feature-list--columns">
                <li>
                    <strong><?php esc_html_e( 'ACF Repeater Loop Grid & Carousel', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Design fully custom grids and carousels using Elementor templates powered by ACF repeater fields.', 'repeaterly' ); ?></span>
                </li>
                <li>
                    <strong><?php esc_html_e( 'ACF Relationship Loop Builder', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Showcase related posts, products or portfolios in grids and carousels based on ACF Relationship fields.', 'repeaterly' ); ?></span>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Nested Repeater Support', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Effortlessly render nested ACF repeater fields for complex layouts such as multi-level pricing tables or feature groups.', 'repeaterly' ); ?></span>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Post ID Targeting & Global Content', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Fetch repeater or relationship data from any post ID to build reusable global sections across your site.', 'repeaterly' ); ?></span>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Built-in Pagination & Load More', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Handle large datasets gracefully with pagination or “load more” controls for your repeater and relationship loops.', 'repeaterly' ); ?></span>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Use Any Elementor Widget in Loops', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Mix and match your favorite widgets inside loop templates with full Dynamic Tags support.', 'repeaterly' ); ?></span>
                </li>
            </ul>

            <div class="repeaterly-overview__cta-row">
                <a class="button button-primary repeaterly-overview__cta-primary" href="<?php echo esc_url( 'https://repeaterly.com/' ); ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e( 'Upgrade to Repeaterly Pro', 'repeaterly' ); ?>
                </a>
                <a class="button repeaterly-overview__cta-secondary" href="<?php echo esc_url( 'https://repeaterly.com/#pricing' ); ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e( 'View Pricing & Plans', 'repeaterly' ); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="repeaterly-overview__sections">
        <div class="repeaterly-card">
            <h2 class="repeaterly-section-title">
                <?php esc_html_e( 'What’s New in 2.x', 'repeaterly' ); ?>
            </h2>
            <ul class="repeaterly-feature-list">
                <li>
                    <strong><?php esc_html_e( 'Dynamic Tags Upgrade', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Dynamic Tags now work with all Elementor widgets, including ACF fields, subfields and post data – no Elementor Pro required.', 'repeaterly' ); ?></span>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Relationship Field Loop Builder (Pro)', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Create advanced grids and carousels powered by ACF Relationship fields, ideal for related posts or product listings.', 'repeaterly' ); ?></span>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Improved ACF Integration', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Better compatibility, more field types supported and smoother workflows inside Elementor.', 'repeaterly' ); ?></span>
                </li>
            </ul>
        </div>

        <div class="repeaterly-card">
            <h2 class="repeaterly-section-title">
                <?php esc_html_e( 'Getting Started in 3 Steps', 'repeaterly' ); ?>
            </h2>
            <ol class="repeaterly-steps">
                <li>
                    <strong><?php esc_html_e( 'Create Your ACF Fields', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Add ACF Repeater or Relationship fields (and any nested subfields) to the post types you want to enhance.', 'repeaterly' ); ?></span>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Design in Elementor', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Use Repeaterly widgets or Dynamic Tags to bind your layout to ACF data instead of hardcoded content.', 'repeaterly' ); ?></span>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Scale Content Without Cloning Sections', 'repeaterly' ); ?></strong>
                    <span><?php esc_html_e( 'Add or edit items in ACF, and Repeaterly will automatically update the layout across your site.', 'repeaterly' ); ?></span>
                </li>
            </ol>
        </div>
    </div>
</div>