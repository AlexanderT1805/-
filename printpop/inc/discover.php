<?php
/**
 * Поток „Открий“: заявка към продуктите, филтри и показване на карта.
 *
 * Редът се смесва в браузъра при всяко отваряне (подходящо при кеширане на страници).
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Прочетени филтри от адреса (GET). Само четене, затова без nonce.
 */
function printpop_discover_filters() {
	// phpcs:disable WordPress.Security.NonceVerification
	return array(
		'q'        => isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '',
		'cat'      => isset( $_GET['cat'] ) ? sanitize_title( wp_unslash( $_GET['cat'] ) ) : '',
		'material' => isset( $_GET['material'] ) ? sanitize_title( wp_unslash( $_GET['material'] ) ) : '',
		'min'      => isset( $_GET['min'] ) && is_numeric( $_GET['min'] ) ? max( 0, (float) $_GET['min'] ) : '',
		'max'      => isset( $_GET['max'] ) && is_numeric( $_GET['max'] ) ? max( 0, (float) $_GET['max'] ) : '',
		'paged'    => max( 1, (int) get_query_var( 'paged', 1 ), isset( $_GET['pg'] ) ? (int) $_GET['pg'] : 1 ),
	);
	// phpcs:enable
}

function printpop_discover_query( $filters ) {
	$args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => (int) apply_filters( 'printpop_discover_per_page', 12 ),
		'paged'               => $filters['paged'],
		'ignore_sticky_posts' => true,
		'tax_query'           => array( 'relation' => 'AND' ), // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_query'          => array( 'relation' => 'AND' ), // phpcs:ignore WordPress.DB.SlowDBQuery
	);

	// Скрити от каталога продукти.
	$args['tax_query'][] = array(
		'taxonomy' => 'product_visibility',
		'field'    => 'name',
		'terms'    => array( 'exclude-from-catalog' ),
		'operator' => 'NOT IN',
	);

	if ( '' !== $filters['q'] ) {
		$args['s'] = $filters['q'];
	}
	if ( '' !== $filters['cat'] ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => array( $filters['cat'] ),
		);
	}
	if ( '' !== $filters['material'] && taxonomy_exists( 'pa_material' ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'pa_material',
			'field'    => 'slug',
			'terms'    => array( $filters['material'] ),
		);
	}
	if ( '' !== $filters['min'] || '' !== $filters['max'] ) {
		$args['meta_query'][] = array(
			'key'     => '_price',
			'value'   => array( '' !== $filters['min'] ? $filters['min'] : 0, '' !== $filters['max'] ? $filters['max'] : 99999999 ),
			'type'    => 'NUMERIC',
			'compare' => 'BETWEEN',
		);
	}

	return new WP_Query( $args );
}

/**
 * Картата в потока. Видео, ако има, иначе снимка на модела.
 */
function printpop_render_reel( $product ) {
	$id        = $product->get_id();
	$title     = $product->get_name();
	$url       = get_permalink( $id );
	$video     = esc_url( get_post_meta( $id, '_printpop_video_url', true ) );
	$use_case  = get_post_meta( $id, '_printpop_use_case', true );
	$image_id  = $product->get_image_id();
	$poster    = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';
	$first_cat = '';
	$terms     = get_the_terms( $id, 'product_cat' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$first_cat = $terms[0]->name;
	}
	?>
	<article class="pp-reel" data-product-id="<?php echo esc_attr( $id ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
		<div class="pp-reel__media">
			<?php if ( $video ) : ?>
				<video class="pp-reel__video" muted loop playsinline preload="none"
					data-src="<?php echo esc_url( $video ); ?>"
					<?php echo $poster ? 'poster="' . esc_url( $poster ) . '"' : ''; ?>></video>
			<?php elseif ( $image_id ) : ?>
				<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'pp-reel__img', 'loading' => 'lazy', 'alt' => $title ) ); ?>
			<?php else : ?>
				<div class="pp-reel__placeholder"><?php echo printpop_icon( 'cube' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php endif; ?>
		</div>

		<a class="pp-reel__link" href="<?php echo esc_url( $url ); ?>">
			<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: име на модела */ __( 'Отвори %s', 'printpop' ), $title ) ); ?></span>
		</a>

		<div class="pp-reel__info">
			<?php if ( $first_cat ) : ?>
				<span class="pp-pill"><?php echo esc_html( $first_cat ); ?></span>
			<?php endif; ?>
			<h2 class="pp-reel__title"><?php echo esc_html( $title ); ?></h2>
			<?php if ( $use_case ) : ?>
				<p class="pp-reel__text"><?php echo esc_html( $use_case ); ?></p>
			<?php endif; ?>
			<div class="pp-reel__row">
				<?php if ( '' !== $product->get_price() ) : ?>
					<span class="pp-reel__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
				<?php endif; ?>
				<a class="pp-btn pp-btn--light pp-btn--sm" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Виж модела', 'printpop' ); ?></a>
			</div>
		</div>

		<div class="pp-reel__actions">
			<button type="button" class="pp-fab" data-pp-action="like" aria-label="<?php esc_attr_e( 'Добави в „Любими“', 'printpop' ); ?>">
				<?php echo printpop_icon( 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</button>
			<button type="button" class="pp-fab" data-pp-action="skip" aria-label="<?php esc_attr_e( 'Пропусни', 'printpop' ); ?>">
				<?php echo printpop_icon( 'skip' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</button>
		</div>

		<span class="pp-reel__stamp pp-reel__stamp--like" aria-hidden="true"><?php esc_html_e( 'Любими', 'printpop' ); ?></span>
		<span class="pp-reel__stamp pp-reel__stamp--skip" aria-hidden="true"><?php esc_html_e( 'Пропусни', 'printpop' ); ?></span>
	</article>
	<?php
}
