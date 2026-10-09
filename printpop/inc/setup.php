<?php
/**
 * Настройка на темата, зареждане на стилове и скриптове.
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

function printpop_setup() {
	load_theme_textdomain( 'printpop', PRINTPOP_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 48,
			'width'       => 180,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// WooCommerce.
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 480,
			'single_image_width'    => 900,
			'product_grid'          => array(
				'default_columns' => 4,
				'min_columns'     => 2,
				'max_columns'     => 4,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary' => __( 'Основно меню', 'printpop' ),
			'footer'  => __( 'Меню в долната част', 'printpop' ),
		)
	);
}
add_action( 'after_setup_theme', 'printpop_setup' );

function printpop_content_width() {
	$GLOBALS['content_width'] = 1120;
}
add_action( 'after_setup_theme', 'printpop_content_width', 0 );

/**
 * Предварително зареждане на шрифта (кирилица + латиница), за да няма скачане на текста.
 */
function printpop_preload_fonts() {
	foreach ( array( 'montserrat-cyrillic-wght-normal', 'montserrat-latin-wght-normal' ) as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( PRINTPOP_URI . '/assets/fonts/' . $font . '.woff2' )
		);
	}
}
add_action( 'wp_head', 'printpop_preload_fonts', 1 );

function printpop_enqueue() {
	wp_enqueue_style( 'printpop', PRINTPOP_URI . '/assets/css/main.css', array(), PRINTPOP_VERSION );

	$script_args = array(
		'in_footer' => true,
		'strategy'  => 'defer',
	);

	wp_enqueue_script( 'printpop-main', PRINTPOP_URI . '/assets/js/main.js', array(), PRINTPOP_VERSION, $script_args );

	$favorites_on = printpop_integration( 'favorites' );

	wp_localize_script(
		'printpop-main',
		'printpopData',
		array(
			'isLoggedIn'      => is_user_logged_in(),
			'accountUrl'      => printpop_link( 'account' ),
			'cartUrl'         => printpop_link( 'cart' ),
			'favoritesActive' => $favorites_on,
			'fragmentsUrl'    => class_exists( 'WC_AJAX' ) ? esc_url_raw( WC_AJAX::get_endpoint( 'get_refreshed_fragments' ) ) : '',
			'i18n'            => array(
				'added'          => __( 'Добавено в количката', 'printpop' ),
				'toCart'         => __( 'Към количката', 'printpop' ),
				'keepBrowsing'   => __( 'Продължи разглеждането', 'printpop' ),
				'addError'       => __( 'Не успяхме да добавим продукта. Опитай отново.', 'printpop' ),
				'loginTitle'     => __( 'Влез, за да запазиш в „Любими“', 'printpop' ),
				'loginText'      => __( 'Любимите се пазят в профила ти. Влез или създай профил — отнема минута.', 'printpop' ),
				'loginCta'       => __( 'Вход / регистрация', 'printpop' ),
				'later'          => __( 'По-късно', 'printpop' ),
				'favPending'     => __( 'Функцията „Любими“ още не е свързана със сайта. Моделът не е запазен.', 'printpop' ),
				'skipped'        => __( 'Пропуснато', 'printpop' ),
				'saved'          => __( 'Запазено в „Любими“', 'printpop' ),
				'noMore'         => __( 'Това е последният модел в тази подредба.', 'printpop' ),
				'distortWarning' => __( 'Размерите не са в първоначалното съотношение — моделът може да се изкриви.', 'printpop' ),
				'distortHint'    => __( 'Промяната само на един размер може да изкриви модела.', 'printpop' ),
				'priceOnQuote'   => __( 'Цената се уточнява', 'printpop' ),
				'fileTooBig'     => __( 'Файлът е твърде голям.', 'printpop' ),
				'badType'        => __( 'Този формат не се приема. Приемани: ', 'printpop' ),
				'close'          => __( 'Затвори', 'printpop' ),
			),
		)
	);

	if ( printpop_is_template( 'template-discover.php' ) ) {
		wp_enqueue_script( 'printpop-discover', PRINTPOP_URI . '/assets/js/discover.js', array( 'printpop-main' ), PRINTPOP_VERSION, $script_args );
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_script( 'printpop-product', PRINTPOP_URI . '/assets/js/product.js', array( 'printpop-main' ), PRINTPOP_VERSION, $script_args );
	}

	if ( printpop_is_template( 'template-upload.php' ) ) {
		wp_enqueue_script( 'printpop-upload', PRINTPOP_URI . '/assets/js/upload.js', array( 'printpop-main' ), PRINTPOP_VERSION, $script_args );
	}
}
add_action( 'wp_enqueue_scripts', 'printpop_enqueue', 20 );

/**
 * Използваме собствени стилове вместо козметичните на WooCommerce
 * (структурните „layout“ и „smallscreen“ остават).
 */
add_filter(
	'woocommerce_enqueue_styles',
	function ( $styles ) {
		unset( $styles['woocommerce-general'] );
		return $styles;
	}
);

/**
 * Класове на <body>.
 */
function printpop_body_classes( $classes ) {
	$classes[] = 'pp-theme';
	if ( printpop_is_template( 'template-discover.php' ) ) {
		$classes[] = 'pp-is-discover';
	}
	return $classes;
}
add_filter( 'body_class', 'printpop_body_classes' );
