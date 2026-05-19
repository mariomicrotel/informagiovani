<?php
/**
 * Pagina admin "Home page" — gestione testi e blocchi della homepage.
 * Sezioni hero, percorsi rapidi, come funziona, CTA orientamento.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Admin_Home {

	const OPTION = 'ig_enna_home';

	public static function init() {
		add_action( 'admin_init',          [ __CLASS__, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
	}

	/** Carica wp.media solo sulla nostra pagina admin. */
	public static function enqueue_admin_assets( $hook ) {
		if ( strpos( (string) $hook, 'ig-enna-home' ) === false ) { return; }
		wp_enqueue_media();
	}

	public static function register_settings() {
		register_setting( 'ig_enna_home_group', self::OPTION, [
			'type'              => 'array',
			'sanitize_callback' => 'ig_enna_sanitize_home',
			'default'           => ig_enna_default_home(),
		] );
	}

	public static function render_page() {
		if ( ! current_user_can( 'ig_enna_manage' ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'ig-enna' ), 403 );
		}

		$home  = ig_enna_get_home();
		$areas = ig_enna_default_areas();
		?>
		<div class="wrap ig-enna-admin">
			<h1><?php esc_html_e( 'Home page', 'ig-enna' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Modifica i testi e i blocchi visibili sulla home page. Le sezioni dinamiche (Opportunità in evidenza, Scadenze, Eventi) sono popolate automaticamente dai contenuti pubblicati.', 'ig-enna' ); ?>
			</p>

			<form method="post" action="options.php" class="ig-enna-home-form">
				<?php settings_fields( 'ig_enna_home_group' ); ?>

				<!-- ========== HERO ========== -->
				<h2><?php esc_html_e( 'Hero', 'ig-enna' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="ig_enna_home_chip"><?php esc_html_e( 'Chip "Servizio del Comune"', 'ig-enna' ); ?></label></th>
						<td><input type="text" id="ig_enna_home_chip" class="regular-text" name="<?php echo esc_attr( self::OPTION ); ?>[hero][chip]" value="<?php echo esc_attr( $home['hero']['chip'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="ig_enna_home_title"><?php esc_html_e( 'Titolo (HTML)', 'ig-enna' ); ?></label></th>
						<td>
							<textarea id="ig_enna_home_title" class="large-text" rows="2" name="<?php echo esc_attr( self::OPTION ); ?>[hero][title_html]"><?php echo esc_textarea( $home['hero']['title_html'] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Sono ammessi tag inline (es. <em>) per evidenziare una parola.', 'ig-enna' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ig_enna_home_lead"><?php esc_html_e( 'Testo introduttivo', 'ig-enna' ); ?></label></th>
						<td><textarea id="ig_enna_home_lead" class="large-text" rows="3" name="<?php echo esc_attr( self::OPTION ); ?>[hero][lead]"><?php echo esc_textarea( $home['hero']['lead'] ); ?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><label for="ig_enna_home_search_ph"><?php esc_html_e( 'Placeholder ricerca', 'ig-enna' ); ?></label></th>
						<td><input type="text" id="ig_enna_home_search_ph" class="regular-text" name="<?php echo esc_attr( self::OPTION ); ?>[hero][search_placeholder]" value="<?php echo esc_attr( $home['hero']['search_placeholder'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="ig_enna_home_popular"><?php esc_html_e( 'Ricerche popolari', 'ig-enna' ); ?></label></th>
						<td>
							<textarea id="ig_enna_home_popular" class="large-text" rows="5" name="<?php echo esc_attr( self::OPTION ); ?>[hero][popular]"><?php echo esc_textarea( implode( "\n", (array) $home['hero']['popular'] ) ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Uno per riga. Diventano link che pre-compilano la ricerca.', 'ig-enna' ); ?></p>
						</td>
					</tr>
				</table>

				<!-- ========== PERCORSI RAPIDI ========== -->
				<h2><?php esc_html_e( 'Percorsi rapidi (6 box)', 'ig-enna' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Ogni box ha la sua immagine di sfondo. Il testo (titolo, descrizione, CTA) è in overlay su un velo scuro per garantirne la leggibilità.', 'ig-enna' ); ?>
					<br>
					<?php esc_html_e( 'Dimensioni consigliate per ogni immagine: 1200×800 px (rapporto 3:2). Peso JPEG ottimizzato: 80-150 KB.', 'ig-enna' ); ?>
					<br>
					<?php esc_html_e( 'Se non carichi un\'immagine, viene usato un placeholder colorato dedicato all\'area.', 'ig-enna' ); ?>
				</p>
				<input type="hidden" name="<?php echo esc_attr( self::OPTION ); ?>[quickpaths_bg_image_id]" value="<?php echo esc_attr( isset( $home['quickpaths_bg_image_id'] ) ? (int) $home['quickpaths_bg_image_id'] : 0 ); ?>" />
				<table class="widefat ig-enna-table-rep ig-enna-table-rep--quickpaths">
					<thead>
						<tr>
							<th style="width:220px;"><?php esc_html_e( 'Immagine', 'ig-enna' ); ?></th>
							<th style="width:160px;"><?php esc_html_e( 'Area', 'ig-enna' ); ?></th>
							<th><?php esc_html_e( 'Titolo', 'ig-enna' ); ?></th>
							<th><?php esc_html_e( 'Descrizione', 'ig-enna' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php for ( $i = 0; $i < 6; $i++ ) :
							$row     = $home['quickpaths'][ $i ] ?? [ 'image_id' => 0, 'area' => '', 'title' => '', 'desc' => '' ];
							$base    = self::OPTION . '[quickpaths][' . $i . ']';
							$img_id  = isset( $row['image_id'] ) ? (int) $row['image_id'] : 0;
							$img_url = ig_enna_quickpath_image( $row );
						?>
							<tr>
								<td>
									<div class="ig-enna-media-picker" data-target="<?php echo esc_attr( 'ig-qp-img-' . $i ); ?>">
										<div class="ig-enna-media-picker__preview"
											style="background-image: url('<?php echo esc_url( $img_url ); ?>');"
											role="img"
											aria-label="<?php echo esc_attr( $img_id ? __( 'Immagine caricata', 'ig-enna' ) : __( 'Placeholder', 'ig-enna' ) ); ?>"></div>
										<input type="hidden"
											id="<?php echo esc_attr( 'ig-qp-img-' . $i ); ?>"
											name="<?php echo esc_attr( $base . '[image_id]' ); ?>"
											value="<?php echo esc_attr( $img_id ); ?>" />
										<div class="ig-enna-media-picker__actions">
											<button type="button" class="button ig-enna-media-picker__choose">
												<?php echo $img_id ? esc_html__( 'Cambia immagine', 'ig-enna' ) : esc_html__( 'Carica immagine', 'ig-enna' ); ?>
											</button>
											<button type="button" class="button-link delete ig-enna-media-picker__remove" <?php echo $img_id ? '' : 'hidden'; ?>>
												<?php esc_html_e( 'Rimuovi', 'ig-enna' ); ?>
											</button>
										</div>
									</div>
								</td>
								<td>
									<select name="<?php echo esc_attr( $base . '[area]' ); ?>">
										<option value=""><?php esc_html_e( '— Nessuna —', 'ig-enna' ); ?></option>
										<?php foreach ( $areas as $slug => $label ) : ?>
											<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $row['area'], $slug ); ?>><?php echo esc_html( $label ); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
								<td><input type="text" class="regular-text" name="<?php echo esc_attr( $base . '[title]' ); ?>" value="<?php echo esc_attr( $row['title'] ); ?>" /></td>
								<td><textarea class="large-text" rows="3" name="<?php echo esc_attr( $base . '[desc]' ); ?>"><?php echo esc_textarea( $row['desc'] ); ?></textarea></td>
							</tr>
						<?php endfor; ?>
					</tbody>
				</table>

				<!-- ========== COME FUNZIONA ========== -->
				<h2><?php esc_html_e( 'Come funziona', 'ig-enna' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Eyebrow', 'ig-enna' ); ?></label></th>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION ); ?>[howit_intro][eyebrow]" value="<?php echo esc_attr( $home['howit_intro']['eyebrow'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Titolo (HTML)', 'ig-enna' ); ?></label></th>
						<td><input type="text" class="large-text" name="<?php echo esc_attr( self::OPTION ); ?>[howit_intro][title_html]" value="<?php echo esc_attr( $home['howit_intro']['title_html'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Testo', 'ig-enna' ); ?></label></th>
						<td><textarea class="large-text" rows="3" name="<?php echo esc_attr( self::OPTION ); ?>[howit_intro][lead]"><?php echo esc_textarea( $home['howit_intro']['lead'] ); ?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'CTA "Crea profilo"', 'ig-enna' ); ?></label></th>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION ); ?>[howit_intro][cta_label]" value="<?php echo esc_attr( $home['howit_intro']['cta_label'] ); ?>" /></td>
					</tr>
				</table>

				<table class="widefat ig-enna-table-rep">
					<thead>
						<tr>
							<th style="width:60px;">#</th>
							<th><?php esc_html_e( 'Titolo step', 'ig-enna' ); ?></th>
							<th><?php esc_html_e( 'Descrizione step', 'ig-enna' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php for ( $i = 0; $i < 4; $i++ ) :
							$row = $home['howit'][ $i ] ?? [ 'title' => '', 'desc' => '' ];
							$base = self::OPTION . '[howit][' . $i . ']';
						?>
							<tr>
								<td style="text-align:center;font-weight:700;"><?php printf( '%02d', $i + 1 ); ?></td>
								<td><input type="text" class="regular-text" name="<?php echo esc_attr( $base . '[title]' ); ?>" value="<?php echo esc_attr( $row['title'] ); ?>" /></td>
								<td><textarea class="large-text" rows="2" name="<?php echo esc_attr( $base . '[desc]' ); ?>"><?php echo esc_textarea( $row['desc'] ); ?></textarea></td>
							</tr>
						<?php endfor; ?>
					</tbody>
				</table>

				<!-- ========== CTA ORIENTAMENTO ========== -->
				<h2><?php esc_html_e( 'CTA "Hai bisogno di un confronto?"', 'ig-enna' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Eyebrow', 'ig-enna' ); ?></label></th>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION ); ?>[cta][eyebrow]" value="<?php echo esc_attr( $home['cta']['eyebrow'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Titolo', 'ig-enna' ); ?></label></th>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION ); ?>[cta][title]" value="<?php echo esc_attr( $home['cta']['title'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Testo', 'ig-enna' ); ?></label></th>
						<td><textarea class="large-text" rows="3" name="<?php echo esc_attr( self::OPTION ); ?>[cta][lead]"><?php echo esc_textarea( $home['cta']['lead'] ); ?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Etichetta modalità', 'ig-enna' ); ?></label></th>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION ); ?>[cta][modes_label]" value="<?php echo esc_attr( $home['cta']['modes_label'] ); ?>" /></td>
					</tr>
				</table>

				<table class="widefat ig-enna-table-rep">
					<thead>
						<tr>
							<th style="width:60px;"><?php esc_html_e( 'Icona', 'ig-enna' ); ?></th>
							<th style="width:200px;"><?php esc_html_e( 'Modalità', 'ig-enna' ); ?></th>
							<th><?php esc_html_e( 'Dettaglio', 'ig-enna' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php for ( $i = 0; $i < 4; $i++ ) :
							$row = $home['cta']['modes'][ $i ] ?? [ 'icon' => '', 'label' => '', 'detail' => '' ];
							$base = self::OPTION . '[cta][modes][' . $i . ']';
						?>
							<tr>
								<td><input type="text" name="<?php echo esc_attr( $base . '[icon]' ); ?>" value="<?php echo esc_attr( $row['icon'] ); ?>" maxlength="4" style="width:50px;text-align:center;" /></td>
								<td><input type="text" class="regular-text" name="<?php echo esc_attr( $base . '[label]' ); ?>" value="<?php echo esc_attr( $row['label'] ); ?>" /></td>
								<td><input type="text" class="large-text" name="<?php echo esc_attr( $base . '[detail]' ); ?>" value="<?php echo esc_attr( $row['detail'] ); ?>" /></td>
							</tr>
						<?php endfor; ?>
					</tbody>
				</table>

				<p class="submit">
					<?php submit_button( __( 'Salva modifiche', 'ig-enna' ), 'primary', 'submit', false ); ?>
					<?php
					$home_url = home_url( '/' );
					?>
					<a class="button button-secondary" href="<?php echo esc_url( $home_url ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'Apri home page', 'ig-enna' ); ?> ↗
					</a>
				</p>
			</form>
		</div>
		<style>
			.ig-enna-home-form h2 { margin-top: 32px; padding-bottom: 8px; border-bottom: 1px solid #dcdcde; }
			.ig-enna-table-rep { margin-top: 8px; }
			.ig-enna-table-rep td, .ig-enna-table-rep th { vertical-align: middle; padding: 8px; }
			.ig-enna-table-rep textarea { min-height: 44px; }
			.ig-enna-media-picker { display: flex; flex-direction: column; gap: 8px; }
			.ig-enna-media-picker__preview {
				width: 180px; height: 90px;
				background-size: cover; background-position: center;
				background-color: #f0f0f1;
				border-radius: 6px; border: 1px solid #c3c4c7;
			}
			.ig-enna-media-picker__preview--wide {
				width: 100%; max-width: 720px; height: 360px;
			}
			.ig-enna-media-picker__actions { display: flex; gap: 8px; align-items: center; }
			.ig-enna-media-picker__remove { color: #b32d2e; cursor: pointer; }
		</style>
		<script>
		jQuery(function ($) {
			// Eseguo a DOM ready: wp.media è caricato nel footer (wp_enqueue_media)
			// e a questo punto è disponibile.
			if (typeof wp === 'undefined' || ! wp.media) {
				if (window.console) console.warn('IG Enna: wp.media non caricato — il picker non funzionerà.');
				return;
			}

			$(document).on('click', '.ig-enna-media-picker__choose', function (e) {
				e.preventDefault();
				var $picker  = $(this).closest('.ig-enna-media-picker');
				var $input   = $('#' + $picker.data('target'));
				var $preview = $picker.find('.ig-enna-media-picker__preview');
				var $remove  = $picker.find('.ig-enna-media-picker__remove');

				var frame = wp.media({
					title:   <?php echo wp_json_encode( __( 'Scegli un\'immagine per il box', 'ig-enna' ) ); ?>,
					button:  { text: <?php echo wp_json_encode( __( 'Usa questa immagine', 'ig-enna' ) ); ?> },
					library: { type: 'image' },
					multiple: false
				});
				frame.on('select', function () {
					var att = frame.state().get('selection').first().toJSON();
					var url = (att.sizes && att.sizes.large && att.sizes.large.url) ? att.sizes.large.url : att.url;
					$input.val(att.id);
					$preview.css('background-image', "url('" + url + "')");
					$remove.prop('hidden', false);
					$picker.find('.ig-enna-media-picker__choose').text(<?php echo wp_json_encode( __( 'Cambia immagine', 'ig-enna' ) ); ?>);
				});
				frame.open();
			});
			$(document).on('click', '.ig-enna-media-picker__remove', function (e) {
				e.preventDefault();
				var $picker = $(this).closest('.ig-enna-media-picker');
				var $input  = $('#' + $picker.data('target'));
				$input.val('0');
				$picker.find('.ig-enna-media-picker__preview').css('background-image', '');
				$(this).prop('hidden', true);
				$picker.find('.ig-enna-media-picker__choose').text(<?php echo wp_json_encode( __( 'Carica immagine', 'ig-enna' ) ); ?>);
			});
		});
		</script>
		<?php
	}
}
