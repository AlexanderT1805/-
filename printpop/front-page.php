<?php
/**
 * Начална страница.
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

get_header();

$discover = printpop_link( 'discover' );
$upload   = printpop_link( 'upload' );

// Категории от заданието. Връзката води към поток „Открий“ с филтър по категория (ако категорията съществува).
$categories = array(
	array( 'slug' => 'prakticheski-chasti', 'title' => __( 'Практични и резервни части', 'printpop' ), 'text' => __( 'Скоби, капачки, дръжки, копчета и други малки части, които вече не се намират.', 'printpop' ), 'icon' => 'wrench' ),
	array( 'slug' => 'figurki', 'title' => __( 'Фигурки и хоби модели', 'printpop' ), 'text' => __( 'Колекционерски фигурки, макети и модели за настолни игри.', 'printpop' ), 'icon' => 'figure' ),
	array( 'slug' => 'personalizirani', 'title' => __( 'Персонализирани по снимка или текст', 'printpop' ), 'text' => __( 'Изпрати снимка или идея и получи оферта за изделие само за теб.', 'printpop' ), 'icon' => 'photo' ),
	array( 'slug' => 'dekoracii-podaratsi', 'title' => __( 'Декорации и подаръци', 'printpop' ), 'text' => __( 'Малки изненади за дома и за хората, които обичаш.', 'printpop' ), 'icon' => 'gift' ),
);

$steps = array(
	array( 'upload', __( 'Избери или изпрати идея', 'printpop' ), __( 'Разгледай каталога или качи 3D файл, снимка или описание.', 'printpop' ) ),
	array( 'cube', __( 'Провери и потвърди', 'printpop' ), __( 'Конфигурираш размер, материал, цвят и качество. Нестандартните заявки получават оферта.', 'printpop' ) ),
	array( 'card', __( 'Плати с карта', 'printpop' ), __( 'След плащането поръчката влиза в опашката за печат по реда на плащане.', 'printpop' ) ),
	array( 'truck', __( 'Получи в офис на куриер', 'printpop' ), __( 'Доставяме до офис на Еконт или Спиди и те информираме на всяка стъпка.', 'printpop' ) ),
);

$latest = array();
if ( function_exists( 'wc_get_products' ) ) {
	$latest = wc_get_products(
		array(
			'status'     => 'publish',
			'limit'      => 4,
			'orderby'    => 'date',
			'order'      => 'DESC',
			'visibility' => 'catalog',
		)
	);
}
?>
<main id="main" class="pp-main">

	<section class="pp-hero">
		<div class="pp-container pp-hero__grid">
			<div class="pp-hero__copy">
				<p class="pp-eyebrow"><?php esc_html_e( '3D печат по поръчка', 'printpop' ); ?></p>
				<h1 class="pp-hero__title"><?php esc_html_e( 'Имаш идея? Ние я превръщаме в 3D модел.', 'printpop' ); ?></h1>
				<p class="pp-hero__lead"><?php esc_html_e( 'От полезни части до персонални подаръци — изпрати идея и получи оферта.', 'printpop' ); ?></p>
				<div class="pp-hero__cta">
					<?php if ( $discover ) : ?>
						<a class="pp-btn pp-btn--primary pp-btn--lg" href="<?php echo esc_url( $discover ); ?>"><?php esc_html_e( 'Разгледай модели', 'printpop' ); ?></a>
					<?php endif; ?>
					<?php if ( $upload ) : ?>
						<a class="pp-btn pp-btn--ghost pp-btn--lg" href="<?php echo esc_url( $upload ); ?>"><?php esc_html_e( 'Изпрати идея', 'printpop' ); ?></a>
					<?php endif; ?>
				</div>
			</div>

			<div class="pp-hero__art" aria-hidden="true">
				<svg viewBox="0 0 360 320" class="pp-layers" role="presentation" focusable="false">
					<defs>
						<linearGradient id="ppg" x1="0" x2="1"><stop offset="0" stop-color="#6a3fd9"/><stop offset="1" stop-color="#9b7bff"/></linearGradient>
					</defs>
					<ellipse cx="180" cy="270" rx="140" ry="26" fill="#ececf1"/>
					<g class="pp-layers__stack">
						<rect x="70"  y="222" width="220" height="22" rx="11" fill="#1d1d1f"/>
						<rect x="82"  y="196" width="196" height="22" rx="11" fill="#2a2a2e"/>
						<rect x="96"  y="170" width="168" height="22" rx="11" fill="#3a3a40"/>
						<rect x="110" y="144" width="140" height="22" rx="11" fill="url(#ppg)"/>
						<rect x="126" y="118" width="108" height="22" rx="11" fill="url(#ppg)" opacity=".9"/>
						<rect x="144" y="92"  width="72"  height="22" rx="11" fill="url(#ppg)" opacity=".8"/>
						<rect x="162" y="66"  width="36"  height="22" rx="11" fill="url(#ppg)" opacity=".65"/>
					</g>
					<path class="pp-layers__nozzle" d="M170 18h20v22l-10 14-10-14z" fill="#1d1d1f"/>
				</svg>
			</div>
		</div>
	</section>

	<section class="pp-section" aria-labelledby="pp-cats-title">
		<div class="pp-container">
			<div class="pp-section__head">
				<h2 id="pp-cats-title"><?php esc_html_e( 'Какво можем да отпечатаме', 'printpop' ); ?></h2>
				<p class="pp-muted"><?php esc_html_e( 'Обслужваме всеки, който има нужда от 3D печат.', 'printpop' ); ?></p>
			</div>
			<div class="pp-grid pp-grid--4">
				<?php
				foreach ( $categories as $cat ) :
					$term = taxonomy_exists( 'product_cat' ) ? get_term_by( 'slug', $cat['slug'], 'product_cat' ) : false;
					$href = $discover;
					if ( $discover && $term && ! is_wp_error( $term ) ) {
						$href = add_query_arg( 'cat', $cat['slug'], $discover );
					}
					?>
					<a class="pp-tile" href="<?php echo esc_url( $href ? $href : home_url( '/' ) ); ?>">
						<span class="pp-tile__icon"><?php echo printpop_icon( $cat['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<h3><?php echo esc_html( $cat['title'] ); ?></h3>
						<p class="pp-muted"><?php echo esc_html( $cat['text'] ); ?></p>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php if ( $latest ) : ?>
		<section class="pp-section pp-section--tint" aria-labelledby="pp-new-title">
			<div class="pp-container">
				<div class="pp-section__head pp-section__head--row">
					<h2 id="pp-new-title"><?php esc_html_e( 'Най-нови модели', 'printpop' ); ?></h2>
					<?php if ( $discover ) : ?>
						<a class="pp-link" href="<?php echo esc_url( $discover ); ?>"><?php esc_html_e( 'Всички модели', 'printpop' ); ?> <?php echo printpop_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
					<?php endif; ?>
				</div>
				<div class="pp-grid pp-grid--4">
					<?php foreach ( $latest as $product ) : ?>
						<a class="pp-card pp-product-card" href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
							<span class="pp-product-card__img">
								<?php echo $product->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</span>
							<span class="pp-product-card__title"><?php echo esc_html( $product->get_name() ); ?></span>
							<?php if ( '' !== $product->get_price() ) : ?>
								<span class="pp-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
							<?php endif; ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="pp-section" aria-labelledby="pp-how-title">
		<div class="pp-container">
			<div class="pp-section__head">
				<h2 id="pp-how-title"><?php esc_html_e( 'Как работи', 'printpop' ); ?></h2>
			</div>
			<ol class="pp-grid pp-grid--4 pp-how">
				<?php foreach ( $steps as $i => $step ) : ?>
					<li class="pp-how__item">
						<span class="pp-how__num"><?php echo esc_html( $i + 1 ); ?></span>
						<span class="pp-how__icon"><?php echo printpop_icon( $step[0] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<h3><?php echo esc_html( $step[1] ); ?></h3>
						<p class="pp-muted"><?php echo esc_html( $step[2] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>

	<section class="pp-section pp-section--cta">
		<div class="pp-container">
			<div class="pp-cta">
				<div>
					<h2><?php esc_html_e( 'Имаш файл или само идея?', 'printpop' ); ?></h2>
					<p><?php esc_html_e( 'Качи 3D файл за проверка или опиши какво искаш — ще прегледаме заявката ръчно и ще изпратим оферта.', 'printpop' ); ?></p>
				</div>
				<?php if ( $upload ) : ?>
					<a class="pp-btn pp-btn--light pp-btn--lg" href="<?php echo esc_url( $upload ); ?>"><?php esc_html_e( 'Качи модел или изпрати идея', 'printpop' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
