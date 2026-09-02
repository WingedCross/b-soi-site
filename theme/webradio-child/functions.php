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

/**
 * Sécurité : masquer la version de WordPress.
 *
 * Retire le numéro de version du <head> et des URLs de fichiers CSS/JS
 * (?ver=7.1), pour ne pas faciliter la recherche de failles connues
 * correspondant à la version exacte du site.
 */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

function webradio_child_remove_version_query_arg( $src ) {
	if ( strpos( $src, 'ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'style_loader_src', 'webradio_child_remove_version_query_arg', 9999 );
add_filter( 'script_loader_src', 'webradio_child_remove_version_query_arg', 9999 );

/**
 * Sécurité : désactiver XML-RPC.
 *
 * XML-RPC est un vecteur classique d'attaques brute-force amplifiées
 * (méthode system.multicall) et de DDoS par pingback. Le filtre
 * xmlrpc_enabled seul ne suffit pas à bloquer les requêtes réelles, on
 * bloque donc directement l'accès au fichier xmlrpc.php. On évite
 * wp_die() ici : dans le contexte XML-RPC, WordPress route wp_die()
 * vers un handler qui dépend d'une classe pas encore chargée à ce
 * stade (page blanche/erreur fatale) — on sort donc une réponse texte
 * brute nous-mêmes.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );
remove_action( 'wp_head', 'rsd_link' );
add_filter(
	'wp_headers',
	function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);

add_action(
	'init',
	function () {
		if ( false !== strpos( $_SERVER['REQUEST_URI'], 'xmlrpc.php' ) ) {
			status_header( 403 );
			header( 'Content-Type: text/plain; charset=utf-8' );
			echo 'XML-RPC services are disabled on this site.';
			exit;
		}
	}
);

/**
 * Sécurité : bloquer l'énumération des utilisateurs via ?author=N.
 *
 * Empêche de deviner les identifiants des comptes admin en itérant
 * sur des URLs du type b-soi.fr/?author=1, /?author=2, etc. Hook sur
 * "init" plutôt que "template_redirect" pour s'exécuter avant que
 * WordPress ne redirige lui-même vers l'URL propre de l'auteur.
 */
add_action(
	'init',
	function () {
		if ( ! is_admin() && ! empty( $_GET['author'] ) ) {
			wp_die( 'Accès non autorisé.', 'Erreur', array( 'response' => 403 ) );
		}
	}
);

/**
 * Sécurité : bloquer l'énumération des utilisateurs via l'API REST.
 *
 * L'endpoint /wp-json/wp/v2/users expose la liste des comptes (dont les
 * identifiants admin) à n'importe quel visiteur non connecté — même
 * vecteur d'attaque que ?author=N, mais par une porte différente. On ne
 * bloque que pour les visiteurs non connectés, pour ne pas casser des
 * fonctionnalités internes de l'éditeur (sélecteur d'auteur pour les
 * comptes Editor/Administrator, qui restent connectés).
 */
add_filter(
	'rest_authentication_errors',
	function ( $result ) {
		if ( ! empty( $result ) ) {
			return $result;
		}
		if ( ! is_user_logged_in() && false !== strpos( $_SERVER['REQUEST_URI'], '/wp-json/wp/v2/users' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Accès non autorisé.', 'webradio-child' ), array( 'status' => 403 ) );
		}
		return $result;
	}
);

/**
 * Sécurité : uniformiser les messages d'erreur de connexion.
 *
 * Par défaut, WordPress indique si c'est le nom d'utilisateur qui est
 * inconnu OU le mot de passe qui est incorrect — ce qui confirme à un
 * attaquant qu'un identifiant existe, facilitant les attaques ciblées.
 * On remplace par un message neutre, identique dans tous les cas.
 */
add_filter(
	'login_errors',
	function () {
		return __( 'Identifiants incorrects.', 'webradio-child' );
	}
);