<?php
/**
 * Brooks Law v2 — default page template.
 *
 * @package Brooks_Law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="content" class="site-main">
	<?php while ( have_posts() ) : the_post(); ?>

		<?php $brooks_hero = brooks_law_page_hero_media(); ?>
		<div class="page-hero<?php echo esc_attr( $brooks_hero['class'] ); ?>"<?php echo $brooks_hero['style']; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?>>
			<?php echo $brooks_hero['media']; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_get_attachment_image() output. ?>
			<div class="wrap">
				<?php brooks_law_breadcrumb(); ?>
				<h1><?php the_title(); ?></h1>
				<?php brooks_law_page_contact_toggle(); ?>
			</div>
		</div>

		<?php brooks_law_page_action_row(); ?>

		<div class="wrap section">
			<div class="content-layout">
				<article <?php post_class(); ?>>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				</article>

				<div class="content-sidebar">
					<?php brooks_law_contact_box(); ?>
				</div>
			</div>
		</div>

	<?php endwhile; ?>
</main>

<?php
get_footer();
