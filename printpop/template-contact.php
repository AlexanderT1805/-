<?php
/**
 * Template Name: Контакти
 *
 * Данните идват от Външен вид → Customize → PrintPop → Контакти. Темата не добавя примерни контакти.
 * Формата е шорткод на плъгин (Customize → PrintPop → Форми).
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

get_header();

$c       = printpop_contact();
$socials = printpop_socials();
$items   = array();

if ( $c['email'] ) {
	$items[] = array( 'mail', __( 'Имейл', 'printpop' ), '<a href="mailto:' . esc_attr( $c['email'] ) . '">' . esc_html( $c['email'] ) . '</a>' );
}
if ( $c['phone'] ) {
	$items[] = array( 'phone', __( 'Телефон', 'printpop' ), '<a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $c['phone'] ) ) . '">' . esc_html( $c['phone'] ) . '</a>' );
}
if ( $c['address'] ) {
	$items[] = array( 'pin', __( 'Къде сме', 'printpop' ), esc_html( $c['address'] ) );
}
if ( $c['hours'] ) {
	$items[] = array( 'clock', __( 'Работно време', 'printpop' ), esc_html( $c['hours'] ) );
}
?>
<main id="main" class="pp-main">
	<div class="pp-container">
		<header class="pp-pagehead pp-pagehead--center">
			<p class="pp-eyebrow"><?php esc_html_e( 'Контакти', 'printpop' ); ?></p>
			<h1><?php echo esc_html( get_the_title() ? get_the_title() : __( 'Свържи се с нас', 'printpop' ) ); ?></h1>
			<p class="pp-lead"><?php esc_html_e( 'Въпрос за поръчка, модел или оферта? Пиши ни.', 'printpop' ); ?></p>
		</header>

		<div class="pp-contact">
			<div class="pp-contact__info">
				<?php if ( $items ) : ?>
					<ul class="pp-infolist">
						<?php foreach ( $items as $item ) : ?>
							<li class="pp-card">
								<span class="pp-infolist__icon"><?php echo printpop_icon( $item[0] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
								<div>
									<strong><?php echo esc_html( $item[1] ); ?></strong>
									<p><?php echo wp_kses( $item[2], array( 'a' => array( 'href' => array() ) ) ); ?></p>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<div class="pp-card">
						<p class="pp-pending"><?php echo printpop_pending_badge(); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
						<p class="pp-muted"><?php esc_html_e( 'Данните за контакт още не са попълнени. Добави ги от Външен вид → Customize → PrintPop → Контакти.', 'printpop' ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( $socials ) : ?>
					<ul class="pp-socials">
						<?php foreach ( $socials as $label => $url ) : ?>
							<li><a class="pp-btn pp-btn--ghost pp-btn--sm" href="<?php echo esc_url( $url ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $label ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<div class="pp-contact__form pp-card">
				<h2><?php esc_html_e( 'Изпрати съобщение', 'printpop' ); ?></h2>
				<?php if ( ! printpop_render_shortcode_setting( 'form_contact' ) ) : ?>
					<p class="pp-pending"><?php echo printpop_pending_badge(); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Формата още не е свързана с плъгин за форми.', 'printpop' ); ?></p>
					<?php if ( $c['email'] ) : ?>
						<a class="pp-btn pp-btn--primary" href="mailto:<?php echo esc_attr( $c['email'] ); ?>"><?php esc_html_e( 'Пиши ни по имейл', 'printpop' ); ?></a>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<?php
		while ( have_posts() ) :
			the_post();
			if ( trim( wp_strip_all_tags( get_the_content() ) ) ) :
				?>
				<div class="pp-prose pp-prose--after"><?php the_content(); ?></div>
				<?php
			endif;
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
