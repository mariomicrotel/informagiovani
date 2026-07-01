<?php
/**
 * Form iscrizione eventi. Variabili attese:
 *   $preselect_event_id (int|null) — se si vuole pre-selezionare un evento.
 */
defined( 'ABSPATH' ) || exit;

$events  = IG_Enna_Event_Registrations::available_events( 50 );
$notices = IG_Enna_Auth::pop_notices();
$logged  = is_user_logged_in();
$me      = $logged ? wp_get_current_user() : null;
$profile = $logged && class_exists( 'IG_Enna_User_Profile' ) ? IG_Enna_User_Profile::get( $me->ID ) : [];
$default_name  = $logged ? trim( $me->first_name . ' ' . $me->last_name ) ?: $me->display_name : '';
$default_email = $logged ? $me->user_email : '';
$default_phone = $logged && ! empty( $profile['ig_phone'] ) ? $profile['ig_phone'] : '';
$preselect     = isset( $preselect_event_id ) ? (int) $preselect_event_id : ( isset( $_GET['ig_event'] ) ? (int) $_GET['ig_event'] : 0 );
?>
<div class="ig-enna ig ig-enna-event-signup">

	<?php foreach ( $notices as $n ) : ?>
		<div class="ig-enna-notice ig-enna-notice--<?php echo esc_attr( $n['type'] ); ?>">
			<?php echo esc_html( $n['msg'] ); ?>
		</div>
	<?php endforeach; ?>

	<header class="ig-enna-event-signup__head">
		<h2><?php esc_html_e( 'Iscriviti a un evento', 'ig-enna' ); ?></h2>
		<p><?php esc_html_e( 'Scegli l\'evento a cui vuoi partecipare. Ti confermeremo l\'iscrizione via email.', 'ig-enna' ); ?></p>
	</header>

	<?php if ( ! $events ) : ?>
		<div class="ig-enna-empty">
			<h3><?php esc_html_e( 'Nessun evento aperto', 'ig-enna' ); ?></h3>
			<p><?php esc_html_e( 'Al momento non ci sono eventi con iscrizioni aperte. Torna a trovarci: pubblichiamo nuovi appuntamenti ogni settimana.', 'ig-enna' ); ?></p>
		</div>
	<?php else : ?>

	<form method="post" action="" class="ig-enna-form ig-enna-event-form">
		<?php wp_nonce_field( IG_Enna_Auth::EVENT_REG_NONCE, '_ig_nonce' ); ?>
		<input type="hidden" name="ig_enna_action" value="event_register" />

		<label>
			<span><?php esc_html_e( 'Evento', 'ig-enna' ); ?> *</span>
			<select class="ig-enna-select" name="event_id" required id="ig-event-select">
				<option value=""><?php esc_html_e( '— Seleziona un evento —', 'ig-enna' ); ?></option>
				<?php foreach ( $events as $ev ) :
					$d     = get_post_meta( $ev->ID, '_ig_enna_event_date', true );
					$t     = get_post_meta( $ev->ID, '_ig_enna_event_time', true );
					$place = get_post_meta( $ev->ID, '_ig_enna_event_place', true );
					$mode  = get_post_meta( $ev->ID, '_ig_enna_event_mode', true );
					$cap   = (int) get_post_meta( $ev->ID, '_ig_enna_event_capacity', true );
					$active = IG_Enna_Event_Registrations::count_active( $ev->ID );
					$full  = $cap > 0 && $active >= $cap;
					$ts    = $d ? strtotime( $t ? $d . ' ' . $t : $d ) : 0;
					$label = $ev->post_title;
					if ( $ts ) { $label .= ' · ' . date_i18n( 'd M Y', $ts ) . ( $t ? ' ' . $t : '' ); }
					if ( $place ) { $label .= ' · ' . $place; }
					if ( $full ) { $label .= ' · ' . __( '(pieno · lista d\'attesa)', 'ig-enna' ); }
				?>
					<option
						value="<?php echo (int) $ev->ID; ?>"
						<?php selected( $preselect, $ev->ID ); ?>
						data-mode="<?php echo esc_attr( $mode ); ?>"
						data-place="<?php echo esc_attr( $place ); ?>"
						data-date="<?php echo esc_attr( $ts ? date_i18n( 'l d F Y', $ts ) : '' ); ?><?php if ( $t ) : ?> · <?php echo esc_attr( $t ); ?><?php endif; ?>"
						data-cap="<?php echo esc_attr( $cap ); ?>"
						data-active="<?php echo esc_attr( $active ); ?>"
					>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</label>

		<div class="ig-enna-event-form__preview" id="ig-event-preview" hidden aria-live="polite"></div>

		<div class="ig-enna-form-row">
			<label>
				<span><?php esc_html_e( 'Nome e cognome', 'ig-enna' ); ?> *</span>
				<input class="ig-enna-input" type="text" name="name" value="<?php echo esc_attr( $default_name ); ?>" required maxlength="150" />
			</label>
			<label>
				<span><?php esc_html_e( 'Email', 'ig-enna' ); ?> *</span>
				<input class="ig-enna-input" type="email" name="email" value="<?php echo esc_attr( $default_email ); ?>" required />
			</label>
		</div>

		<label>
			<span><?php esc_html_e( 'Telefono (facoltativo)', 'ig-enna' ); ?></span>
			<input class="ig-enna-input" type="tel" name="phone" value="<?php echo esc_attr( $default_phone ); ?>" />
		</label>

		<label>
			<span><?php esc_html_e( 'Note (facoltative)', 'ig-enna' ); ?></span>
			<textarea class="ig-enna-input" name="notes" rows="3" placeholder="<?php esc_attr_e( 'Es. esigenze di accessibilità, allergie, arrivo con accompagnatore…', 'ig-enna' ); ?>"></textarea>
		</label>

		<label class="ig-enna-checkbox">
			<input type="checkbox" name="ig_consent_priv" required />
			<span><?php esc_html_e( 'Ho letto l\'informativa sulla privacy e acconsento al trattamento dei dati.', 'ig-enna' ); ?></span>
		</label>

		<button type="submit" class="ig-enna-btn ig-enna-btn--primary">
			<?php esc_html_e( 'Invia iscrizione', 'ig-enna' ); ?>
		</button>
	</form>

	<script>
	(function () {
		var sel = document.getElementById('ig-event-select');
		var box = document.getElementById('ig-event-preview');
		if (!sel || !box) return;
		function update() {
			var opt = sel.options[sel.selectedIndex];
			if (!opt || !opt.value) { box.hidden = true; box.innerHTML = ''; return; }
			var date  = opt.dataset.date  || '';
			var mode  = opt.dataset.mode  || '';
			var place = opt.dataset.place || '';
			var cap   = parseInt(opt.dataset.cap || '0', 10);
			var act   = parseInt(opt.dataset.active || '0', 10);
			var full  = cap > 0 && act >= cap;
			var lines = [];
			if (date)  lines.push('<strong><?php echo esc_js( __( 'Quando:', 'ig-enna' ) ); ?></strong> ' + date);
			if (mode === 'online')  lines.push('<strong><?php echo esc_js( __( 'Modalità:', 'ig-enna' ) ); ?></strong> Online');
			if (mode === 'presenza') lines.push('<strong><?php echo esc_js( __( 'Modalità:', 'ig-enna' ) ); ?></strong> In presenza');
			if (place) lines.push('<strong><?php echo esc_js( __( 'Dove:', 'ig-enna' ) ); ?></strong> ' + place);
			if (cap > 0) {
				lines.push('<strong><?php echo esc_js( __( 'Posti:', 'ig-enna' ) ); ?></strong> ' + act + ' / ' + cap + (full ? ' — <em><?php echo esc_js( __( 'evento pieno, verrai messo in lista d\'attesa', 'ig-enna' ) ); ?></em>' : ''));
			}
			box.innerHTML = lines.join('<br>');
			box.hidden = false;
			box.classList.toggle('is-full', full);
		}
		sel.addEventListener('change', update);
		update();
	})();
	</script>

	<?php endif; ?>
</div>
