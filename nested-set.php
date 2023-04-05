<?php
/**
 * Plugin Name: Nested-Set Terms
 * Plugin URI: https://github.com/amir2539/wp-taxonomy-nested-set
 * Description: This plugin will store terms and taxonomies in nested-set algorithm and will save a lot of time in queries.
 * Author : AmirMohammad Torkaman
 * Author URI: https://github.com/amir2539
 * Version: 0.1
 * Requires at least: 5.0
 * Requires PHP: 8
 **/

defined( 'ABSPATH' ) || exit;

include 'includes/class-nested-term-install.php';
include 'includes/class-nested-term.php';
include 'includes/class-nested-term-query.php';
include 'includes/functions.php';
include 'includes/class-nested-term-hooks.php';
include 'includes/class-nested-admin.php';