<?php
/**
 * Plugin Name: WooCommerce CPF Validator
 * Description: Adds a CPF field to the WooCommerce checkout and validates it server-side.
 * Version: 1.0.0
 * Requires Plugins: woocommerce
 * License: MIT
 */

defined( 'ABSPATH' ) || exit;

// Tells WooCommerce this plugin works with High-Performance Order Storage (HPOS).
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

// Returns true only for a mathematically valid CPF (11 digits, correct check digits, not all the same digit).
function wcpfv_is_valid_cpf( $cpf ) {
	$cpf = preg_replace( '/\D/', '', (string) $cpf );

	if ( strlen( $cpf ) !== 11 || preg_match( '/^(\d)\1{10}$/', $cpf ) ) {
		return false;
	}

	for ( $t = 9; $t < 11; $t++ ) {
		$sum = 0;
		for ( $i = 0; $i < $t; $i++ ) {
			$sum += (int) $cpf[ $i ] * ( ( $t + 1 ) - $i );
		}
		$digit = ( ( 10 * $sum ) % 11 ) % 10;
		if ( (int) $cpf[ $t ] !== $digit ) {
			return false;
		}
	}

	return true;
}

// Turns a CPF into a masked form like ***.***.247-25 so the full number isn't stored on the order.
function wcpfv_mask_cpf( $cpf ) {
	$cpf = preg_replace( '/\D/', '', (string) $cpf );
	return '***.***.' . substr( $cpf, 6, 3 ) . '-' . substr( $cpf, 9, 2 );
}

// Adds a required CPF field to the classic checkout, right after the billing fields.
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
	$fields['billing']['billing_cpf'] = array(
		'type'        => 'text',
		'label'       => __( 'CPF', 'woocommerce-cpf-validator' ),
		'placeholder' => '000.000.000-00',
		'required'    => true,
		'class'       => array( 'form-row-wide' ),
		'priority'    => 120,
	);
	return $fields;
} );

// Blocks checkout if the CPF fails validation, with an error shown to the customer.
add_action( 'woocommerce_checkout_process', function () {
	$cpf = isset( $_POST['billing_cpf'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_cpf'] ) ) : '';

	if ( ! wcpfv_is_valid_cpf( $cpf ) ) {
		wc_add_notice( __( 'Please enter a valid CPF.', 'woocommerce-cpf-validator' ), 'error' );
	}
} );

// Saves only the masked CPF on the order, not the full number.
add_action( 'woocommerce_checkout_create_order', function ( $order ) {
	if ( ! empty( $_POST['billing_cpf'] ) ) {
		$cpf = sanitize_text_field( wp_unslash( $_POST['billing_cpf'] ) );
		$order->update_meta_data( '_billing_cpf_masked', wcpfv_mask_cpf( $cpf ) );
	}
} );