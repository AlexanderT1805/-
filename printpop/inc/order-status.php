<?php
/**
 * Статуси на поръчката за клиента: Получена → Печата се → Изпратена → Доставена.
 *
 * Съответствие със статусите на WooCommerce:
 *  Получена  = „В обработка“ (wc-processing, след успешно плащане) — преименуван
 *  Печата се = wc-printing  (нов)
 *  Изпратена = wc-shipped   (нов)
 *  Доставена = „Завършена“ (wc-completed) — преименуван; маркирайте я само при доставка.
 *
 * Имейл при промяна и SMS при „Изпратена“ изискват отделна интеграция (вж. README).
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

function printpop_register_order_statuses() {
	register_post_status(
		'wc-printing',
		array(
			'label'                     => _x( 'Печата се', 'Order status', 'printpop' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: брой */
			'label_count'               => _n_noop( 'Печата се <span class="count">(%s)</span>', 'Печата се <span class="count">(%s)</span>', 'printpop' ),
		)
	);
	register_post_status(
		'wc-shipped',
		array(
			'label'                     => _x( 'Изпратена', 'Order status', 'printpop' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: брой */
			'label_count'               => _n_noop( 'Изпратена <span class="count">(%s)</span>', 'Изпратени <span class="count">(%s)</span>', 'printpop' ),
		)
	);
}
add_action( 'init', 'printpop_register_order_statuses' );

function printpop_order_statuses( $statuses ) {
	$ordered = array();
	foreach ( $statuses as $key => $label ) {
		if ( 'wc-processing' === $key ) {
			$label = _x( 'Получена', 'Order status', 'printpop' );
		}
		if ( 'wc-completed' === $key ) {
			$label = _x( 'Доставена', 'Order status', 'printpop' );
		}
		$ordered[ $key ] = $label;
		if ( 'wc-processing' === $key ) {
			$ordered['wc-printing'] = _x( 'Печата се', 'Order status', 'printpop' );
			$ordered['wc-shipped']  = _x( 'Изпратена', 'Order status', 'printpop' );
		}
	}
	return $ordered;
}
add_filter( 'wc_order_statuses', 'printpop_order_statuses' );

/**
 * Плащането е получено и за новите статуси (за отчети и стойности на клиента).
 */
add_filter(
	'woocommerce_order_is_paid_statuses',
	function ( $statuses ) {
		return array_merge( $statuses, array( 'printing', 'shipped' ) );
	}
);

/**
 * Етапи за клиента (4 стъпки) и къде е поръчката в момента.
 *
 * @return array{steps: array<string,string>, current: int, halted: bool}|null
 */
function printpop_order_progress( $order ) {
	$steps = array(
		'received'  => __( 'Получена', 'printpop' ),
		'printing'  => __( 'Печата се', 'printpop' ),
		'shipped'   => __( 'Изпратена', 'printpop' ),
		'delivered' => __( 'Доставена', 'printpop' ),
	);

	$map = array(
		'processing' => 0,
		'printing'   => 1,
		'shipped'    => 2,
		'completed'  => 3,
	);

	$status = $order->get_status();
	if ( ! isset( $map[ $status ] ) ) {
		return null; // Неплатена, отказана, върната и т.н. — без проследяване.
	}

	return array(
		'steps'   => $steps,
		'current' => $map[ $status ],
		'halted'  => 'on-hold' === $status,
	);
}

/**
 * Лента със статуса на страницата на поръчката, „благодарим“ и проследяването.
 */
function printpop_render_order_tracker( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	$progress = printpop_order_progress( $order );
	if ( ! $progress ) {
		return;
	}
	?>
	<section class="pp-card pp-tracker" aria-label="<?php esc_attr_e( 'Статус на поръчката', 'printpop' ); ?>">
		<ol class="pp-steps">
			<?php
			$i = 0;
			foreach ( $progress['steps'] as $label ) :
				$state = $i < $progress['current'] ? 'done' : ( $i === $progress['current'] ? 'current' : 'todo' );
				?>
				<li class="pp-steps__item is-<?php echo esc_attr( $state ); ?>" <?php echo 'current' === $state ? 'aria-current="step"' : ''; ?>>
					<span class="pp-steps__dot"><?php echo 'done' === $state ? printpop_icon( 'check' ) : esc_html( $i + 1 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span class="pp-steps__label"><?php echo esc_html( $label ); ?></span>
				</li>
				<?php
				$i++;
			endforeach;
			?>
		</ol>
	</section>
	<?php
}
add_action( 'woocommerce_view_order', 'printpop_render_order_tracker', 5 );
add_action( 'woocommerce_thankyou', 'printpop_render_order_tracker', 5 );
