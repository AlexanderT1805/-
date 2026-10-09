<?php
/**
 * Template Name: Любими
 *
 * Запазването изисква профил и плъгин за любими. Темата не симулира запазване.
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
			<h1><?php echo esc_html( get_the_title() ? get_the_title() : __( 'Любими', 'printpop' ) ); ?></h1>
		</header>

		<?php if ( ! is_user_logged_in() ) : ?>
			<div class="pp-empty">
				<span class="pp-empty__icon"><?php echo printpop_icon( 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<h2><?php esc_html_e( 'Влез, за да виждаш любимите си модели', 'printpop' ); ?></h2>
				<p class="pp-muted"><?php esc_html_e( 'Любимите се пазят в профила ти.', 'printpop' ); ?></p>
				<?php if ( $account ) : ?>
					<a class="pp-btn pp-btn--primary" href="<?php echo esc_url( $account ); ?>"><?php esc_html_e( 'Вход / регистрация', 'printpop' ); ?></a>
				<?php endif; ?>
			</div>
		<?php elseif ( printpop_integration( 'favorites' ) ) : ?>
			<?php do_action( 'printpop_render_favorites_page' ); ?>
		<?php else : ?>
			<div class="pp-empty">
				<span class="pp-empty__icon"><?php echo printpop_icon( 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<h2><?php esc_html_e( 'Любимите още не са свързани', 'printpop' ); ?></h2>
				<p class="pp-muted"><?php echo printpop_pending_badge(); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Изисква плъгин за любими. Дотогава моделите не се запазват.', 'printpop' ); ?></p>
				<?php if ( printpop_link( 'discover' ) ) : ?>
					<a class="pp-btn pp-btn--ghost" href="<?php echo esc_url( printpop_link( 'discover' ) ); ?>"><?php esc_html_e( 'Разгледай модели', 'printpop' ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
