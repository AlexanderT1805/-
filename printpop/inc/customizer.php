<?php
/**
 * Настройки в Customizer: контакти, форми (шорткодове), поръчки, социални мрежи.
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

function printpop_sanitize_shortcode( $value ) {
	$value = trim( wp_kses_post( (string) $value ) );
	return ( '' === $value || '[' === substr( $value, 0, 1 ) ) ? $value : '';
}

function printpop_sanitize_money( $value ) {
	$value = str_replace( ',', '.', (string) $value );
	return ( is_numeric( $value ) && (float) $value > 0 ) ? (string) (float) $value : '';
}

function printpop_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'printpop',
		array(
			'title'    => __( 'PrintPop', 'printpop' ),
			'priority' => 30,
		)
	);

	$fields = array(
		'printpop_contacts' => array(
			'title'  => __( 'Контакти', 'printpop' ),
			'desc'   => __( 'Оставени празни, данните не се показват на сайта. Темата не добавя примерни контакти.', 'printpop' ),
			'fields' => array(
				'contact_email'   => array( __( 'Имейл', 'printpop' ), 'sanitize_email', 'email' ),
				'contact_phone'   => array( __( 'Телефон', 'printpop' ), 'sanitize_text_field', 'text' ),
				'contact_address' => array( __( 'Населено място / адрес', 'printpop' ), 'sanitize_text_field', 'text' ),
				'contact_hours'   => array( __( 'Работно време', 'printpop' ), 'sanitize_text_field', 'text' ),
			),
		),
		'printpop_social'   => array(
			'title'  => __( 'Социални мрежи', 'printpop' ),
			'desc'   => '',
			'fields' => array(
				'social_instagram' => array( 'Instagram (URL)', 'esc_url_raw', 'url' ),
				'social_facebook'  => array( 'Facebook (URL)', 'esc_url_raw', 'url' ),
				'social_tiktok'    => array( 'TikTok (URL)', 'esc_url_raw', 'url' ),
			),
		),
		'printpop_forms'    => array(
			'title'  => __( 'Форми (шорткодове от плъгини)', 'printpop' ),
			'desc'   => __( 'Поставете шорткод на форма от плъгин за безопасно качване на файлове (напр. [fluentform id="1"]). Темата не обработва файлове сама. Празно поле = показва се обозначен, неактивен дизайн.', 'printpop' ),
			'fields' => array(
				'form_model'   => array( __( 'Шорткод: готов 3D файл', 'printpop' ), 'printpop_sanitize_shortcode', 'text' ),
				'form_idea'    => array( __( 'Шорткод: снимка / текст / идея', 'printpop' ), 'printpop_sanitize_shortcode', 'text' ),
				'form_contact' => array( __( 'Шорткод: форма за контакти', 'printpop' ), 'printpop_sanitize_shortcode', 'text' ),
			),
		),
		'printpop_orders'   => array(
			'title'  => __( 'Поръчки', 'printpop' ),
			'desc'   => __( 'Конкретните суми се задават след избора на принтер. Темата не съдържа предварително зададени цени.', 'printpop' ),
			'fields' => array(
				'min_order' => array( __( 'Минимална стойност на поръчка (без доставка)', 'printpop' ), 'printpop_sanitize_money', 'text' ),
			),
		),
	);

	foreach ( $fields as $section_id => $section ) {
		$wp_customize->add_section(
			$section_id,
			array(
				'title'       => $section['title'],
				'description' => $section['desc'],
				'panel'       => 'printpop',
			)
		);

		foreach ( $section['fields'] as $key => $def ) {
			$wp_customize->add_setting(
				'printpop_' . $key,
				array(
					'default'           => '',
					'sanitize_callback' => $def[1],
					'transport'         => 'refresh',
				)
			);
			$wp_customize->add_control(
				'printpop_' . $key,
				array(
					'label'   => $def[0],
					'section' => $section_id,
					'type'    => $def[2],
				)
			);
		}
	}
}
add_action( 'customize_register', 'printpop_customize_register' );
