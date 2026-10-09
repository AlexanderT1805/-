<?php
/**
 * Резервен шаблон (блог/архиви/търсене).
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="pp-main">
	<div class="pp-container pp-prose-wrap">
		<?php if ( is_home() && ! is_front_page() || is_archive() || is_search() ) : ?>
			<header class="pp-pagehead">
				<h1>
					<?php
					if ( is_search() ) {
						/* translators: %s: търсен израз */
						printf( esc_html__( 'Резултати за „%s“', 'printpop' ), esc_html( get_search_query() ) );
					} elseif ( is_archive() ) {
						the_archive_title();
					} else {
						single_post_title();
					}
					?>
				</h1>
			</header>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="pp-posts">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'pp-post' ); ?>>
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<div class="pp-prose"><?php the_excerpt(); ?></div>
					</article>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Няма намерено съдържание.', 'printpop' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
