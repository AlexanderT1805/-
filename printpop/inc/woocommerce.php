<?php
/**
 * WooCommerce: каса, количка, минимална поръчка, фрагмент за броя в количката.
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Брой артикули в количката — обновява се автоматично след добавяне.
 */
function printpop_cart_count_html() {
	$count = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
	$class = $count > 0 ? 'pp-cart-count' : 'pp-cart-count is-empty';
	return sprintf(
		'<span class="%1$s" data-pp-cart-count>%2$s</span>',
		esc_attr( $class ),
		esc_html( $count )
	);
}

function printpop_cart_fragments( $fragments ) {
	$fragments['span.pp-cart-count'] = printpop_cart_count_html();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'printpop_cart_fragments' );

/**
 * Минимална стойност на поръчката (настройва се в Customizer; празно = изключено).
 * Работи и при класическата, и при блоковата количка/каса.
 */
function printpop_check_min_order() {
	$min = (float) printpop_opt( 'min_order', 0 );
	if ( $min <= 0 || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	$subtotal = (float) WC()->cart->get_subtotal();
	if ( $subtotal < $min ) {
		wc_add_notice(
			sprintf(
				/* translators: 1: минимална сума, 2: текуща сума */
				__( 'Минималната стойност на поръчка е %1$s. Сега в количката има %2$s.', 'printpop' ),
				wc_price( $min ),
				wc_price( $subtotal )
			),
			'error'
		);
	}
}
add_action( 'woocommerce_check_cart_items', 'printpop_check_min_order' );

/**
 * Данни при поръчка като гост: име, имейл и телефон са задължителни.
 * Адресните полета се управляват от плъгина за Еконт/Спиди (доставка до офис).
 */
function printpop_required_billing_fields( $fields ) {
	foreach ( array( 'billing_first_name', 'billing_last_name', 'billing_email', 'billing_phone' ) as $key ) {
		if ( isset( $fields[ $key ] ) ) {
			$fields[ $key ]['required'] = true;
		}
	}
	if ( isset( $fields['billing_phone'] ) ) {
		$fields['billing_phone']['label']        = __( 'Телефон', 'printpop' );
		$fields['billing_phone']['autocomplete'] = 'tel';
		$fields['billing_phone']['priority']     = 25;
	}
	return $fields;
}
add_filter( 'woocommerce_billing_fields', 'printpop_required_billing_fields' );

/**
 * Обозначена точка за избор на офис на куриер на касата.
 */
function printpop_checkout_courier_slot() {
	echo '<section class="pp-card pp-courier" aria-labelledby="pp-courier-title">';
	echo '<h3 id="pp-courier-title">' . esc_html__( 'Доставка до офис на Еконт или Спиди', 'printpop' ) . '</h3>';

	if ( printpop_integration( 'courier' ) ) {
		do_action( 'printpop_render_courier_picker' );
	} else {
		echo '<p>' . printpop_pending_badge() . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<p class="pp-muted">' . esc_html__( 'Тук ще се избират куриер и офис, а цената на доставката ще се изчислява автоматично. Изисква плъгин за Еконт/Спиди.', 'printpop' ) . '</p>';
	}
	echo '</section>';
}
add_action( 'woocommerce_before_checkout_billing_form', 'printpop_checkout_courier_slot', 5 );

/**
 * Само карта през myPOS: бележка на касата, ако няма активен метод за плащане.
 */
function printpop_no_gateway_message( $message ) {
	return esc_html__( 'Плащането с карта през myPOS още не е свързано. Моля, свържете се с нас за поръчката.', 'printpop' );
}
add_filter( 'woocommerce_no_available_payment_methods_message', 'printpop_no_gateway_message' );

/**
 * Пагинация/брой на страница в магазина — по-спокойна мрежа.
 */
add_filter( 'loop_shop_columns', function () {
	return 4;
} );
