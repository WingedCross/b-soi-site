<?php
/**
 * Lecteur radio : shortcode [wr_radio_player].
 *
 * Utilisation : [wr_radio_player]
 * (les réglages viennent du Customizer > Diffuseur radio, pré-remplis
 * avec le widget RadioKing existant de b-soï)
 *
 * @package WebRadio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function webradio_render_player_shortcode( $atts = array() ) {
	$atts = shortcode_atts(
		array(
			'title'    => get_theme_mod( 'webradio_player_title', __( 'En direct maintenant', 'webradio-child' ) ),
			'subtitle' => get_theme_mod( 'webradio_player_subtitle', '' ),
		),
		$atts,
		'wr_radio_player'
	);

	$type         = get_theme_mod( 'webradio_player_type', 'iframe' );
	$embed_url    = get_theme_mod( 'webradio_player_embed_url', '' );
	$extra_script = get_theme_mod( 'webradio_player_extra_script', '' );
	$stream_url   = get_theme_mod( 'webradio_player_stream_url', '' );
	$width        = absint( get_theme_mod( 'webradio_player_width', 275 ) );
	$height       = absint( get_theme_mod( 'webradio_player_height', 365 ) );

	ob_start();
	?>
	<div class="wr-radio-player">
		<div class="wr-radio-player__badge"><?php esc_html_e( 'Live', 'webradio-child' ); ?></div>

		<div class="wr-radio-player__info">
			<p class="wr-radio-player__title"><?php echo esc_html( $atts['title'] ); ?></p>
			<?php if ( ! empty( $atts['subtitle'] ) ) : ?>
				<p class="wr-radio-player__subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( 'iframe' === $type && $embed_url ) : ?>
			<div class="wr-radio-player__frame-wrap">
				<iframe
					src="<?php echo esc_url( $embed_url ); ?>"
					width="<?php echo esc_attr( $width ); ?>"
					height="<?php echo esc_attr( $height ); ?>"
					frameborder="0"
					scrolling="no"
					allow="autoplay"
					title="<?php esc_attr_e( 'Lecteur radio en direct — B-Soï', 'webradio-child' ); ?>"
				></iframe>
				<?php if ( $extra_script ) : ?>
					<script type="text/javascript" src="<?php echo esc_url( $extra_script ); ?>"></script>
				<?php endif; ?>
			</div>
		<?php elseif ( 'audio' === $type && $stream_url ) : ?>
			<div class="wr-radio-player__frame-wrap">
				<audio controls preload="none">
					<source src="<?php echo esc_url( $stream_url ); ?>">
					<?php esc_html_e( 'Votre navigateur ne supporte pas la lecture audio intégrée.', 'webradio-child' ); ?>
				</audio>
			</div>
		<?php else : ?>
			<div class="wr-radio-player__fallback">
				<?php esc_html_e( 'Aucun lecteur configuré pour le moment.', 'webradio-child' ); ?>
				<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
					—
					<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=webradio_player_section' ) ); ?>">
						<?php esc_html_e( 'Configurer le diffuseur', 'webradio-child' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'wr_radio_player', 'webradio_render_player_shortcode' );

/**
 * Le lecteur s'insère n'importe où via le shortcode [wr_radio_player] —
 * dans un bloc "Shortcode" de l'éditeur, ou directement dans un template
 * HTML du thème (voir templates/front-page.html). Il remplace la page
 * "Diffuseur" existante, qui peut être conservée en parallèle le temps
 * de la validation sur staging.
 */
