<?php
/**
 * Подготвен дизайн на формата „снимка / текст / идея“ (без обработка — чака плъгин).
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

$formats = array_map( 'strtoupper', printpop_idea_formats() );
?>
<div class="pp-formgrid" role="group" aria-label="<?php esc_attr_e( 'Форма за идея (преглед на дизайна)', 'printpop' ); ?>" data-pp-demo-form>
	<label class="pp-input">
		<span><?php esc_html_e( 'Опиши идеята си', 'printpop' ); ?></span>
		<textarea rows="5" placeholder="<?php esc_attr_e( 'Какво искаш да получиш и за какво ще го използваш?', 'printpop' ); ?>"></textarea>
	</label>

	<div class="pp-drop pp-drop--compact" data-pp-drop data-accept="<?php echo esc_attr( implode( ',', printpop_idea_formats() ) ); ?>">
		<?php echo printpop_icon( 'photo' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<p><strong><?php esc_html_e( 'Добави снимки или чертеж (по желание)', 'printpop' ); ?></strong></p>
		<p class="pp-muted"><?php echo esc_html( sprintf( /* translators: %s: формати */ __( 'Приемани формати: %s', 'printpop' ), implode( ', ', $formats ) ) ); ?></p>
		<input type="file" id="pp-idea-file" accept=".<?php echo esc_attr( implode( ',.', printpop_idea_formats() ) ); ?>" multiple data-pp-file>
		<label class="pp-btn pp-btn--ghost pp-btn--sm" for="pp-idea-file"><?php esc_html_e( 'Избери файлове', 'printpop' ); ?></label>
		<p class="pp-drop__name" data-pp-filename aria-live="polite"></p>
		<p class="pp-drop__error" data-pp-fileerror role="alert"></p>
	</div>

	<div class="pp-formrow pp-formrow--3">
		<label class="pp-input"><span><?php esc_html_e( 'Желана ширина (мм)', 'printpop' ); ?></span><input type="number" inputmode="decimal" min="1" step="any"></label>
		<label class="pp-input"><span><?php esc_html_e( 'Височина (мм)', 'printpop' ); ?></span><input type="number" inputmode="decimal" min="1" step="any"></label>
		<label class="pp-input"><span><?php esc_html_e( 'Дълбочина (мм)', 'printpop' ); ?></span><input type="number" inputmode="decimal" min="1" step="any"></label>
	</div>

	<div class="pp-formrow">
		<label class="pp-input"><span><?php esc_html_e( 'Цвят', 'printpop' ); ?></span><input type="text"></label>
		<label class="pp-input"><span><?php esc_html_e( 'Имейл за връзка', 'printpop' ); ?></span><input type="email" autocomplete="email"></label>
	</div>
	<label class="pp-input"><span><?php esc_html_e( 'Телефон (по желание)', 'printpop' ); ?></span><input type="tel" autocomplete="tel"></label>

	<label class="pp-check">
		<input type="checkbox" required data-pp-rights>
		<span><?php esc_html_e( 'Потвърждавам, че имам право да използвам и възпроизвеждам качените материали.', 'printpop' ); ?></span>
	</label>

	<button type="button" class="pp-btn pp-btn--primary pp-btn--lg" aria-disabled="true" disabled><?php esc_html_e( 'Изпрати за оферта', 'printpop' ); ?></button>
</div>
