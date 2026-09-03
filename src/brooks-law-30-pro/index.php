<?php
/**
 * Brooks Law v2 — index / blog listing.
 *
 * @package Brooks_Law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="content" class="site-main">
	<div class="page-hero">
		<div class="wrap">
			<h1>
				<?php
				if ( is_home() && ! is_front_page() ) {
					single_post_title();
				} elseif ( is_archive() ) {
					the_archive_title();
				} else {
					esc_html_e( 'Legal Updates', 'brooks-law-30-pro' );
				}
				?>
			</h1>
		</div>
	</div>

	<div class="wrap section">
		<?php if ( have_posts() ) : ?>
			<ul class="post-list">
				<?php while ( have_posts() ) : the_post(); ?>
					<li>
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="post-meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
						<?php the_excerpt(); ?>
					</li>
				<?php endwhile; ?>
			</ul>

			<nav class="pagination" aria-label="<?php esc_attr_e( 'Posts', 'brooks-law-30-pro' ); ?>">
				<?php echo wp_kses_post( paginate_links( array( 'type' => 'plain' ) ) ); ?>
			</nav>
		<?php else : ?>
			<p><?php esc_html_e( 'No posts to show yet.', 'brooks-law-30-pro' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
