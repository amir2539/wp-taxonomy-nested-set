<?php
/**
 * Plugin Name: nested set fort wordpress
 * Developer : AmirMohammad Torkaman
 * Email : amirtorkaman5204@gmail.com
 * Author Uri : amirtorkaman.ir
 **/

defined( 'ABSPATH' ) || exit;

include "includes/class-nested-term.php";
include "includes/class-nested-term-query.php";
include "includes/functions.php";
include "includes/move-terms.php";

add_action( 'init', 'amir_add_nested_set' );


function amir_add_nested_set() {

	if (  isset( $_GET['move'] ) ) {

		nested_move_terms();
		die();
	}

	if ( ! isset( $_GET['nested'] ) ) {
		return false;
	}


	$term = nested_get_term( 9 );

	$term->update_term( $term, [
		'parent' => 4,
	] );

	die();
}


function echo_pre( $value ) {
	echo "<pre>";
	print_r( $value );
	echo "</pre>";
}