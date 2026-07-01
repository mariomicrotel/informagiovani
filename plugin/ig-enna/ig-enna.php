<?php
/**
 * Plugin Name:       Informagiovani Enna Manager
 * Plugin URI:        https://comune.enna.it/
 * Description:       Gestione completa dell'Informagiovani del Comune di Enna: schede informative, eventi, ticket, appuntamenti, colloqui, partner, area personale e backend gestionale.
 * Version:           0.5.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Comune di Enna
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ig-enna
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'IG_ENNA_VERSION',     '0.5.0' );
define( 'IG_ENNA_DB_VERSION',  '2' );
define( 'IG_ENNA_FILE',        __FILE__ );
define( 'IG_ENNA_BASENAME',    plugin_basename( __FILE__ ) );
define( 'IG_ENNA_DIR',         plugin_dir_path( __FILE__ ) );
define( 'IG_ENNA_URL',         plugin_dir_url( __FILE__ ) );

require_once IG_ENNA_DIR . 'includes/helpers.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-roles.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-db.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-cpt.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-taxonomies.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-i18n.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-assets.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-shortcodes.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-activator.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-deactivator.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-scheda-meta.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-scheda-protocol.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-evento-meta.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-partner-meta.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-percorso-meta.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-admin-list.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-user-profile.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-user-saves.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-cv.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-tickets.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-appointments.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-event-registrations.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-colloqui.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-auth.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-rest.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-audit.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-newsletter.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-notifications.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-gdpr.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-seed.php';
require_once IG_ENNA_DIR . 'admin/class-ig-enna-admin-menu.php';
require_once IG_ENNA_DIR . 'admin/class-ig-enna-settings.php';
require_once IG_ENNA_DIR . 'admin/class-ig-enna-admin-home.php';
require_once IG_ENNA_DIR . 'admin/class-ig-enna-admin-tickets.php';
require_once IG_ENNA_DIR . 'admin/class-ig-enna-admin-appointments.php';
require_once IG_ENNA_DIR . 'admin/class-ig-enna-admin-colloqui.php';
require_once IG_ENNA_DIR . 'admin/class-ig-enna-admin-audit.php';
require_once IG_ENNA_DIR . 'admin/class-ig-enna-admin-newsletter.php';
require_once IG_ENNA_DIR . 'admin/class-ig-enna-admin-report.php';
require_once IG_ENNA_DIR . 'public/class-ig-enna-public.php';
require_once IG_ENNA_DIR . 'public/class-ig-enna-frontend.php';
require_once IG_ENNA_DIR . 'includes/class-ig-enna-plugin.php';

register_activation_hook(   __FILE__, [ 'IG_Enna_Activator',   'activate' ] );
register_deactivation_hook( __FILE__, [ 'IG_Enna_Deactivator', 'deactivate' ] );

add_action( 'plugins_loaded', [ 'IG_Enna_Plugin', 'instance' ] );
