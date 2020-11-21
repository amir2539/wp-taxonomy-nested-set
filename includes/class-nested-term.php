<?php
/**
 * Developer : AmirMohammad Torkaman
 * Email : amirtorkaman5204@gmail.com
 * Author Uri : amirtorkaman.ir
 **/

defined( 'ABSPATH' ) || exit;

/**
 * Class Nested_Terms
 * An alternative data structure for storing and retrieving terms
 */
class  Nested_Term {

	/**
	 * nested set table name
	 *  wpdv->prefix . nested_set
	 *
	 * @var string $table
	 */
	public $table;
	const TABLE = "wp_taxonomy_lookup";

	/**
	 * term id.
	 *
	 * @var $term_id
	 */
	public $term_id;

	/**
	 * term's name.
	 *
	 * @var $name
	 */
	public $name;

	/**
	 * term's slug.
	 *
	 * @var $slug
	 */
	public $slug;

	/**
	 * $term's taxonomy.
	 *
	 * @var $taxonomy
	 */
	public $taxonomy;

	/**
	 * term's description.
	 *
	 * @var $description
	 */
	public $description;

	/**
	 * number of term used.
	 *
	 * @var $count
	 */
	public $count;

	/**
	 * term's group.
	 *
	 * @var $term_group
	 */
	public $term_group;

	/**
	 * term;s left index.
	 *
	 * @var $left
	 */
	public $left;

	/**
	 * term's right index.
	 *
	 * @var $right
	 */
	public $right;

	/**
	 * parent term id.
	 *
	 * @var $parent
	 */
	public $parent;

	/**
	 * specify left index name in database.
	 *
	 * @var string $leftName
	 */
	public $leftName = "_lft";

	/**
	 * specify right index name in database.
	 *
	 * @var string $rightName
	 */
	public $rightName = "_rgt";

	public $query_vars;


	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . "taxonomy_lookup";
	}

	/**
	 * @param        $id
	 * @param string $taxonomy
	 *
	 * @return array|bool|object return false if term does not exists
	 *                           return Nesterm_Terms object if find
	 */
	public function get_instance( $id, string $taxonomy = "" ) {
		global $wpdb;

		$taxonomy_clause = "";
		if ( ! empty( $taxonomy ) ) {
			$taxonomy_clause = " and taxonomy = '$taxonomy' ";
		}

		$query = "SELECT * from {$this->table} where term_id = $id" . $taxonomy_clause;
		$term  = $wpdb->get_row( $query );

		// if term does not found or its taxonomy parent
		if ( is_null( $term ) ) {
			return false;
		}
		foreach ( get_object_vars( $term ) as $key => $value ) {
			$this->$key = $value;
		}
		//add left and right
		$this->left  = $term->{$this->leftName};
		$this->right = $term->{$this->rightName};

		return $this;
	}


	/**
	 * @param Nested_Term|int $term
	 * @param array           $args
	 *
	 * @return bool|Nested_Term
	 */
	public function update_term( $term, array $args ) {
		global $wpdb;

		if ( $term instanceof Nested_Term ) {
			$term_id = $term->term_id;
		} else {
			$term_id = $term;
		}

		$term = $this->get_instance( $term_id );
		//compare what fields have hcanged

		$args = $this->array_compare( (array) $term, (array) $args );


		if ( isset( $args['parent'] ) && intval( $args['parent'] ) > 0 ) {
			$nested_query = new Nested_Term_Query();
			$nested_query->re_insert( $term_id, $args['parent'] );
		}

		unset( $args['parent'] );
		unset( $args['count'] );

		return $wpdb->update( $this->table,
			$args, [
				'term_id' => $term_id,
			] );
	}

	private function array_compare( array $arr1, array $arr2 ) {
		$result = [];

		foreach ( $arr1 as $key => $value ) {
			if ( $arr2[ $key ] != $value && isset( $arr2[ $key ] ) ) {
				$result[ $key ] = $arr2[ $key ];
			}
		}

		return $result;
	}


	/**
	 * returns all of children of given id in all levels
	 *
	 * @param int    $parent
	 *
	 * @param string $taxonomy
	 *
	 * @return array|object|WP_Error
	 */
	public function get_all_children( int $parent, string $taxonomy = "" ) {
		global $wpdb;

		$taxonomy_clause = "";

		if ( ! empty( $taxonomy ) ) {
			$taxonomy_clause = " and taxonomy = '$taxonomy' ";
		}

		$parent = $wpdb->get_row( "SELECT * from {$this->table} where term_id = {$parent}" . $taxonomy_clause );

		if ( is_null( $parent ) ) {
			return new WP_Error( 'invalid_term', __( 'Empty Term.' ) );
		}

		$parent_left  = $parent->{$this->leftName} + 1;
		$parent_right = $parent->{$this->rightName} - 1;

		$query = "SELECT * from {$this->table} where {$this->leftName} between {$parent_left} and {$parent_right} 
		and {$this->rightName} between {$parent_left} and {$parent_right}" . $taxonomy_clause;

		return $wpdb->get_results( $query );
	}


	/**
	 * return one level children
	 *
	 * @param int    $parent
	 *
	 * @param string $taxonomy
	 *
	 * @return array|object|WP_Error
	 */
	public function get_children( int $parent, string $taxonomy = "" ) {
		global $wpdb;

		$taxonomy_clause = "";

		if ( ! empty( $taxonomy ) ) {
			$taxonomy_clause = " and taxonomy = '$taxonomy' ";
		}

		$parent_id = $parent;
		$parent    = $wpdb->get_row( "SELECT * from {$this->table} where term_id = {$parent}" . $taxonomy_clause );

		if ( is_null( $parent ) ) {
			return new WP_Error( 'invalid_term', __( 'Empty Term.' ) );
		}

		$query = "SELECT * from {$this->table} where parent = {$parent_id}" . $taxonomy_clause;

		return $wpdb->get_results( $query );
	}


	/**
	 * Delete term and fix left and right indexes
	 *
	 * @param Nested_Term|int $term
	 *
	 * @return bool
	 */
	public function delete_node( $term ) {


		if ( $term instanceof Nested_Term ) {
			$term_id = $term->term_id;
		} else {
			$term_id = $term;
		}

		$nested = new Nested_Term_Query();

		return $nested->delete_node( $term_id );

	}

	/**
	 * @param Nested_Term| int $term
	 *
	 * @global                 $wpdb
	 */
	public function get_hierarchy( $term ) {
		global $wpdb;

		if ( ! ( $term instanceof Nested_Term ) ) {
			$term = $this->get_instance( $term );
		}

		$results = $wpdb->get_results( "SELECT * FROM {$this->table} where {$this->leftName} =< {$term->left} AND {$this->rightName} >= {$term->right}
		orderby {$this->leftName} ASC" );

		$result = [];
		foreach ( $results as $term ) {
			$term          = (array) $term;
			$term['left']  = $term[ $this->leftName ];
			$term['right'] = $term[ $this->rightName ];

			$term     = (object) $term;
			$result[] = $term;
		}

		return $result;

	}
}
