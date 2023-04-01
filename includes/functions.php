<?php
/**
 * Developer : AmirMohammad Torkaman
 * Email : amirtorkaman5204@gmail.com
 **/

/**
 * @param string $term_id
 * @param string $taxonomy
 *
 * @return Nested_Term|bool
 */
function nested_get_term( string $term_id, string $taxonomy = "" ) {
	$term = new Nested_Term();

	return $term->get_instance( $term_id, $taxonomy );
}

/**
 * @param int   $term_id
 * @param array $args  name
 *                     slug
 *                     term_group
 *
 * @return bool|Nested_Term return object of updated term when find term
 *                          false if not found
 */
function nested_update_term( int $term_id, array $args ) {
	$nested_term = new Nested_Term();

	$result = $nested_term->update_term( $term_id, $args );
	unset( $nested_term );

	return $result;
}

function nested_delete_term( int $term_id ) {
	$nested_term = new Nested_Term();

	$result = $nested_term->delete_node( $term_id );
	unset( $nested_term );

	return $result;
}

function nested_get_ancestors( int $term_id ) {
	$nested_term = new Nested_Term_Query();
	$terms       = $nested_term->get_ancestors( $term_id );
	unset( $nested_term );

	return $terms;
}

function nested_get_hierarchy( int $term_id ) {
	$nested_term = new Nested_Term();

	return $nested_term->get_hierarchy( $term_id );
}