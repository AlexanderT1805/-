<?php
/**
 * Template Name: Открий (видео поток)
 *
 * Вертикален поток с модели. Видео, ако има; иначе снимка на модела.
 * Плъзгане надясно = „Любими“, наляво = „Пропусни“. Докосване = продуктова страница.
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

get_header();

$has_wc  = class_exists( 'WooCommerce' ) && function_exists( 'printpop_discover_query' );
$filters = $has_wc ? printpop_discover_filters() : array();
$query   = $has_wc ? printpop_discover_query( $filters ) : null;

$cats = $has_wc ? get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
	)
) : array();
$cats = is_wp_error( $cats ) ? array() : $cats;

$materials = ( $has_wc && taxonomy_exists( 'pa_material' ) ) ? get_terms(
	array(
		'taxonomy'   => 'pa_material',
		'hide_empty' => true,
	)
) : array();
$materials = is_wp_error( $materials ) ? array() : $materials;

$active = $has_wc ? count( array_filter( array( $filters['cat'], $filters['material'], $filters['min'], $filters['max'] ), 'strlen' ) ) : 0;
$base   = get_permalink();
?>
<main id="main" class="pp-main pp-discover">
	<div class="pp-discover__shell">

		<form class="pp-discover__bar" method="get" action="<?php echo esc_url( $base ); ?>" role="search">
			<label class="pp-search">
				<?php echo printpop_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Търси модел', 'printpop' ); ?></span>
				<input type="search" name="q" value="<?php echo esc_attr( $filters['q'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Търси модел…', 'printpop' ); ?>" enterkeyhint="search">
			</label>
			<button type="button" class="pp-btn pp-btn--ghost pp-btn--icon" data-pp-filter-open aria-haspopup="dialog" aria-controls="pp-filters">
				<?php echo printpop_icon( 'filter' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php esc_html_e( 'Филтри', 'printpop' ); ?></span>
				<?php if ( $active ) : ?><span class="pp-count-badge"><?php echo esc_html( $active ); ?></span><?php endif; ?>
			</button>

			<div class="pp-sheet" id="pp-filters" role="dialog" aria-modal="true" aria-labelledby="pp-filters-title" hidden data-pp-filters>
				<div class="pp-sheet__panel">
					<div class="pp-sheet__head">
						<h2 id="pp-filters-title"><?php esc_html_e( 'Филтри', 'printpop' ); ?></h2>
						<button type="button" class="pp-iconbtn" data-pp-filter-close>
							<?php echo printpop_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<span class="screen-reader-text"><?php esc_html_e( 'Затвори', 'printpop' ); ?></span>
						</button>
					</div>

					<div class="pp-sheet__body">
						<label class="pp-field-block">
							<span><?php esc_html_e( 'Категория', 'printpop' ); ?></span>
							<select name="cat">
								<option value=""><?php esc_html_e( 'Всички категории', 'printpop' ); ?></option>
								<?php foreach ( $cats as $term ) : ?>
									<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $filters['cat'] ?? '', $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>

						<label class="pp-field-block">
							<span><?php esc_html_e( 'Материал', 'printpop' ); ?></span>
							<select name="material" <?php disabled( ! $materials ); ?>>
								<option value=""><?php esc_html_e( 'Всички материали', 'printpop' ); ?></option>
								<?php foreach ( $materials as $term ) : ?>
									<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $filters['material'] ?? '', $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
								<?php endforeach; ?>
							</select>
							<?php if ( ! $materials ) : ?>
								<small class="pp-muted"><?php esc_html_e( 'Няма материали — добави атрибут „Материал“ в WooCommerce.', 'printpop' ); ?></small>
							<?php endif; ?>
						</label>

						<fieldset class="pp-field-block">
							<legend><?php echo esc_html( sprintf( /* translators: %s: валута */ __( 'Цена (%s)', 'printpop' ), $has_wc ? get_woocommerce_currency_symbol() : '' ) ); ?></legend>
							<div class="pp-range">
								<input type="number" inputmode="decimal" min="0" step="any" name="min" value="<?php echo esc_attr( $filters['min'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'от', 'printpop' ); ?>" aria-label="<?php esc_attr_e( 'Цена от', 'printpop' ); ?>">
								<span aria-hidden="true">–</span>
								<input type="number" inputmode="decimal" min="0" step="any" name="max" value="<?php echo esc_attr( $filters['max'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'до', 'printpop' ); ?>" aria-label="<?php esc_attr_e( 'Цена до', 'printpop' ); ?>">
							</div>
						</fieldset>
					</div>

					<div class="pp-sheet__foot">
						<a class="pp-btn pp-btn--ghost" href="<?php echo esc_url( $base ); ?>"><?php esc_html_e( 'Изчисти', 'printpop' ); ?></a>
						<button type="submit" class="pp-btn pp-btn--primary"><?php esc_html_e( 'Покажи резултатите', 'printpop' ); ?></button>
					</div>
				</div>
				<div class="pp-sheet__backdrop" data-pp-filter-close></div>
			</div>
		</form>

		<?php if ( ! $has_wc ) : ?>
			<div class="pp-empty">
				<span class="pp-empty__icon"><?php echo printpop_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<h1><?php esc_html_e( 'Каталогът ще се показва тук', 'printpop' ); ?></h1>
				<p class="pp-muted"><?php esc_html_e( 'За да работи потокът, активирай WooCommerce и добави модели като продукти.', 'printpop' ); ?></p>
			</div>

		<?php elseif ( ! $query->have_posts() ) : ?>
			<div class="pp-empty">
				<span class="pp-empty__icon"><?php echo printpop_icon( 'cube' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<h1>
					<?php echo $active || '' !== $filters['q'] ? esc_html__( 'Няма модели по тези критерии', 'printpop' ) : esc_html__( 'Скоро тук ще има модели', 'printpop' ); ?>
				</h1>
				<p class="pp-muted">
					<?php echo $active || '' !== $filters['q'] ? esc_html__( 'Опитай с други филтри или потърси друг израз.', 'printpop' ) : esc_html__( 'Каталогът се попълва постепенно. Междувременно можеш да изпратиш своя идея.', 'printpop' ); ?>
				</p>
				<div class="pp-actions">
					<?php if ( $active || '' !== $filters['q'] ) : ?>
						<a class="pp-btn pp-btn--ghost" href="<?php echo esc_url( $base ); ?>"><?php esc_html_e( 'Изчисти филтрите', 'printpop' ); ?></a>
					<?php endif; ?>
					<?php if ( printpop_link( 'upload' ) ) : ?>
						<a class="pp-btn pp-btn--primary" href="<?php echo esc_url( printpop_link( 'upload' ) ); ?>"><?php esc_html_e( 'Изпрати идея', 'printpop' ); ?></a>
					<?php endif; ?>
				</div>
			</div>

		<?php else : ?>
			<h1 class="screen-reader-text"><?php the_title(); ?></h1>
			<div class="pp-feed" data-pp-feed tabindex="0" aria-label="<?php esc_attr_e( 'Поток с модели. Използвай стрелките нагоре и надолу, за да сменяш модел.', 'printpop' ); ?>">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$product = wc_get_product( get_the_ID() );
					if ( $product ) {
						printpop_render_reel( $product );
					}
				endwhile;
				wp_reset_postdata();
				?>

				<section class="pp-reel pp-reel--end" data-pp-end>
					<div class="pp-reel__endcard">
						<h2><?php esc_html_e( 'Това са всички модели засега', 'printpop' ); ?></h2>
						<p class="pp-muted"><?php esc_html_e( 'Не намери това, което търсиш? Изпрати идея и ще получиш оферта.', 'printpop' ); ?></p>
						<div class="pp-actions">
							<?php if ( $query->max_num_pages > $filters['paged'] ) : ?>
								<a class="pp-btn pp-btn--primary" href="<?php echo esc_url( add_query_arg( array_merge( array_filter( array_diff_key( $filters, array( 'paged' => 1 ) ), 'strlen' ), array( 'pg' => $filters['paged'] + 1 ) ), $base ) ); ?>"><?php esc_html_e( 'Още модели', 'printpop' ); ?></a>
							<?php endif; ?>
							<?php if ( printpop_link( 'upload' ) ) : ?>
								<a class="pp-btn pp-btn--ghost" href="<?php echo esc_url( printpop_link( 'upload' ) ); ?>"><?php esc_html_e( 'Изпрати идея', 'printpop' ); ?></a>
							<?php endif; ?>
						</div>
					</div>
				</section>
			</div>

			<p class="pp-swipehint" aria-hidden="true">
				<span>&larr; <?php esc_html_e( 'Пропусни', 'printpop' ); ?></span>
				<span><?php esc_html_e( 'Любими', 'printpop' ); ?> &rarr;</span>
			</p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
