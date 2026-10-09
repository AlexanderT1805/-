<?php
/**
 * Създаване на основните страници с едно натискане на бутон (по избор).
 *
 * Нищо не се създава автоматично при активиране — администраторът вижда
 * съобщение и решава. Правните страници се създават като чернови.
 *
 * @package PrintPop
 */

defined( 'ABSPATH' ) || exit;

function printpop_pages_definition() {
	return array(
		'home'      => array( __( 'Начало', 'printpop' ), 'nachalo', '', 'publish', '' ),
		'discover'  => array( __( 'Открий', 'printpop' ), 'otkrij', 'template-discover.php', 'publish', '' ),
		'upload'    => array( __( 'Качи модел или изпрати идея', 'printpop' ), 'kachi-model', 'template-upload.php', 'publish', '' ),
		'about'     => array( __( 'За нас', 'printpop' ), 'za-nas', 'template-about.php', 'publish', '' ),
		'contact'   => array( __( 'Контакти', 'printpop' ), 'kontakti', 'template-contact.php', 'publish', '' ),
		'favorites' => array( __( 'Любими', 'printpop' ), 'lyubimi', 'template-favorites.php', 'publish', '' ),
		'track'     => array( __( 'Проследяване на поръчка', 'printpop' ), 'sledi-porachka', '', 'publish', '[woocommerce_order_tracking]' ),
		'terms'     => array( __( 'Общи условия', 'printpop' ), 'obshti-usloviya', 'template-terms.php', 'draft', '' ),
		'privacy'   => array( __( 'Политика за поверителност', 'printpop' ), 'poveritelnost', 'template-privacy.php', 'draft', '' ),
		'shipping'  => array( __( 'Доставка и връщане', 'printpop' ), 'dostavka-i-vrashtane', 'template-shipping.php', 'draft', '' ),
	);
}

/**
 * Кои страници още липсват (по шаблон, а за „Начало“ — по настройката за начална страница).
 */
function printpop_missing_pages() {
	$missing = array();
	foreach ( printpop_pages_definition() as $key => $def ) {
		if ( 'home' === $key ) {
			if ( 'page' !== get_option( 'show_on_front' ) || ! get_option( 'page_on_front' ) ) {
				$missing[] = $key;
			}
			continue;
		}
		if ( 'track' === $key ) {
			if ( ! printpop_find_page_with_shortcode( 'woocommerce_order_tracking' ) ) {
				$missing[] = $key;
			}
			continue;
		}
		$exists = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => 1,
				'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => $def[2], // phpcs:ignore WordPress.DB.SlowDBQuery
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		if ( ! $exists ) {
			$missing[] = $key;
		}
	}
	return $missing;
}

function printpop_find_page_with_shortcode( $tag ) {
	$ids = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => 1,
			's'              => '[' . $tag,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	return $ids ? $ids[0] : 0;
}

function printpop_pages_notice() {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'printpop_pages_notice_hidden' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && ! in_array( $screen->base, array( 'dashboard', 'themes', 'edit' ), true ) ) {
		return;
	}
	$missing = printpop_missing_pages();
	if ( ! $missing ) {
		return;
	}

	$create = wp_nonce_url( admin_url( 'admin-post.php?action=printpop_create_pages' ), 'printpop_create_pages' );
	$hide   = wp_nonce_url( admin_url( 'admin-post.php?action=printpop_hide_pages_notice' ), 'printpop_hide_pages_notice' );
	?>
	<div class="notice notice-info">
		<p><strong><?php esc_html_e( 'PrintPop: основните страници още не са готови.', 'printpop' ); ?></strong>
			<?php esc_html_e( 'Създай липсващите страници (Начало, Открий, Качи модел, За нас, Контакти, Любими, Проследяване на поръчка) и задай „Начало“ като начална страница. Правните страници се създават като чернови без текст.', 'printpop' ); ?></p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( $create ); ?>"><?php esc_html_e( 'Създай страниците', 'printpop' ); ?></a>
			<a class="button-link" href="<?php echo esc_url( $hide ); ?>"><?php esc_html_e( 'Ще ги създам ръчно — скрий съобщението', 'printpop' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'printpop_pages_notice' );

function printpop_handle_create_pages() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Нямаш права за това действие.', 'printpop' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'printpop_create_pages' );

	$missing = printpop_missing_pages();
	$defs    = printpop_pages_definition();

	foreach ( $missing as $key ) {
		list( $title, $slug, $template, $status, $content ) = $defs[ $key ];

		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_status'  => $status,
				'post_content' => $content,
			)
		);
		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}
		if ( $template ) {
			update_post_meta( $id, '_wp_page_template', $template );
		}
		if ( 'home' === $key ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $id );
		}
	}

	wp_safe_redirect( admin_url( 'edit.php?post_type=page' ) );
	exit;
}
add_action( 'admin_post_printpop_create_pages', 'printpop_handle_create_pages' );

function printpop_handle_hide_pages_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Нямаш права за това действие.', 'printpop' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'printpop_hide_pages_notice' );
	update_option( 'printpop_pages_notice_hidden', 1 );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
}
add_action( 'admin_post_printpop_hide_pages_notice', 'printpop_handle_hide_pages_notice' );
