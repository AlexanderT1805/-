<?php
/**
 * Template Name: За нас
 *
 * Съдържанието се редактира от редактора на страницата. Ако е празно, се показва кратък примерен
 * вариант, който можеш да замениш.
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="pp-main">
	<div class="pp-container pp-narrow">
		<?php
		while ( have_posts() ) :
			the_post();
			$has_content = '' !== trim( wp_strip_all_tags( get_the_content() ) );
			?>
			<header class="pp-pagehead pp-pagehead--center">
				<p class="pp-eyebrow"><?php esc_html_e( 'За нас', 'printpop' ); ?></p>
				<h1><?php echo esc_html( get_the_title() ? get_the_title() : __( 'За PrintPop', 'printpop' ) ); ?></h1>
			</header>

			<?php if ( $has_content ) : ?>
				<div class="pp-prose"><?php the_content(); ?></div>
			<?php else : ?>
				<div class="pp-prose">
					<p class="pp-lead"><?php esc_html_e( 'PrintPop е 3D печат по поръчка — от полезни части до персонални подаръци.', 'printpop' ); ?></p>
					<p><?php esc_html_e( 'Каталогът ни е подбран от нас: моделите се подготвят за печат, проверяват се в софтуер за нарязване и при нужда се правят пробни разпечатки. Ако не намираш това, което търсиш, изпрати идея, снимка или готов 3D файл и ще получиш оферта.', 'printpop' ); ?></p>
					<p><?php esc_html_e( 'В началото доставяме в България до офис на Еконт или Спиди.', 'printpop' ); ?></p>
					<?php if ( current_user_can( 'edit_post', get_the_ID() ) ) : ?>
						<p class="pp-note pp-note--soft">
							<?php echo printpop_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<span><?php esc_html_e( 'Това е примерен текст, видим само докато страницата е празна. Редактирай я от редактора на WordPress.', 'printpop' ); ?>
							<a href="<?php echo esc_url( get_edit_post_link() ); ?>"><?php esc_html_e( 'Редактирай', 'printpop' ); ?></a></span>
						</p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endwhile; ?>

		<div class="pp-actions pp-actions--center">
			<?php if ( printpop_link( 'discover' ) ) : ?>
				<a class="pp-btn pp-btn--primary" href="<?php echo esc_url( printpop_link( 'discover' ) ); ?>"><?php esc_html_e( 'Разгледай модели', 'printpop' ); ?></a>
			<?php endif; ?>
			<?php if ( printpop_link( 'contact' ) ) : ?>
				<a class="pp-btn pp-btn--ghost" href="<?php echo esc_url( printpop_link( 'contact' ) ); ?>"><?php esc_html_e( 'Свържи се с нас', 'printpop' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php
get_footer();
