<?php
/**
 * Допълнителни полета на продукта: видео, 3D модел, какво решава, размери.
 *
 * Мета ключове:
 *  _printpop_video_url   URL на видео (mp4/webm) от Медия или външно.
 *  _printpop_model_url   URL на .glb/.gltf файл за бъдещия 3D viewer.
 *  _printpop_use_case    Кратко описание „какъв проблем решава“.
 *  _printpop_base_dims   Оригинални размери „Ш×В×Д“ в мм (напр. 40x30x20).
 *  _printpop_size_presets Готови размери, по един на ред: Име|Ш|В|Д (мм).
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

function printpop_add_product_metabox() {
	add_meta_box(
		'printpop_product',
		__( 'PrintPop: видео, 3D и размери', 'printpop' ),
		'printpop_render_product_metabox',
		'product',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'printpop_add_product_metabox' );

function printpop_render_product_metabox( $post ) {
	wp_nonce_field( 'printpop_product_meta', 'printpop_product_nonce' );

	$fields = array(
		'_printpop_use_case'     => array( __( 'Какъв проблем решава (1–2 изречения)', 'printpop' ), 'textarea' ),
		'_printpop_video_url'    => array( __( 'Видео (URL на mp4/webm; празно = показва се снимка)', 'printpop' ), 'url' ),
		'_printpop_model_url'    => array( __( '3D модел за преглед (URL на .glb/.gltf; чака viewer)', 'printpop' ), 'url' ),
		'_printpop_base_dims'    => array( __( 'Оригинални размери в мм, напр. 40x30x20', 'printpop' ), 'text' ),
		'_printpop_size_presets' => array( __( 'Готови размери, по един на ред: Име|Ширина|Височина|Дълбочина (мм)', 'printpop' ), 'textarea' ),
	);

	echo '<div class="pp-metabox">';
	foreach ( $fields as $key => $def ) {
		$value = get_post_meta( $post->ID, $key, true );
		echo '<p><label for="' . esc_attr( $key ) . '"><strong>' . esc_html( $def[0] ) . '</strong></label><br>';
		if ( 'textarea' === $def[1] ) {
			printf( '<textarea class="widefat" rows="4" id="%1$s" name="%1$s">%2$s</textarea>', esc_attr( $key ), esc_textarea( $value ) );
		} else {
			printf( '<input class="widefat" type="%3$s" id="%1$s" name="%1$s" value="%2$s">', esc_attr( $key ), esc_attr( $value ), esc_attr( $def[1] ) );
		}
		echo '</p>';
	}
	echo '</div>';
}

function printpop_save_product_meta( $post_id ) {
	if ( ! isset( $_POST['printpop_product_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['printpop_product_nonce'] ) ), 'printpop_product_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$sanitizers = array(
		'_printpop_use_case'     => 'sanitize_textarea_field',
		'_printpop_video_url'    => 'esc_url_raw',
		'_printpop_model_url'    => 'esc_url_raw',
		'_printpop_base_dims'    => 'sanitize_text_field',
		'_printpop_size_presets' => 'sanitize_textarea_field',
	);

	foreach ( $sanitizers as $key => $callback ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$value = call_user_func( $callback, wp_unslash( $_POST[ $key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post_product', 'printpop_save_product_meta' );

/**
 * Разбира „40x30x20“ → array( 40.0, 30.0, 20.0 ) или празен масив.
 */
function printpop_parse_dims( $string ) {
	if ( ! preg_match( '/^\s*([\d.,]+)\s*[x×х*]\s*([\d.,]+)\s*[x×х*]\s*([\d.,]+)\s*$/iu', (string) $string, $m ) ) {
		return array();
	}
	return array_map(
		function ( $v ) {
			return (float) str_replace( ',', '.', $v );
		},
		array( $m[1], $m[2], $m[3] )
	);
}

/**
 * Готови размери на продукта: [ ['label'=>, 'w'=>, 'h'=>, 'd'=>], … ].
 */
function printpop_get_size_presets( $product_id ) {
	$raw     = (string) get_post_meta( $product_id, '_printpop_size_presets', true );
	$presets = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line ) );
		if ( count( $parts ) < 4 || '' === $parts[0] ) {
			continue;
		}
		$w = (float) str_replace( ',', '.', $parts[1] );
		$h = (float) str_replace( ',', '.', $parts[2] );
		$d = (float) str_replace( ',', '.', $parts[3] );
		if ( $w <= 0 || $h <= 0 || $d <= 0 ) {
			continue;
		}
		$presets[] = array(
			'label' => sanitize_text_field( $parts[0] ),
			'w'     => $w,
			'h'     => $h,
			'd'     => $d,
		);
	}
	return $presets;
}

/**
 * Цветове на атрибута pa_color: поле „цвят“ на термина.
 */
function printpop_color_term_fields( $term = null ) {
	$value = $term ? get_term_meta( $term->term_id, 'printpop_hex', true ) : '';
	?>
	<tr class="form-field">
		<th scope="row"><label for="printpop_hex"><?php esc_html_e( 'Цвят за кръгчето (HEX)', 'printpop' ); ?></label></th>
		<td>
			<input type="text" id="printpop_hex" name="printpop_hex" value="<?php echo esc_attr( $value ); ?>" placeholder="#1d1d1f" maxlength="7">
			<p class="description"><?php esc_html_e( 'Пример: #1d1d1f. Ако е празно, темата познава основни имена (бял, черен, сив, червен, син, зелен, жълт, оранжев, лилав, розов).', 'printpop' ); ?></p>
		</td>
	</tr>
	<?php
	wp_nonce_field( 'printpop_term_hex', 'printpop_term_nonce' );
}
add_action( 'pa_color_add_form_fields', function () {
	printpop_color_term_fields( null );
} );
add_action( 'pa_color_edit_form_fields', 'printpop_color_term_fields' );

function printpop_save_color_term( $term_id ) {
	if ( ! isset( $_POST['printpop_term_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['printpop_term_nonce'] ) ), 'printpop_term_hex' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_product_terms' ) ) {
		return;
	}
	$hex = isset( $_POST['printpop_hex'] ) ? sanitize_hex_color( sanitize_text_field( wp_unslash( $_POST['printpop_hex'] ) ) ) : '';
	if ( $hex ) {
		update_term_meta( $term_id, 'printpop_hex', $hex );
	} else {
		delete_term_meta( $term_id, 'printpop_hex' );
	}
}
add_action( 'created_pa_color', 'printpop_save_color_term' );
add_action( 'edited_pa_color', 'printpop_save_color_term' );

/**
 * HEX за кръгче: от поле на термина или по име.
 */
function printpop_color_hex( $term ) {
	$hex = get_term_meta( $term->term_id, 'printpop_hex', true );
	if ( $hex ) {
		return $hex;
	}
	$name = function_exists( 'mb_strtolower' ) ? mb_strtolower( $term->name, 'UTF-8' ) : strtolower( $term->name );
	$map  = array(
		'бял' => '#f5f5f7', 'white' => '#f5f5f7',
		'черен' => '#1d1d1f', 'black' => '#1d1d1f',
		'сив' => '#8e8e93', 'grey' => '#8e8e93', 'gray' => '#8e8e93',
		'червен' => '#d6322b', 'red' => '#d6322b',
		'син' => '#2563eb', 'blue' => '#2563eb',
		'зелен' => '#2f9e59', 'green' => '#2f9e59',
		'жълт' => '#f2c200', 'yellow' => '#f2c200',
		'оранжев' => '#f2831f', 'orange' => '#f2831f',
		'лилав' => '#6a3fd9', 'purple' => '#6a3fd9',
		'розов' => '#f06ca8', 'pink' => '#f06ca8',
	);
	foreach ( $map as $key => $value ) {
		if ( 0 === strpos( $name, $key ) ) {
			return $value;
		}
	}
	return '';
}
