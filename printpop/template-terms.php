<?php
/**
 * Template Name: Общи условия (временен)
 *
 * Временен шаблон — без окончателен правен текст.
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part(
	'template-parts/legal',
	null,
	array(
		'title'   => __( 'Общи условия', 'printpop' ),
		'intro'   => __( 'Условията за ползване на сайта и за поръчки.', 'printpop' ),
		'outline' => array(
			__( 'Данни за търговеца', 'printpop' ),
			__( 'Предмет и процес на поръчка (каталог и персонални заявки)', 'printpop' ),
			__( 'Оферти по заявка и плащане с карта', 'printpop' ),
			__( 'Права върху качени модели и отговорност на клиента', 'printpop' ),
			__( 'Срокове за изработка и опашка за печат', 'printpop' ),
			__( 'Рекламации и отговорност', 'printpop' ),
			__( 'Приложимо право и контакт', 'printpop' ),
		),
	)
);

get_footer();
