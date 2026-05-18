<?php
/**
 * Form prenotazione colloquio.
 */
defined( 'ABSPATH' ) || exit;

$notices  = IG_Enna_Auth::pop_notices();
$logged   = is_user_logged_in();
$me       = $logged ? wp_get_current_user() : null;
$min_date = current_time( 'Y-m-d' );
?>
<div class="ig-enna ig ig-enna-booking">
	<?php foreach ( $notices as $n ) : ?>
		<div class="ig-enna-notice ig-enna-notice--<?php echo esc_attr( $n['type'] ); ?>"><?php echo esc_html( $n['msg'] ); ?></div>
	<?php endforeach; ?>

	<header class="ig-enna-booking__head">
		<div class="ig-enna-eyebrow"><?php esc_html_e( 'Sportello orientamento · gratuito', 'ig-enna' ); ?></div>
		<h1><?php esc_html_e( 'Prenota un colloquio', 'ig-enna' ); ?></h1>
		<p><?php esc_html_e( 'Inviaci una richiesta indicando data preferita e modalità. Un operatore ti contatterà per confermare lo slot.', 'ig-enna' ); ?></p>
	</header>

	<div class="ig-enna-booking__layout">
		<form method="post" action="" class="ig-enna-form ig-enna-booking__form">
			<?php wp_nonce_field( IG_Enna_Auth::BOOKING_NONCE, '_ig_nonce' ); ?>
			<input type="hidden" name="ig_enna_action" value="booking_create" />

			<?php if ( ! $logged ) : ?>
				<h3 class="ig-enna-section-title"><?php esc_html_e( 'I tuoi dati', 'ig-enna' ); ?></h3>
				<div class="ig-enna-form-row">
					<label><span><?php esc_html_e( 'Nome e cognome', 'ig-enna' ); ?></span>
						<input class="ig-enna-input" type="text" name="guest_name" required />
					</label>
					<label><span><?php esc_html_e( 'Email', 'ig-enna' ); ?></span>
						<input class="ig-enna-input" type="email" name="guest_email" required />
					</label>
				</div>
				<label><span><?php esc_html_e( 'Telefono (facoltativo)', 'ig-enna' ); ?></span>
					<input class="ig-enna-input" type="tel" name="guest_phone" />
				</label>
			<?php else : ?>
				<p class="description">
					<?php
					/* translators: %s = nome utente */
					printf(
						esc_html__( 'Stai prenotando come %s. Confermeremo via email.', 'ig-enna' ),
						'<strong>' . esc_html( $me->display_name ?: $me->user_login ) . '</strong>'
					);
					?>
				</p>
			<?php endif; ?>

			<h3 class="ig-enna-section-title"><?php esc_html_e( 'Quando ti va bene?', 'ig-enna' ); ?></h3>
			<div class="ig-enna-form-row">
				<label><span><?php esc_html_e( 'Data preferita', 'ig-enna' ); ?></span>
					<input class="ig-enna-input" type="date" name="date" required min="<?php echo esc_attr( $min_date ); ?>" />
				</label>
				<label><span><?php esc_html_e( 'Fascia oraria', 'ig-enna' ); ?></span>
					<select class="ig-enna-select" name="time" required>
						<option value=""><?php esc_html_e( '— Seleziona —', 'ig-enna' ); ?></option>
						<?php
						foreach ( [ '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '15:00', '15:30', '16:00', '16:30', '17:00' ] as $slot ) {
							echo '<option value="' . esc_attr( $slot ) . '">' . esc_html( $slot ) . '</option>';
						}
						?>
					</select>
				</label>
			</div>

			<h3 class="ig-enna-section-title"><?php esc_html_e( 'Modalità', 'ig-enna' ); ?></h3>
			<div class="ig-enna-mode-options">
				<?php
				$modes = [
					'presenza' => [ '👤', __( 'In presenza', 'ig-enna' ), __( 'Sportello in Piazza Garibaldi, 1', 'ig-enna' ) ],
					'online'   => [ '🎥', __( 'Online', 'ig-enna' ),     __( 'Google Meet o Zoom · 30 min', 'ig-enna' ) ],
				];
				$first = true;
				foreach ( $modes as $k => $m ) : ?>
					<label class="ig-enna-mode-option">
						<input type="radio" name="mode" value="<?php echo esc_attr( $k ); ?>" <?php checked( $first ); ?> />
						<span class="ig-enna-mode-option__icon"><?php echo esc_html( $m[0] ); ?></span>
						<span class="ig-enna-mode-option__body">
							<strong><?php echo esc_html( $m[1] ); ?></strong>
							<small><?php echo esc_html( $m[2] ); ?></small>
						</span>
					</label>
				<?php $first = false; endforeach; ?>
			</div>

			<h3 class="ig-enna-section-title"><?php esc_html_e( 'Di cosa vuoi parlare?', 'ig-enna' ); ?></h3>
			<label><span><?php esc_html_e( 'Argomento (facoltativo)', 'ig-enna' ); ?></span>
				<textarea class="ig-enna-input" name="topic" rows="4" placeholder="<?php esc_attr_e( 'Es. cerco un tirocinio nel settore turistico, vorrei aprire un\'attività…', 'ig-enna' ); ?>"></textarea>
			</label>

			<label class="ig-enna-checkbox">
				<input type="checkbox" name="ig_consent_priv" required />
				<span><?php esc_html_e( 'Ho letto l\'informativa sulla privacy e acconsento al trattamento dei dati.', 'ig-enna' ); ?></span>
			</label>

			<button class="ig-enna-btn ig-enna-btn--primary ig-enna-btn--lg" type="submit">
				<?php esc_html_e( 'Invia richiesta', 'ig-enna' ); ?>
			</button>
		</form>

		<aside class="ig-enna-booking__side">
			<div class="ig-enna-booking__info">
				<h3>📍 <?php esc_html_e( 'Sportello fisico', 'ig-enna' ); ?></h3>
				<p>Piazza Garibaldi, 1<br/>94100 Enna<br/><br/>Lun–Ven · 9:00–13:00 / 15:00–17:30</p>
			</div>
			<div class="ig-enna-booking__info">
				<h3>📞 <?php esc_html_e( 'Contatti diretti', 'ig-enna' ); ?></h3>
				<p>0935 40 04 00<br/>
				<?php echo esc_html( ig_enna_get_setting( 'contact_email', 'informagiovani@comune.enna.it' ) ); ?></p>
			</div>
			<div class="ig-enna-booking__info">
				<h3>✅ <?php esc_html_e( 'Cosa aspettarti', 'ig-enna' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Conferma entro 48 ore lavorative', 'ig-enna' ); ?></li>
					<li><?php esc_html_e( 'Durata indicativa: 30 minuti', 'ig-enna' ); ?></li>
					<li><?php esc_html_e( 'Gratuito · senza vincolo', 'ig-enna' ); ?></li>
				</ul>
			</div>
		</aside>
	</div>
</div>
