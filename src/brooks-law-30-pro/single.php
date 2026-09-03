<?php
/**
 * Brooks Law v2 — single post.
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
			</div>
		</div>

		<div class="wrap section">
			<div class="content-layout">
				<article <?php post_class(); ?>>
					<p class="post-meta">
						<?php
						printf(
							/* translators: %s: publish date. */
							esc_html__( 'Published %s', 'brooks-law-30-pro' ),
							'<time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>'
						);
						?>
					</p>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
						<?php brooks_law_post_nav_render(); ?>
					</article>

				<div class="content-sidebar">
					<?php brooks_law_contact_box(); ?>
				</div>
			</div>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>

	<?php endwhile; ?>
</main>

<?php
get_footer();
