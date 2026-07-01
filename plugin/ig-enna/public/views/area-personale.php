<?php
/**
 * Area personale: login se ospite, dashboard se loggato.
 * Variabile: $atts (array).
 */
defined( 'ABSPATH' ) || exit;

$notices = IG_Enna_Auth::pop_notices();
?>
<div class="ig-enna ig ig-enna-area">

	<?php foreach ( $notices as $n ) : ?>
		<div class="ig-enna-notice ig-enna-notice--<?php echo esc_attr( $n['type'] ); ?>">
			<?php echo esc_html( $n['msg'] ); ?>
		</div>
	<?php endforeach; ?>

	<?php if ( ! is_user_logged_in() ) : ?>

		<div class="ig-enna-auth">
			<section class="ig-enna-auth__panel">
				<h2><?php esc_html_e( 'Accedi', 'ig-enna' ); ?></h2>
				<?php
				wp_login_form( [
					'redirect'       => esc_url( get_permalink() ),
					'label_username' => __( 'Email o username', 'ig-enna' ),
					'label_password' => __( 'Password', 'ig-enna' ),
					'label_log_in'   => __( 'Entra', 'ig-enna' ),
					'remember'       => true,
					'value_remember' => true,
				] );
				?>
				<p class="ig-enna-auth__hint">
					<a href="<?php echo esc_url( wp_lostpassword_url( get_permalink() ) ); ?>">
						<?php esc_html_e( 'Hai dimenticato la password?', 'ig-enna' ); ?>
					</a>
				</p>
			</section>

			<?php if ( ig_enna_get_setting( 'enable_public_registration', 1 ) ) : ?>
			<section class="ig-enna-auth__panel">
				<h2><?php esc_html_e( 'Registrati', 'ig-enna' ); ?></h2>
				<form method="post" action="">
					<?php wp_nonce_field( IG_Enna_Auth::REGISTER_NONCE, '_ig_nonce' ); ?>
					<input type="hidden" name="ig_enna_action" value="register" />
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() ); ?>" />

					<div class="ig-enna-form-row">
						<label><span><?php esc_html_e( 'Nome', 'ig-enna' ); ?></span>
							<input class="ig-enna-input" type="text" name="first_name" required />
						</label>
						<label><span><?php esc_html_e( 'Cognome', 'ig-enna' ); ?></span>
							<input class="ig-enna-input" type="text" name="last_name" required />
						</label>
					</div>
					<label><span><?php esc_html_e( 'Email', 'ig-enna' ); ?></span>
						<input class="ig-enna-input" type="email" name="email" required />
					</label>
					<label><span><?php esc_html_e( 'Password', 'ig-enna' ); ?></span>
						<input class="ig-enna-input" type="password" name="password" minlength="8" required />
					</label>
					<label class="ig-enna-checkbox">
						<input type="checkbox" name="ig_consent_priv" required />
						<span><?php esc_html_e( 'Ho letto l\'informativa sulla privacy', 'ig-enna' ); ?></span>
					</label>
					<button class="ig-enna-btn ig-enna-btn--primary" type="submit">
						<?php esc_html_e( 'Crea account', 'ig-enna' ); ?>
					</button>
				</form>
			</section>
			<?php endif; ?>
		</div>

	<?php else :
		$user    = wp_get_current_user();
		$profile = IG_Enna_User_Profile::get( $user->ID );
		$pct     = IG_Enna_User_Profile::completion( $user->ID );
		$saved_ids = IG_Enna_User_Saves::ids_for_user( $user->ID, 'scheda' );
		$tab     = isset( $_GET['ig_tab'] ) ? sanitize_key( $_GET['ig_tab'] ) : 'per_te';
		$base    = get_permalink();
	?>

		<header class="ig-enna-area__head">
			<div>
				<h1><?php
					/* translators: %s = nome utente */
					printf( esc_html__( 'Ciao, %s', 'ig-enna' ), esc_html( $user->display_name ?: $user->user_login ) );
				?></h1>
				<p class="ig-enna-area__sub">
					<?php
					printf(
						/* translators: %d = percentuale completamento profilo */
						esc_html__( 'Profilo completato al %d%%', 'ig-enna' ),
						(int) $pct
					);
					?>
				</p>
			</div>
			<a class="ig-enna-btn ig-enna-btn--ghost ig-enna-btn--sm" href="<?php echo esc_url( wp_logout_url( $base ) ); ?>">
				<?php esc_html_e( 'Esci', 'ig-enna' ); ?>
			</a>
		</header>

		<nav class="ig-enna-tabs" aria-label="<?php esc_attr_e( 'Sezioni area personale', 'ig-enna' ); ?>">
			<?php
			$tabs = [
				'per_te'     => __( '✨ Per te', 'ig-enna' ),
				'panoramica' => __( 'Panoramica', 'ig-enna' ),
				'salvati'    => __( 'Salvati', 'ig-enna' ),
				'richieste'  => __( 'Richieste', 'ig-enna' ),
				'percorsi'   => __( 'Percorsi', 'ig-enna' ),
				'cv'         => __( 'Il mio CV', 'ig-enna' ),
				'profilo'    => __( 'Profilo', 'ig-enna' ),
			];
			foreach ( $tabs as $k => $label ) :
				$is_active = ( $tab === $k );
				$cls = $is_active ? 'ig-enna-tab is-active' : 'ig-enna-tab';
				$url = esc_url( add_query_arg( 'ig_tab', $k, $base ) );
				?>
				<a class="<?php echo esc_attr( $cls ); ?>" href="<?php echo $url; ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>

		<?php if ( $tab === 'per_te' ) :
			include IG_ENNA_DIR . 'public/views/user-suggestions.php';
		?>

		<?php elseif ( $tab === 'panoramica' ) : ?>
			<section class="ig-enna-area__panel">
				<div class="ig-enna-stats">
					<div class="ig-enna-stat">
						<div class="ig-enna-stat__label"><?php esc_html_e( 'Opportunità salvate', 'ig-enna' ); ?></div>
						<div class="ig-enna-stat__num"><?php echo count( $saved_ids ); ?></div>
					</div>
					<div class="ig-enna-stat">
						<div class="ig-enna-stat__label"><?php esc_html_e( 'Completamento profilo', 'ig-enna' ); ?></div>
						<div class="ig-enna-stat__num"><?php echo (int) $pct; ?>%</div>
					</div>
					<?php
					$my_open = IG_Enna_Tickets::query( [ 'user_id' => $user->ID, 'limit' => 999 ] );
					$open_count = 0;
					foreach ( $my_open['rows'] as $tk ) {
						if ( ! in_array( $tk['status'], [ 'done', 'closed' ], true ) ) {
							$open_count++;
						}
					}
					?>
					<div class="ig-enna-stat">
						<div class="ig-enna-stat__label"><?php esc_html_e( 'Richieste aperte', 'ig-enna' ); ?></div>
						<div class="ig-enna-stat__num"><?php echo (int) $open_count; ?></div>
						<a class="ig-enna-stat__hint" href="<?php echo esc_url( add_query_arg( 'ig_tab', 'richieste', $base ) ); ?>">
							<?php esc_html_e( 'Vai alle richieste', 'ig-enna' ); ?>
						</a>
					</div>
				</div>

				<?php if ( $saved_ids ) :
					$recent = array_slice( $saved_ids, 0, 3 ); ?>
					<h3 class="ig-enna-section-title"><?php esc_html_e( 'Ultime opportunità salvate', 'ig-enna' ); ?></h3>
					<div class="ig-enna-list">
						<?php
						$q = new WP_Query( [
							'post_type'      => 'ig_scheda',
							'post__in'       => $recent,
							'orderby'        => 'post__in',
							'posts_per_page' => -1,
						] );
						while ( $q->have_posts() ) : $q->the_post();
							include IG_ENNA_DIR . 'public/views/card-scheda.php';
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				<?php else : ?>
					<div class="ig-enna-empty">
						<h3><?php esc_html_e( 'Nessuna opportunità salvata', 'ig-enna' ); ?></h3>
						<p><?php esc_html_e( 'Sfoglia le opportunità e clicca "Salva" sulla card per ritrovarle qui.', 'ig-enna' ); ?></p>
					</div>
				<?php endif; ?>
			</section>

		<?php elseif ( $tab === 'salvati' ) : ?>
			<section class="ig-enna-area__panel">
				<?php if ( $saved_ids ) :
					$q = new WP_Query( [
						'post_type'      => 'ig_scheda',
						'post__in'       => $saved_ids,
						'orderby'        => 'post__in',
						'posts_per_page' => -1,
					] ); ?>
					<div class="ig-enna-list">
						<?php while ( $q->have_posts() ) : $q->the_post();
							include IG_ENNA_DIR . 'public/views/card-scheda.php';
						endwhile;
						wp_reset_postdata(); ?>
					</div>
				<?php else : ?>
					<div class="ig-enna-empty">
						<h3><?php esc_html_e( 'Nessuna opportunità salvata', 'ig-enna' ); ?></h3>
						<p><?php esc_html_e( 'Inizia a esplorare le opportunità dell\'Informagiovani.', 'ig-enna' ); ?></p>
					</div>
				<?php endif; ?>
			</section>

		<?php elseif ( $tab === 'richieste' ) :
			$my_tickets   = IG_Enna_Tickets::query( [ 'user_id' => $user->ID, 'limit' => 50 ] );
			$areas        = get_terms( [ 'taxonomy' => 'ig_area', 'hide_empty' => false ] );
			$my_appts     = IG_Enna_Appointments::query( [ 'user_id' => $user->ID, 'limit' => 20, 'orderby' => 'slot_start DESC' ] );
			$appt_statuses = IG_Enna_Appointments::statuses();
			$appt_modes    = IG_Enna_Appointments::modes();
			$min_date      = current_time( 'Y-m-d' );
			?>
			<section class="ig-enna-area__panel">

				<!-- ============ PRENOTA COLLOQUIO ============ -->
				<h3 class="ig-enna-section-title">🗓 <?php esc_html_e( 'Prenota un colloquio con un operatore', 'ig-enna' ); ?></h3>
				<p class="description" style="margin-top:-6px;margin-bottom:12px;">
					<?php esc_html_e( 'Indica quando ti va bene, in presenza o online. Un operatore ti contatterà per confermare lo slot entro 48h lavorative.', 'ig-enna' ); ?>
				</p>
				<form method="post" action="" class="ig-enna-form ig-enna-inline-booking">
					<?php wp_nonce_field( IG_Enna_Auth::BOOKING_NONCE, '_ig_nonce' ); ?>
					<input type="hidden" name="ig_enna_action" value="booking_create" />

					<div class="ig-enna-form-row">
						<label><span><?php esc_html_e( 'Data preferita', 'ig-enna' ); ?> *</span>
							<input class="ig-enna-input" type="date" name="date" required min="<?php echo esc_attr( $min_date ); ?>" />
						</label>
						<label><span><?php esc_html_e( 'Fascia oraria', 'ig-enna' ); ?> *</span>
							<select class="ig-enna-select" name="time" required>
								<option value=""><?php esc_html_e( '— Seleziona —', 'ig-enna' ); ?></option>
								<?php foreach ( [ '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '15:00', '15:30', '16:00', '16:30', '17:00' ] as $slot ) : ?>
									<option value="<?php echo esc_attr( $slot ); ?>"><?php echo esc_html( $slot ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</div>

					<div class="ig-enna-form-row">
						<label><span><?php esc_html_e( 'Modalità', 'ig-enna' ); ?> *</span>
							<select class="ig-enna-select" name="mode" required>
								<option value="presenza"><?php esc_html_e( '👤 In presenza (Piazza Garibaldi 1)', 'ig-enna' ); ?></option>
								<option value="online"><?php esc_html_e( '🎥 Online (Google Meet o Zoom · 30 min)', 'ig-enna' ); ?></option>
							</select>
						</label>
						<label><span><?php esc_html_e( 'Area', 'ig-enna' ); ?></span>
							<select class="ig-enna-select" name="area_slug">
								<option value=""><?php esc_html_e( '— Argomento generico —', 'ig-enna' ); ?></option>
								<?php foreach ( $areas as $t ) : ?>
									<option value="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</div>

					<label><span><?php esc_html_e( 'Di cosa vuoi parlare? (facoltativo)', 'ig-enna' ); ?></span>
						<textarea class="ig-enna-input" name="topic" rows="2" placeholder="<?php esc_attr_e( 'Es. sto cercando un tirocinio nel settore turistico, vorrei un consiglio su bando…', 'ig-enna' ); ?>"></textarea>
					</label>

					<label class="ig-enna-checkbox">
						<input type="checkbox" name="ig_consent_priv" required />
						<span><?php esc_html_e( 'Ho letto l\'informativa privacy e acconsento al trattamento dei dati.', 'ig-enna' ); ?></span>
					</label>

					<button class="ig-enna-btn ig-enna-btn--primary" type="submit">
						<?php esc_html_e( '🗓 Prenota colloquio', 'ig-enna' ); ?>
					</button>
				</form>

				<?php if ( $my_appts ) : ?>
					<h4 class="ig-enna-section-title" style="margin-top:24px;font-size:14px;"><?php esc_html_e( 'I tuoi colloqui prenotati', 'ig-enna' ); ?></h4>
					<ul class="ig-enna-mini-appts">
						<?php foreach ( $my_appts as $ap ) :
							$ts = strtotime( $ap['slot_start'] );
							$status = $ap['status'];
							$mode   = $ap['mode'];
						?>
							<li>
								<div class="ig-enna-mini-appts__when">
									<strong><?php echo $ts ? esc_html( date_i18n( 'd M Y', $ts ) ) : ''; ?></strong>
									<small><?php echo $ts ? esc_html( date_i18n( 'H:i', $ts ) ) : ''; ?></small>
								</div>
								<div class="ig-enna-mini-appts__body">
									<span class="ig-enna-badge ig-enna-badge--apstate-<?php echo esc_attr( $status ); ?>">
										<?php echo esc_html( $appt_statuses[ $status ] ?? $status ); ?>
									</span>
									<span class="ig-enna-badge ig-enna-badge--mode-<?php echo esc_attr( $mode ); ?>">
										<?php echo esc_html( $appt_modes[ $mode ] ?? $mode ); ?>
									</span>
									<?php if ( ! empty( $ap['notes'] ) ) : ?>
										<p class="ig-enna-mini-appts__notes"><?php echo esc_html( wp_trim_words( $ap['notes'], 20 ) ); ?></p>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<hr style="margin: 32px 0; border: 0; border-top: 1px solid #e2e8f0;">

				<!-- ============ TICKET SCRITTO ============ -->
				<h3 class="ig-enna-section-title">📨 <?php esc_html_e( 'Oppure invia una richiesta scritta', 'ig-enna' ); ?></h3>
				<p class="description" style="margin-top:-6px;margin-bottom:12px;">
					<?php esc_html_e( 'Se preferisci una risposta scritta senza colloquio (documentazione, chiarimento veloce…), usa questo modulo.', 'ig-enna' ); ?>
				</p>
				<form method="post" action="" class="ig-enna-form ig-enna-ticket-form">
					<?php wp_nonce_field( IG_Enna_Auth::TICKET_NONCE, '_ig_nonce' ); ?>
					<input type="hidden" name="ig_enna_action" value="ticket_create" />

					<label><span><?php esc_html_e( 'Oggetto', 'ig-enna' ); ?></span>
						<input class="ig-enna-input" type="text" name="subject" required maxlength="200" />
					</label>

					<div class="ig-enna-form-row">
						<label><span><?php esc_html_e( 'Area', 'ig-enna' ); ?></span>
							<select class="ig-enna-select" name="area_slug">
								<option value=""><?php esc_html_e( '— Seleziona —', 'ig-enna' ); ?></option>
								<?php foreach ( $areas as $t ) : ?>
									<option value="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<label><span><?php esc_html_e( 'Priorità', 'ig-enna' ); ?></span>
							<select class="ig-enna-select" name="priority">
								<?php foreach ( IG_Enna_Tickets::priorities() as $k => $label ) : ?>
									<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $k, 'media' ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</div>

					<label><span><?php esc_html_e( 'Messaggio', 'ig-enna' ); ?></span>
						<textarea class="ig-enna-input" name="message" rows="5" required minlength="10"></textarea>
					</label>

					<button class="ig-enna-btn ig-enna-btn--primary" type="submit"><?php esc_html_e( 'Invia richiesta', 'ig-enna' ); ?></button>
				</form>

				<h3 class="ig-enna-section-title" style="margin-top:32px;"><?php esc_html_e( 'Le mie richieste', 'ig-enna' ); ?></h3>

				<?php if ( $my_tickets['rows'] ) : ?>
					<div class="ig-enna-tickets">
						<?php foreach ( $my_tickets['rows'] as $tk ) :
							$ts = strtotime( $tk['created_at'] );
							$st_label = IG_Enna_Tickets::status_label( $tk['status'] );
							$pr_label = IG_Enna_Tickets::priority_label( $tk['priority'] );
						?>
							<article class="ig-enna-ticket">
								<header class="ig-enna-ticket__head">
									<span class="ig-enna-ticket__code">R-<?php echo (int) $tk['id']; ?></span>
									<span class="ig-enna-badge ig-enna-badge--tkstate-<?php echo esc_attr( $tk['status'] ); ?>"><?php echo esc_html( $st_label ); ?></span>
									<span class="ig-enna-badge ig-enna-badge--prio-<?php echo esc_attr( $tk['priority'] ); ?>"><?php echo esc_html( $pr_label ); ?></span>
									<?php if ( $tk['area_slug'] ) : ?>
										<span class="ig-enna-badge ig-enna-badge--area-<?php echo esc_attr( $tk['area_slug'] ); ?>"><?php echo esc_html( $tk['area_slug'] ); ?></span>
									<?php endif; ?>
									<time class="ig-enna-ticket__time" datetime="<?php echo esc_attr( $tk['created_at'] ); ?>">
										<?php echo $ts ? esc_html( date_i18n( 'd M Y · H:i', $ts ) ) : ''; ?>
									</time>
								</header>
								<h4 class="ig-enna-ticket__subject"><?php echo esc_html( $tk['subject'] ); ?></h4>
								<div class="ig-enna-ticket__msg"><?php echo wp_kses_post( wpautop( $tk['message'] ) ); ?></div>
							</article>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<div class="ig-enna-empty">
						<h3><?php esc_html_e( 'Nessuna richiesta inviata', 'ig-enna' ); ?></h3>
						<p><?php esc_html_e( 'Usa il form sopra per scrivere allo sportello.', 'ig-enna' ); ?></p>
					</div>
				<?php endif; ?>
			</section>

		<?php elseif ( $tab === 'percorsi' ) :
			$paths = IG_Enna_Percorso_Meta::paths_for_user( $user->ID );
			?>
			<section class="ig-enna-area__panel">
				<?php if ( $paths ) : ?>
					<div class="ig-enna-percorsi">
						<?php foreach ( $paths as $pid ) :
							$post = get_post( $pid );
							if ( ! $post || $post->post_status !== 'publish' ) { continue; }
							$tipo     = get_post_meta( $pid, '_ig_enna_percorso_tipo', true );
							$durata   = get_post_meta( $pid, '_ig_enna_percorso_durata', true );
							$ref      = get_post_meta( $pid, '_ig_enna_percorso_referente', true );
							$fasi     = IG_Enna_Percorso_Meta::fasi_as_array( $pid );
							$labels   = IG_Enna_Percorso_Meta::tipi();
						?>
							<article class="ig-enna-percorso">
								<header class="ig-enna-percorso__head">
									<?php if ( $tipo && isset( $labels[ $tipo ] ) ) : ?>
										<span class="ig-enna-badge ig-enna-badge--ptipo-<?php echo esc_attr( $tipo ); ?>"><?php echo esc_html( $labels[ $tipo ] ); ?></span>
									<?php endif; ?>
									<h3 class="ig-enna-percorso__title"><?php echo esc_html( $post->post_title ); ?></h3>
								</header>

								<dl class="ig-enna-dl">
									<?php if ( $durata ) : ?>
										<dt><?php esc_html_e( 'Durata', 'ig-enna' ); ?></dt>
										<dd><?php echo esc_html( $durata ); ?></dd>
									<?php endif; ?>
									<?php if ( $ref ) : ?>
										<dt><?php esc_html_e( 'Tutor', 'ig-enna' ); ?></dt>
										<dd><?php echo esc_html( $ref ); ?></dd>
									<?php endif; ?>
								</dl>

								<?php if ( $fasi ) : ?>
									<h4 class="ig-enna-section-title" style="margin-top:14px;"><?php esc_html_e( 'Fasi', 'ig-enna' ); ?></h4>
									<ol class="ig-enna-percorso__fasi">
										<?php foreach ( $fasi as $f ) : ?>
											<li><?php echo esc_html( $f ); ?></li>
										<?php endforeach; ?>
									</ol>
								<?php endif; ?>

								<?php $content = apply_filters( 'the_content', $post->post_content ); if ( trim( wp_strip_all_tags( $content ) ) !== '' ) : ?>
									<div class="ig-enna-prose"><?php echo $content; ?></div>
								<?php endif; ?>
							</article>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<div class="ig-enna-empty">
						<h3><?php esc_html_e( 'Nessun percorso attivo', 'ig-enna' ); ?></h3>
						<p><?php esc_html_e( 'I percorsi vengono assegnati dagli operatori dopo un colloquio di orientamento.', 'ig-enna' ); ?></p>
					</div>
				<?php endif; ?>
			</section>

		<?php elseif ( $tab === 'cv' ) :
			$cv      = IG_Enna_CV::get( $user->ID );
			$cv_view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : '';
			if ( $cv_view === 'print' ) {
				include IG_ENNA_DIR . 'public/views/user-cv-print.php';
			} else {
				include IG_ENNA_DIR . 'public/views/user-cv-form.php';
			}
		?>

		<?php elseif ( $tab === 'profilo' ) :
			$avatar_url = IG_Enna_Avatar::get_url( $user->ID, 'medium' );
			$avatar_id  = IG_Enna_Avatar::get_id( $user->ID );
		?>
			<section class="ig-enna-area__panel">

				<h3 class="ig-enna-section-title"><?php esc_html_e( 'Foto profilo', 'ig-enna' ); ?></h3>
				<div class="ig-enna-avatar-panel">
					<div class="ig-enna-avatar-panel__preview">
						<?php if ( $avatar_url ) : ?>
							<img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php esc_attr_e( 'Foto profilo attuale', 'ig-enna' ); ?>" />
						<?php else : ?>
							<div class="ig-enna-avatar-panel__placeholder" aria-hidden="true">
								<svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
									<circle cx="12" cy="8" r="4"/><path d="M4 22c0-4.4 3.6-8 8-8s8 3.6 8 8"/>
								</svg>
							</div>
						<?php endif; ?>
					</div>
					<div class="ig-enna-avatar-panel__body">
						<form method="post" action="" enctype="multipart/form-data" class="ig-enna-form ig-enna-avatar-form">
							<?php wp_nonce_field( IG_Enna_Auth::AVATAR_NONCE, '_ig_nonce' ); ?>
							<input type="hidden" name="ig_enna_action" value="avatar_upload" />
							<input type="hidden" name="redirect_tab" value="profilo" />
							<label class="ig-enna-avatar-panel__file">
								<span class="ig-enna-btn ig-enna-btn--secondary ig-enna-btn--sm">
									<?php echo $avatar_id ? esc_html__( '📷 Cambia foto', 'ig-enna' ) : esc_html__( '📷 Carica foto', 'ig-enna' ); ?>
								</span>
								<input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" onchange="this.form.submit()" />
							</label>
							<p class="ig-enna-avatar-panel__hint">
								<?php esc_html_e( 'JPG, PNG o WEBP, max 5 MB. La foto compare anche nel tuo CV Europass.', 'ig-enna' ); ?>
							</p>
						</form>
						<?php if ( $avatar_id ) : ?>
							<form method="post" action="" onsubmit="return confirm('<?php echo esc_js( __( 'Rimuovere la foto profilo?', 'ig-enna' ) ); ?>')">
								<?php wp_nonce_field( IG_Enna_Auth::AVATAR_NONCE, '_ig_nonce' ); ?>
								<input type="hidden" name="ig_enna_action" value="avatar_delete" />
								<input type="hidden" name="redirect_tab" value="profilo" />
								<button type="submit" class="ig-enna-btn ig-enna-btn--ghost ig-enna-btn--sm ig-enna-avatar-panel__remove">
									<?php esc_html_e( '🗑 Rimuovi foto', 'ig-enna' ); ?>
								</button>
							</form>
						<?php endif; ?>
					</div>
				</div>

				<form method="post" action="" class="ig-enna-form">
					<?php wp_nonce_field( IG_Enna_Auth::PROFILE_NONCE, '_ig_nonce' ); ?>
					<input type="hidden" name="ig_enna_action" value="profile" />

					<h3 class="ig-enna-section-title"><?php esc_html_e( 'Dati anagrafici', 'ig-enna' ); ?></h3>
					<div class="ig-enna-form-row">
						<label><span><?php esc_html_e( 'Nome', 'ig-enna' ); ?></span>
							<input class="ig-enna-input" type="text" name="ig_profile[first_name]" value="<?php echo esc_attr( $user->first_name ); ?>" />
						</label>
						<label><span><?php esc_html_e( 'Cognome', 'ig-enna' ); ?></span>
							<input class="ig-enna-input" type="text" name="ig_profile[last_name]" value="<?php echo esc_attr( $user->last_name ); ?>" />
						</label>
					</div>

					<div class="ig-enna-form-row">
						<label><span><?php esc_html_e( 'Età', 'ig-enna' ); ?></span>
							<input class="ig-enna-input" type="number" min="14" max="120" name="ig_profile[ig_age]" value="<?php echo esc_attr( $profile['ig_age'] ); ?>" />
						</label>
						<label><span><?php esc_html_e( 'Città', 'ig-enna' ); ?></span>
							<input class="ig-enna-input" type="text" name="ig_profile[ig_city]" value="<?php echo esc_attr( $profile['ig_city'] ); ?>" />
						</label>
					</div>

					<div class="ig-enna-form-row">
						<label><span><?php esc_html_e( 'Telefono', 'ig-enna' ); ?></span>
							<input class="ig-enna-input" type="tel" name="ig_profile[ig_phone]" value="<?php echo esc_attr( $profile['ig_phone'] ); ?>" />
						</label>
						<label><span><?php esc_html_e( 'Status', 'ig-enna' ); ?></span>
							<select class="ig-enna-select" name="ig_profile[ig_status]">
								<option value=""><?php esc_html_e( '— Seleziona —', 'ig-enna' ); ?></option>
								<?php foreach ( IG_Enna_User_Profile::status_options() as $opt ) : ?>
									<option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $profile['ig_status'], $opt ); ?>>
										<?php echo esc_html( $opt ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</label>
					</div>

					<h3 class="ig-enna-section-title"><?php esc_html_e( 'Interessi e competenze', 'ig-enna' ); ?></h3>
					<label><span><?php esc_html_e( 'Aree di interesse (separate da virgola)', 'ig-enna' ); ?></span>
						<input class="ig-enna-input" type="text" name="ig_profile[ig_interests]" value="<?php echo esc_attr( implode( ', ', $profile['ig_interests'] ) ); ?>" placeholder="Lavoro, Impresa, Estero" />
					</label>
					<label><span><?php esc_html_e( 'Competenze (separate da virgola)', 'ig-enna' ); ?></span>
						<input class="ig-enna-input" type="text" name="ig_profile[ig_competenze]" value="<?php echo esc_attr( implode( ', ', $profile['ig_competenze'] ) ); ?>" placeholder="Excel, Inglese B2, HACCP" />
					</label>
					<label><span><?php esc_html_e( 'Lingue conosciute', 'ig-enna' ); ?></span>
						<input class="ig-enna-input" type="text" name="ig_profile[ig_lingue]" value="<?php echo esc_attr( $profile['ig_lingue'] ); ?>" placeholder="Italiano: madrelingua · Inglese: B1" />
					</label>

					<h3 class="ig-enna-section-title"><?php esc_html_e( 'Studi', 'ig-enna' ); ?></h3>
					<div class="ig-enna-form-row">
						<label><span><?php esc_html_e( 'Titolo di studio', 'ig-enna' ); ?></span>
							<input class="ig-enna-input" type="text" name="ig_profile[ig_studio_titolo]" value="<?php echo esc_attr( $profile['ig_studio_titolo'] ); ?>" />
						</label>
						<label><span><?php esc_html_e( 'Anno', 'ig-enna' ); ?></span>
							<input class="ig-enna-input" type="number" min="1900" max="2100" name="ig_profile[ig_studio_anno]" value="<?php echo esc_attr( $profile['ig_studio_anno'] ); ?>" />
						</label>
						<label><span><?php esc_html_e( 'Voto', 'ig-enna' ); ?></span>
							<input class="ig-enna-input" type="text" name="ig_profile[ig_studio_voto]" value="<?php echo esc_attr( $profile['ig_studio_voto'] ); ?>" />
						</label>
					</div>

					<label class="ig-enna-checkbox">
						<input type="checkbox" name="ig_profile[ig_consent_priv]" value="1" <?php checked( ! empty( $profile['ig_consent_priv'] ) ); ?> />
						<span><?php esc_html_e( 'Confermo di aver letto l\'informativa sulla privacy', 'ig-enna' ); ?></span>
					</label>

					<button class="ig-enna-btn ig-enna-btn--primary" type="submit"><?php esc_html_e( 'Salva profilo', 'ig-enna' ); ?></button>
				</form>
			</section>
		<?php endif; ?>

	<?php endif; ?>
</div>
