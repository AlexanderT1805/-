<?php
/**
 * Точки за свързване с бъдещи плъгини и услуги.
 *
 * Темата само извиква действия и филтри; самата логика е в плъгините.
 * Пълен списък: README.md → „Точки за свързване“.
 *
 * Действия (actions), които плъгин може да ползва:
 *  - printpop_render_model_form       Форма „готов 3D файл“ (ако не е зададен шорткод).
 *  - printpop_render_idea_form        Форма „снимка/текст/идея“.
 *  - printpop_render_3d_viewer        Въртящ се 3D преглед на продуктовата страница ($product_id, $model_url).
 *  - printpop_render_favorites_page   Съдържание на страницата „Любими“ за влезли потребители.
 *  - printpop_render_courier_picker   Избор на офис на Еконт/Спиди на касата.
 *  - printpop_price_preview           Допълнителна информация под цената в конфигуратора ($product).
 *
 * Филтри за „включване“ на интеграция (връщат true):
 *  - printpop_integration_upload_file | upload_idea | viewer | favorites | courier | pricing
 *
 * JavaScript събития (върху document):
 *  - printpop:favorite   { productId, liked, done(ok, message) } — вж. assets/js/discover.js
 *  - printpop:config     { productId, size, material, color, quality, quantity } — при промяна в конфигуратора
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Разрешени формати. Реалните ограничения (размер, антивирус, съхранение) се задават в плъгина за качване.
 */
function printpop_model_formats() {
	return apply_filters( 'printpop_model_formats', array( 'stl', '3mf', 'obj', 'step', 'stp' ) );
}

function printpop_idea_formats() {
	return apply_filters( 'printpop_idea_formats', array( 'jpg', 'jpeg', 'png', 'pdf' ) );
}
