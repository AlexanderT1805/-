<?php
/**
 * Страница 404.
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="pp-main">
	<div class="pp-container pp-empty">
		<span class="pp-empty__icon"><?php echo printpop_icon( 'cube' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<h1><?php esc_html_e( 'Тази страница не е намерена', 'printpop' ); ?></h1>
		<p class="pp-muted"><?php esc_html_e( 'Може адресът да е променен. Върни се към началото или разгледай моделите.', 'printpop' ); ?></p>
		<div class="pp-actions">
			<a class="pp-btn pp-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Към началото', 'printpop' ); ?></a>
			<?php if ( printpop_link( 'discover' ) ) : ?>
				<a class="pp-btn pp-btn--ghost" href="<?php echo esc_url( printpop_link( 'discover' ) ); ?>"><?php esc_html_e( 'Разгледай модели', 'printpop' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php
get_footer();
