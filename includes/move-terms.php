<?php
/**
 * Developer : AmirMohammad Torkaman
 * Email : amirtorkaman5204@gmail.com
 * Author Uri : amirtorkaman.ir
 **/

defined( 'ABSPATH' ) || exit;

function fk_move_terms() {
	global $wpdb;

	$query = "SELECT * from {$wpdb->terms} as t inner join {$wpdb->term_taxonomy} as tt on t.term_id = tt.term_id";

	$terms = $wpdb->get_results( $query );

	$parents = [];

	$nested = new Nested_Term_Query();

	/** @var WP_Term $term */
	foreach ( $terms as $term ) {

		$parent = isset( $parents[ $term->parent ] ) ? $parents[ $term->parent ] : 0;

		$node_id = $nested->insert( $term->name, $term->slug, $term->taxonomy, $parent, $term->description, $term->term_group, $term->count );

		$parents[ $term->term_id ] = $node_id;
	}
}