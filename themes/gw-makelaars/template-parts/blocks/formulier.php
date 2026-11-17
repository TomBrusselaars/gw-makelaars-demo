<?php
/** Blok: Contactformulier. Verwerking: inc/formulier.php (nonce, honeypot, rate-limit). */
defined( 'ABSPATH' ) || exit;
$status = isset( $_GET['gw_form'] ) ? sanitize_key( wp_unslash( $_GET['gw_form'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification -- alleen een melding tonen
?>
<section class="section_formulier background-color-sand"<?php echo gw_anker( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="padding-global">
		<div class="container-large">
			<div class="formulier_layout">
				<div class="formulier_text">
					<?php gw_sectiekop( $a['tag'], $a['kop'] ); ?>
					<?php if ( $a['tekst'] ) : ?><p class="text-size-medium"<?php echo gw_reveal( 1 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $a['tekst'] ); ?></p><?php endif; ?>
				</div>
				<form class="formulier_form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"<?php echo gw_reveal( 2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
					<?php if ( 'ok' === $status ) : ?><p class="formulier_melding" role="status"><?php echo esc_html( $a['succes'] ); ?></p>
					<?php elseif ( 'fout' === $status ) : ?><p class="formulier_melding is-fout" role="alert">Het versturen is niet gelukt. Controleer je gegevens of bel ons even.</p><?php endif; ?>
					<input type="hidden" name="action" value="gw_formulier">
					<input type="hidden" name="gw_pagina" value="<?php echo (int) get_queried_object_id(); ?>">
					<?php wp_nonce_field( 'gw_formulier', 'gw_nonce' ); ?>
					<label>Naam *<input name="naam" required autocomplete="name"></label>
					<label>E-mail *<input name="email" type="email" required autocomplete="email"></label>
					<label>Telefoon<input name="telefoon" type="tel" autocomplete="tel"></label>
					<label>Bericht<textarea name="bericht" rows="4"></textarea></label>
					<input class="formulier_hp" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
					<button class="button" type="submit"><?php echo esc_html( $a['knop_label'] ); ?></button>
				</form>
			</div>
		</div>
	</div>
</section>
