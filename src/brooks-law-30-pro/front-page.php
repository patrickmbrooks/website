<?php
/**
 * Brooks Law v2 — front page.
 *
 * Template-driven homepage: one H1, static typographic hero (no slider),
 * practice cards, about, optional testimonials and case results, contact.
 * All copy is editable under Customizer → Brooks Law Firm.
 *
 * @package Brooks_Law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$phone      = brooks_law_get_option( 'firm_phone' );
$phone_link = brooks_law_tel( brooks_law_get_option( 'firm_phone_link', $phone ) );
$cell       = brooks_law_get_option( 'firm_cell' );
$cell_link  = brooks_law_tel( brooks_law_get_option( 'firm_cell_link', $cell ) );
?>

<main id="content" class="site-main">

	<!-- Hero -->
	<?php $hero = brooks_law_hero_media( brooks_law_get_option( 'hero_image', 0 ) ); ?>
	<?php
	$blv_video = brooks_law_hero_video_url();
	if ( $blv_video && false === strpos( $hero['class'], 'has-image' ) ) {
		$hero['class'] .= ' has-image';
	}
	?>
	<section class="hero<?php echo esc_attr( $hero['class'] ); ?>"<?php echo $hero['style']; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in brooks_law_hero_media(). ?>>
		<?php echo $hero['media']; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_get_attachment_image() output. ?>
		<?php brooks_law_hero_video_render( $blv_video ); ?>
		<div class="wrap">
			<p class="kicker"><?php echo esc_html( brooks_law_get_option( 'hero_kicker' ) ); ?></p>
			<h1 class="hero-heading"><?php echo esc_html( brooks_law_get_option( 'hero_heading' ) ); ?></h1>
			<p class="lead hero-lead"><?php echo esc_html( brooks_law_get_option( 'hero_lead' ) ); ?></p>

			<?php brooks_law_contact_toggle( array( 'context' => 'hero', 'matter' => 0 ) ); ?>

			<p><a class="hero-consult" href="#contact"><?php esc_html_e( 'Request a free consultation', 'brooks-law-30-pro' ); ?></a></p>

			<ul class="trust-points">
				<?php
				for ( $i = 1; $i <= 3; $i++ ) {
					$point = brooks_law_get_option( "trust_point_{$i}" );
					if ( '' !== trim( (string) $point ) ) {
						printf( '<li>%s</li>', esc_html( $point ) );
					}
				}
				?>
			</ul>
		</div>
	</section>

	<!-- Action Center -->
	<?php $ac_tiles = get_theme_mod( 'ac_enable', true ) ? brooks_law_get_action_tiles() : array(); ?>
	<?php if ( ! empty( $ac_tiles ) ) : ?>
	<section id="action-center" class="section band-limestone" aria-labelledby="action-center-heading">
		<div class="wrap">
			<header class="rule-header">
				<p class="kicker"><span><?php echo esc_html( get_theme_mod( 'ac_kicker', 'Where You Stand Right Now' ) ); ?></span><span class="stamp" aria-hidden="true"></span></p>
				<h2 id="action-center-heading"><?php echo esc_html( get_theme_mod( 'ac_heading', 'Start With What Happened' ) ); ?></h2>
				<p class="subtitle"><?php echo esc_html( get_theme_mod( 'ac_subheading', 'Pick the closest match — each one goes straight to a page that explains the process in Shelby County and the surrounding courts.' ) ); ?></p>
			</header>

			<ul class="ac-grid">
				<?php $ac_icons = brooks_law_sa_icons(); ?>
				<?php foreach ( $ac_tiles as $tile ) : ?>
					<li class="ac-item">
						<a class="ac-tile<?php echo $tile['hot'] ? ' ac-tile--hot' : ''; ?>" href="<?php echo esc_url( $tile['url'] ); ?>">
							<span class="ac-disc" aria-hidden="true">
								<svg class="ac-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><?php echo $ac_icons[ $tile['icon'] ]['svg']; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG from brooks_law_sa_icons(). ?></svg>
							</span>
							<span class="ac-text">
								<?php if ( $tile['hot'] && '' !== trim( (string) get_theme_mod( 'ac_hot_tag', 'Time-sensitive' ) ) ) : ?>
									<span class="ac-tag"><?php echo esc_html( get_theme_mod( 'ac_hot_tag', 'Time-sensitive' ) ); ?></span>
								<?php endif; ?>
								<span class="ac-title"><?php echo esc_html( $tile['title'] ); ?></span>
								<?php if ( '' !== trim( (string) $tile['sub'] ) ) : ?>
									<span class="ac-sub"><?php echo esc_html( $tile['sub'] ); ?></span>
								<?php endif; ?>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php $ac_more = brooks_law_action_center_more_url(); ?>
			<?php if ( '' !== $ac_more ) : ?>
				<p class="ac-more"><a href="<?php echo esc_url( $ac_more ); ?>"><?php echo esc_html( get_theme_mod( 'ac_more_label', 'See every charge we defend' ) ); ?> <span aria-hidden="true">&rarr;</span></a></p>
			<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- Practice areas -->
	<?php $cards = brooks_law_practice_cards(); ?>
	<?php if ( ! empty( $cards ) ) : ?>
	<section class="section" aria-labelledby="practice-heading">
		<div class="wrap">
			<header class="rule-header">
				<p class="kicker"><span><?php esc_html_e( 'The Practice', 'brooks-law-30-pro' ); ?></span><span class="stamp" aria-hidden="true"></span></p>
				<h2 id="practice-heading"><?php esc_html_e( 'Areas of Practice', 'brooks-law-30-pro' ); ?></h2>
				<p class="subtitle"><?php esc_html_e( 'A focused practice serving individuals, families, and businesses in Memphis, Shelby County, and the surrounding area.', 'brooks-law-30-pro' ); ?></p>
			</header>

			<div class="practice-grid">
				<?php foreach ( $cards as $card ) : ?>
					<article class="practice-card">
						<p class="numeral" aria-hidden="true"><?php echo esc_html( $card['numeral'] ); ?></p>
						<h3><a href="<?php echo esc_url( $card['url'] ); ?>"><?php echo esc_html( $card['title'] ); ?></a></h3>
						<p><?php echo esc_html( $card['desc'] ); ?></p>
						<p class="more" aria-hidden="true"><?php esc_html_e( 'Learn more →', 'brooks-law-30-pro' ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- About -->
	<section class="section band-limestone" aria-labelledby="about-heading">
		<div class="wrap">
			<header class="rule-header">
				<p class="kicker"><span><?php esc_html_e( 'The Firm', 'brooks-law-30-pro' ); ?></span><span class="stamp" aria-hidden="true"></span></p>
				<h2 id="about-heading"><?php echo esc_html( brooks_law_get_option( 'about_heading' ) ); ?></h2>
			</header>

			<div class="about-grid">
				<div class="flow">
					<?php echo wp_kses_post( wpautop( brooks_law_get_option( 'about_text' ) ) ); ?>
					<p class="attorney-line"><?php echo esc_html( brooks_law_get_option( 'about_attorney_line' ) ); ?></p>
				</div>
				<?php
				$photo_id = (int) brooks_law_get_option( 'about_photo', 0 );
				if ( $photo_id ) :
					?>
					<figure class="about-photo">
						<?php echo wp_get_attachment_image( $photo_id, 'large', false, array( 'loading' => 'lazy' ) ); ?>
					</figure>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- Testimonials (Customizer toggle) -->
	<?php $testimonials = brooks_law_get_option( 'testimonials_enable' ) ? brooks_law_testimonials() : array(); ?>
	<?php if ( ! empty( $testimonials ) ) : ?>
	<section class="section" aria-labelledby="testimonials-heading">
		<div class="wrap">
			<header class="rule-header">
				<p class="kicker"><span><?php esc_html_e( 'Client Voices', 'brooks-law-30-pro' ); ?></span><span class="stamp" aria-hidden="true"></span></p>
				<h2 id="testimonials-heading"><?php echo esc_html( brooks_law_get_option( 'testimonials_heading' ) ); ?></h2>
			</header>

			<div class="testimonial-grid">
				<?php foreach ( $testimonials as $t ) : ?>
					<figure class="testimonial">
						<blockquote><?php echo esc_html( $t['quote'] ); ?></blockquote>
						<?php if ( '' !== $t['name'] ) : ?>
							<figcaption><?php echo esc_html( $t['name'] ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>

			<?php $t_note = brooks_law_get_option( 'testimonials_note' ); ?>
			<?php if ( '' !== trim( (string) $t_note ) ) : ?>
				<p class="section-disclaimer"><?php echo esc_html( $t_note ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- Case results (Customizer toggle) -->
	<?php $results = brooks_law_get_option( 'results_enable' ) ? brooks_law_case_results() : array(); ?>
	<?php if ( ! empty( $results ) ) : ?>
	<section class="section band-limestone" aria-labelledby="results-heading">
		<div class="wrap">
			<header class="rule-header">
				<p class="kicker"><span><?php esc_html_e( 'On the Record', 'brooks-law-30-pro' ); ?></span><span class="stamp" aria-hidden="true"></span></p>
				<h2 id="results-heading"><?php echo esc_html( brooks_law_get_option( 'results_heading' ) ); ?></h2>
			</header>

			<ul class="results-list">
				<?php foreach ( $results as $r ) : ?>
					<li>
						<span class="charge"><?php echo esc_html( $r['charge'] ); ?></span>
						<?php if ( '' !== $r['outcome'] ) : ?>
							<span class="outcome"><?php echo esc_html( $r['outcome'] ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php $r_note = brooks_law_get_option( 'results_note' ); ?>
			<?php if ( '' !== trim( (string) $r_note ) ) : ?>
				<p class="section-disclaimer"><?php echo esc_html( $r_note ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- Service area bubbles (Customizer toggle) -->
	<?php $sa_areas = get_theme_mod( 'sa_enable', true ) ? brooks_law_get_service_areas() : array(); ?>
	<?php if ( ! empty( $sa_areas ) ) : ?>
	<section id="service-areas" class="section" aria-labelledby="service-areas-heading">
		<div class="wrap">
			<header class="rule-header">
				<p class="kicker"><span><?php esc_html_e( 'Where We Appear', 'brooks-law-30-pro' ); ?></span><span class="stamp" aria-hidden="true"></span></p>
				<h2 id="service-areas-heading"><?php echo esc_html( get_theme_mod( 'sa_heading', 'Communities We Serve' ) ); ?></h2>
				<?php $sa_sub = get_theme_mod( 'sa_subheading', 'Criminal defense in courts across the Mid-South — select your community for local court information.' ); ?>
				<?php if ( '' !== trim( (string) $sa_sub ) ) : ?>
					<p class="subtitle"><?php echo esc_html( $sa_sub ); ?></p>
				<?php endif; ?>
			</header>

			<ul class="sa-grid" role="list">
				<?php foreach ( $sa_areas as $sa ) : ?>
					<li class="sa-item">
						<a class="sa-bubble" href="<?php echo esc_url( $sa['url'] ); ?>">
							<span class="sa-disc<?php echo $sa['img_id'] ? ' has-photo' : ''; ?>">
								<?php if ( $sa['img_id'] ) : ?>
									<?php
									echo wp_get_attachment_image(
										$sa['img_id'],
										'medium',
										false,
										array(
											'loading' => 'lazy',
											'class'   => 'sa-photo',
											'alt'     => '',
										)
									);
									?>
								<?php else : ?>
									<?php $sa_icons = brooks_law_sa_icons(); ?>
									<svg class="sa-pin" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><?php echo $sa_icons[ $sa['icon'] ]['svg']; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG from brooks_law_sa_icons(). ?></svg>
								<?php endif; ?>
							</span>
							<span class="sa-label"><?php echo esc_html( $sa['label'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php endif; ?>

	<!-- Contact -->
	<section id="contact" class="section band-dark" aria-labelledby="contact-heading">
		<div class="wrap">
			<header class="rule-header">
				<p class="kicker"><span><?php esc_html_e( 'Consultations', 'brooks-law-30-pro' ); ?></span><span class="stamp" aria-hidden="true"></span></p>
				<h2 id="contact-heading"><?php echo esc_html( brooks_law_get_option( 'contact_heading' ) ); ?></h2>
				<p class="subtitle"><?php echo esc_html( brooks_law_get_option( 'contact_intro' ) ); ?></p>
			</header>

			<div class="contact-grid">
				<div class="contact-item">
					<h3><?php esc_html_e( 'Telephone', 'brooks-law-30-pro' ); ?></h3>
					<p class="big"><a href="tel:<?php echo esc_attr( $phone_link ); ?>"><?php echo esc_html( $phone ); ?></a></p>
					<p><?php esc_html_e( 'Office', 'brooks-law-30-pro' ); ?></p>
					<p class="big"><a href="tel:<?php echo esc_attr( $cell_link ); ?>"><?php echo esc_html( $cell ); ?></a></p>
					<p><?php esc_html_e( 'Criminal line — call or text', 'brooks-law-30-pro' ); ?></p>
				</div>
				<div class="contact-item">
					<h3><?php esc_html_e( 'Email', 'brooks-law-30-pro' ); ?></h3>
					<p class="big"><a href="mailto:<?php echo esc_attr( brooks_law_get_option( 'firm_email' ) ); ?>"><?php echo esc_html( brooks_law_get_option( 'firm_email' ) ); ?></a></p>
				</div>
				<div class="contact-item">
					<h3><?php esc_html_e( 'Office', 'brooks-law-30-pro' ); ?></h3>
					<p><?php echo esc_html( brooks_law_get_option( 'firm_address' ) ); ?><br>
					<?php echo esc_html( brooks_law_get_option( 'firm_city_state' ) ); ?></p>
					<p><?php echo esc_html( brooks_law_get_option( 'firm_hours' ) ); ?></p>
				</div>
			</div>

			<?php
			$form_shortcode = trim( (string) brooks_law_get_option( 'contact_form_shortcode', '' ) );
			if ( '' !== $form_shortcode ) :
				?>
				<div class="contact-form-slot">
					<?php echo do_shortcode( $form_shortcode ); ?>
				</div>
			<?php endif; ?>

			<p class="contact-note"><?php echo esc_html( brooks_law_get_option( 'contact_note' ) ); ?></p>
		</div>
	</section>

</main>

<?php
get_footer();
