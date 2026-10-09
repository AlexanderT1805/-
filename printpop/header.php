<?php
/**
 * Горна част: лого, меню, профил и количка.
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#ffffff">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Към съдържанието', 'printpop' ); ?></a>

<header class="pp-header" data-pp-header>
	<div class="pp-container pp-header__inner">
		<div class="pp-header__brand"><?php echo printpop_logo(); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>

		<nav id="pp-nav" class="pp-nav" aria-label="<?php esc_attr_e( 'Основна навигация', 'printpop' ); ?>" data-pp-nav>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'pp-menu',
					'fallback_cb'    => 'printpop_fallback_menu',
					'depth'          => 1,
				)
			);
			?>
		</nav>

		<div class="pp-header__actions">
			<?php if ( printpop_link( 'account' ) ) : ?>
				<a class="pp-iconbtn pp-iconbtn--desktop" href="<?php echo esc_url( printpop_link( 'account' ) ); ?>">
					<?php echo printpop_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Профил', 'printpop' ); ?></span>
				</a>
			<?php endif; ?>
			<?php if ( printpop_link( 'cart' ) ) : ?>
				<a class="pp-iconbtn pp-iconbtn--cart" href="<?php echo esc_url( printpop_link( 'cart' ) ); ?>">
					<?php echo printpop_icon( 'cart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Количка', 'printpop' ); ?></span>
					<?php echo function_exists( 'printpop_cart_count_html' ) ? printpop_cart_count_html() : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
			<?php endif; ?>
			<button type="button" class="pp-iconbtn pp-iconbtn--menu" aria-expanded="false" aria-controls="pp-nav" data-pp-menu-toggle>
				<?php echo printpop_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Меню', 'printpop' ); ?></span>
			</button>
		</div>
	</div>
</header>
