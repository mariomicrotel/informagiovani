<?php
/**
 * Pagina admin: gestione tipologie schede.
 * Le 7 built-in sono in sola lettura; le custom sono editabili/rimovibili.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Admin_Types {

	const PAGE_SLUG = 'ig-enna-types';
	const NONCE     = 'ig_enna_types_save';
	const NONCE_DEL = 'ig_enna_types_del';

	public static function init() {
		add_action( 'admin_post_ig_enna_types_save',   [ __CLASS__, 'handle_save' ] );
		add_action( 'admin_post_ig_enna_types_delete', [ __CLASS__, 'handle_delete' ] );
	}

	public static function handle_save() {
		if ( ! current_user_can( 'ig_enna_manage' ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'ig-enna' ), 403 );
		}
		if ( ! isset( $_POST['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ig_nonce'] ) ), self::NONCE ) ) {
			wp_die( esc_html__( 'Nonce non valido.', 'ig-enna' ), 403 );
		}

		$key = isset( $_POST['type_key'] ) ? sanitize_text_field( wp_unslash( $_POST['type_key'] ) ) : '';
		$data = [
			'prefix'        => isset( $_POST['prefix'] )        ? wp_unslash( $_POST['prefix'] )        : '',
			'workflow'      => isset( $_POST['workflow'] )      ? sanitize_key( wp_unslash( $_POST['workflow'] ) ) : 'draft',
			'reminder_days' => isset( $_POST['reminder_days'] ) ? (int) $_POST['reminder_days']         : 7,
			'notify'        => isset( $_POST['notify'] )        ? sanitize_key( wp_unslash( $_POST['notify'] ) ) : 'ig_enna_editor_schede',
			'checklist'     => isset( $_POST['checklist'] )     ? wp_unslash( $_POST['checklist'] )     : '',
			'field_config'  => [
				'contributo' => [
					'label'       => isset( $_POST['fc_contributo_label'] )       ? wp_unslash( $_POST['fc_contributo_label'] )       : '',
					'placeholder' => isset( $_POST['fc_contributo_placeholder'] ) ? wp_unslash( $_POST['fc_contributo_placeholder'] ) : '',
					'hint'        => isset( $_POST['fc_contributo_hint'] )        ? wp_unslash( $_POST['fc_contributo_hint'] )        : '',
				],
				'durata' => [
					'label'       => isset( $_POST['fc_durata_label'] )       ? wp_unslash( $_POST['fc_durata_label'] )       : '',
					'placeholder' => isset( $_POST['fc_durata_placeholder'] ) ? wp_unslash( $_POST['fc_durata_placeholder'] ) : '',
					'hint'        => isset( $_POST['fc_durata_hint'] )        ? wp_unslash( $_POST['fc_durata_hint'] )        : '',
				],
				'short' => [
					'placeholder' => isset( $_POST['fc_short_placeholder'] ) ? wp_unslash( $_POST['fc_short_placeholder'] ) : '',
				],
				'deadline_label' => [
					'placeholder' => isset( $_POST['fc_deadline_placeholder'] ) ? wp_unslash( $_POST['fc_deadline_placeholder'] ) : '',
				],
			],
		];

		$res = IG_Enna_Scheda_Types::save_custom( $key, $data );
		if ( is_wp_error( $res ) ) {
			$redir = add_query_arg( [ 'page' => self::PAGE_SLUG, 'ig_error' => rawurlencode( $res->get_error_message() ) ], admin_url( 'admin.php' ) );
		} else {
			$redir = add_query_arg( [ 'page' => self::PAGE_SLUG, 'ig_saved' => rawurlencode( $key ) ], admin_url( 'admin.php' ) );
		}
		wp_safe_redirect( $redir );
		exit;
	}

	public static function handle_delete() {
		if ( ! current_user_can( 'ig_enna_manage' ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'ig-enna' ), 403 );
		}
		$key = isset( $_GET['type_key'] ) ? sanitize_text_field( wp_unslash( $_GET['type_key'] ) ) : '';
		if ( ! isset( $_GET['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_ig_nonce'] ) ), self::NONCE_DEL . ':' . $key ) ) {
			wp_die( esc_html__( 'Nonce non valido.', 'ig-enna' ), 403 );
		}
		IG_Enna_Scheda_Types::delete_custom( $key );
		wp_safe_redirect( add_query_arg( [ 'page' => self::PAGE_SLUG, 'ig_deleted' => rawurlencode( $key ) ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function render() {
		if ( ! current_user_can( 'ig_enna_manage' ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'ig-enna' ), 403 );
		}
		$all         = IG_Enna_Scheda_Types::all();
		$workflows   = IG_Enna_Scheda_Meta::workflow_states();
		$notify_opts = IG_Enna_Scheda_Types::notify_roles();
		$edit_key    = isset( $_GET['edit'] ) ? sanitize_text_field( wp_unslash( $_GET['edit'] ) ) : '';
		$edit_data   = ( $edit_key && isset( $all[ $edit_key ] ) && ! IG_Enna_Scheda_Types::is_builtin( $edit_key ) )
			? $all[ $edit_key ]
			: null;
		?>
		<div class="wrap ig-enna-admin">
			<h1><?php esc_html_e( 'Tipologie schede', 'ig-enna' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Le 7 tipologie di sistema (Bando, Concorso, Programma, Master, Mobilità, Contributo, Altro) sono in sola lettura. Puoi aggiungerne di nuove (es. Corso online, Webinar, Tirocinio) con codice protocollo, workflow e checklist personalizzati.', 'ig-enna' ); ?>
			</p>

			<?php if ( isset( $_GET['ig_saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p>
					<?php
					/* translators: %s = nome tipologia */
					printf( esc_html__( 'Tipologia "%s" salvata.', 'ig-enna' ), esc_html( wp_unslash( $_GET['ig_saved'] ) ) );
					?>
				</p></div>
			<?php elseif ( isset( $_GET['ig_error'] ) ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( wp_unslash( $_GET['ig_error'] ) ); ?></p></div>
			<?php elseif ( isset( $_GET['ig_deleted'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p>
					<?php
					/* translators: %s = nome */
					printf( esc_html__( 'Tipologia "%s" eliminata.', 'ig-enna' ), esc_html( wp_unslash( $_GET['ig_deleted'] ) ) );
					?>
				</p></div>
			<?php endif; ?>

			<!-- ===================== LISTA ===================== -->
			<h2><?php esc_html_e( 'Tipologie attive', 'ig-enna' ); ?></h2>
			<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th style="width:22%;"><?php esc_html_e( 'Tipologia', 'ig-enna' ); ?></th>
						<th style="width:12%;"><?php esc_html_e( 'Prefisso', 'ig-enna' ); ?></th>
						<th style="width:14%;"><?php esc_html_e( 'Workflow', 'ig-enna' ); ?></th>
						<th style="width:12%;"><?php esc_html_e( 'Reminder', 'ig-enna' ); ?></th>
						<th style="width:22%;"><?php esc_html_e( 'Notifica a', 'ig-enna' ); ?></th>
						<th style="width:18%;"><?php esc_html_e( 'Azioni', 'ig-enna' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $all as $k => $cfg ) :
						$is_builtin = IG_Enna_Scheda_Types::is_builtin( $k );
						$edit_url = add_query_arg( [ 'page' => self::PAGE_SLUG, 'edit' => rawurlencode( $k ) ], admin_url( 'admin.php' ) );
						$del_url  = wp_nonce_url(
							add_query_arg( [ 'action' => 'ig_enna_types_delete', 'type_key' => rawurlencode( $k ) ], admin_url( 'admin-post.php' ) ),
							self::NONCE_DEL . ':' . $k, '_ig_nonce'
						);
					?>
						<tr>
							<td>
								<strong><?php echo esc_html( $cfg['label'] ?? $k ); ?></strong>
								<?php if ( $is_builtin ) : ?>
									<span class="ig-enna-badge" style="background:#e0e5eb;color:#334;margin-left:6px;font-size:10px;padding:1px 6px;border-radius:10px;"><?php esc_html_e( 'sistema', 'ig-enna' ); ?></span>
								<?php else : ?>
									<span class="ig-enna-badge" style="background:#d4eddd;color:#186e44;margin-left:6px;font-size:10px;padding:1px 6px;border-radius:10px;"><?php esc_html_e( 'custom', 'ig-enna' ); ?></span>
								<?php endif; ?>
							</td>
							<td><code><?php echo esc_html( $cfg['prefix'] ); ?></code></td>
							<td><?php echo esc_html( $workflows[ $cfg['workflow'] ] ?? $cfg['workflow'] ); ?></td>
							<td><?php printf( esc_html( _n( '%d giorno', '%d giorni', (int) $cfg['reminder_days'], 'ig-enna' ) ), (int) $cfg['reminder_days'] ); ?></td>
							<td><code><?php echo esc_html( $cfg['notify'] ); ?></code></td>
							<td>
								<?php if ( ! $is_builtin ) : ?>
									<a href="<?php echo esc_url( $edit_url ); ?>" class="button button-small"><?php esc_html_e( 'Modifica', 'ig-enna' ); ?></a>
									<a href="<?php echo esc_url( $del_url ); ?>" class="button button-small" style="color:#b32d2e;" onclick="return confirm('<?php echo esc_js( __( 'Confermi l\'eliminazione della tipologia?', 'ig-enna' ) ); ?>')"><?php esc_html_e( 'Elimina', 'ig-enna' ); ?></a>
								<?php else : ?>
									<em style="color:#8a94a4;font-size:12px;"><?php esc_html_e( 'sola lettura', 'ig-enna' ); ?></em>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<!-- ===================== FORM AGGIUNGI/MODIFICA ===================== -->
			<h2 style="margin-top:32px;">
				<?php echo $edit_data ? esc_html__( 'Modifica tipologia', 'ig-enna' ) : esc_html__( 'Aggiungi nuova tipologia', 'ig-enna' ); ?>
			</h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ig-enna-types-form">
				<input type="hidden" name="action" value="ig_enna_types_save" />
				<?php wp_nonce_field( self::NONCE, '_ig_nonce' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="type_key"><?php esc_html_e( 'Nome tipologia', 'ig-enna' ); ?> *</label></th>
						<td>
							<input type="text" id="type_key" name="type_key" class="regular-text"
								value="<?php echo esc_attr( $edit_key ); ?>"
								<?php echo $edit_data ? 'readonly' : ''; ?>
								required maxlength="60" placeholder="Es. Corso online, Webinar, Tirocinio" />
							<p class="description">
								<?php esc_html_e( 'Compare nel dropdown "Tipo" dell\'editor scheda. Se modifichi un tipo esistente il nome non può essere cambiato (crea un nuovo tipo).', 'ig-enna' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="prefix"><?php esc_html_e( 'Prefisso protocollo', 'ig-enna' ); ?></label></th>
						<td>
							<input type="text" id="prefix" name="prefix" class="regular-text"
								value="<?php echo esc_attr( $edit_data ? $edit_data['prefix'] : '' ); ?>"
								maxlength="6" placeholder="Es. CORS" />
							<p class="description">
								<?php esc_html_e( '2-6 lettere/numeri maiuscoli. Diventa parte del codice progressivo (es. CORS-2026-001). Deve essere unico. Se lasciato vuoto viene derivato dal nome.', 'ig-enna' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="workflow"><?php esc_html_e( 'Workflow di default', 'ig-enna' ); ?></label></th>
						<td>
							<select id="workflow" name="workflow">
								<?php foreach ( $workflows as $wk => $wl ) : ?>
									<option value="<?php echo esc_attr( $wk ); ?>" <?php selected( $edit_data['workflow'] ?? 'draft', $wk ); ?>><?php echo esc_html( $wl ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Stato applicato automaticamente alle nuove schede di questa tipologia.', 'ig-enna' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="reminder_days"><?php esc_html_e( 'Giorni pre-scadenza reminder', 'ig-enna' ); ?></label></th>
						<td>
							<input type="number" id="reminder_days" name="reminder_days" min="1" max="90"
								value="<?php echo esc_attr( $edit_data['reminder_days'] ?? 7 ); ?>" />
							<p class="description"><?php esc_html_e( 'Quando il cron giornaliero rileva una scheda pubblicata con deadline entro questi giorni, invia email al ruolo notificato.', 'ig-enna' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="notify"><?php esc_html_e( 'Notifica a', 'ig-enna' ); ?></label></th>
						<td>
							<select id="notify" name="notify">
								<?php foreach ( $notify_opts as $nk => $nl ) : ?>
									<option value="<?php echo esc_attr( $nk ); ?>" <?php selected( $edit_data['notify'] ?? 'ig_enna_editor_schede', $nk ); ?>><?php echo esc_html( $nl ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="checklist"><?php esc_html_e( 'Checklist operativa', 'ig-enna' ); ?></label></th>
						<td>
							<textarea id="checklist" name="checklist" class="large-text" rows="6" placeholder="Una voce per riga…"><?php
								echo esc_textarea( $edit_data ? implode( "\n", (array) $edit_data['checklist'] ) : "Titolo chiaro e completo\nDescrizione breve (max 200 caratteri)\nArea tematica assegnata\nFonte verificata + link\nDeadline (o 'sempre aperta')" );
							?></textarea>
							<p class="description"><?php esc_html_e( 'Punti mostrati nel metabox "Protocollo automatico" dell\'editor scheda per guidare la compilazione.', 'ig-enna' ); ?></p>
						</td>
					</tr>
				</table>

				<h3><?php esc_html_e( 'Campi contestuali', 'ig-enna' ); ?></h3>
				<p class="description" style="margin-bottom:8px;">
					<?php esc_html_e( 'Etichetta, placeholder e hint dei campi dell\'editor scheda quando questa tipologia è selezionata.', 'ig-enna' ); ?>
				</p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Campo "Contributo"', 'ig-enna' ); ?></th>
						<td>
							<input type="text" name="fc_contributo_label" class="regular-text" placeholder="Label (es. Contributo economico)" value="<?php echo esc_attr( $edit_data['field_config']['contributo']['label'] ?? '' ); ?>" /><br>
							<input type="text" name="fc_contributo_placeholder" class="regular-text" placeholder="Placeholder (es. fino a 30.000 €)" value="<?php echo esc_attr( $edit_data['field_config']['contributo']['placeholder'] ?? '' ); ?>" style="margin-top:4px;" /><br>
							<input type="text" name="fc_contributo_hint" class="regular-text" placeholder="Hint (frase sotto il campo)" value="<?php echo esc_attr( $edit_data['field_config']['contributo']['hint'] ?? '' ); ?>" style="margin-top:4px;" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Campo "Durata"', 'ig-enna' ); ?></th>
						<td>
							<input type="text" name="fc_durata_label" class="regular-text" placeholder="Label (es. Durata progetto)" value="<?php echo esc_attr( $edit_data['field_config']['durata']['label'] ?? '' ); ?>" /><br>
							<input type="text" name="fc_durata_placeholder" class="regular-text" placeholder="Placeholder (es. 12 mesi)" value="<?php echo esc_attr( $edit_data['field_config']['durata']['placeholder'] ?? '' ); ?>" style="margin-top:4px;" /><br>
							<input type="text" name="fc_durata_hint" class="regular-text" placeholder="Hint (opzionale)" value="<?php echo esc_attr( $edit_data['field_config']['durata']['hint'] ?? '' ); ?>" style="margin-top:4px;" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Campo "Sintesi breve"', 'ig-enna' ); ?></th>
						<td>
							<input type="text" name="fc_short_placeholder" class="large-text" placeholder="Placeholder (es. Es. Corso online 40 ore su Python…)" value="<?php echo esc_attr( $edit_data['field_config']['short']['placeholder'] ?? '' ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Campo "Etichetta scadenza"', 'ig-enna' ); ?></th>
						<td>
							<input type="text" name="fc_deadline_placeholder" class="large-text" placeholder="Placeholder (es. iscrizioni fino al 31 marzo)" value="<?php echo esc_attr( $edit_data['field_config']['deadline_label']['placeholder'] ?? '' ); ?>" />
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" class="button button-primary">
						<?php echo $edit_data ? esc_html__( 'Aggiorna tipologia', 'ig-enna' ) : esc_html__( 'Aggiungi tipologia', 'ig-enna' ); ?>
					</button>
					<?php if ( $edit_data ) : ?>
						<a href="<?php echo esc_url( add_query_arg( 'page', self::PAGE_SLUG, admin_url( 'admin.php' ) ) ); ?>" class="button"><?php esc_html_e( 'Annulla', 'ig-enna' ); ?></a>
					<?php endif; ?>
				</p>
			</form>
		</div>
		<?php
	}
}
