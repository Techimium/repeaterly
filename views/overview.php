<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Repeaterly Overview Admin Screen.
 *
 * Uses class-based div/span markup instead of h1/h2/p/ul so that
 * wp-admin core styles never interfere with the page design.
 * Heading semantics are preserved via role="heading" + aria-level.
 *
 * @package Repeaterly
 */

$repeaterly_is_pro = class_exists( 'Repeaterly_Pro' );
?>

<div class="repeaterly-overview wrap">

	<!-- ============ HERO ============ -->
	<div class="rply-hero">
		<div class="rply-hero__content">
			<div class="rply-hero__badges">
				<span class="rply-chip rply-chip--glass">
					<?php esc_html_e( 'Works with Free Elementor', 'repeaterly' ); ?>
				</span>
				<?php if ( $repeaterly_is_pro ) : ?>
					<span class="rply-chip rply-chip--success">
						<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg>
						<?php esc_html_e( 'Pro Active', 'repeaterly' ); ?>
					</span>
				<?php else : ?>
					<span class="rply-chip rply-chip--pro">
						<?php esc_html_e( 'Pro Available', 'repeaterly' ); ?>
					</span>
				<?php endif; ?>
			</div>

			<div class="rply-hero__title" role="heading" aria-level="1">
				<?php esc_html_e( 'Turn ACF Repeater, Flexible Content & Relationship Fields into Elementor Layouts', 'repeaterly' ); ?>
			</div>

			<div class="rply-hero__subtitle">
				<?php esc_html_e( 'Build dynamic grids, carousels, tabs and modular pages from ACF Repeater, Nested Repeater, Flexible Content and Relationship fields – powered by Dynamic Tags on every Elementor widget. No Elementor Pro. No code.', 'repeaterly' ); ?>
			</div>

			<div class="rply-hero__cta">
				<?php if ( $repeaterly_is_pro ) : ?>
					<a class="rply-btn rply-btn--primary" href="<?php echo esc_url( 'https://repeaterly.com/documentation' ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Read the Documentation', 'repeaterly' ); ?>
						<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M11 4l-1.1 1.1L13.7 9H3v2h10.7l-3.8 3.9L11 16l6-6z"/></svg>
					</a>
				<?php else : ?>
					<a class="rply-btn rply-btn--primary" href="<?php echo esc_url( 'https://repeaterly.com/' ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Upgrade to Pro', 'repeaterly' ); ?>
						<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M11 4l-1.1 1.1L13.7 9H3v2h10.7l-3.8 3.9L11 16l6-6z"/></svg>
					</a>
				<?php endif; ?>
				<?php if ( $repeaterly_is_pro ) : ?>
					<a class="rply-btn rply-btn--ghost" href="<?php echo esc_url( 'https://repeaterly.com/contact/' ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Contact Support', 'repeaterly' ); ?>
					</a>
				<?php else : ?>
					<a class="rply-btn rply-btn--ghost" href="<?php echo esc_url( 'https://repeaterly.com/documentation' ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'View Documentation', 'repeaterly' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="rply-hero__media">
			<div class="rply-video">
				<iframe
					src="https://www.youtube.com/embed/QU9MhjB3cWs"
					title="<?php esc_attr_e( 'Repeaterly – ACF Repeater & Relationship Fields for Elementor', 'repeaterly' ); ?>"
					allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
					referrerpolicy="strict-origin-when-cross-origin"
					allowfullscreen
					loading="lazy"
				></iframe>
			</div>
			<div class="rply-hero__media-caption">
				<?php esc_html_e( 'Watch the 2-minute overview & setup', 'repeaterly' ); ?>
			</div>
		</div>

		<div class="rply-hero__stats">
			<div class="rply-hero__stat">
				<span class="rply-hero__stat-value"><?php esc_html_e( '1 layout', 'repeaterly' ); ?></span>
				<span class="rply-hero__stat-label"><?php esc_html_e( 'unlimited items – no duplicated sections', 'repeaterly' ); ?></span>
			</div>
			<div class="rply-hero__stat">
				<span class="rply-hero__stat-value"><?php esc_html_e( '0 lines of code', 'repeaterly' ); ?></span>
				<span class="rply-hero__stat-label"><?php esc_html_e( 'connect fields visually inside Elementor', 'repeaterly' ); ?></span>
			</div>
			<div class="rply-hero__stat">
				<span class="rply-hero__stat-value"><?php esc_html_e( 'Any widget', 'repeaterly' ); ?></span>
				<span class="rply-hero__stat-label"><?php esc_html_e( 'Dynamic Tags work everywhere', 'repeaterly' ); ?></span>
			</div>
		</div>
	</div>

	<!-- ============ GETTING STARTED ============ -->
	<div class="rply-section">
		<div class="rply-section__head">
			<div class="rply-section__title" role="heading" aria-level="2">
				<?php esc_html_e( 'Up and running in 3 steps', 'repeaterly' ); ?>
			</div>
			<div class="rply-section__desc">
				<?php esc_html_e( 'From plain ACF fields to a live dynamic layout in minutes.', 'repeaterly' ); ?>
			</div>
		</div>

		<div class="rply-steps">
			<div class="rply-step">
				<span class="rply-step__num">1</span>
				<div class="rply-step__title" role="heading" aria-level="3"><?php esc_html_e( 'Create your ACF fields', 'repeaterly' ); ?></div>
				<div class="rply-step__desc"><?php esc_html_e( 'Add Repeater or Relationship fields (nested subfields welcome) to any post type.', 'repeaterly' ); ?></div>
			</div>
			<div class="rply-step">
				<span class="rply-step__num">2</span>
				<div class="rply-step__title" role="heading" aria-level="3"><?php esc_html_e( 'Design in Elementor', 'repeaterly' ); ?></div>
				<div class="rply-step__desc"><?php esc_html_e( 'Drop in a Repeaterly widget or bind any widget to your data with Dynamic Tags.', 'repeaterly' ); ?></div>
			</div>
			<div class="rply-step">
				<span class="rply-step__num">3</span>
				<div class="rply-step__title" role="heading" aria-level="3"><?php esc_html_e( 'Scale without cloning', 'repeaterly' ); ?></div>
				<div class="rply-step__desc"><?php esc_html_e( 'Add or edit items in ACF – your layout updates everywhere, automatically.', 'repeaterly' ); ?></div>
			</div>
		</div>
	</div>

	<!-- ============ FREE FEATURES ============ -->
	<div class="rply-section">
		<div class="rply-section__head">
			<div class="rply-section__title" role="heading" aria-level="2">
				<?php esc_html_e( 'Everything included for free', 'repeaterly' ); ?>
			</div>
			<div class="rply-section__desc">
				<?php esc_html_e( 'Capabilities normally locked behind Elementor Pro – yours at no cost.', 'repeaterly' ); ?>
			</div>
		</div>

		<div class="rply-grid rply-grid--4">
			<div class="rply-feature">
				<span class="rply-feature__icon rply-feature__icon--indigo">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.6 11 13 3.4a2 2 0 0 0-1.4-.6H5a2 2 0 0 0-2 2v6.6c0 .5.2 1 .6 1.4l7.6 7.6a2 2 0 0 0 2.8 0l6.6-6.6a2 2 0 0 0 0-2.8zM7.5 9A1.5 1.5 0 1 1 9 7.5 1.5 1.5 0 0 1 7.5 9z"/></svg>
				</span>
				<div class="rply-feature__title" role="heading" aria-level="3"><?php esc_html_e( 'Dynamic Tags for any widget', 'repeaterly' ); ?></div>
				<div class="rply-feature__desc"><?php esc_html_e( 'Use ACF fields, subfields and post data inside every Elementor widget.', 'repeaterly' ); ?></div>
			</div>
			<div class="rply-feature">
				<span class="rply-feature__icon rply-feature__icon--teal">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v4H4zm0 5.5h16v4H4zM4 16h16v4H4z" opacity=".9"/></svg>
				</span>
				<div class="rply-feature__title" role="heading" aria-level="3"><?php esc_html_e( 'Repeater-based widgets', 'repeaterly' ); ?></div>
				<div class="rply-feature__desc"><?php esc_html_e( 'Dynamic icon lists, accordions and galleries backed by ACF repeaters.', 'repeaterly' ); ?></div>
			</div>
			<div class="rply-feature">
				<span class="rply-feature__icon rply-feature__icon--amber">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13 2 4 13.5h5.5L11 22l9-11.5h-5.5z"/></svg>
				</span>
				<div class="rply-feature__title" role="heading" aria-level="3"><?php esc_html_e( 'Performance-first workflow', 'repeaterly' ); ?></div>
				<div class="rply-feature__desc"><?php esc_html_e( 'One layout, data-driven content – a leaner database and faster pages.', 'repeaterly' ); ?></div>
			</div>
			<div class="rply-feature">
				<span class="rply-feature__icon rply-feature__icon--rose">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.7-9.6-9A5.4 5.4 0 0 1 12 6.6 5.4 5.4 0 0 1 21.6 12c-2.1 4.3-9.6 9-9.6 9z"/></svg>
				</span>
				<div class="rply-feature__title" role="heading" aria-level="3"><?php esc_html_e( 'Beginner friendly', 'repeaterly' ); ?></div>
				<div class="rply-feature__desc"><?php esc_html_e( 'Install, connect your fields, drag and drop. No PHP required, ever.', 'repeaterly' ); ?></div>
			</div>
		</div>
	</div>

	<!-- ============ PRO SHOWCASE ============ -->
	<div class="rply-pro" id="rply-pro">
		<div class="rply-pro__head">
			<span class="rply-chip rply-chip--pro-solid"><?php esc_html_e( 'PRO', 'repeaterly' ); ?></span>
			<div class="rply-pro__title" role="heading" aria-level="2">
				<?php esc_html_e( 'Go further with Repeaterly Pro', 'repeaterly' ); ?>
			</div>
			<div class="rply-pro__desc">
				<?php esc_html_e( 'Seven template-driven widgets for ACF Repeater, Relationship and Flexible Content fields – with grids, carousels, tabs, pagination and nested data. Built for serious, content-heavy sites.', 'repeaterly' ); ?>
			</div>
		</div>

		<div class="rply-grid rply-grid--3 rply-pro__grid">
			<div class="rply-pro-feature">
				<div class="rply-pro-feature__title" role="heading" aria-level="3"><?php esc_html_e( 'ACF Repeater Loop Grid', 'repeaterly' ); ?></div>
				<div class="rply-pro-feature__desc"><?php esc_html_e( 'Render any ACF Repeater as a responsive grid from one Elementor template – custom columns per device, equal-height cards and independent row & column gaps.', 'repeaterly' ); ?></div>
			</div>
			<div class="rply-pro-feature">
				<div class="rply-pro-feature__title" role="heading" aria-level="3"><?php esc_html_e( 'ACF Repeater Loop Carousel', 'repeaterly' ); ?></div>
				<div class="rply-pro-feature__desc"><?php esc_html_e( 'The same repeater data as a touch-ready carousel: slides to show & scroll, autoplay with pause-on-hover, custom arrows & dots, infinite loop and a 3D coverflow effect.', 'repeaterly' ); ?></div>
			</div>
			<div class="rply-pro-feature">
				<div class="rply-pro-feature__title" role="heading" aria-level="3"><?php esc_html_e( 'ACF Repeater Tabs', 'repeaterly' ); ?></div>
				<div class="rply-pro-feature__desc"><?php esc_html_e( 'Turn each ACF Repeater row into a tab: use a sub-field for the label and render an Elementor template as the panel, with Elementor styling and responsive behavior.', 'repeaterly' ); ?></div>
			</div>
			<div class="rply-pro-feature">
				<div class="rply-pro-feature__title" role="heading" aria-level="3"><?php esc_html_e( 'ACF Flexible Content builder', 'repeaterly' ); ?></div>
				<div class="rply-pro-feature__desc"><?php esc_html_e( 'Map each Flexible Content layout to its own Elementor template and let Repeaterly assemble the whole page, block by block – true modular, code-free page building.', 'repeaterly' ); ?></div>
			</div>
			<div class="rply-pro-feature">
				<div class="rply-pro-feature__title" role="heading" aria-level="3"><?php esc_html_e( 'Relationship Loop Grid', 'repeaterly' ); ?></div>
				<div class="rply-pro-feature__desc"><?php esc_html_e( 'Display ACF Relationship fields as a responsive grid of related posts, products or portfolios – each card built from your own Elementor template.', 'repeaterly' ); ?></div>
			</div>
			<div class="rply-pro-feature">
				<div class="rply-pro-feature__title" role="heading" aria-level="3"><?php esc_html_e( 'Relationship Loop Carousel', 'repeaterly' ); ?></div>
				<div class="rply-pro-feature__desc"><?php esc_html_e( 'Show related posts, products or portfolios in a sleek carousel with autoplay, custom arrows & dots and seamless infinite looping.', 'repeaterly' ); ?></div>
			</div>
			<div class="rply-pro-feature">
				<div class="rply-pro-feature__title" role="heading" aria-level="3"><?php esc_html_e( 'ACF Image Carousel', 'repeaterly' ); ?></div>
				<div class="rply-pro-feature__desc"><?php esc_html_e( 'Spin up a polished image carousel straight from an ACF gallery or image field, with full navigation, autoplay and spacing controls.', 'repeaterly' ); ?></div>
			</div>
			<div class="rply-pro-feature">
				<div class="rply-pro-feature__title" role="heading" aria-level="3"><?php esc_html_e( 'Nested repeater support', 'repeaterly' ); ?></div>
				<div class="rply-pro-feature__desc"><?php esc_html_e( 'Render multi-level, nested repeaters for pricing tables, feature groups, itineraries and any deeply structured content.', 'repeaterly' ); ?></div>
			</div>
			<div class="rply-pro-feature">
				<div class="rply-pro-feature__title" role="heading" aria-level="3"><?php esc_html_e( 'Pagination, Load More & Infinite Scroll', 'repeaterly' ); ?></div>
				<div class="rply-pro-feature__desc"><?php esc_html_e( 'Handle large datasets gracefully – choose classic Load More buttons or seamless infinite scroll, with a configurable items-per-page limit.', 'repeaterly' ); ?></div>
			</div>
			<div class="rply-pro-feature">
				<div class="rply-pro-feature__title" role="heading" aria-level="3"><?php esc_html_e( 'Post ID targeting', 'repeaterly' ); ?></div>
				<div class="rply-pro-feature__desc"><?php esc_html_e( 'Pull ACF data from any post to build reusable, global sections you can drop anywhere across your site.', 'repeaterly' ); ?></div>
			</div>
			<div class="rply-pro-feature">
				<div class="rply-pro-feature__title" role="heading" aria-level="3"><?php esc_html_e( 'Any widget inside loops', 'repeaterly' ); ?></div>
				<div class="rply-pro-feature__desc"><?php esc_html_e( 'Compose loop templates from any Elementor widget with full Dynamic Tags support – no layout limits, no compromises.', 'repeaterly' ); ?></div>
			</div>
		</div>

		<?php if ( ! $repeaterly_is_pro ) : ?>
			<div class="rply-pro__cta">
				<a class="rply-btn rply-btn--pro" href="<?php echo esc_url( 'https://repeaterly.com/' ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Upgrade to Repeaterly Pro', 'repeaterly' ); ?>
					<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M11 4l-1.1 1.1L13.7 9H3v2h10.7l-3.8 3.9L11 16l6-6z"/></svg>
				</a>
				<a class="rply-btn rply-btn--ghost-dark" href="<?php echo esc_url( 'https://repeaterly.com/#pricing' ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'View Pricing & Plans', 'repeaterly' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>

	<!-- ============ FREE VS PRO ============ -->
	<?php if ( ! $repeaterly_is_pro ) : ?>
	<div class="rply-section">
		<div class="rply-section__head">
			<div class="rply-section__title" role="heading" aria-level="2">
				<?php esc_html_e( 'Free vs Pro at a glance', 'repeaterly' ); ?>
			</div>
		</div>

		<div class="rply-compare" role="table" aria-label="<?php esc_attr_e( 'Free vs Pro feature comparison', 'repeaterly' ); ?>">
			<div class="rply-compare__row rply-compare__row--head" role="row">
				<span class="rply-compare__cell rply-compare__cell--feature" role="columnheader"><?php esc_html_e( 'Feature', 'repeaterly' ); ?></span>
				<span class="rply-compare__cell" role="columnheader"><?php esc_html_e( 'Free', 'repeaterly' ); ?></span>
				<span class="rply-compare__cell rply-compare__cell--pro" role="columnheader"><?php esc_html_e( 'Pro', 'repeaterly' ); ?></span>
			</div>

			<div class="rply-compare__row" role="row">
				<span class="rply-compare__cell rply-compare__cell--feature" role="cell"><?php esc_html_e( 'Dynamic Tags for all Elementor widgets', 'repeaterly' ); ?></span>
				<span class="rply-compare__cell" role="cell"><span class="rply-yes" aria-label="<?php esc_attr_e( 'Included', 'repeaterly' ); ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg></span></span>
				<span class="rply-compare__cell rply-compare__cell--pro" role="cell"><span class="rply-yes" aria-label="<?php esc_attr_e( 'Included', 'repeaterly' ); ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg></span></span>
			</div>

			<div class="rply-compare__row" role="row">
				<span class="rply-compare__cell rply-compare__cell--feature" role="cell"><?php esc_html_e( 'ACF repeater widgets (lists, accordions, galleries)', 'repeaterly' ); ?></span>
				<span class="rply-compare__cell" role="cell"><span class="rply-yes" aria-label="<?php esc_attr_e( 'Included', 'repeaterly' ); ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg></span></span>
				<span class="rply-compare__cell rply-compare__cell--pro" role="cell"><span class="rply-yes" aria-label="<?php esc_attr_e( 'Included', 'repeaterly' ); ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg></span></span>
			</div>

			<div class="rply-compare__row" role="row">
				<span class="rply-compare__cell rply-compare__cell--feature" role="cell"><?php esc_html_e( 'Pull ACF fields from an ACF Options Page', 'repeaterly' ); ?></span>
				<span class="rply-compare__cell" role="cell"><span class="rply-yes" aria-label="<?php esc_attr_e( 'Included', 'repeaterly' ); ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg></span></span>
				<span class="rply-compare__cell rply-compare__cell--pro" role="cell"><span class="rply-yes" aria-label="<?php esc_attr_e( 'Included', 'repeaterly' ); ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg></span></span>
			</div>

			<div class="rply-compare__row" role="row">
				<span class="rply-compare__cell rply-compare__cell--feature" role="cell"><?php esc_html_e( 'Repeater Loop Grid (columns, gaps, equal height)', 'repeaterly' ); ?></span>
				<span class="rply-compare__cell" role="cell"><span class="rply-no" aria-label="<?php esc_attr_e( 'Not included', 'repeaterly' ); ?>">&ndash;</span></span>
				<span class="rply-compare__cell rply-compare__cell--pro" role="cell"><span class="rply-yes" aria-label="<?php esc_attr_e( 'Included', 'repeaterly' ); ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg></span></span>
			</div>

			<div class="rply-compare__row" role="row">
				<span class="rply-compare__cell rply-compare__cell--feature" role="cell"><?php esc_html_e( 'Repeater Loop Carousel (autoplay, arrows, coverflow)', 'repeaterly' ); ?></span>
				<span class="rply-compare__cell" role="cell"><span class="rply-no" aria-label="<?php esc_attr_e( 'Not included', 'repeaterly' ); ?>">&ndash;</span></span>
				<span class="rply-compare__cell rply-compare__cell--pro" role="cell"><span class="rply-yes" aria-label="<?php esc_attr_e( 'Included', 'repeaterly' ); ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg></span></span>
			</div>

			<div class="rply-compare__row" role="row">
				<span class="rply-compare__cell rply-compare__cell--feature" role="cell"><?php esc_html_e( 'ACF Repeater Tabs', 'repeaterly' ); ?></span>
				<span class="rply-compare__cell" role="cell"><span class="rply-no" aria-label="<?php esc_attr_e( 'Not included', 'repeaterly' ); ?>">&ndash;</span></span>
				<span class="rply-compare__cell rply-compare__cell--pro" role="cell"><span class="rply-yes" aria-label="<?php esc_attr_e( 'Included', 'repeaterly' ); ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg></span></span>
			</div>

			<div class="rply-compare__row" role="row">
				<span class="rply-compare__cell rply-compare__cell--feature" role="cell"><?php esc_html_e( 'ACF Flexible Content layout builder', 'repeaterly' ); ?></span>
				<span class="rply-compare__cell" role="cell"><span class="rply-no" aria-label="<?php esc_attr_e( 'Not included', 'repeaterly' ); ?>">&ndash;</span></span>
				<span class="rply-compare__cell rply-compare__cell--pro" role="cell"><span class="rply-yes" aria-label="<?php esc_attr_e( 'Included', 'repeaterly' ); ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg></span></span>
			</div>

			<div class="rply-compare__row" role="row">
				<span class="rply-compare__cell rply-compare__cell--feature" role="cell"><?php esc_html_e( 'ACF Image Carousel', 'repeaterly' ); ?></span>
				<span class="rply-compare__cell" role="cell"><span class="rply-no" aria-label="<?php esc_attr_e( 'Not included', 'repeaterly' ); ?>">&ndash;</span></span>
				<span class="rply-compare__cell rply-compare__cell--pro" role="cell"><span class="rply-yes" aria-label="<?php esc_attr_e( 'Included', 'repeaterly' ); ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg></span></span>
			</div>

			<div class="rply-compare__row" role="row">
				<span class="rply-compare__cell rply-compare__cell--feature" role="cell"><?php esc_html_e( 'Relationship Loop Grid & Carousel', 'repeaterly' ); ?></span>
				<span class="rply-compare__cell" role="cell"><span class="rply-no" aria-label="<?php esc_attr_e( 'Not included', 'repeaterly' ); ?>">&ndash;</span></span>
				<span class="rply-compare__cell rply-compare__cell--pro" role="cell"><span class="rply-yes" aria-label="<?php esc_attr_e( 'Included', 'repeaterly' ); ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg></span></span>
			</div>

			<div class="rply-compare__row" role="row">
				<span class="rply-compare__cell rply-compare__cell--feature" role="cell"><?php esc_html_e( 'Nested repeater rendering', 'repeaterly' ); ?></span>
				<span class="rply-compare__cell" role="cell"><span class="rply-no" aria-label="<?php esc_attr_e( 'Not included', 'repeaterly' ); ?>">&ndash;</span></span>
				<span class="rply-compare__cell rply-compare__cell--pro" role="cell"><span class="rply-yes" aria-label="<?php esc_attr_e( 'Included', 'repeaterly' ); ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg></span></span>
			</div>

			<div class="rply-compare__row" role="row">
				<span class="rply-compare__cell rply-compare__cell--feature" role="cell"><?php esc_html_e( 'Pagination & Load More', 'repeaterly' ); ?></span>
				<span class="rply-compare__cell" role="cell"><span class="rply-no" aria-label="<?php esc_attr_e( 'Not included', 'repeaterly' ); ?>">&ndash;</span></span>
				<span class="rply-compare__cell rply-compare__cell--pro" role="cell"><span class="rply-yes" aria-label="<?php esc_attr_e( 'Included', 'repeaterly' ); ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg></span></span>
			</div>

			<div class="rply-compare__row" role="row">
				<span class="rply-compare__cell rply-compare__cell--feature" role="cell"><?php esc_html_e( 'Post ID targeting for global sections', 'repeaterly' ); ?></span>
				<span class="rply-compare__cell" role="cell"><span class="rply-no" aria-label="<?php esc_attr_e( 'Not included', 'repeaterly' ); ?>">&ndash;</span></span>
				<span class="rply-compare__cell rply-compare__cell--pro" role="cell"><span class="rply-yes" aria-label="<?php esc_attr_e( 'Included', 'repeaterly' ); ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7.6 13.2 4.4 10l-1.1 1.1 4.3 4.3 9-9-1.1-1.1z"/></svg></span></span>
			</div>

			<div class="rply-compare__row rply-compare__row--foot" role="row">
				<span class="rply-compare__cell rply-compare__cell--feature" role="cell"></span>
				<span class="rply-compare__cell" role="cell"></span>
				<span class="rply-compare__cell rply-compare__cell--pro" role="cell">
					<a class="rply-btn rply-btn--pro rply-btn--sm" href="<?php echo esc_url( 'https://repeaterly.com/#pricing' ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Get Pro', 'repeaterly' ); ?>
					</a>
				</span>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<!-- ============ RESOURCES ============ -->
	<div class="rply-section">
		<div class="rply-section__head">
			<div class="rply-section__title" role="heading" aria-level="2">
				<?php esc_html_e( 'Helpful resources', 'repeaterly' ); ?>
			</div>
			<div class="rply-section__desc">
				<?php esc_html_e( 'Everything you need to get the most out of Repeaterly.', 'repeaterly' ); ?>
			</div>
		</div>

		<div class="rply-resources">
			<a class="rply-resources__item" href="<?php echo esc_url( 'https://repeaterly.com/documentation' ); ?>" target="_blank" rel="noopener noreferrer">
				<span class="rply-resources__icon rply-resources__icon--indigo">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 2H8a3 3 0 0 0-3 3v14a3 3 0 0 0 3 3h11a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1zm-1 16H8a1 1 0 0 1 0-2h10zm0-4H8a3 3 0 0 0-1 .2V5a1 1 0 0 1 1-1h10z"/></svg>
				</span>
				<span class="rply-resources__title"><?php esc_html_e( 'Documentation', 'repeaterly' ); ?></span>
				<span class="rply-resources__desc"><?php esc_html_e( 'Step-by-step guides for every widget, Dynamic Tag and setting.', 'repeaterly' ); ?></span>
			</a>
			<a class="rply-resources__item" href="<?php echo esc_url( 'https://repeaterly.com/contact/' ); ?>" target="_blank" rel="noopener noreferrer">
				<span class="rply-resources__icon rply-resources__icon--teal">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2A10 10 0 0 0 2 12a9.9 9.9 0 0 0 1.5 5.3L2 22l4.9-1.4A10 10 0 1 0 12 2zm1 15h-2v-2h2zm1.6-6.1c-.5.5-.9.9-1.1 1.3a2.8 2.8 0 0 0-.3 1.3h-2.4a4.6 4.6 0 0 1 .4-2 6.6 6.6 0 0 1 1.4-1.7 3.2 3.2 0 0 0 .7-.8 1.5 1.5 0 0 0 .2-.8 1.3 1.3 0 0 0-.4-1 1.6 1.6 0 0 0-1.1-.4 1.7 1.7 0 0 0-1.2.4 2.2 2.2 0 0 0-.6 1.2L7.9 8a4 4 0 0 1 1.4-2.4A4.3 4.3 0 0 1 12 4.7a4.2 4.2 0 0 1 2.9 1 3.2 3.2 0 0 1 1.1 2.5 3 3 0 0 1-.4 1.5 6.5 6.5 0 0 1-1 1.2z"/></svg>
				</span>
				<span class="rply-resources__title"><?php esc_html_e( 'Contact Support', 'repeaterly' ); ?></span>
				<span class="rply-resources__desc"><?php esc_html_e( 'Stuck on something? Reach our team directly and we\'ll help you out.', 'repeaterly' ); ?></span>
			</a>
			<a class="rply-resources__item" href="<?php echo esc_url( 'https://wordpress.org/support/plugin/repeaterly/reviews/#new-post' ); ?>" target="_blank" rel="noopener noreferrer">
				<span class="rply-resources__icon rply-resources__icon--amber">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/></svg>
				</span>
				<span class="rply-resources__title"><?php esc_html_e( 'Leave a Review', 'repeaterly' ); ?></span>
				<span class="rply-resources__desc"><?php esc_html_e( 'Enjoying Repeaterly? A ★★★★★ review keeps us building.', 'repeaterly' ); ?></span>
			</a>
		</div>

		<div class="rply-meta rply-meta--center">
			<?php esc_html_e( 'Tested with the latest WordPress and Elementor versions. GPLv2 or later.', 'repeaterly' ); ?>
		</div>
	</div>
</div>
