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
class  Nested_Terms {

	/**
	 * nested set table name
	 *
	 * @var string $table
	 */
	private $table = "nested_set";


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


	protected $query_var_defaults;

	/**
	 * Nested_Terms constructor.
	 * Define and use global database object
	 *
	 * @param null $term
	 */
	public function __construct( $term = NULL ) {
		if ( ! is_null( $term ) ) {
			foreach ( get_object_vars( $term ) as $key => $value ) {
				$this->$key = $value;
			}
		}


	}

	private function get_max() {
		global $wpdb;

		$max = $wpdb->get_var( "SELECT max({$this->rightName}) as aggregate from {$this->table}" );

		return $max ?? 0;
	}

	/**
	 * @param string $taxonomy taxonomy name
	 *
	 * @return bool false when taxonomy does not exists
	 *              true when taxonomy exists
	 */
	public function taxonomy_exists( string $taxonomy ): bool {

		global $wpdb;

		$taxonomy = $wpdb->get_var( "SELECT taxonomy from {$this->table} where taxonomy = '{$taxonomy}' LIMIT 1" );

		return is_null( $taxonomy ) ? false : true;
	}

	/**
	 * @param string      $name
	 * @param string      $slug
	 * @param string      $taxonomy
	 * @param int         $parent
	 *
	 * @param string|null $description
	 * @param int         $term_group
	 *
	 * @param int         $count
	 *
	 * @return bool|false|int returns id of inserted node
	 *                        if there is error in database returns false
	 */
	public function insert( string $name, string $slug, string $taxonomy, int $parent = 0, string $description = NULL, int $term_group = 0 , int $count =0) {

		//insert new with max left and right
		//if is new taxonomy create a root
		global $wpdb;

		$parent_left = 0;

		if ( ! $this->taxonomy_exists( $taxonomy ) ) {
			$result = $this->make_taxonomy_root( $taxonomy );
			$max    = $result['right'];

			$parent_left = $max - 1;
			$parent      = $result['id'];
		} else {
			$max = $this->get_max();
		}

		$left  = $max + 1;
		$right = $max + 2;

		$wpdb->insert( $this->table, [
			'name'           => $name,
			'slug'           => $slug,
			'taxonomy'       => $taxonomy,
			$this->leftName  => $left,
			$this->rightName => $right,
			'description'    => $description,
			'term_group'     => $term_group,
		] );

		$node_id = $wpdb->insert_id;

		//get parent left and update it
		$parent_left = $parent_left != 0 ? $parent_left : $this->get_parent_left( $parent );

		$diff = $left - ( $parent_left + 1 );

		$left_range = $parent_left + 1;


		$query = "UPDATE {$this->table} set {$this->leftName} = case 
					when {$this->leftName} BETWEEN  {$left} and {$right} then {$this->leftName}-{$diff} 
					when {$this->leftName} BETWEEN  {$left_range} and {$right} then {$this->leftName}+2 
					else {$this->leftName} end ,
					
					{$this->rightName} = case 
					when {$this->rightName} BETWEEN  {$left} and {$right} then {$this->rightName}-{$diff} 
					when {$this->rightName} BETWEEN  {$left_range} and {$right} then {$this->rightName}+2 
					else '{$this->rightName}' end 
					
					where ({$this->leftName} between {$left_range} and {$right} or {$this->rightName} between {$left_range} and {$right})";


		$result = $wpdb->query( $query );

		if ( ! empty( $wpdb->last_error ) || ! $result ) {
			file_put_contents( __DIR__ . '/logs/query.log', json_encode( [
					'query' => $query,
					'error' => $wpdb->last_error,
				], JSON_PRETTY_PRINT ) . PHP_EOL, FILE_APPEND );

			return false;
		}

		// update node
		$this->update_node( $node_id, ( $left - $diff ), ( $right - $diff ), $parent );

		return $node_id;

	}


	/**
	 * @param int      $id
	 * @param int      $left
	 * @param int      $right
	 *
	 * @param int|null $parent
	 *
	 * @return bool|false|int
	 */
	public function update_node( int $id, int $left, int $right, int $parent = 0 ) {
		global $wpdb;

		return $wpdb->update( $this->table, [
			$this->leftName  => $left,
			$this->rightName => $right,
			'parent'         => $parent,
		], [
			'id' => $id,
		] );
	}

	/**
	 * @param string $taxonomy
	 *
	 * @return array
	 * @return array return right index nad id of inserted root
	 */
	public function make_taxonomy_root( string $taxonomy ): array {
		global $wpdb;

		$max = $this->get_max();

		$left  = $max + 1;
		$right = $max + 2;

		$wpdb->insert( $this->table, [
			'name'           => $taxonomy,
			'taxonomy'       => $taxonomy,
			$this->leftName  => $left,
			$this->rightName => $right,
		] );


		return [
			'right' => $right,
			'id'    => $wpdb->insert_id,
		];
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


	/**
	 * @param        $id
	 * @param string $taxonomy
	 *
	 * @return array|bool|object return false if term does not exists
	 *                           return Nesterm_Terms object if find
	 */
	public function get_term( $id, string $taxonomy = "" ) {
		global $wpdb;

		$taxonomy_clause = "";
		if ( ! empty( $taxonomy ) ) {
			$taxonomy_clause = " and taxonomy = '$taxonomy' ";
		}

		$query = "SELECT * from {$this->table} were id = $id" . $taxonomy_clause;
		$term  = $wpdb->get_row( $$query );

		return is_null( $term ) ? false : new Nested_Terms( $term );
	}

}
