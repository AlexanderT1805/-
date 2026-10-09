<?php
/**
 * PrintPop — функции на темата.
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

define( 'PRINTPOP_VERSION', '1.0.0' );
define( 'PRINTPOP_DIR', get_template_directory() );
define( 'PRINTPOP_URI', get_template_directory_uri() );

require_once PRINTPOP_DIR . '/inc/helpers.php';
require_once PRINTPOP_DIR . '/inc/setup.php';
require_once PRINTPOP_DIR . '/inc/customizer.php';
require_once PRINTPOP_DIR . '/inc/setup-pages.php';
require_once PRINTPOP_DIR . '/inc/integrations.php';

if ( class_exists( 'WooCommerce' ) ) {
	require_once PRINTPOP_DIR . '/inc/woocommerce.php';
	require_once PRINTPOP_DIR . '/inc/product-meta.php';
	require_once PRINTPOP_DIR . '/inc/product-options.php';
	require_once PRINTPOP_DIR . '/inc/order-status.php';
	require_once PRINTPOP_DIR . '/inc/discover.php';
}
