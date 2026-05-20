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
		$tab     = isset( $_GET['ig_tab'] ) ? sanitize_key( $_GET['ig_tab'] ) : 'panoramica';
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

		<?php if ( $tab === 'panoramica' ) : ?>
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
			$my_tickets = IG_Enna_Tickets::query( [ 'user_id' => $user->ID, 'limit' => 50 ] );
			$areas      = get_terms( [ 'taxonomy' => 'ig_area', 'hide_empty' => false ] );
			?>
			<section class="ig-enna-area__panel">
				<h3 class="ig-enna-section-title"><?php esc_html_e( 'Nuova richiesta', 'ig-enna' ); ?></h3>
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
			$cv = IG_Enna_CV::get( $user->ID );
			include IG_ENNA_DIR . 'public/views/user-cv-form.php';
		?>

		<?php elseif ( $tab === 'profilo' ) : ?>
			<section class="ig-enna-area__panel">
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
