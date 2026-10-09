<?php
/**
 * Долна част и мобилна навигация.
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

$contact = printpop_contact();
$socials = printpop_socials();

$info_links = array(
	__( 'Общи условия', 'printpop' )        => printpop_link( 'terms' ),
	__( 'Поверителност', 'printpop' )       => printpop_link( 'privacy' ),
	__( 'Доставка и връщане', 'printpop' )  => printpop_link( 'shipping' ),
);
?>
<footer class="pp-footer">
	<div class="pp-container pp-footer__grid">
		<div class="pp-footer__brand">
			<?php echo printpop_logo(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<p class="pp-muted"><?php esc_html_e( '3D печат по поръчка: от полезни части до персонални подаръци.', 'printpop' ); ?></p>
		</div>

		<nav class="pp-footer__col" aria-label="<?php esc_attr_e( 'Навигация', 'printpop' ); ?>">
			<h2 class="pp-footer__title"><?php esc_html_e( 'Сайт', 'printpop' ); ?></h2>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'depth'          => 1,
						'menu_class'     => 'pp-footer__list',
					)
				);
			} else {
				echo '<ul class="pp-footer__list">';
				$links = array(
					__( 'Открий', 'printpop' )     => printpop_link( 'discover' ),
					__( 'Качи модел', 'printpop' ) => printpop_link( 'upload' ),
					__( 'За нас', 'printpop' )     => printpop_link( 'about' ),
					__( 'Контакти', 'printpop' )   => printpop_link( 'contact' ),
				);
				foreach ( $links as $label => $url ) {
					if ( $url ) {
						printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
					}
				}
				echo '</ul>';
			}
			?>
		</nav>

		<nav class="pp-footer__col" aria-label="<?php esc_attr_e( 'Информация', 'printpop' ); ?>">
			<h2 class="pp-footer__title"><?php esc_html_e( 'Информация', 'printpop' ); ?></h2>
			<ul class="pp-footer__list">
				<?php foreach ( $info_links as $label => $url ) : ?>
					<?php if ( $url ) : ?>
						<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
				<?php $track = function_exists( 'printpop_find_page_with_shortcode' ) ? printpop_find_page_with_shortcode( 'woocommerce_order_tracking' ) : 0; ?>
				<?php if ( $track && 'publish' === get_post_status( $track ) ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $track ) ); ?>"><?php esc_html_e( 'Проследи поръчка', 'printpop' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</nav>

		<div class="pp-footer__col">
			<h2 class="pp-footer__title"><?php esc_html_e( 'Контакти', 'printpop' ); ?></h2>
			<ul class="pp-footer__list">
				<?php if ( $contact['email'] ) : ?>
					<li><a href="mailto:<?php echo esc_attr( $contact['email'] ); ?>"><?php echo esc_html( $contact['email'] ); ?></a></li>
				<?php endif; ?>
				<?php if ( $contact['phone'] ) : ?>
					<li><a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $contact['phone'] ) ); ?>"><?php echo esc_html( $contact['phone'] ); ?></a></li>
				<?php endif; ?>
				<?php foreach ( $socials as $label => $url ) : ?>
					<li><a href="<?php echo esc_url( $url ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $label ); ?></a></li>
				<?php endforeach; ?>
				<?php if ( ! $contact['email'] && ! $contact['phone'] && ! $socials && printpop_link( 'contact' ) ) : ?>
					<li><a href="<?php echo esc_url( printpop_link( 'contact' ) ); ?>"><?php esc_html_e( 'Свържи се с нас', 'printpop' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
	<div class="pp-container pp-footer__legal">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
		<p><?php esc_html_e( 'Доставка до офис на Еконт и Спиди.', 'printpop' ); ?></p>
	</div>
</footer>

<?php
$bottom = array(
	array( 'home', __( 'Начало', 'printpop' ), printpop_link( 'home' ), 'home' ),
	array( 'discover', __( 'Открий', 'printpop' ), printpop_link( 'discover' ), 'compass' ),
	array( 'upload', __( 'Качи модел', 'printpop' ), printpop_link( 'upload' ), 'upload' ),
	array( 'favorites', __( 'Любими', 'printpop' ), printpop_link( 'favorites' ), 'heart' ),
	array( 'account', __( 'Профил', 'printpop' ), printpop_link( 'account' ), 'user' ),
);
$current_url = printpop_current_url();
?>
<nav class="pp-tabbar" aria-label="<?php esc_attr_e( 'Мобилна навигация', 'printpop' ); ?>">
	<ul>
		<?php
		foreach ( $bottom as $item ) :
			list( $key, $label, $url, $icon ) = $item;
			$is_current                       = $url && trailingslashit( $url ) === $current_url;
			if ( 'account' === $key && function_exists( 'is_account_page' ) && is_account_page() ) {
				$is_current = true;
			}
			if ( ! $url ) {
				$url = printpop_link( 'home' ); // Страницата още не е създадена — безопасен резервен адрес.
			}
			?>
			<li>
				<a href="<?php echo esc_url( $url ); ?>" <?php echo $is_current ? 'aria-current="page"' : ''; ?>>
					<?php echo printpop_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span><?php echo esc_html( $label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<div class="pp-toasts" aria-live="polite" aria-atomic="false" data-pp-toasts></div>

<?php wp_footer(); ?>
</body>
</html>
