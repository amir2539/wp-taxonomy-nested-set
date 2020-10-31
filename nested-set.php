<?php
/**
 * Plugin Name: nested set fort wordpress
 * Developer : AmirMohammad Torkaman
 * Email : amirtorkaman5204@gmail.com
 * Author Uri : amirtorkaman.ir
 **/

defined( 'ABSPATH' ) || exit;

include "includes/class-nested-terms.php";

add_action( 'init', 'amir_add_nested_set' );


function amir_add_nested_set() {
	if ( ! isset( $_GET['nest'] ) ) {
		return false;
	}


	$nested = new Nested_Terms();

	$result = $nested->get_all_children( 4 );

	echo "<pre>";
	print_r( $result );
	echo "</pre>";



	die();
}