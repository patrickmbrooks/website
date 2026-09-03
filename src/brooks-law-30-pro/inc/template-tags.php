<?php
/**
 * Brooks Law v2 — reusable template tags.
 *
 * @package Brooks_Law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Section header with the "docket rule" treatment.
 *
 * @param string $kicker   Small-caps label.
 * @param string $title    Section H2.
 * @param string $subtitle Optional supporting line.
 */
function brooks_law_section_header( $kicker, $title, $subtitle = '' ) {
	?>
	<header class="rule-header">
		<p class="kicker"><span><?php echo esc_html( $kicker ); ?></span><span class="stamp" aria-hidden="true"></span></p>
		<h2><?php echo esc_html( $title ); ?></h2>
		<?php if ( '' !== $subtitle ) : ?>
			<p class="subtitle"><?php echo esc_html( $subtitle ); ?></p>
		<?php endif; ?>
	</header>
	<?php
}

/**
 * Breadcrumb trail: Home › Parent › Current page.
 */
function brooks_law_breadcrumb() {
	if ( is_front_page() ) {
		return;
	}
	?>
	<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'brooks-law-30-pro' ); ?>">
		<ol>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'brooks-law-30-pro' ); ?></a></li>
			<?php
			if ( is_page() ) {
				$ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );
				foreach ( $ancestors as $ancestor_id ) {
					printf(
						'<li><a href="%1$s">%2$s</a></li>',
						esc_url( get_permalink( $ancestor_id ) ),
						esc_html( get_the_title( $ancestor_id ) )
					);
				}
			} elseif ( is_singular( 'post' ) ) {
				$blog_page = (int) get_option( 'page_for_posts' );
				if ( $blog_page ) {
					printf(
						'<li><a href="%1$s">%2$s</a></li>',
						esc_url( get_permalink( $blog_page ) ),
						esc_html( get_the_title( $blog_page ) )
					);
				}
			}
			?>
			<li><span aria-current="page"><?php the_title(); ?></span></li>
		</ol>
	</nav>
	<?php
}

/**
 * Sidebar contact box for interior and practice-area pages.
 */
function brooks_law_contact_box() {
	$phone      = brooks_law_get_option( 'firm_phone' );
	$phone_link = brooks_law_tel( brooks_law_get_option( 'firm_phone_link', brooks_law_get_option( 'firm_phone' ) ) );
	$cell       = brooks_law_get_option( 'firm_cell' );
	$cell_link  = brooks_law_tel( brooks_law_get_option( 'firm_cell_link', brooks_law_get_option( 'firm_cell' ) ) );
	$email      = brooks_law_get_option( 'firm_email' );
	?>
	<aside class="sidebar-box" aria-label="<?php esc_attr_e( 'Contact the firm', 'brooks-law-30-pro' ); ?>">
		<h2><?php esc_html_e( 'Talk to a Lawyer', 'brooks-law-30-pro' ); ?></h2>
		<?php /* translators: %s: office phone number as displayed. */ ?>
		<a class="btn btn-brass" href="tel:<?php echo esc_attr( $phone_link ); ?>"><?php echo esc_html( sprintf( __( 'Call %s', 'brooks-law-30-pro' ), $phone ) ); ?></a>
		<?php /* translators: %s: mobile/criminal-line number as displayed. */ ?>
		<a class="btn btn-outline" href="tel:<?php echo esc_attr( $cell_link ); ?>"><?php echo esc_html( sprintf( __( 'Call or text %s', 'brooks-law-30-pro' ), $cell ) ); ?></a>
		<p class="fine"><?php echo esc_html( brooks_law_get_option( 'topbar_note' ) ); ?></p>
		<p class="fine"><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
	</aside>
	<?php
}

/**
 * Related practice pages: children of the current page, or siblings if the
 * page has a parent. Renders nothing when there is nothing to show.
 */
function brooks_law_related_practices() {
	$post_id   = get_the_ID();
	$parent_id = wp_get_post_parent_id( $post_id );

	$args = array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 8,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
		'post_parent'    => $parent_id ? $parent_id : $post_id,
		'post__not_in'   => array( $post_id ),
	);

	$related = get_posts( $args );
	if ( empty( $related ) ) {
		return;
	}
	?>
	<aside class="sidebar-box" aria-label="<?php esc_attr_e( 'Related pages', 'brooks-law-30-pro' ); ?>">
		<h2><?php esc_html_e( 'Related Pages', 'brooks-law-30-pro' ); ?></h2>
		<ul>
			<?php foreach ( $related as $page ) : ?>
				<li><a href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php echo esc_html( get_the_title( $page ) ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</aside>
	<?php
}

/**
 * Render a hero background photo plus the CSS custom properties that drive
 * its scrim. Returns the class and style attributes the caller needs.
 *
 * The image is marked high priority and eager — it is the largest element on
 * screen, so hinting it correctly is what keeps the loading score from
 * dropping when a photo is added.
 *
 * @param int $image_id Attachment ID. 0 renders nothing.
 * @return array {
 *     @type string $class Extra class for the hero element.
 *     @type string $style Inline custom properties.
 *     @type string $media Markup for the image layer.
 * }
 */
function brooks_law_hero_media( $image_id ) {
	$image_id = (int) $image_id;

	$empty = array(
		'class' => '',
		'style' => '',
		'media' => '',
	);

	if ( ! $image_id || ! wp_attachment_is_image( $image_id ) ) {
		return $empty;
	}

	$overlay = (float) brooks_law_get_option( 'hero_overlay', 0.82 );
	$overlay = max( 0.5, min( 0.95, $overlay ) );

	$focus = (string) brooks_law_get_option( 'hero_focus', 'center' );
	if ( ! in_array( $focus, array( 'top', 'center', 'bottom' ), true ) ) {
		$focus = 'center';
	}

	$image = wp_get_attachment_image(
		$image_id,
		'full',
		false,
		array(
			'alt'           => '',
			'class'         => 'hero-photo',
			'loading'       => 'eager',
			'decoding'      => 'async',
			'fetchpriority' => 'high',
		)
	);

	if ( '' === $image ) {
		return $empty;
	}

	return array(
		'class' => ' has-image',
		'style' => sprintf(
			' style="--hero-overlay: %1$s; --hero-focus: %2$s;"',
			esc_attr( (string) $overlay ),
			esc_attr( $focus )
		),
		'media' => '<div class="hero-media" aria-hidden="true">' . $image . '</div>',
	);
}

/**
 * Hero media for the current page, using its Featured Image.
 *
 * @return array Same shape as brooks_law_hero_media().
 */
function brooks_law_page_hero_media() {
	if ( ! is_singular() || ! has_post_thumbnail() ) {
		return array(
			'class' => '',
			'style' => '',
			'media' => '',
		);
	}

	return brooks_law_hero_media( get_post_thumbnail_id() );
}

/**
 * Footer attorney-advertising disclaimer (Customizer-editable, safe default).
 */
function brooks_law_render_disclaimer() {
	$text = brooks_law_get_option( 'footer_disclaimer' );
	if ( '' === trim( (string) $text ) ) {
		return;
	}
	echo '<div class="footer-legal">' . wp_kses_post( wpautop( $text ) ) . '</div>';
}

/**
 * Footer copyright with a dynamic year — never a hand-typed "[2026]" again.
 */
function brooks_law_render_copyright() {
	printf(
		'<p class="footer-copyright">&copy; %1$s %2$s. %3$s</p>',
		esc_html( gmdate( 'Y' ) ),
		esc_html( brooks_law_get_option( 'firm_shortname' ) ),
		esc_html__( 'All rights reserved.', 'brooks-law-30-pro' )
	);
}

/*
 * The mobile sticky call bar moved to inc/contact-toggle.php in 3.1.
 * brooks_law_call_bar() still exists under the same name and is still called
 * from footer.php — it now renders the text-first split bar.
 */
