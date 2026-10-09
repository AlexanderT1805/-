<?php
/**
 * Template Name: Качи модел или изпрати идея
 *
 * Два пътя: готов 3D файл и снимка/текст/идея. Самото качване се прави от плъгин
 * (шорткод в Външен вид → Customize → PrintPop → Форми, или действията printpop_render_*_form).
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

get_header();

$account = printpop_link( 'account' );
?>
<main id="main" class="pp-main">
	<div class="pp-container">
		<header class="pp-pagehead pp-pagehead--center">
			<p class="pp-eyebrow"><?php esc_html_e( 'Персонален печат', 'printpop' ); ?></p>
			<h1><?php esc_html_e( 'Качи модел или изпрати идея', 'printpop' ); ?></h1>
			<p class="pp-lead"><?php esc_html_e( 'Избери как искаш да започнем.', 'printpop' ); ?></p>
		</header>

		<div class="pp-paths" data-pp-paths>
			<div class="pp-pathtabs" role="tablist" aria-label="<?php esc_attr_e( 'Избор на път', 'printpop' ); ?>">
				<button type="button" role="tab" class="pp-pathtab" id="pp-path-model-tab" aria-controls="pp-path-model" aria-selected="true" data-pp-path="model">
					<?php echo printpop_icon( 'cube' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="pp-pathtab__title"><?php esc_html_e( 'Имам готов 3D файл', 'printpop' ); ?></span>
					<span class="pp-pathtab__text"><?php esc_html_e( 'STL, 3MF, OBJ, STEP — проверяваме го преди печат.', 'printpop' ); ?></span>
				</button>
				<button type="button" role="tab" class="pp-pathtab" id="pp-path-idea-tab" aria-controls="pp-path-idea" aria-selected="false" tabindex="-1" data-pp-path="idea">
					<?php echo printpop_icon( 'photo' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="pp-pathtab__title"><?php esc_html_e( 'Имам снимка, текст или идея', 'printpop' ); ?></span>
					<span class="pp-pathtab__text"><?php esc_html_e( 'Ние правим модела и ти изпращаме оферта.', 'printpop' ); ?></span>
				</button>
			</div>

			<section class="pp-pathpanel pp-card" role="tabpanel" id="pp-path-model" aria-labelledby="pp-path-model-tab">
				<h2><?php esc_html_e( 'Готов 3D файл', 'printpop' ); ?></h2>
				<?php if ( ! printpop_integration( 'upload_file' ) && ! trim( (string) printpop_opt( 'form_model' ) ) ) : ?>
					<p class="pp-pending"><?php echo printpop_pending_badge(); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Дизайнът е готов, но качването още не е свързано. Бутонът е изключен.', 'printpop' ); ?></p>
				<?php endif; ?>
				<?php
				if ( printpop_integration( 'upload_file' ) ) {
					do_action( 'printpop_render_model_form' );
				} elseif ( ! printpop_render_shortcode_setting( 'form_model' ) ) {
					get_template_part( 'template-parts/upload', 'model' );
				}
				?>
			</section>

			<section class="pp-pathpanel pp-card" role="tabpanel" id="pp-path-idea" aria-labelledby="pp-path-idea-tab" hidden>
				<h2><?php esc_html_e( 'Снимка, текст или идея', 'printpop' ); ?></h2>
				<?php if ( ! printpop_integration( 'upload_idea' ) && ! trim( (string) printpop_opt( 'form_idea' ) ) ) : ?>
					<p class="pp-pending"><?php echo printpop_pending_badge(); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Дизайнът е готов, но изпращането още не е свързано. Бутонът е изключен.', 'printpop' ); ?></p>
				<?php endif; ?>
				<?php
				if ( printpop_integration( 'upload_idea' ) ) {
					do_action( 'printpop_render_idea_form' );
				} elseif ( ! printpop_render_shortcode_setting( 'form_idea' ) ) {
					get_template_part( 'template-parts/upload', 'idea' );
				}
				?>
				<aside class="pp-callout">
					<?php echo printpop_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<div>
						<strong><?php esc_html_e( 'Как става офертата', 'printpop' ); ?></strong>
						<p class="pp-muted">
							<?php esc_html_e( 'Преглеждаме заявката и ти изпращаме оферта. За да я видиш и платиш, създаваш профил.', 'printpop' ); ?>
							<?php if ( $account && ! is_user_logged_in() ) : ?>
								<a href="<?php echo esc_url( $account ); ?>"><?php esc_html_e( 'Вход / регистрация', 'printpop' ); ?></a>
							<?php endif; ?>
						</p>
					</div>
				</aside>
			</section>
		</div>

		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				if ( trim( get_the_content() ) ) :
					?>
					<div class="pp-prose pp-prose--after"><?php the_content(); ?></div>
					<?php
				endif;
			endwhile;
			?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
