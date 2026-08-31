<?php
/**
 * WebRadio — thème enfant de Twenty Twenty-Five.
 *
 * @package WebRadio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Sécurité : pas d'accès direct.
}

define( 'WEBRADIO_CHILD_VERSION', '1.0.0' );

/**
 * Charge les styles : celui du thème parent (Twenty Twenty-Five), puis
 * le style.css du thème enfant (en-tête uniquement), puis nos styles de
 * composants propres au site (lecteur radio, agenda, boutons).
 */
function webradio_child_enqueue_styles() {
	$parent_theme = wp_get_theme( get_template() );

	wp_enqueue_style(
		'twentytwentyfive-style',
		get_template_directory_uri() . '/style.css',
		array(),
		$parent_theme->get( 'Version' )
	);

	wp_enqueue_style(
		'webradio-child-style',
		get_stylesheet_uri(),
		array( 'twentytwentyfive-style' ),
		WEBRADIO_CHILD_VERSION
	);

	wp_enqueue_style(
		'webradio-components',
		get_stylesheet_directory_uri() . '/assets/css/components.css',
		array( 'webradio-child-style' ),
		WEBRADIO_CHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'webradio_child_enqueue_styles' );

/**
 * Réglages spécifiques au site (indépendants du fait que le thème soit
 * classique ou par blocs — ces fichiers ne changent pas).
 */
require get_stylesheet_directory() . '/inc/customizer.php';
require get_stylesheet_directory() . '/inc/radio-player.php';

/**
 * Ajoute une classe de contexte "agenda" sur le body quand le plugin
 * d'événements (The Events Calendar) est actif sur la requête en cours,
 * pour faciliter le ciblage CSS dans components.css.
 */
function webradio_child_body_classes( $classes ) {
	if ( function_exists( 'tribe_is_event_query' ) && tribe_is_event_query() ) {
		$classes[] = 'wr-agenda-context';
	}
	return $classes;
}
add_filter( 'body_class', 'webradio_child_body_classes' );

/**
 * Affichage de l'agenda — shortcode maison [wr_agenda].
 *
 * Le plugin gratuit "The Events Calendar" NE fournit PAS le shortcode
 * [tribe_events] : celui-ci est réservé à Events Calendar PRO (payant).
 * La version gratuite crée en revanche un type de contenu "tribe_events"
 * interrogeable normalement — on écrit donc notre propre shortcode qui va
 * chercher les prochains événements directement via WP_Query, et les
 * affiche avec notre propre balisage (facilement stylable avec la charte
 * graphique du site, sans dépendre du plugin payant).
 */
function webradio_child_agenda_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'events_per_page' => 3,
		),
		$atts,
		'wr_agenda'
	);

	if ( ! post_type_exists( 'tribe_events' ) ) {
		return webradio_child_agenda_missing_plugin_notice();
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'tribe_events',
			'posts_per_page' => (int) $atts['events_per_page'],
			'meta_key'       => '_EventStartDate',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => '_EventStartDate',
					'value'   => current_time( 'Y-m-d H:i:s' ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				),
			),
		)
	);

	if ( ! $query->have_posts() ) {
		return '<p class="wr-agenda-empty">' . esc_html__( 'Aucun événement à venir pour le moment.', 'webradio-child' ) . '</p>';
	}

	ob_start();
	echo '<div class="wr-agenda-list">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$start_date   = get_post_meta( get_the_ID(), '_EventStartDate', true );
		$date_display = $start_date ? date_i18n( 'j F Y — H:i', strtotime( $start_date ) ) : '';
		?>
		<div class="wr-agenda-list__item wr-card">
			<?php if ( $date_display ) : ?>
				<span class="wr-tag"><?php echo esc_html( $date_display ); ?></span>
			<?php endif; ?>
			<h3 class="wr-agenda-list__title">
				<a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
			</h3>
		</div>
		<?php
	}
	echo '</div>';
	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'wr_agenda', 'webradio_child_agenda_shortcode' );

function webradio_child_agenda_missing_plugin_notice() {
	ob_start();
	?>
	<div class="wr-card">
		<p>
			<?php
			esc_html_e(
				'Aucun plugin d\'agenda détecté. Installez « The Events Calendar » (gratuit, WordPress.org) pour activer cette section automatiquement — aucune modification de code nécessaire.',
				'webradio-child'
			);
			?>
		</p>
		<?php if ( current_user_can( 'install_plugins' ) ) : ?>
			<p>
				<a class="wr-btn" href="<?php echo esc_url( admin_url( 'plugin-install.php?s=the+events+calendar&tab=search&type=term' ) ); ?>">
					<?php esc_html_e( 'Installer le plugin agenda', 'webradio-child' ); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}