<?php
/**
 * Обвивка за всички страници на WooCommerce (продукти, архиви).
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="pp-main pp-wc">
	<div class="pp-container">
		<?php woocommerce_content(); ?>
	</div>
</main>
<?php
get_footer();
