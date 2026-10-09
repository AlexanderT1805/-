<?php
/**
 * Подготвен дизайн на формата „готов 3D файл“ (без обработка — чака плъгин).
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

$formats = array_map( 'strtoupper', printpop_model_formats() );
?>
<div class="pp-formgrid" role="group" aria-label="<?php esc_attr_e( 'Форма за 3D файл (преглед на дизайна)', 'printpop' ); ?>" data-pp-demo-form>
	<div class="pp-drop" data-pp-drop data-accept="<?php echo esc_attr( implode( ',', printpop_model_formats() ) ); ?>">
		<?php echo printpop_icon( 'upload' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<p><strong><?php esc_html_e( 'Избери 3D файл', 'printpop' ); ?></strong></p>
		<p class="pp-muted"><?php echo esc_html( sprintf( /* translators: %s: формати */ __( 'Приемани формати: %s', 'printpop' ), implode( ', ', $formats ) ) ); ?></p>
		<input type="file" id="pp-model-file" accept=".<?php echo esc_attr( implode( ',.', printpop_model_formats() ) ); ?>" data-pp-file>
		<label class="pp-btn pp-btn--ghost pp-btn--sm" for="pp-model-file"><?php esc_html_e( 'Избери файл', 'printpop' ); ?></label>
		<p class="pp-drop__name" data-pp-filename aria-live="polite"></p>
		<p class="pp-drop__error" data-pp-fileerror role="alert"></p>
	</div>

	<div class="pp-formrow pp-formrow--3">
		<label class="pp-input"><span><?php esc_html_e( 'Ширина', 'printpop' ); ?></span><input type="number" inputmode="decimal" min="1" step="any"></label>
		<label class="pp-input"><span><?php esc_html_e( 'Височина', 'printpop' ); ?></span><input type="number" inputmode="decimal" min="1" step="any"></label>
		<label class="pp-input"><span><?php esc_html_e( 'Дълбочина', 'printpop' ); ?></span><input type="number" inputmode="decimal" min="1" step="any"></label>
	</div>
	<p class="pp-note"><?php echo printpop_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Размерите са в милиметри. Ако не ги въведеш, ще използваме тези от файла.', 'printpop' ); ?></span></p>

	<div class="pp-formrow">
		<label class="pp-input"><span><?php esc_html_e( 'Материал', 'printpop' ); ?></span><input type="text" placeholder="<?php esc_attr_e( 'Напр. по предпочитание', 'printpop' ); ?>"></label>
		<label class="pp-input"><span><?php esc_html_e( 'Цвят', 'printpop' ); ?></span><input type="text"></label>
	</div>
	<div class="pp-formrow">
		<label class="pp-input"><span><?php esc_html_e( 'Количество', 'printpop' ); ?></span><input type="number" min="1" value="1"></label>
		<label class="pp-input"><span><?php esc_html_e( 'Имейл за връзка', 'printpop' ); ?></span><input type="email" autocomplete="email"></label>
	</div>
	<label class="pp-input"><span><?php esc_html_e( 'Бележка (по желание)', 'printpop' ); ?></span><textarea rows="3"></textarea></label>

	<label class="pp-check">
		<input type="checkbox" required data-pp-rights>
		<span><?php esc_html_e( 'Потвърждавам, че имам право да възпроизвеждам този модел (авторски права или лиценз).', 'printpop' ); ?></span>
	</label>

	<button type="button" class="pp-btn pp-btn--primary pp-btn--lg" aria-disabled="true" disabled><?php esc_html_e( 'Изпрати за проверка', 'printpop' ); ?></button>
	<p class="pp-muted pp-small"><?php esc_html_e( 'Ако файлът не може да бъде анализиран автоматично или проверката открие проблем, ще го прегледаме ръчно, а цената ще бъде потвърдена с оферта.', 'printpop' ); ?></p>
</div>
