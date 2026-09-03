<?php
/**
 * Brooks Law v2 — 404.
 *
 * @package Brooks_Law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="content" class="site-main">
	<section class="error-404">
		<div class="wrap">
			<h1><?php esc_html_e( 'Page not found', 'brooks-law-30-pro' ); ?></h1>
			<p class="prose"><?php esc_html_e( 'That page may have moved. Try a search, or start from one of the pages below — and if you need to talk to a lawyer, the phone is faster than the website.', 'brooks-law-30-pro' ); ?></p>

			<?php get_search_form(); ?>

			<h2><?php esc_html_e( 'Frequently visited pages', 'brooks-law-30-pro' ); ?></h2>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/criminal-defense-2/' ) ); ?>"><?php esc_html_e( 'Criminal Defense', 'brooks-law-30-pro' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/dui/' ) ); ?>"><?php esc_html_e( 'DUI Defense', 'brooks-law-30-pro' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/drug-offense/' ) ); ?>"><?php esc_html_e( 'Drug Charges', 'brooks-law-30-pro' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/domestic-violence/' ) ); ?>"><?php esc_html_e( 'Domestic Assault', 'brooks-law-30-pro' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/contact-updated/' ) ); ?>"><?php esc_html_e( 'Contact the Firm', 'brooks-law-30-pro' ); ?></a></li>
			</ul>

			<p>
				<?php
				$brooks_phone      = brooks_law_get_option( 'firm_phone' );
				$brooks_phone_link = brooks_law_tel( brooks_law_get_option( 'firm_phone_link', $brooks_phone ) );
				/* translators: %s: office phone number as displayed. */
				$brooks_call_label = sprintf( __( 'Call %s', 'brooks-law-30-pro' ), $brooks_phone );

				printf(
					'<a class="btn btn-brass" href="tel:%1$s">%2$s</a>',
					esc_attr( $brooks_phone_link ),
					esc_html( $brooks_call_label )
				);
				?>
			</p>
		</div>
	</section>
</main>

<?php
get_footer();
