<?php
/**
 * Помощни функции: настройки, връзки, икони, лого.
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Стойност от Customizer.
 */
function printpop_opt( $key, $default = '' ) {
	return get_theme_mod( 'printpop_' . $key, $default );
}

/**
 * Шаблони на страниците, които темата разпознава (ключ => файл).
 */
function printpop_templates() {
	return array(
		'discover'  => 'template-discover.php',
		'upload'    => 'template-upload.php',
		'about'     => 'template-about.php',
		'contact'   => 'template-contact.php',
		'favorites' => 'template-favorites.php',
		'terms'     => 'template-terms.php',
		'privacy'   => 'template-privacy.php',
		'shipping'  => 'template-shipping.php',
	);
}

/**
 * Адрес на публикувана страница по нейния шаблон.
 */
function printpop_page_url_by_template( $template ) {
	static $cache = array();
	if ( ! isset( $cache[ $template ] ) ) {
		$ids               = get_posts(
			array(
				'post_type'        => 'page',
				'post_status'      => 'publish',
				'posts_per_page'   => 1,
				'meta_key'         => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'       => $template, // phpcs:ignore WordPress.DB.SlowDBQuery
				'fields'           => 'ids',
				'no_found_rows'    => true,
				'suppress_filters' => false,
			)
		);
		$cache[ $template ] = $ids ? get_permalink( $ids[0] ) : '';
	}
	return $cache[ $template ];
}

/**
 * Връзка към ключова страница на сайта. Връща празен низ, ако страницата не съществува.
 */
function printpop_link( $key ) {
	$wc = class_exists( 'WooCommerce' );

	switch ( $key ) {
		case 'home':
			return home_url( '/' );
		case 'shop':
			return $wc ? wc_get_page_permalink( 'shop' ) : '';
		case 'cart':
			return $wc ? wc_get_cart_url() : '';
		case 'checkout':
			return $wc ? wc_get_checkout_url() : '';
		case 'account':
			return $wc ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
		case 'discover':
			$url = printpop_page_url_by_template( 'template-discover.php' );
			return $url ? $url : ( $wc ? wc_get_page_permalink( 'shop' ) : '' );
		default:
			$templates = printpop_templates();
			if ( isset( $templates[ $key ] ) ) {
				return printpop_page_url_by_template( $templates[ $key ] );
			}
	}
	return '';
}

/**
 * Дали дадена интеграция е свързана (плъгин я обявява чрез филтър).
 *
 * Ключове: upload_file, upload_idea, viewer, favorites, courier, pricing.
 */
function printpop_integration( $key ) {
	return (bool) apply_filters( 'printpop_integration_' . $key, false );
}

/**
 * Етикет „Предстои свързване“ за места, които очакват плъгин.
 */
function printpop_pending_badge() {
	return '<span class="pp-badge pp-badge--pending">' . esc_html__( 'Предстои свързване', 'printpop' ) . '</span>';
}

/**
 * Лого. Ако има качено лого от Customizer — използва него, иначе текстов знак.
 */
function printpop_logo() {
	if ( has_custom_logo() ) {
		return get_custom_logo();
	}
	$out  = '<a class="pp-logo" href="' . esc_url( home_url( '/' ) ) . '" rel="home" aria-label="' . esc_attr( get_bloginfo( 'name' ) . ' — ' . __( 'начало', 'printpop' ) ) . '">';
	$out .= '<span class="pp-logo__word" aria-hidden="true">Print</span><span class="pp-logo__pop" aria-hidden="true">pop</span>';
	$out .= '</a>';
	return $out;
}

/**
 * Данни за контакт от Customizer.
 */
function printpop_contact() {
	return array(
		'email'   => sanitize_email( printpop_opt( 'contact_email' ) ),
		'phone'   => sanitize_text_field( printpop_opt( 'contact_phone' ) ),
		'address' => sanitize_text_field( printpop_opt( 'contact_address' ) ),
		'hours'   => sanitize_text_field( printpop_opt( 'contact_hours' ) ),
	);
}

/**
 * Социални мрежи от Customizer (само попълнените).
 */
function printpop_socials() {
	$map = array(
		'instagram' => 'Instagram',
		'facebook'  => 'Facebook',
		'tiktok'    => 'TikTok',
	);
	$out = array();
	foreach ( $map as $key => $label ) {
		$url = esc_url( printpop_opt( 'social_' . $key ) );
		if ( $url ) {
			$out[ $label ] = $url;
		}
	}
	return $out;
}

/**
 * Показва шорткод от настройка, ако е зададен. Връща true при изведено съдържание.
 */
function printpop_render_shortcode_setting( $key ) {
	$code = trim( (string) printpop_opt( $key ) );
	if ( '' === $code || '[' !== substr( $code, 0, 1 ) ) {
		return false;
	}
	echo do_shortcode( wp_kses_post( $code ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	return true;
}

/**
 * Икони (24×24, контурни). Връща готов SVG низ.
 */
function printpop_icon( $name, $class = '' ) {
	static $icons = array(
		'home'     => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10.5V20h13v-9.5"/><path d="M10 20v-5h4v5"/>',
		'compass'  => '<circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5z"/>',
		'upload'   => '<path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M4 15v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4"/>',
		'heart'    => '<path d="M12 20s-7.5-4.4-7.5-10A4.3 4.3 0 0 1 12 7.4 4.3 4.3 0 0 1 19.5 10c0 5.6-7.5 10-7.5 10z"/>',
		'user'     => '<circle cx="12" cy="8.5" r="3.5"/><path d="M5 20c.6-3.6 3.4-5.5 7-5.5s6.4 1.9 7 5.5"/>',
		'cart'     => '<path d="M3 4h2.2l2 11h10.6l1.7-8H6.3"/><circle cx="9" cy="19.5" r="1.2"/><circle cx="17" cy="19.5" r="1.2"/>',
		'search'   => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
		'filter'   => '<path d="M4 6h16"/><path d="M7 12h10"/><path d="M10 18h4"/>',
		'close'    => '<path d="m6 6 12 12"/><path d="M18 6 6 18"/>',
		'check'    => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
		'play'     => '<path d="M8 5.5v13l11-6.5z"/>',
		'arrow'    => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
		'menu'     => '<path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/>',
		'mail'     => '<rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="m4 7.5 8 6 8-6"/>',
		'phone'    => '<path d="M6.5 4h3l1.5 4-2 1.5a11 11 0 0 0 5.5 5.5L16 13l4 1.5v3a2 2 0 0 1-2 2A14 14 0 0 1 4.5 6a2 2 0 0 1 2-2z"/>',
		'pin'      => '<path d="M12 21s6.5-5.7 6.5-11A6.5 6.5 0 0 0 5.5 10c0 5.3 6.5 11 6.5 11z"/><circle cx="12" cy="10" r="2.3"/>',
		'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
		'cube'     => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9z"/><path d="m4 7.5 8 4.5 8-4.5"/><path d="M12 12v9"/>',
		'wrench'   => '<path d="M14.5 6a4 4 0 0 0 4.8 5.2L20 12l-7 7a2.1 2.1 0 0 1-3-3l7-7z"/><path d="m15 5.5 3.5 3.5"/>',
		'figure'   => '<circle cx="12" cy="5.5" r="2.5"/><path d="M7.5 21v-7L5 11.5 8 9h8l3 2.5-2.5 2.5v7"/>',
		'gift'     => '<rect x="4" y="9" width="16" height="11" rx="1.5"/><path d="M3 9h18v3H3z"/><path d="M12 9v11"/><path d="M12 9c-1-3.5-5-4-5-1.5S10 9 12 9zm0 0c1-3.5 5-4 5-1.5S14 9 12 9z"/>',
		'photo'    => '<rect x="3.5" y="5" width="17" height="14" rx="2"/><circle cx="9" cy="10" r="1.6"/><path d="m4 17 5-4.5 3.5 3 3-2.5L20 16"/>',
		'sparkle'  => '<path d="M12 4v4M12 16v4M4 12h4M16 12h4"/><path d="m8 8 1.5 1.5M14.5 14.5 16 16M16 8l-1.5 1.5M9.5 14.5 8 16"/>',
		'truck'    => '<path d="M3 6.5h11V16H3z"/><path d="M14 10h4l3 3v3h-7z"/><circle cx="7" cy="17.5" r="1.7"/><circle cx="17" cy="17.5" r="1.7"/>',
		'shield'   => '<path d="M12 3.5 5 6v5.5c0 4.3 3 7.6 7 9 4-1.4 7-4.7 7-9V6z"/><path d="m9 12 2.2 2.2L15.5 10"/>',
		'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/>',
		'chevron'  => '<path d="m6 9 6 6 6-6"/>',
		'plus'     => '<path d="M12 5v14M5 12h14"/>',
		'minus'    => '<path d="M5 12h14"/>',
		'rotate'   => '<path d="M20 12a8 8 0 1 1-2.5-5.8"/><path d="M20 4v4.5h-4.5"/>',
		'lock'     => '<rect x="5" y="10.5" width="14" height="9.5" rx="2"/><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5"/>',
		'skip'     => '<path d="m6 6 12 12"/><path d="M18 6 6 18"/>',
		'card'     => '<rect x="3" y="5.5" width="18" height="13" rx="2"/><path d="M3 10h18"/>',
		'queue'    => '<path d="M4 7h12"/><path d="M4 12h9"/><path d="M4 17h6"/><path d="m16 15 2 2 3.5-4"/>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="pp-icon %1$s" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%2$s</svg>',
		esc_attr( $class ),
		$icons[ $name ] // phpcs:ignore WordPress.Security.EscapeOutput -- статични SVG пътища.
	);
}

/**
 * Основно меню по подразбиране, когато няма зададено в Външен вид → Менюта.
 */
function printpop_fallback_menu() {
	$items = array(
		__( 'Начало', 'printpop' )        => printpop_link( 'home' ),
		__( 'Открий', 'printpop' )        => printpop_link( 'discover' ),
		__( 'Качи модел', 'printpop' )    => printpop_link( 'upload' ),
		__( 'За нас', 'printpop' )        => printpop_link( 'about' ),
		__( 'Контакти', 'printpop' )      => printpop_link( 'contact' ),
	);

	echo '<ul class="pp-menu">';
	foreach ( $items as $label => $url ) {
		if ( ! $url ) {
			continue;
		}
		$current = trailingslashit( $url ) === printpop_current_url() ? ' aria-current="page"' : '';
		printf( '<li><a href="%s"%s>%s</a></li>', esc_url( $url ), $current, esc_html( $label ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</ul>';
}

/**
 * Дали текущата заявка е към страница с даден шаблон.
 */
function printpop_is_template( $file ) {
	return is_page() && get_page_template_slug() === $file;
}

/**
 * Адрес на текущата страница без параметри (за отбелязване на активен елемент).
 */
function printpop_current_url() {
	global $wp;
	return trailingslashit( home_url( isset( $wp->request ) ? $wp->request : '' ) );
}
