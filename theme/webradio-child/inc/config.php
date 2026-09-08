<?php
/**
 * Configuration centralisée du thème.
 *
 * Source unique des valeurs par défaut du lecteur radio, réutilisées à
 * la fois par le Customizer (inc/customizer.php, pour pré-remplir les
 * champs admin) et par le shortcode du lecteur (inc/radio-player.php,
 * en repli si get_theme_mod() ne trouve rien). Modifier une valeur ici
 * suffit à la mettre à jour aux deux endroits — plus besoin de les
 * garder synchronisées à la main.
 *
 * @package WebRadio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Valeurs par défaut du lecteur radio (widget RadioKing de b-soï).
 *
 * @return array
 */
function webradio_player_defaults() {
	return array(
		'type'         => 'iframe',
		'embed_url'    => 'https://player.radioking.io/b-soi/?c=%23CC5500&c2=%23FAFAFA&f=v&i=1&p=1&s=0&alb=1&li=1&popup=1&plc=0&h=365&l=275&v=2',
		'extra_script' => 'https://player.radioking.io/scripts/iframe.bundle.js',
		'stream_url'   => '',
		'title'        => __( 'En direct maintenant', 'webradio-child' ),
		'subtitle'     => __( 'Écoutez B-Soï en direct, 24h/24.', 'webradio-child' ),
		'width'        => 275,
		'height'       => 365,
	);
}
