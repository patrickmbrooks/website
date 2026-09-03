<?php
/**
 * Brooks Law v2 — search results.
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
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Search results for “%s”', 'brooks-law-30-pro' ),
					esc_html( get_search_query() )
				);
				?>
			</h1>
		</div>
	</div>

	<div class="wrap section">
		<?php get_search_form(); ?>

		<?php if ( have_posts() ) : ?>
			<ul class="post-list">
				<?php while ( have_posts() ) : the_post(); ?>
					<li>
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php the_excerpt(); ?>
					</li>
				<?php endwhile; ?>
			</ul>

			<nav class="pagination" aria-label="<?php esc_attr_e( 'Search results', 'brooks-law-30-pro' ); ?>">
				<?php echo wp_kses_post( paginate_links( array( 'type' => 'plain' ) ) ); ?>
			</nav>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing matched that search. Try different words, or start from one of the practice areas below.', 'brooks-law-30-pro' ); ?></p>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/criminal-defense-2/' ) ); ?>"><?php esc_html_e( 'Criminal Defense', 'brooks-law-30-pro' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/dui/' ) ); ?>"><?php esc_html_e( 'DUI Defense', 'brooks-law-30-pro' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/domestic-violence/' ) ); ?>"><?php esc_html_e( 'Domestic Assault', 'brooks-law-30-pro' ); ?></a></li>
			</ul>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
