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
	 *
	 * @var string $table
	 */
	public $table = "nested_set";
	const TABLE = "nested_set";

	/**
	 * term id.
	 *
	 * @var $id
	 */
	public $id;

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


	/**
	 * Nested_Terms constructor.
	 * Define and use global database object
	 *
	 * @param null   $term
	 * @param string $taxonomy
	 */
	public function __construct( $term, $taxonomy = "" ) {


		return $this->get_instance( $term, $taxonomy );

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

		$query = "SELECT * from {$this->table} were id = $id" . $taxonomy_clause;
		$term  = $wpdb->get_row( $$query );

		if ( is_null( $term ) ) {
			return false;
		}

		foreach ( get_object_vars( $term ) as $key => $value ) {
			$this->$key = $value;
		}

		return $this;
	}


	/**
	 * @param Nested_Term|int $term
	 * @param array           $args
	 *
	 * @return bool|Nested_Term
	 */
	public static function update_term( $term, array $args ) {
		global $wpdb;

		unset( $args['parent'] );
		//@todo: check for parent change

		unset( $args['count'] );


		if ( $term instanceof Nested_Term ) {
			$term_id = $term->id;
		} else {
			$term_id = $term;
		}

		$result = $wpdb->update( self::TABLE,
			$args, [
				'id' => $term_id,
			] );

		return $result ? new Nested_Term( $term_id ) : false;

	}


	/**
	 * @param int $parent
	 *
	 * @return int left index of parent
	 */
	public function get_parent_left( int $parent ): int {
		global $wpdb;

		$left = $wpdb->get_var( "SELECT {$this->leftName} from {$this->table} where id = {$parent}" );

		return $left ?? 0;
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

		$parent = $wpdb->get_row( "SELECT * from {$this->table} where id = {$parent}" . $taxonomy_clause );

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

		$parent = $wpdb->get_row( "SELECT * from {$this->table} where id = {$parent}" . $taxonomy_clause );

		if ( is_null( $parent ) ) {
			return new WP_Error( 'invalid_term', __( 'Empty Term.' ) );
		}

		$query = "SELECT * from {$this->table} where parent = {$parent}" . $taxonomy_clause;

		return $wpdb->get_results( $query );
	}

}
