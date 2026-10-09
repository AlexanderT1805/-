<?php
/**
 * Конфигуратор на продукта: размер, материал, цвят, качество (+ количество от WooCommerce).
 *
 * Темата записва избора във всеки ред на количката и в поръчката.
 * Тя НЕ променя цената според избора — това е задача на бъдещия модул за ценообразуване
 * (вж. README, „Автоматично ценообразуване“).
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Нива на качество (етикет по ключ). Началното е „balanced“.
 */
function printpop_qualities() {
	return array(
		'economy'  => array(
			'label' => __( 'Икономично', 'printpop' ),
			'hint'  => __( 'По-бързо, по-видими слоеве', 'printpop' ),
		),
		'balanced' => array(
			'label' => __( 'Балансирано', 'printpop' ),
			'hint'  => __( 'Баланс между време и детайл', 'printpop' ),
		),
		'high'     => array(
			'label' => __( 'Високо качество', 'printpop' ),
			'hint'  => __( 'По-фини детайли, по-бавно', 'printpop' ),
		),
	);
}

/**
 * Граници на размерите в мм. Задават се според избрания принтер.
 */
function printpop_dimension_limits() {
	return apply_filters( 'printpop_dimension_limits', array( 'min' => 1, 'max' => 10000 ) );
}

function printpop_product_terms( $product_id, $taxonomy ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}
	$terms = wc_get_product_terms( $product_id, $taxonomy, array( 'fields' => 'all' ) );
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Показва конфигуратора вътре във формата за добавяне в количката (само прости продукти).
 */
function printpop_render_configurator() {
	global $product;
	if ( ! $product || ! $product->is_type( 'simple' ) ) {
		return;
	}

	$id         = $product->get_id();
	$presets    = printpop_get_size_presets( $id );
	$base       = printpop_parse_dims( get_post_meta( $id, '_printpop_base_dims', true ) );
	$materials  = printpop_product_terms( $id, 'pa_material' );
	$colors     = printpop_product_terms( $id, 'pa_color' );
	$qualities  = printpop_qualities();
	$limits     = printpop_dimension_limits();
	$unit_price = '' === $product->get_price() ? '' : wc_get_price_to_display( $product );

	$posted = array(
		'pp_w' => isset( $_POST['pp_w'] ) ? wc_clean( wp_unslash( $_POST['pp_w'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
		'pp_h' => isset( $_POST['pp_h'] ) ? wc_clean( wp_unslash( $_POST['pp_h'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
		'pp_d' => isset( $_POST['pp_d'] ) ? wc_clean( wp_unslash( $_POST['pp_d'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
	);
	foreach ( array( 'pp_w' => 0, 'pp_h' => 1, 'pp_d' => 2 ) as $k => $i ) {
		if ( '' === $posted[ $k ] && $base ) {
			$posted[ $k ] = $base[ $i ];
		}
	}
	?>
	<div class="pp-config" data-pp-config
		data-product-id="<?php echo esc_attr( $id ); ?>"
		data-unit-price="<?php echo esc_attr( $unit_price ); ?>"
		data-currency="<?php echo esc_attr( get_woocommerce_currency() ); ?>"
		data-base="<?php echo esc_attr( $base ? implode( ',', $base ) : '' ); ?>"
		data-min="<?php echo esc_attr( $limits['min'] ); ?>"
		data-max="<?php echo esc_attr( $limits['max'] ); ?>">

		<?php wp_nonce_field( 'printpop_add_to_cart', 'pp_nonce' ); ?>

		<fieldset class="pp-field">
			<legend><?php esc_html_e( 'Размер', 'printpop' ); ?></legend>

			<?php if ( $presets ) : ?>
				<div class="pp-chips" role="radiogroup" aria-label="<?php esc_attr_e( 'Готови размери', 'printpop' ); ?>">
					<?php foreach ( $presets as $i => $preset ) : ?>
						<label class="pp-chip">
							<input type="radio" name="pp_size" value="<?php echo esc_attr( $i ); ?>"
								data-w="<?php echo esc_attr( $preset['w'] ); ?>" data-h="<?php echo esc_attr( $preset['h'] ); ?>" data-d="<?php echo esc_attr( $preset['d'] ); ?>">
							<span><?php echo esc_html( $preset['label'] ); ?></span>
							<small><?php echo esc_html( sprintf( '%s×%s×%s мм', floatval( $preset['w'] ), floatval( $preset['h'] ), floatval( $preset['d'] ) ) ); ?></small>
						</label>
					<?php endforeach; ?>
					<label class="pp-chip">
						<input type="radio" name="pp_size" value="custom" checked>
						<span><?php esc_html_e( 'Свои размери', 'printpop' ); ?></span>
					</label>
				</div>
			<?php else : ?>
				<input type="hidden" name="pp_size" value="custom">
			<?php endif; ?>

			<div class="pp-dims">
				<?php
				$dims = array(
					'pp_w' => __( 'Ширина', 'printpop' ),
					'pp_h' => __( 'Височина', 'printpop' ),
					'pp_d' => __( 'Дълбочина', 'printpop' ),
				);
				foreach ( $dims as $name => $label ) :
					?>
					<label class="pp-dim" for="<?php echo esc_attr( $name ); ?>">
						<span><?php echo esc_html( $label ); ?></span>
						<span class="pp-dim__input">
							<input type="number" inputmode="decimal" step="any" min="<?php echo esc_attr( $limits['min'] ); ?>" max="<?php echo esc_attr( $limits['max'] ); ?>"
								id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $posted[ $name ] ); ?>">
							<em>мм</em>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
			<p class="pp-note" data-pp-distort role="status" aria-live="polite">
				<?php echo printpop_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php esc_html_e( 'Промяната само на един размер може да изкриви модела.', 'printpop' ); ?></span>
			</p>
		</fieldset>

		<?php if ( $materials ) : ?>
			<fieldset class="pp-field">
				<legend><?php esc_html_e( 'Материал', 'printpop' ); ?></legend>
				<div class="pp-chips" role="radiogroup">
					<?php foreach ( $materials as $i => $term ) : ?>
						<label class="pp-chip">
							<input type="radio" name="pp_material" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( 0, $i ); ?>>
							<span><?php echo esc_html( $term->name ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</fieldset>
		<?php endif; ?>

		<?php if ( $colors ) : ?>
			<fieldset class="pp-field">
				<legend><?php esc_html_e( 'Цвят', 'printpop' ); ?> <span class="pp-field__value" data-pp-color-name></span></legend>
				<div class="pp-swatches" role="radiogroup">
					<?php
					foreach ( $colors as $i => $term ) :
						$hex = printpop_color_hex( $term );
						?>
						<label class="pp-swatch" title="<?php echo esc_attr( $term->name ); ?>">
							<input type="radio" name="pp_color" value="<?php echo esc_attr( $term->slug ); ?>" data-name="<?php echo esc_attr( $term->name ); ?>" <?php checked( 0, $i ); ?>>
							<span class="pp-swatch__dot" <?php echo $hex ? 'style="--swatch:' . esc_attr( $hex ) . '"' : ''; ?>></span>
							<span class="screen-reader-text"><?php echo esc_html( $term->name ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
				<p class="pp-note pp-note--soft"><?php esc_html_e( 'Цветът се записва към поръчката. 3D прегледът остава в базов цвят.', 'printpop' ); ?></p>
			</fieldset>
		<?php endif; ?>

		<fieldset class="pp-field">
			<legend><?php esc_html_e( 'Качество', 'printpop' ); ?></legend>
			<div class="pp-segments" role="radiogroup">
				<?php foreach ( $qualities as $key => $q ) : ?>
					<label class="pp-segment">
						<input type="radio" name="pp_quality" value="<?php echo esc_attr( $key ); ?>" <?php checked( 'balanced', $key ); ?>>
						<span class="pp-segment__label"><?php echo esc_html( $q['label'] ); ?></span>
						<small><?php echo esc_html( $q['hint'] ); ?></small>
					</label>
				<?php endforeach; ?>
			</div>
		</fieldset>

		<div class="pp-total" aria-live="polite">
			<div>
				<span class="pp-total__label"><?php esc_html_e( 'Общо', 'printpop' ); ?></span>
				<strong class="pp-total__value" data-pp-total><?php echo $unit_price !== '' ? wp_kses_post( wc_price( $unit_price ) ) : esc_html__( 'Цената се уточнява', 'printpop' ); ?></strong>
			</div>
			<p class="pp-muted pp-total__note">
				<?php
				if ( printpop_integration( 'pricing' ) ) {
					do_action( 'printpop_price_preview', $product );
				} else {
					echo printpop_pending_badge() . ' '; // phpcs:ignore WordPress.Security.EscapeOutput
					esc_html_e( 'Сега цената е единичната цена × количество. Размерът, материалът и качеството ще влияят на цената след свързване на модула за ценообразуване.', 'printpop' );
				}
				?>
			</p>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_before_add_to_cart_button', 'printpop_render_configurator', 5 );

/**
 * Събира и проверява избора от формата.
 *
 * @return array{options: array, errors: string[]}
 */
function printpop_collect_options( $product_id ) {
	$result = array(
		'options' => array(),
		'errors'  => array(),
	);

	if ( ! isset( $_POST['pp_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pp_nonce'] ) ), 'printpop_add_to_cart' ) ) {
		// Заявка без конфигуратор (напр. бърз бутон от списък) — добавя се без опции.
		return $result;
	}

	$limits = printpop_dimension_limits();
	$dims   = array();
	foreach ( array( 'pp_w' => __( 'ширина', 'printpop' ), 'pp_h' => __( 'височина', 'printpop' ), 'pp_d' => __( 'дълбочина', 'printpop' ) ) as $key => $label ) {
		$raw = isset( $_POST[ $key ] ) ? str_replace( ',', '.', sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ) : '';
		if ( '' === $raw ) {
			continue;
		}
		if ( ! is_numeric( $raw ) || (float) $raw < $limits['min'] || (float) $raw > $limits['max'] ) {
			/* translators: 1: име на размера, 2: минимум, 3: максимум */
			$result['errors'][] = sprintf( __( 'Невалидна %1$s: въведи число между %2$s и %3$s мм.', 'printpop' ), $label, $limits['min'], $limits['max'] );
			continue;
		}
		$dims[ $key ] = round( (float) $raw, 2 );
	}
	if ( $dims && 3 !== count( $dims ) && ! $result['errors'] ) {
		$result['errors'][] = __( 'Въведи и трите размера (ширина, височина, дълбочина) или остави всички полета празни.', 'printpop' );
	}
	if ( 3 === count( $dims ) ) {
		$result['options']['size'] = sprintf( '%s × %s × %s мм', $dims['pp_w'], $dims['pp_h'], $dims['pp_d'] );
		$presets                   = printpop_get_size_presets( $product_id );
		$chosen                    = isset( $_POST['pp_size'] ) ? sanitize_text_field( wp_unslash( $_POST['pp_size'] ) ) : 'custom';
		if ( 'custom' !== $chosen && isset( $presets[ (int) $chosen ] ) ) {
			$p = $presets[ (int) $chosen ];
			if ( (float) $p['w'] === $dims['pp_w'] && (float) $p['h'] === $dims['pp_h'] && (float) $p['d'] === $dims['pp_d'] ) {
				$result['options']['size'] = $p['label'] . ' (' . $result['options']['size'] . ')';
			}
		}
	}

	foreach ( array( 'pp_material' => 'pa_material', 'pp_color' => 'pa_color' ) as $field => $taxonomy ) {
		$terms = printpop_product_terms( $product_id, $taxonomy );
		if ( ! $terms ) {
			continue;
		}
		$slug  = isset( $_POST[ $field ] ) ? sanitize_title( wp_unslash( $_POST[ $field ] ) ) : '';
		$match = null;
		foreach ( $terms as $term ) {
			if ( $term->slug === $slug ) {
				$match = $term;
				break;
			}
		}
		if ( ! $match ) {
			$result['errors'][] = 'pp_material' === $field ? __( 'Избери материал.', 'printpop' ) : __( 'Избери цвят.', 'printpop' );
			continue;
		}
		$result['options'][ 'pp_material' === $field ? 'material' : 'color' ] = $match->name;
	}

	$qualities = printpop_qualities();
	$quality   = isset( $_POST['pp_quality'] ) ? sanitize_key( wp_unslash( $_POST['pp_quality'] ) ) : 'balanced';
	if ( ! isset( $qualities[ $quality ] ) ) {
		$quality = 'balanced';
	}
	$result['options']['quality'] = $qualities[ $quality ]['label'];

	return $result;
}

function printpop_validate_add_to_cart( $passed, $product_id ) {
	$collected = printpop_collect_options( $product_id );
	foreach ( $collected['errors'] as $message ) {
		wc_add_notice( $message, 'error' );
		$passed = false;
	}
	return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'printpop_validate_add_to_cart', 10, 2 );

function printpop_add_cart_item_data( $cart_item_data, $product_id ) {
	$collected = printpop_collect_options( $product_id );
	if ( $collected['options'] ) {
		$cart_item_data['printpop_options'] = $collected['options'];
	}
	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'printpop_add_cart_item_data', 10, 2 );

function printpop_option_labels() {
	return array(
		'size'     => __( 'Размер', 'printpop' ),
		'material' => __( 'Материал', 'printpop' ),
		'color'    => __( 'Цвят', 'printpop' ),
		'quality'  => __( 'Качество', 'printpop' ),
	);
}

function printpop_show_cart_item_data( $item_data, $cart_item ) {
	if ( empty( $cart_item['printpop_options'] ) ) {
		return $item_data;
	}
	$labels = printpop_option_labels();
	foreach ( $cart_item['printpop_options'] as $key => $value ) {
		if ( isset( $labels[ $key ] ) ) {
			$item_data[] = array(
				'key'   => $labels[ $key ],
				'value' => $value,
			);
		}
	}
	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'printpop_show_cart_item_data', 10, 2 );

function printpop_save_order_item_data( $item, $cart_item_key, $values ) {
	if ( empty( $values['printpop_options'] ) ) {
		return;
	}
	$labels = printpop_option_labels();
	foreach ( $values['printpop_options'] as $key => $value ) {
		if ( isset( $labels[ $key ] ) ) {
			$item->add_meta_data( $labels[ $key ], $value );
		}
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'printpop_save_order_item_data', 10, 3 );
