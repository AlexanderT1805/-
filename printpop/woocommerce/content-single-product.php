<?php
/**
 * Страница на продукта.
 *
 * Снимки / видео / 3D преглед, конфигуратор (inc/product-options.php) и бутон „Добави в количката“.
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput
	return;
}

$id        = $product->get_id();
$video     = esc_url( get_post_meta( $id, '_printpop_video_url', true ) );
$model_url = esc_url( get_post_meta( $id, '_printpop_model_url', true ) );
$use_case  = get_post_meta( $id, '_printpop_use_case', true );
$poster    = $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'large' ) : '';
$viewer_on = printpop_integration( 'viewer' );
?>
<article id="product-<?php echo esc_attr( $id ); ?>" <?php wc_product_class( 'pp-product', $product ); ?>>

	<nav class="pp-crumbs" aria-label="<?php esc_attr_e( 'Път', 'printpop' ); ?>">
		<a href="<?php echo esc_url( printpop_link( 'discover' ) ? printpop_link( 'discover' ) : home_url( '/' ) ); ?>">&larr; <?php esc_html_e( 'Към моделите', 'printpop' ); ?></a>
	</nav>

	<div class="pp-product__grid">

		<div class="pp-product__media" data-pp-media>
			<div class="pp-mediatabs" role="tablist" aria-label="<?php esc_attr_e( 'Вид преглед', 'printpop' ); ?>">
				<button type="button" role="tab" id="pp-tab-photos" aria-controls="pp-panel-photos" aria-selected="true" data-pp-tab="photos"><?php esc_html_e( 'Снимки', 'printpop' ); ?></button>
				<?php if ( $video ) : ?>
					<button type="button" role="tab" id="pp-tab-video" aria-controls="pp-panel-video" aria-selected="false" tabindex="-1" data-pp-tab="video"><?php esc_html_e( 'Видео', 'printpop' ); ?></button>
				<?php endif; ?>
				<button type="button" role="tab" id="pp-tab-3d" aria-controls="pp-panel-3d" aria-selected="false" tabindex="-1" data-pp-tab="3d"><?php esc_html_e( '3D преглед', 'printpop' ); ?></button>
			</div>

			<div class="pp-mediapanel" role="tabpanel" id="pp-panel-photos" aria-labelledby="pp-tab-photos">
				<?php woocommerce_show_product_images(); ?>
			</div>

			<?php if ( $video ) : ?>
				<div class="pp-mediapanel" role="tabpanel" id="pp-panel-video" aria-labelledby="pp-tab-video" hidden>
					<video class="pp-video" controls playsinline preload="none" <?php echo $poster ? 'poster="' . esc_url( $poster ) . '"' : ''; ?>>
						<source src="<?php echo esc_url( $video ); ?>">
					</video>
				</div>
			<?php endif; ?>

			<div class="pp-mediapanel" role="tabpanel" id="pp-panel-3d" aria-labelledby="pp-tab-3d" hidden>
				<?php if ( $viewer_on ) : ?>
					<div class="pp-viewer"><?php do_action( 'printpop_render_3d_viewer', $id, $model_url ); ?></div>
				<?php else : ?>
					<div class="pp-viewer pp-viewer--placeholder" data-integration="viewer">
						<?php echo printpop_icon( 'rotate' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<p><strong><?php esc_html_e( 'Тук ще има въртящ се 3D преглед', 'printpop' ); ?></strong></p>
						<p class="pp-muted">
							<?php echo printpop_pending_badge(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php
							echo $model_url
								? esc_html__( '3D файлът е зададен, но viewer плъгинът още не е свързан.', 'printpop' )
								: esc_html__( 'Добави 3D файл (.glb) в полето на продукта и свържи viewer.', 'printpop' );
							?>
						</p>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="pp-product__summary">
			<?php
			$terms = get_the_terms( $id, 'product_cat' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				echo '<p class="pp-eyebrow">' . esc_html( $terms[0]->name ) . '</p>';
			}
			?>
			<h1 class="pp-product__title"><?php the_title(); ?></h1>

			<?php if ( $use_case ) : ?>
				<p class="pp-product__usecase"><?php echo esc_html( $use_case ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $product->get_price() ) : ?>
				<p class="pp-product__price">
					<span class="pp-muted"><?php esc_html_e( 'От', 'printpop' ); ?></span>
					<?php echo wp_kses_post( $product->get_price_html() ); ?>
				</p>
			<?php endif; ?>

			<?php if ( $product->get_short_description() ) : ?>
				<div class="pp-prose"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
			<?php endif; ?>

			<?php woocommerce_template_single_add_to_cart(); ?>

			<ul class="pp-assure">
				<li><?php echo printpop_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Файловете се проверяват преди печат', 'printpop' ); ?></li>
				<li><?php echo printpop_icon( 'truck' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Доставка до офис на Еконт или Спиди', 'printpop' ); ?></li>
			</ul>
		</div>
	</div>

	<?php if ( $product->get_description() ) : ?>
		<section class="pp-product__desc pp-prose" aria-labelledby="pp-desc-title">
			<h2 id="pp-desc-title"><?php esc_html_e( 'Описание', 'printpop' ); ?></h2>
			<?php echo wp_kses_post( wpautop( $product->get_description() ) ); ?>
		</section>
	<?php endif; ?>

	<?php woocommerce_output_related_products(); ?>
</article>
