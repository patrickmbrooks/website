<?php
/**
 * Brooks Law v2 — header.
 *
 * @package Brooks_Law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$brooks_phone      = brooks_law_get_option( 'firm_phone' );
$brooks_phone_link = brooks_law_tel( brooks_law_get_option( 'firm_phone_link', $brooks_phone ) );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'brooks-law-30-pro' ); ?></a>

<div class="topbar">
	<div class="wrap">
		<span class="topbar-note"><?php echo esc_html( brooks_law_get_option( 'topbar_note' ) ); ?></span>
		<a href="tel:<?php echo esc_attr( $brooks_phone_link ); ?>">
			<?php
			/* translators: %s: office phone number as displayed. */
			echo esc_html( sprintf( __( 'Call %s', 'brooks-law-30-pro' ), $brooks_phone ) );
			?>
		</a>
	</div>
</div>

<header class="site-header">
	<div class="wrap">
		<div class="brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php endif; ?>
			<div>
				<p class="name"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></p>
				<p class="tag"><?php echo esc_html( brooks_law_get_option( 'firm_tagline' ) ); ?></p>
			</div>
		</div>

		<button class="menu-toggle" aria-expanded="false" aria-controls="primary-menu">
			<?php esc_html_e( 'Menu', 'brooks-law-30-pro' ); ?>
		</button>

		<nav class="main-navigation" aria-label="<?php esc_attr_e( 'Primary', 'brooks-law-30-pro' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'menu_id'        => 'primary-menu',
				'container'      => false,
				'fallback_cb'    => false,
			) );
			?>
		</nav>
	</div>
</header>
