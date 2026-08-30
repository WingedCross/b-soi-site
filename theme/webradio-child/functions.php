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
 * Les templates HTML (front-page.html, page-agenda.html) utilisent le
 * shortcode [tribe_events] fourni par le plugin "The Events Calendar".
 * Tant que ce plugin n'est pas installé, WordPress affiche le texte brut
 * du shortcode aux visiteurs — on affiche un message d'aide à la place,
 * uniquement si le vrai shortcode n'est pas déjà enregistré.
 */
function webradio_child_agenda_fallback_shortcode() {
	if ( ! shortcode_exists( 'tribe_events' ) ) {
		add_shortcode( 'tribe_events', 'webradio_child_agenda_missing_plugin_notice' );
	}
}
add_action( 'init', 'webradio_child_agenda_fallback_shortcode', 20 );

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
