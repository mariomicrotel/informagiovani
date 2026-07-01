<?php
/**
 * Form CV (formato Europass) per l'area personale.
 * Variabile: $cv (array) — vedi IG_Enna_CV::default_structure()
 */
defined( 'ABSPATH' ) || exit;

$pct    = IG_Enna_CV::completion( get_current_user_id() );
$levels = IG_Enna_CV::cefr_levels();
$gens   = IG_Enna_CV::gender_options();

// Pre-popola almeno una riga vuota se le sezioni ripetibili sono vuote,
// per dare all'utente un punto di partenza visibile.
$experience = $cv['experience'] ?: [ [
	'from' => '', 'to' => '', 'current' => 0, 'role' => '', 'employer' => '', 'city' => '', 'sector' => '', 'description' => '',
] ];
$education = $cv['education'] ?: [ [
	'from' => '', 'to' => '', 'current' => 0, 'qualification' => '', 'school' => '', 'city' => '', 'subjects' => '', 'grade' => '',
] ];
$languages = $cv['languages'] ?: [ [
	'language' => '', 'listening' => '', 'reading' => '', 'spoken_interaction' => '', 'spoken_production' => '', 'writing' => '',
] ];
?>
<section class="ig-enna-area__panel ig-enna-cv-form">

	<header class="ig-enna-cv-form__head">
		<h2><?php esc_html_e( 'Il tuo CV — formato Europass', 'ig-enna' ); ?></h2>
		<p class="ig-enna-cv-form__hint">
			<?php
			printf(
				/* translators: %d = percentuale */
				esc_html__( 'Compila le sezioni rilevanti. Tutto è privato e visibile solo a te e al tuo operatore. Completamento: %d%%', 'ig-enna' ),
				(int) $pct
			);
			?>
		</p>
		<?php if ( ! empty( $cv['updated_at'] ) ) : ?>
			<p class="ig-enna-cv-form__updated">
				<?php
				/* translators: %s = data */
				printf( esc_html__( 'Ultimo aggiornamento: %s', 'ig-enna' ), esc_html( $cv['updated_at'] ) );
				?>
			</p>
		<?php endif; ?>
	</header>

	<?php
	$avatar_url_cv = IG_Enna_Avatar::get_url( get_current_user_id(), 'medium' );
	$avatar_id_cv  = IG_Enna_Avatar::get_id( get_current_user_id() );
	?>
	<div class="ig-enna-cv__avatar-block">
		<div class="ig-enna-cv__avatar-preview">
			<?php if ( $avatar_url_cv ) : ?>
				<img src="<?php echo esc_url( $avatar_url_cv ); ?>" alt="<?php esc_attr_e( 'Foto CV', 'ig-enna' ); ?>" />
			<?php else : ?>
				<div class="ig-enna-cv__avatar-placeholder" aria-hidden="true">
					<svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="8" r="4"/><path d="M4 22c0-4.4 3.6-8 8-8s8 3.6 8 8"/>
					</svg>
				</div>
			<?php endif; ?>
		</div>
		<div class="ig-enna-cv__avatar-side">
			<strong><?php esc_html_e( 'Foto sul CV', 'ig-enna' ); ?></strong>
			<p><?php esc_html_e( 'La foto viene mostrata anche nell\'anteprima Europass. È la stessa del tuo profilo.', 'ig-enna' ); ?></p>
			<form method="post" action="" enctype="multipart/form-data" class="ig-enna-avatar-form">
				<?php wp_nonce_field( IG_Enna_Auth::AVATAR_NONCE, '_ig_nonce' ); ?>
				<input type="hidden" name="ig_enna_action" value="avatar_upload" />
				<input type="hidden" name="redirect_tab" value="cv" />
				<label class="ig-enna-avatar-panel__file">
					<span class="ig-enna-btn ig-enna-btn--secondary ig-enna-btn--sm">
						<?php echo $avatar_id_cv ? esc_html__( '📷 Cambia foto', 'ig-enna' ) : esc_html__( '📷 Carica foto', 'ig-enna' ); ?>
					</span>
					<input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" onchange="this.form.submit()" />
				</label>
			</form>
			<?php if ( $avatar_id_cv ) : ?>
				<form method="post" action="" style="display:inline-block;margin-left:8px;">
					<?php wp_nonce_field( IG_Enna_Auth::AVATAR_NONCE, '_ig_nonce' ); ?>
					<input type="hidden" name="ig_enna_action" value="avatar_delete" />
					<input type="hidden" name="redirect_tab" value="cv" />
					<button type="submit" class="ig-enna-btn ig-enna-btn--ghost ig-enna-btn--sm"><?php esc_html_e( '🗑 Rimuovi', 'ig-enna' ); ?></button>
				</form>
			<?php endif; ?>
		</div>
	</div>

	<form method="post" action="" class="ig-enna-form ig-enna-cv">
		<?php wp_nonce_field( IG_Enna_Auth::CV_NONCE, '_ig_nonce' ); ?>
		<input type="hidden" name="ig_enna_action" value="cv_save" />

		<!-- ============ PERSONALI ============ -->
		<fieldset class="ig-enna-cv__fs">
			<legend><?php esc_html_e( 'Dati anagrafici', 'ig-enna' ); ?></legend>

			<div class="ig-enna-form-row">
				<label><span><?php esc_html_e( 'Nome', 'ig-enna' ); ?> *</span>
					<input class="ig-enna-input" type="text" name="ig_cv[personal][first_name]" value="<?php echo esc_attr( $cv['personal']['first_name'] ); ?>" required />
				</label>
				<label><span><?php esc_html_e( 'Cognome', 'ig-enna' ); ?> *</span>
					<input class="ig-enna-input" type="text" name="ig_cv[personal][last_name]" value="<?php echo esc_attr( $cv['personal']['last_name'] ); ?>" required />
				</label>
			</div>

			<div class="ig-enna-form-row">
				<label><span><?php esc_html_e( 'Data di nascita', 'ig-enna' ); ?></span>
					<input class="ig-enna-input" type="date" name="ig_cv[personal][birth_date]" value="<?php echo esc_attr( $cv['personal']['birth_date'] ); ?>" />
				</label>
				<label><span><?php esc_html_e( 'Luogo di nascita', 'ig-enna' ); ?></span>
					<input class="ig-enna-input" type="text" name="ig_cv[personal][birth_place]" value="<?php echo esc_attr( $cv['personal']['birth_place'] ); ?>" />
				</label>
				<label><span><?php esc_html_e( 'Genere', 'ig-enna' ); ?></span>
					<select class="ig-enna-select" name="ig_cv[personal][gender]">
						<?php foreach ( $gens as $k => $lab ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $cv['personal']['gender'], $k ); ?>><?php echo esc_html( $lab ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>

			<div class="ig-enna-form-row">
				<label><span><?php esc_html_e( 'Nazionalità', 'ig-enna' ); ?></span>
					<input class="ig-enna-input" type="text" name="ig_cv[personal][nationality]" value="<?php echo esc_attr( $cv['personal']['nationality'] ); ?>" placeholder="Italiana" />
				</label>
				<label><span><?php esc_html_e( 'Paese', 'ig-enna' ); ?></span>
					<input class="ig-enna-input" type="text" name="ig_cv[personal][country]" value="<?php echo esc_attr( $cv['personal']['country'] ); ?>" />
				</label>
			</div>

			<label><span><?php esc_html_e( 'Indirizzo', 'ig-enna' ); ?></span>
				<input class="ig-enna-input" type="text" name="ig_cv[personal][address]" value="<?php echo esc_attr( $cv['personal']['address'] ); ?>" placeholder="Via, numero civico" />
			</label>

			<div class="ig-enna-form-row">
				<label><span><?php esc_html_e( 'Città', 'ig-enna' ); ?></span>
					<input class="ig-enna-input" type="text" name="ig_cv[personal][city]" value="<?php echo esc_attr( $cv['personal']['city'] ); ?>" />
				</label>
				<label><span><?php esc_html_e( 'CAP', 'ig-enna' ); ?></span>
					<input class="ig-enna-input" type="text" name="ig_cv[personal][cap]" value="<?php echo esc_attr( $cv['personal']['cap'] ); ?>" maxlength="10" />
				</label>
			</div>

			<div class="ig-enna-form-row">
				<label><span><?php esc_html_e( 'Telefono', 'ig-enna' ); ?></span>
					<input class="ig-enna-input" type="tel" name="ig_cv[personal][phone]" value="<?php echo esc_attr( $cv['personal']['phone'] ); ?>" />
				</label>
				<label><span><?php esc_html_e( 'Email', 'ig-enna' ); ?></span>
					<input class="ig-enna-input" type="email" name="ig_cv[personal][email]" value="<?php echo esc_attr( $cv['personal']['email'] ); ?>" />
				</label>
			</div>

			<div class="ig-enna-form-row">
				<label><span><?php esc_html_e( 'Sito web / portfolio', 'ig-enna' ); ?></span>
					<input class="ig-enna-input" type="url" name="ig_cv[personal][website]" value="<?php echo esc_attr( $cv['personal']['website'] ); ?>" placeholder="https://" />
				</label>
				<label><span><?php esc_html_e( 'LinkedIn', 'ig-enna' ); ?></span>
					<input class="ig-enna-input" type="url" name="ig_cv[personal][linkedin]" value="<?php echo esc_attr( $cv['personal']['linkedin'] ); ?>" placeholder="https://www.linkedin.com/in/…" />
				</label>
			</div>
		</fieldset>

		<!-- ============ PROFILO ============ -->
		<fieldset class="ig-enna-cv__fs">
			<legend><?php esc_html_e( 'Profilo professionale', 'ig-enna' ); ?></legend>
			<label><span><?php esc_html_e( 'Breve presentazione (3-5 righe)', 'ig-enna' ); ?></span>
				<textarea class="ig-enna-input" name="ig_cv[profile]" rows="4" placeholder="<?php esc_attr_e( 'Es. Neolaureata in Economia, con esperienza in marketing digitale e gestione social media…', 'ig-enna' ); ?>"><?php echo esc_textarea( $cv['profile'] ); ?></textarea>
			</label>
		</fieldset>

		<!-- ============ ESPERIENZA ============ -->
		<fieldset class="ig-enna-cv__fs" data-rep="experience">
			<legend><?php esc_html_e( 'Esperienze lavorative', 'ig-enna' ); ?></legend>

			<div class="ig-enna-cv__rows">
				<?php foreach ( $experience as $i => $r ) : ?>
					<div class="ig-enna-cv__row" data-idx="<?php echo (int) $i; ?>">
						<div class="ig-enna-form-row">
							<label><span><?php esc_html_e( 'Dal', 'ig-enna' ); ?></span>
								<input class="ig-enna-input" type="month" name="ig_cv[experience][<?php echo (int) $i; ?>][from]" value="<?php echo esc_attr( $r['from'] ); ?>" />
							</label>
							<label><span><?php esc_html_e( 'Al', 'ig-enna' ); ?></span>
								<input class="ig-enna-input" type="month" name="ig_cv[experience][<?php echo (int) $i; ?>][to]" value="<?php echo esc_attr( $r['to'] ); ?>" />
							</label>
							<label class="ig-enna-checkbox">
								<input type="checkbox" name="ig_cv[experience][<?php echo (int) $i; ?>][current]" value="1" <?php checked( ! empty( $r['current'] ) ); ?> />
								<span><?php esc_html_e( 'In corso', 'ig-enna' ); ?></span>
							</label>
						</div>
						<div class="ig-enna-form-row">
							<label><span><?php esc_html_e( 'Ruolo / posizione', 'ig-enna' ); ?></span>
								<input class="ig-enna-input" type="text" name="ig_cv[experience][<?php echo (int) $i; ?>][role]" value="<?php echo esc_attr( $r['role'] ); ?>" />
							</label>
							<label><span><?php esc_html_e( 'Datore di lavoro', 'ig-enna' ); ?></span>
								<input class="ig-enna-input" type="text" name="ig_cv[experience][<?php echo (int) $i; ?>][employer]" value="<?php echo esc_attr( $r['employer'] ); ?>" />
							</label>
						</div>
						<div class="ig-enna-form-row">
							<label><span><?php esc_html_e( 'Città', 'ig-enna' ); ?></span>
								<input class="ig-enna-input" type="text" name="ig_cv[experience][<?php echo (int) $i; ?>][city]" value="<?php echo esc_attr( $r['city'] ); ?>" />
							</label>
							<label><span><?php esc_html_e( 'Settore', 'ig-enna' ); ?></span>
								<input class="ig-enna-input" type="text" name="ig_cv[experience][<?php echo (int) $i; ?>][sector]" value="<?php echo esc_attr( $r['sector'] ); ?>" placeholder="Es. Marketing, Sanità, IT…" />
							</label>
						</div>
						<label><span><?php esc_html_e( 'Attività svolte', 'ig-enna' ); ?></span>
							<textarea class="ig-enna-input" rows="3" name="ig_cv[experience][<?php echo (int) $i; ?>][description]"><?php echo esc_textarea( $r['description'] ); ?></textarea>
						</label>
						<div class="ig-enna-cv__row-actions">
							<button type="button" class="ig-enna-btn ig-enna-btn--ghost ig-enna-btn--sm ig-enna-cv__remove"><?php esc_html_e( '— Rimuovi esperienza', 'ig-enna' ); ?></button>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="ig-enna-btn ig-enna-btn--secondary ig-enna-btn--sm ig-enna-cv__add" data-target="experience"><?php esc_html_e( '+ Aggiungi esperienza', 'ig-enna' ); ?></button>
		</fieldset>

		<!-- ============ ISTRUZIONE ============ -->
		<fieldset class="ig-enna-cv__fs" data-rep="education">
			<legend><?php esc_html_e( 'Istruzione e formazione', 'ig-enna' ); ?></legend>

			<div class="ig-enna-cv__rows">
				<?php foreach ( $education as $i => $r ) : ?>
					<div class="ig-enna-cv__row" data-idx="<?php echo (int) $i; ?>">
						<div class="ig-enna-form-row">
							<label><span><?php esc_html_e( 'Dal', 'ig-enna' ); ?></span>
								<input class="ig-enna-input" type="month" name="ig_cv[education][<?php echo (int) $i; ?>][from]" value="<?php echo esc_attr( $r['from'] ); ?>" />
							</label>
							<label><span><?php esc_html_e( 'Al', 'ig-enna' ); ?></span>
								<input class="ig-enna-input" type="month" name="ig_cv[education][<?php echo (int) $i; ?>][to]" value="<?php echo esc_attr( $r['to'] ); ?>" />
							</label>
							<label class="ig-enna-checkbox">
								<input type="checkbox" name="ig_cv[education][<?php echo (int) $i; ?>][current]" value="1" <?php checked( ! empty( $r['current'] ) ); ?> />
								<span><?php esc_html_e( 'In corso', 'ig-enna' ); ?></span>
							</label>
						</div>
						<div class="ig-enna-form-row">
							<label><span><?php esc_html_e( 'Titolo / qualifica', 'ig-enna' ); ?></span>
								<input class="ig-enna-input" type="text" name="ig_cv[education][<?php echo (int) $i; ?>][qualification]" value="<?php echo esc_attr( $r['qualification'] ); ?>" placeholder="Es. Laurea triennale in Scienze della Comunicazione" />
							</label>
							<label><span><?php esc_html_e( 'Istituto / università', 'ig-enna' ); ?></span>
								<input class="ig-enna-input" type="text" name="ig_cv[education][<?php echo (int) $i; ?>][school]" value="<?php echo esc_attr( $r['school'] ); ?>" />
							</label>
						</div>
						<div class="ig-enna-form-row">
							<label><span><?php esc_html_e( 'Città', 'ig-enna' ); ?></span>
								<input class="ig-enna-input" type="text" name="ig_cv[education][<?php echo (int) $i; ?>][city]" value="<?php echo esc_attr( $r['city'] ); ?>" />
							</label>
							<label><span><?php esc_html_e( 'Voto', 'ig-enna' ); ?></span>
								<input class="ig-enna-input" type="text" name="ig_cv[education][<?php echo (int) $i; ?>][grade]" value="<?php echo esc_attr( $r['grade'] ); ?>" placeholder="Es. 110/110" />
							</label>
						</div>
						<label><span><?php esc_html_e( 'Materie principali / competenze acquisite', 'ig-enna' ); ?></span>
							<textarea class="ig-enna-input" rows="2" name="ig_cv[education][<?php echo (int) $i; ?>][subjects]"><?php echo esc_textarea( $r['subjects'] ); ?></textarea>
						</label>
						<div class="ig-enna-cv__row-actions">
							<button type="button" class="ig-enna-btn ig-enna-btn--ghost ig-enna-btn--sm ig-enna-cv__remove"><?php esc_html_e( '— Rimuovi titolo', 'ig-enna' ); ?></button>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="ig-enna-btn ig-enna-btn--secondary ig-enna-btn--sm ig-enna-cv__add" data-target="education"><?php esc_html_e( '+ Aggiungi titolo di studio', 'ig-enna' ); ?></button>
		</fieldset>

		<!-- ============ LINGUE ============ -->
		<fieldset class="ig-enna-cv__fs" data-rep="languages">
			<legend><?php esc_html_e( 'Competenze linguistiche (livelli CEFR)', 'ig-enna' ); ?></legend>
			<p class="ig-enna-cv-form__hint" style="margin-bottom:12px;">
				<?php esc_html_e( 'A1/A2 = base · B1/B2 = autonomo · C1/C2 = padronanza', 'ig-enna' ); ?>
			</p>

			<div class="ig-enna-cv__rows">
				<?php foreach ( $languages as $i => $r ) : ?>
					<div class="ig-enna-cv__row" data-idx="<?php echo (int) $i; ?>">
						<label><span><?php esc_html_e( 'Lingua', 'ig-enna' ); ?></span>
							<input class="ig-enna-input" type="text" name="ig_cv[languages][<?php echo (int) $i; ?>][language]" value="<?php echo esc_attr( $r['language'] ); ?>" placeholder="Italiano, Inglese, Francese…" />
						</label>
						<div class="ig-enna-cv__lang-grid">
							<?php
							$skills = [
								'listening'          => __( 'Ascolto', 'ig-enna' ),
								'reading'            => __( 'Lettura', 'ig-enna' ),
								'spoken_interaction' => __( 'Interazione orale', 'ig-enna' ),
								'spoken_production' => __( 'Produzione orale', 'ig-enna' ),
								'writing'            => __( 'Scrittura', 'ig-enna' ),
							];
							foreach ( $skills as $sk => $sk_lab ) : ?>
								<label>
									<span><?php echo esc_html( $sk_lab ); ?></span>
									<select class="ig-enna-select" name="ig_cv[languages][<?php echo (int) $i; ?>][<?php echo esc_attr( $sk ); ?>]">
										<?php foreach ( $levels as $lv => $lv_lab ) : ?>
											<option value="<?php echo esc_attr( $lv ); ?>" <?php selected( $r[ $sk ] ?? '', $lv ); ?>><?php echo esc_html( $lv_lab ); ?></option>
										<?php endforeach; ?>
									</select>
								</label>
							<?php endforeach; ?>
						</div>
						<div class="ig-enna-cv__row-actions">
							<button type="button" class="ig-enna-btn ig-enna-btn--ghost ig-enna-btn--sm ig-enna-cv__remove"><?php esc_html_e( '— Rimuovi lingua', 'ig-enna' ); ?></button>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="ig-enna-btn ig-enna-btn--secondary ig-enna-btn--sm ig-enna-cv__add" data-target="languages"><?php esc_html_e( '+ Aggiungi lingua', 'ig-enna' ); ?></button>
		</fieldset>

		<!-- ============ ALTRE COMPETENZE ============ -->
		<fieldset class="ig-enna-cv__fs">
			<legend><?php esc_html_e( 'Competenze trasversali e tecniche', 'ig-enna' ); ?></legend>

			<label><span><?php esc_html_e( 'Competenze comunicative', 'ig-enna' ); ?></span>
				<textarea class="ig-enna-input" rows="3" name="ig_cv[communication_skills]" placeholder="<?php esc_attr_e( 'Es. Capacità di parlare in pubblico, lavoro di squadra, scrittura tecnica…', 'ig-enna' ); ?>"><?php echo esc_textarea( $cv['communication_skills'] ); ?></textarea>
			</label>

			<label><span><?php esc_html_e( 'Competenze organizzative e gestionali', 'ig-enna' ); ?></span>
				<textarea class="ig-enna-input" rows="3" name="ig_cv[organisational_skills]" placeholder="<?php esc_attr_e( 'Es. Coordinamento eventi, gestione budget, project management…', 'ig-enna' ); ?>"><?php echo esc_textarea( $cv['organisational_skills'] ); ?></textarea>
			</label>

			<label><span><?php esc_html_e( 'Competenze digitali', 'ig-enna' ); ?></span>
				<textarea class="ig-enna-input" rows="3" name="ig_cv[digital_skills]" placeholder="<?php esc_attr_e( 'Es. Microsoft Office (avanzato), Adobe Photoshop (intermedio), Python, HTML/CSS…', 'ig-enna' ); ?>"><?php echo esc_textarea( $cv['digital_skills'] ); ?></textarea>
			</label>

			<label><span><?php esc_html_e( 'Altre competenze', 'ig-enna' ); ?></span>
				<textarea class="ig-enna-input" rows="2" name="ig_cv[other_skills]" placeholder="<?php esc_attr_e( 'Volontariato, sport, hobby rilevanti, pubblicazioni…', 'ig-enna' ); ?>"><?php echo esc_textarea( $cv['other_skills'] ); ?></textarea>
			</label>

			<label><span><?php esc_html_e( 'Patente di guida', 'ig-enna' ); ?></span>
				<input class="ig-enna-input" type="text" name="ig_cv[driving_licence]" value="<?php echo esc_attr( $cv['driving_licence'] ); ?>" placeholder="Es. B" />
			</label>
		</fieldset>

		<div class="ig-enna-cv-form__actions">
			<button type="submit" class="ig-enna-btn ig-enna-btn--primary"><?php esc_html_e( 'Salva CV', 'ig-enna' ); ?></button>
			<a class="ig-enna-btn ig-enna-btn--secondary" href="<?php echo esc_url( add_query_arg( [ 'ig_tab' => 'cv', 'view' => 'print' ] ) ); ?>" target="_blank" rel="noopener">
				📄 <?php esc_html_e( 'Anteprima e stampa / PDF', 'ig-enna' ); ?>
			</a>
		</div>
	</form>
</section>

<script>
(function () {
	function addRow(target) {
		var fs = document.querySelector('fieldset[data-rep="' + target + '"]');
		if (!fs) return;
		var rows = fs.querySelector('.ig-enna-cv__rows');
		if (!rows) return;
		var first = rows.querySelector('.ig-enna-cv__row');
		if (!first) return;
		var nextIdx = rows.children.length;
		var clone = first.cloneNode(true);
		clone.dataset.idx = nextIdx;
		// Reset input values + rinomina indici
		clone.querySelectorAll('input, textarea, select').forEach(function (el) {
			if (el.type === 'checkbox') { el.checked = false; }
			else { el.value = ''; }
			if (el.name) {
				el.name = el.name.replace(/\[(\d+)\]/, '[' + nextIdx + ']');
			}
		});
		rows.appendChild(clone);
		clone.scrollIntoView({ behavior: 'smooth', block: 'start' });
	}
	function removeRow(btn) {
		var row = btn.closest('.ig-enna-cv__row');
		var rows = btn.closest('.ig-enna-cv__rows');
		if (!row || !rows) return;
		if (rows.children.length <= 1) {
			// Resetta i campi della singola riga rimasta invece di rimuoverla.
			row.querySelectorAll('input, textarea').forEach(function (el) {
				if (el.type === 'checkbox') { el.checked = false; }
				else { el.value = ''; }
			});
			row.querySelectorAll('select').forEach(function (el) { el.selectedIndex = 0; });
			return;
		}
		row.remove();
	}
	document.addEventListener('click', function (e) {
		if (e.target.matches('.ig-enna-cv__add')) {
			e.preventDefault();
			addRow(e.target.dataset.target);
		} else if (e.target.matches('.ig-enna-cv__remove')) {
			e.preventDefault();
			removeRow(e.target);
		}
	});
})();
</script>
