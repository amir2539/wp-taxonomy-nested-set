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
		'taxonomy'     => 'product_cat',
		'hide_empty'   => false,
		//		'parent'     => 2,
		//		'fields'     => 'name',
		//		'number'     => 2,
		//		'offset'     => 1,
		//		'orderby'     => 'count',
		//		'order'      => 'DESC',
		//		'description__like' => ''
		'meta_key'     => 'thumbnail_id',
		'meta_value'   => 60,
		'meta_compare' => '<>',

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