<?php
/**
 * Plugin Name: nested set fort wordpress
 * Developer : AmirMohammad Torkaman
 * Email : amirtorkaman5204@gmail.com
 * Author Uri : amirtorkaman.ir
 **/

defined( 'ABSPATH' ) || exit;

include "includes/class-nested-terms.php";
include "includes/move-terms.php";

add_action( 'init', 'amir_add_nested_set' );


function amir_add_nested_set() {

	if ( ! isset( $_GET['nested'] ) ) {
		return false;
	}

	global $wpdb;

}