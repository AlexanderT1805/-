<?php
/**
 * Временна правна страница: показва текста от редактора, а ако е празен — структура за попълване.
 * Темата НЕ съдържа окончателни правни текстове; те трябва да се изготвят/проверят от юрист.
 *
 * @param array $args { title: string, intro: string, outline: string[] }
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

$title   = isset( $args['title'] ) ? $args['title'] : get_the_title();
$intro   = isset( $args['intro'] ) ? $args['intro'] : '';
$outline = isset( $args['outline'] ) ? $args['outline'] : array();
?>
<main id="main" class="pp-main">
	<div class="pp-container pp-narrow">
		<?php
		while ( have_posts() ) :
			the_post();
			$has_content = '' !== trim( wp_strip_all_tags( get_the_content() ) );
			?>
			<header class="pp-pagehead">
				<h1><?php echo esc_html( get_the_title() ? get_the_title() : $title ); ?></h1>
			</header>

			<?php if ( $has_content ) : ?>
				<div class="pp-prose"><?php the_content(); ?></div>
			<?php else : ?>
				<div class="pp-card pp-legal-placeholder">
					<p><?php echo printpop_pending_badge(); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<p><strong><?php esc_html_e( 'Временна страница', 'printpop' ); ?></strong></p>
					<p class="pp-muted"><?php esc_html_e( 'Окончателният текст предстои. Той трябва да бъде изготвен или одобрен от юрист, преди сайтът да приема поръчки.', 'printpop' ); ?></p>
					<?php if ( $intro ) : ?>
						<p class="pp-muted"><?php echo esc_html( $intro ); ?></p>
					<?php endif; ?>
					<?php if ( $outline ) : ?>
						<h2 class="pp-small-title"><?php esc_html_e( 'Теми, които да бъдат покрити', 'printpop' ); ?></h2>
						<ul class="pp-bullets">
							<?php foreach ( $outline as $line ) : ?>
								<li><?php echo esc_html( $line ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ( current_user_can( 'edit_post', get_the_ID() ) ) : ?>
						<p><a class="pp-btn pp-btn--ghost pp-btn--sm" href="<?php echo esc_url( get_edit_post_link() ); ?>"><?php esc_html_e( 'Редактирай страницата', 'printpop' ); ?></a></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endwhile; ?>
	</div>
</main>
