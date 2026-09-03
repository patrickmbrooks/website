<?php
/**
 * Brooks Law v2 — footer.
 *
 * Replaces the v1 all-pages widget with a curated Footer Menu. If no menu is
 * assigned to the "Footer Menu" location yet, a short hardcoded list of the
 * key hubs renders instead — never the 70-link page dump.
 *
 * @package Brooks_Law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$brooks_phone      = brooks_law_get_option( 'firm_phone' );
$brooks_phone_link = brooks_law_tel( brooks_law_get_option( 'firm_phone_link', $brooks_phone ) );
$brooks_cell       = brooks_law_get_option( 'firm_cell' );
$brooks_cell_link  = brooks_law_tel( brooks_law_get_option( 'firm_cell_link', $brooks_cell ) );
$brooks_email      = brooks_law_get_option( 'firm_email' );
?>

<footer class="site-footer">
	<div class="wrap">
		<div class="footer-grid">
			<div>
				<h2 class="footer-heading"><?php echo esc_html( brooks_law_get_option( 'firm_shortname' ) ); ?></h2>
				<p><a href="tel:<?php echo esc_attr( $brooks_phone_link ); ?>"><?php echo esc_html( $brooks_phone ); ?></a> <?php esc_html_e( '(Office)', 'brooks-law-30-pro' ); ?></p>
				<p><a href="tel:<?php echo esc_attr( $brooks_cell_link ); ?>"><?php echo esc_html( $brooks_cell ); ?></a> <?php esc_html_e( '(Criminal line — call or text)', 'brooks-law-30-pro' ); ?></p>
				<p><a href="mailto:<?php echo esc_attr( $brooks_email ); ?>"><?php echo esc_html( $brooks_email ); ?></a></p>
			</div>

			<nav class="footer-menu" aria-label="<?php esc_attr_e( 'Footer', 'brooks-law-30-pro' ); ?>">
				<h2 class="footer-heading"><?php esc_html_e( 'Practice Areas', 'brooks-law-30-pro' ); ?></h2>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'footer',
						'container'      => false,
						'depth'          => 1,
					) );
				} else {
					$fallback = array(
						'/criminal-defense-2/' => __( 'Criminal Defense', 'brooks-law-30-pro' ),
						'/dui/'                => __( 'DUI Defense', 'brooks-law-30-pro' ),
						'/drug-offense/'       => __( 'Drug Charges', 'brooks-law-30-pro' ),
						'/domestic-violence/'  => __( 'Domestic Assault', 'brooks-law-30-pro' ),
						'/civil-litigation/'   => __( 'Civil Litigation', 'brooks-law-30-pro' ),
						'/traffic/'            => __( 'Traffic Matters', 'brooks-law-30-pro' ),
						'/expungement/'        => __( 'Expungement', 'brooks-law-30-pro' ),
						'/contact-updated/'    => __( 'Contact', 'brooks-law-30-pro' ),
					);
					echo '<ul>';
					foreach ( $fallback as $path => $label ) {
						printf(
							'<li><a href="%1$s">%2$s</a></li>',
							esc_url( home_url( $path ) ),
							esc_html( $label )
						);
					}
					echo '</ul>';
				}
				?>
			</nav>

			<div>
				<h2 class="footer-heading"><?php esc_html_e( 'Office', 'brooks-law-30-pro' ); ?></h2>
				<p><?php echo esc_html( brooks_law_get_option( 'firm_address' ) ); ?><br>
				<?php echo esc_html( brooks_law_get_option( 'firm_city_state' ) ); ?></p>
				<p><?php echo esc_html( brooks_law_get_option( 'firm_hours' ) ); ?></p>
				<p><?php echo esc_html( brooks_law_get_option( 'topbar_note' ) ); ?></p>
			</div>
		</div>

		<?php brooks_law_render_disclaimer(); ?>
		<?php brooks_law_render_copyright(); ?>
	</div>
</footer>

<?php brooks_law_call_bar(); ?>

<?php wp_footer(); ?>
</body>
</html>
