<?php
/**
 * Pagina impostazioni — Settings API.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Settings {

	const OPTION_NAME = 'ig_enna_settings';
	const PAGE_SLUG   = 'ig-enna-settings';
	const GROUP       = 'ig_enna_settings_group';

	public static function init() {
		add_action( 'admin_init', [ __CLASS__, 'register' ] );
	}

	public static function register() {
		register_setting( self::GROUP, self::OPTION_NAME, [
			'type'              => 'array',
			'sanitize_callback' => 'ig_enna_sanitize_settings',
			'default'           => ig_enna_default_settings(),
		] );

		add_settings_section(
			'ig_enna_general',
			__( 'Generale', 'ig-enna' ),
			function () {
				echo '<p>' . esc_html__( 'Configurazione di base del plugin.', 'ig-enna' ) . '</p>';
			},
			self::PAGE_SLUG
		);

		self::add_field( 'org_name',                    __( 'Nome organizzazione', 'ig-enna' ),  'text'     );
		self::add_field( 'contact_email',               __( 'Email di contatto', 'ig-enna' ),    'email'    );
		self::add_field( 'enable_public_registration',  __( 'Abilita registrazione pubblica', 'ig-enna' ), 'checkbox' );
		self::add_field( 'default_sla_hours',           __( 'SLA default ticket (ore)', 'ig-enna' ),  'number'  );
		self::add_field( 'enable_audit_log',            __( 'Abilita audit log', 'ig-enna' ),    'checkbox' );
		self::add_field( 'delete_data_on_uninstall',    __( 'Elimina dati alla disinstallazione', 'ig-enna' ), 'checkbox' );
	}

	private static function add_field( $key, $label, $type ) {
		add_settings_field(
			'ig_enna_field_' . $key,
			esc_html( $label ),
			function () use ( $key, $type ) {
				$opts  = wp_parse_args( get_option( self::OPTION_NAME, [] ), ig_enna_default_settings() );
				$value = isset( $opts[ $key ] ) ? $opts[ $key ] : '';
				$name  = self::OPTION_NAME . '[' . $key . ']';

				switch ( $type ) {
					case 'checkbox':
						printf(
							'<label><input type="checkbox" name="%1$s" value="1" %2$s /> %3$s</label>',
							esc_attr( $name ),
							checked( ! empty( $value ), true, false ),
							esc_html__( 'Attiva', 'ig-enna' )
						);
						break;
					case 'number':
						printf(
							'<input type="number" min="1" name="%1$s" value="%2$s" class="small-text" />',
							esc_attr( $name ),
							esc_attr( (string) $value )
						);
						break;
					case 'email':
						printf(
							'<input type="email" name="%1$s" value="%2$s" class="regular-text" />',
							esc_attr( $name ),
							esc_attr( (string) $value )
						);
						break;
					default:
						printf(
							'<input type="text" name="%1$s" value="%2$s" class="regular-text" />',
							esc_attr( $name ),
							esc_attr( (string) $value )
						);
				}
			},
			self::PAGE_SLUG,
			'ig_enna_general'
		);
	}

	public static function render_page() {
		if ( ! current_user_can( 'ig_enna_manage' ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'ig-enna' ) );
		}
		include IG_ENNA_DIR . 'admin/views/settings.php';
	}
}
