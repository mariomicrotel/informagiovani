<?php
/**
 * Vista stampabile del CV (formato Europass).
 * Variabile: $cv (array) — vedi IG_Enna_CV::default_structure()
 */
defined( 'ABSPATH' ) || exit;

$p          = $cv['personal'];
$age        = IG_Enna_CV::age_from_birth( $p['birth_date'] );
$gens       = IG_Enna_CV::gender_options();
$levels     = IG_Enna_CV::cefr_levels();
$full_name  = trim( $p['first_name'] . ' ' . $p['last_name'] );
$loc_line   = trim( implode( ' ', array_filter( [ $p['cap'], $p['city'] ] ) ) );
$addr_full  = trim( $p['address'] . ( $loc_line ? ', ' . $loc_line : '' ) . ( $p['country'] ? ' (' . $p['country'] . ')' : '' ) );

$fmt_period = function( $row ) {
	$from = ! empty( $row['from'] ) ? date_i18n( 'M Y', strtotime( $row['from'] . '-01' ) ) : '';
	if ( ! empty( $row['current'] ) ) {
		$to = __( 'in corso', 'ig-enna' );
	} else {
		$to = ! empty( $row['to'] ) ? date_i18n( 'M Y', strtotime( $row['to'] . '-01' ) ) : '';
	}
	if ( $from && $to )  { return $from . ' — ' . $to; }
	if ( $from )         { return $from; }
	if ( $to )           { return $to; }
	return '';
};
?>
<article class="ig-enna ig ig-enna-cv-print">

	<aside class="ig-enna-cv-print__no-print">
		<button type="button" class="ig-enna-btn ig-enna-btn--primary" onclick="window.print()">
			🖨 <?php esc_html_e( 'Stampa / Salva come PDF', 'ig-enna' ); ?>
		</button>
		<a class="ig-enna-btn ig-enna-btn--ghost ig-enna-btn--sm" href="<?php echo esc_url( remove_query_arg( 'view' ) ); ?>">
			← <?php esc_html_e( 'Torna a modifica', 'ig-enna' ); ?>
		</a>
		<p class="ig-enna-cv-print__hint">
			<?php esc_html_e( 'Per salvare in PDF: nella finestra di stampa scegli come destinazione "Salva come PDF" (Chrome/Edge) o "Microsoft Print to PDF".', 'ig-enna' ); ?>
		</p>
	</aside>

	<!-- ===== HEADER ===== -->
	<?php $avatar_url_print = IG_Enna_Avatar::get_url( get_current_user_id(), 'medium' ); ?>
	<header class="ig-enna-cv-print__head<?php echo $avatar_url_print ? ' has-avatar' : ''; ?>">
		<?php if ( $avatar_url_print ) : ?>
			<div class="ig-enna-cv-print__avatar">
				<img src="<?php echo esc_url( $avatar_url_print ); ?>" alt="" />
			</div>
		<?php endif; ?>
		<div class="ig-enna-cv-print__head-body">
			<h1 class="ig-enna-cv-print__name"><?php echo esc_html( $full_name ?: __( 'Curriculum vitae', 'ig-enna' ) ); ?></h1>
			<?php if ( $cv['profile'] ) : ?>
				<p class="ig-enna-cv-print__profile"><?php echo esc_html( $cv['profile'] ); ?></p>
			<?php endif; ?>
		</div>
	</header>

	<!-- ===== ANAGRAFICA ===== -->
	<section class="ig-enna-cv-print__section">
		<h2><?php esc_html_e( 'Informazioni personali', 'ig-enna' ); ?></h2>
		<dl class="ig-enna-cv-print__dl">
			<?php if ( $p['birth_date'] ) : ?>
				<dt><?php esc_html_e( 'Data di nascita', 'ig-enna' ); ?></dt>
				<dd><?php echo esc_html( date_i18n( 'd F Y', strtotime( $p['birth_date'] ) ) ); ?><?php if ( $age !== null ) : ?> · <?php printf( esc_html__( '%d anni', 'ig-enna' ), (int) $age ); ?><?php endif; ?></dd>
			<?php endif; ?>
			<?php if ( $p['birth_place'] ) : ?>
				<dt><?php esc_html_e( 'Luogo di nascita', 'ig-enna' ); ?></dt>
				<dd><?php echo esc_html( $p['birth_place'] ); ?></dd>
			<?php endif; ?>
			<?php if ( $p['gender'] && isset( $gens[ $p['gender'] ] ) ) : ?>
				<dt><?php esc_html_e( 'Genere', 'ig-enna' ); ?></dt>
				<dd><?php echo esc_html( $gens[ $p['gender'] ] ); ?></dd>
			<?php endif; ?>
			<?php if ( $p['nationality'] ) : ?>
				<dt><?php esc_html_e( 'Nazionalità', 'ig-enna' ); ?></dt>
				<dd><?php echo esc_html( $p['nationality'] ); ?></dd>
			<?php endif; ?>
			<?php if ( $addr_full ) : ?>
				<dt><?php esc_html_e( 'Indirizzo', 'ig-enna' ); ?></dt>
				<dd><?php echo esc_html( $addr_full ); ?></dd>
			<?php endif; ?>
			<?php if ( $p['phone'] ) : ?>
				<dt><?php esc_html_e( 'Telefono', 'ig-enna' ); ?></dt>
				<dd><?php echo esc_html( $p['phone'] ); ?></dd>
			<?php endif; ?>
			<?php if ( $p['email'] ) : ?>
				<dt><?php esc_html_e( 'Email', 'ig-enna' ); ?></dt>
				<dd><a href="mailto:<?php echo esc_attr( $p['email'] ); ?>"><?php echo esc_html( $p['email'] ); ?></a></dd>
			<?php endif; ?>
			<?php if ( $p['website'] ) : ?>
				<dt><?php esc_html_e( 'Sito web', 'ig-enna' ); ?></dt>
				<dd><a href="<?php echo esc_url( $p['website'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $p['website'] ); ?></a></dd>
			<?php endif; ?>
			<?php if ( $p['linkedin'] ) : ?>
				<dt>LinkedIn</dt>
				<dd><a href="<?php echo esc_url( $p['linkedin'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $p['linkedin'] ); ?></a></dd>
			<?php endif; ?>
		</dl>
	</section>

	<!-- ===== ESPERIENZA ===== -->
	<?php if ( $cv['experience'] ) : ?>
		<section class="ig-enna-cv-print__section">
			<h2><?php esc_html_e( 'Esperienze lavorative', 'ig-enna' ); ?></h2>
			<?php foreach ( $cv['experience'] as $r ) : ?>
				<article class="ig-enna-cv-print__entry">
					<div class="ig-enna-cv-print__entry-period"><?php echo esc_html( $fmt_period( $r ) ); ?></div>
					<div class="ig-enna-cv-print__entry-body">
						<h3><?php echo esc_html( $r['role'] ); ?></h3>
						<?php if ( $r['employer'] || $r['city'] ) : ?>
							<p class="ig-enna-cv-print__entry-org">
								<?php echo esc_html( $r['employer'] ); ?><?php if ( $r['city'] ) : ?> · <?php echo esc_html( $r['city'] ); ?><?php endif; ?>
							</p>
						<?php endif; ?>
						<?php if ( $r['sector'] ) : ?>
							<p class="ig-enna-cv-print__entry-meta"><?php echo esc_html__( 'Settore: ', 'ig-enna' ) . esc_html( $r['sector'] ); ?></p>
						<?php endif; ?>
						<?php if ( $r['description'] ) : ?>
							<p class="ig-enna-cv-print__entry-desc"><?php echo nl2br( esc_html( $r['description'] ) ); ?></p>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</section>
	<?php endif; ?>

	<!-- ===== ISTRUZIONE ===== -->
	<?php if ( $cv['education'] ) : ?>
		<section class="ig-enna-cv-print__section">
			<h2><?php esc_html_e( 'Istruzione e formazione', 'ig-enna' ); ?></h2>
			<?php foreach ( $cv['education'] as $r ) : ?>
				<article class="ig-enna-cv-print__entry">
					<div class="ig-enna-cv-print__entry-period"><?php echo esc_html( $fmt_period( $r ) ); ?></div>
					<div class="ig-enna-cv-print__entry-body">
						<h3><?php echo esc_html( $r['qualification'] ); ?></h3>
						<?php if ( $r['school'] || $r['city'] ) : ?>
							<p class="ig-enna-cv-print__entry-org">
								<?php echo esc_html( $r['school'] ); ?><?php if ( $r['city'] ) : ?> · <?php echo esc_html( $r['city'] ); ?><?php endif; ?>
							</p>
						<?php endif; ?>
						<?php if ( $r['grade'] ) : ?>
							<p class="ig-enna-cv-print__entry-meta"><?php echo esc_html__( 'Voto: ', 'ig-enna' ) . esc_html( $r['grade'] ); ?></p>
						<?php endif; ?>
						<?php if ( $r['subjects'] ) : ?>
							<p class="ig-enna-cv-print__entry-desc"><?php echo nl2br( esc_html( $r['subjects'] ) ); ?></p>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</section>
	<?php endif; ?>

	<!-- ===== LINGUE ===== -->
	<?php if ( $cv['languages'] ) : ?>
		<section class="ig-enna-cv-print__section">
			<h2><?php esc_html_e( 'Competenze linguistiche', 'ig-enna' ); ?></h2>
			<table class="ig-enna-cv-print__lang-tbl">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Lingua', 'ig-enna' ); ?></th>
						<th><?php esc_html_e( 'Ascolto', 'ig-enna' ); ?></th>
						<th><?php esc_html_e( 'Lettura', 'ig-enna' ); ?></th>
						<th><?php esc_html_e( 'Int. orale', 'ig-enna' ); ?></th>
						<th><?php esc_html_e( 'Prod. orale', 'ig-enna' ); ?></th>
						<th><?php esc_html_e( 'Scrittura', 'ig-enna' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $cv['languages'] as $l ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $l['language'] ); ?></strong></td>
							<td><?php echo esc_html( $l['listening'] ); ?></td>
							<td><?php echo esc_html( $l['reading'] ); ?></td>
							<td><?php echo esc_html( $l['spoken_interaction'] ); ?></td>
							<td><?php echo esc_html( $l['spoken_production'] ); ?></td>
							<td><?php echo esc_html( $l['writing'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="ig-enna-cv-print__lang-legend">
				<?php esc_html_e( 'Livelli: A1/A2 base · B1/B2 autonomo · C1/C2 padronanza (CEFR)', 'ig-enna' ); ?>
			</p>
		</section>
	<?php endif; ?>

	<!-- ===== COMPETENZE ===== -->
	<?php if ( $cv['communication_skills'] || $cv['organisational_skills'] || $cv['digital_skills'] || $cv['other_skills'] || $cv['driving_licence'] ) : ?>
		<section class="ig-enna-cv-print__section">
			<h2><?php esc_html_e( 'Competenze', 'ig-enna' ); ?></h2>
			<dl class="ig-enna-cv-print__dl">
				<?php if ( $cv['communication_skills'] ) : ?>
					<dt><?php esc_html_e( 'Comunicative', 'ig-enna' ); ?></dt>
					<dd><?php echo nl2br( esc_html( $cv['communication_skills'] ) ); ?></dd>
				<?php endif; ?>
				<?php if ( $cv['organisational_skills'] ) : ?>
					<dt><?php esc_html_e( 'Organizzative', 'ig-enna' ); ?></dt>
					<dd><?php echo nl2br( esc_html( $cv['organisational_skills'] ) ); ?></dd>
				<?php endif; ?>
				<?php if ( $cv['digital_skills'] ) : ?>
					<dt><?php esc_html_e( 'Digitali', 'ig-enna' ); ?></dt>
					<dd><?php echo nl2br( esc_html( $cv['digital_skills'] ) ); ?></dd>
				<?php endif; ?>
				<?php if ( $cv['other_skills'] ) : ?>
					<dt><?php esc_html_e( 'Altre', 'ig-enna' ); ?></dt>
					<dd><?php echo nl2br( esc_html( $cv['other_skills'] ) ); ?></dd>
				<?php endif; ?>
				<?php if ( $cv['driving_licence'] ) : ?>
					<dt><?php esc_html_e( 'Patente di guida', 'ig-enna' ); ?></dt>
					<dd><?php echo esc_html( $cv['driving_licence'] ); ?></dd>
				<?php endif; ?>
			</dl>
		</section>
	<?php endif; ?>

	<footer class="ig-enna-cv-print__foot">
		<p>
			<?php
			if ( ! empty( $cv['updated_at'] ) ) {
				/* translators: %s = data aggiornamento */
				printf( esc_html__( 'CV aggiornato al %s', 'ig-enna' ), esc_html( date_i18n( 'd F Y', strtotime( $cv['updated_at'] ) ) ) );
			} else {
				printf( esc_html__( 'CV stampato il %s', 'ig-enna' ), esc_html( date_i18n( 'd F Y' ) ) );
			}
			?>
			· <?php echo esc_html__( 'Generato con Informagiovani Enna', 'ig-enna' ); ?>
		</p>
	</footer>
</article>
