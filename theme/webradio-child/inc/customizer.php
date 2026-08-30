<?php
/**
 * Réglages du Customizer : diffuseur radio + réseaux sociaux.
 *
 * Les valeurs par défaut ci-dessous reprennent le widget RadioKing déjà
 * utilisé sur b-soi.fr (page "Diffuseur"), avec ses couleurs mises à jour
 * pour correspondre à la nouvelle charte (orange feu / blanc cassé au lieu
 * du bleu/blanc d'origine). Elles restent modifiables depuis l'admin sans
 * toucher au code.
 *
 * @package WebRadio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function webradio_customize_register( $wp_customize ) {

	// ------------------------------------------------------------------
	// Section : Diffuseur radio
	// ------------------------------------------------------------------
	$wp_customize->add_section(
		'webradio_player_section',
		array(
			'title'       => __( 'Diffuseur radio', 'webradio-child' ),
			'priority'    => 30,
			'description' => __( 'Configurez le lecteur en direct (widget RadioKing par défaut).', 'webradio-child' ),
		)
	);

	// Type d'intégration.
	$wp_customize->add_setting(
		'webradio_player_type',
		array(
			'default'           => 'iframe',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'webradio_player_type',
		array(
			'label'   => __( 'Type d\'intégration', 'webradio-child' ),
			'section' => 'webradio_player_section',
			'type'    => 'radio',
			'choices' => array(
				'iframe' => __( 'Widget fourni par un service tiers (RadioKing, Radio.co…) — iframe', 'webradio-child' ),
				'audio'  => __( 'Flux audio direct (URL Icecast/Shoutcast .mp3/.aac)', 'webradio-child' ),
			),
		)
	);

	// URL du widget iframe (service tiers).
	$wp_customize->add_setting(
		'webradio_player_embed_url',
		array(
			'default'           => 'https://player.radioking.io/b-soi/?c=%23CC5500&c2=%23FAFAFA&f=v&i=1&p=1&s=0&alb=1&li=1&popup=1&plc=0&h=365&l=275&v=2',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'webradio_player_embed_url',
		array(
			'label'       => __( 'URL du widget (iframe)', 'webradio-child' ),
			'description' => __( 'Pré-rempli avec le widget RadioKing de b-soï, recoloré en orange feu / blanc cassé. Modifiable depuis le back-office RadioKing (section Widget/Embed) si vous changez les couleurs ou le format.', 'webradio-child' ),
			'section'     => 'webradio_player_section',
			'type'        => 'url',
		)
	);

	// Script complémentaire (certains widgets, dont RadioKing, en ont besoin).
	$wp_customize->add_setting(
		'webradio_player_extra_script',
		array(
			'default'           => 'https://player.radioking.io/scripts/iframe.bundle.js',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'webradio_player_extra_script',
		array(
			'label'       => __( 'Script complémentaire du widget (optionnel)', 'webradio-child' ),
			'description' => __( 'Nécessaire pour RadioKing (popup, redimensionnement). Laisser vide si votre service n\'en fournit pas.', 'webradio-child' ),
			'section'     => 'webradio_player_section',
			'type'        => 'url',
		)
	);

	// URL du flux audio direct (alternative).
	$wp_customize->add_setting(
		'webradio_player_stream_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'webradio_player_stream_url',
		array(
			'label'       => __( 'URL du flux audio direct', 'webradio-child' ),
			'description' => __( 'Utilisée uniquement si le type d\'intégration est « flux audio direct ».', 'webradio-child' ),
			'section'     => 'webradio_player_section',
			'type'        => 'url',
		)
	);

	// Titre du lecteur.
	$wp_customize->add_setting(
		'webradio_player_title',
		array(
			'default'           => __( 'En direct maintenant', 'webradio-child' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'webradio_player_title',
		array(
			'label'   => __( 'Titre du lecteur', 'webradio-child' ),
			'section' => 'webradio_player_section',
			'type'    => 'text',
		)
	);

	// Sous-titre / accroche.
	$wp_customize->add_setting(
		'webradio_player_subtitle',
		array(
			'default'           => __( 'Écoutez B-Soï en direct, 24h/24.', 'webradio-child' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'webradio_player_subtitle',
		array(
			'label'   => __( 'Sous-titre', 'webradio-child' ),
			'section' => 'webradio_player_section',
			'type'    => 'text',
		)
	);

	// Largeur du widget iframe.
	$wp_customize->add_setting(
		'webradio_player_width',
		array(
			'default'           => 275,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'webradio_player_width',
		array(
			'label'   => __( 'Largeur du widget iframe (en pixels)', 'webradio-child' ),
			'section' => 'webradio_player_section',
			'type'    => 'number',
		)
	);

	// Hauteur du widget iframe.
	$wp_customize->add_setting(
		'webradio_player_height',
		array(
			'default'           => 365,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'webradio_player_height',
		array(
			'label'   => __( 'Hauteur du widget iframe (en pixels)', 'webradio-child' ),
			'section' => 'webradio_player_section',
			'type'    => 'number',
		)
	);

	// ------------------------------------------------------------------
	// Section : Réseaux sociaux (affichés en pied de page)
	// ------------------------------------------------------------------
	$wp_customize->add_section(
		'webradio_social_section',
		array(
			'title'    => __( 'Réseaux sociaux', 'webradio-child' ),
			'priority' => 35,
		)
	);

	$socials = array(
		'facebook_url'  => __( 'URL Facebook', 'webradio-child' ),
		'instagram_url' => __( 'URL Instagram', 'webradio-child' ),
		'x_url'         => __( 'URL X / Twitter', 'webradio-child' ),
		'mixcloud_url'  => __( 'URL Mixcloud (rediffusions)', 'webradio-child' ),
	);

	foreach ( $socials as $id => $label ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $label,
				'section' => 'webradio_social_section',
				'type'    => 'url',
			)
		);
	}
}
add_action( 'customize_register', 'webradio_customize_register' );
