<?php
/**
 * Обикновена страница (в т.ч. Количка, Поръчка и Профил от WooCommerce).
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="pp-main">
	<div class="pp-container pp-prose-wrap">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'pp-page' ); ?>>
				<header class="pp-pagehead">
					<h1><?php the_title(); ?></h1>
				</header>
				<div class="pp-prose">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	</div>
</main>
<?php
get_footer();
