<?php
/**
 * Developer : AmirMohammad Torkaman
 * Email : amirtorkaman5204@gmail.com
 * Author Uri : amirtorkaman.ir
 **/


/**
 * @param string $taxonomy taxonomy name
 *
 * @return bool false when taxonomy does not exists
 *              true when taxonomy exists
 */
function nested_taxonomy_exists( string $taxonomy ): bool {

	$nested_term_query = Nested_Term_Query::get_instance();

	return $nested_term_query->taxonomy_exists( $taxonomy );
}

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