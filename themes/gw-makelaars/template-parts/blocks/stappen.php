<?php
/** Blok: Stappenplan. */
defined( 'ABSPATH' ) || exit;
$donker = 'olive' === $a['kleur'];
?>
<section class="section_stappen <?php echo $donker ? 'background-color-olive text-color-white' : 'background-color-sand'; ?>"<?php echo gw_anker( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="padding-global">
		<div class="container-large">
			<?php gw_sectiekop( $a['tag'], $a['kop'], array( 'licht' => $donker ) ); ?>
			<ol class="stappen_list" style="--kolommen:<?php echo (int) $a['kolommen']; ?>;list-style:none;margin:0;padding:0">
				<?php foreach ( $a['items'] as $i => $s ) : ?>
					<li class="stap"<?php echo gw_reveal( $i % 4 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<span class="stap_nummer" aria-hidden="true"><?php echo (int) $i + 1; ?></span>
						<span class="stap_title"><?php echo esc_html( $s['titel'] ); ?></span>
						<?php if ( $s['tekst'] ) : ?><span class="stap_text"><?php echo esc_html( $s['tekst'] ); ?></span><?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>
