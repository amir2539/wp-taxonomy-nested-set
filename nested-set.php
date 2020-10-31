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

	if ( ! isset( $_GET['nested'] ) ) {
		return false;
	}

	global $wpdb;

	$args = [
		'taxonomy'   => [ 'cat' ],
		'hide_empty' => false,
		'include' => [1, 2],
		'exclude' => [3]
	];

	$query = new Nested_Term_Query( $args );
	$query->get_terms();
	die();

}

function echo_pre( $value ) {
	echo "<pre>";
	print_r( $value );
	echo "</pre>";
}