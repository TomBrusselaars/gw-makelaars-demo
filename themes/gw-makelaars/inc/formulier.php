<?php
/**
 * Verwerking van het contactformulier-blok.
 * Beveiliging: nonce, honeypot, rate-limit per IP, strikte validatie, geen vrije ontvanger
 * (de mail gaat altijd naar het adres uit de Customizer).
 */

defined( 'ABSPATH' ) || exit;

function gw_formulier_verwerk() {
	$terug = wp_get_referer() ?: home_url( '/' );
	$terug = remove_query_arg( 'gw_form', $terug );
	$fout  = static function () use ( $terug ) {
		wp_safe_redirect( add_query_arg( 'gw_form', 'fout', $terug ) . '#contact' );
		exit;
	};

	if ( ! isset( $_POST['gw_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gw_nonce'] ) ), 'gw_formulier' ) ) {
		$fout();
	}
	// Honeypot: bots vullen dit veld in; doe alsof het gelukt is.
	if ( ! empty( $_POST['website'] ) ) {
		wp_safe_redirect( add_query_arg( 'gw_form', 'ok', $terug ) . '#contact' );
		exit;
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'gw_form_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= 5 ) {
		$fout();
	}
	set_transient( $key, $n + 1, 10 * MINUTE_IN_SECONDS );

	$naam   = isset( $_POST['naam'] ) ? sanitize_text_field( wp_unslash( $_POST['naam'] ) ) : '';
	$email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$tel    = isset( $_POST['telefoon'] ) ? sanitize_text_field( wp_unslash( $_POST['telefoon'] ) ) : '';
	$bericht = isset( $_POST['bericht'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bericht'] ) ) : '';
	$naar   = (string) gw_mod( 'email' );
	if ( '' === $naam || ! is_email( $email ) || ! is_email( $naar ) || strlen( $bericht ) > 5000 ) {
		$fout();
	}

	$pagina  = isset( $_POST['gw_pagina'] ) ? absint( $_POST['gw_pagina'] ) : 0;
	$tekst   = "Naam: $naam\nE-mail: $email\nTelefoon: $tel\nPagina: " . ( $pagina ? get_permalink( $pagina ) : home_url( '/' ) ) . "\n\n$bericht";
	$verzond = wp_mail(
		$naar,
		'Nieuw bericht via de website van ' . wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		$tekst,
		array( 'Reply-To: ' . $naam . ' <' . $email . '>' )
	);
	wp_safe_redirect( add_query_arg( 'gw_form', $verzond ? 'ok' : 'fout', $terug ) . '#contact' );
	exit;
}
add_action( 'admin_post_nopriv_gw_formulier', 'gw_formulier_verwerk' );
add_action( 'admin_post_gw_formulier', 'gw_formulier_verwerk' );
