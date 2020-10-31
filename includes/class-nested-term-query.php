<?php
/**
 * Developer : AmirMohammad Torkaman
 * Email : amirtorkaman5204@gmail.com
 * Author Uri : amirtorkaman.ir
 **/


class Nested_Term_Query {


	/**
	 * Container for the main instance of the class.
	 *
	 * @since 5.0.0
	 * @var Nested_Term_Query|null
	 */
	private static $instance = NULL;

	/**
	 * nested set table name
	 *
	 * @var string $table
	 */
	private $table = "nested_set";

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

	/**
	 * get terms args
	 *
	 * @var array $query_vars
	 */
	public $default_query_vars = [];
	public $query_vars = [];

	/**
	 * Nested_Term_Query constructor.
	 *
	 * setup the term queries passed
	 *
	 * @param array $args {
	 *
	 * @todo add query vars  doc
	 *
	 * }
	 */
	public function __construct( array $args = [] ) {

		$this->default_query_vars = [
			'taxonomy'   => NULL,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => true,
			'include'    => [],
			'exclude'    => [],
		];

		$this->query_vars = array_merge( $this->default_query_vars, $args );

//		return $this->get_instance( $args );
	}


	public function get_terms() {
		$args = array_merge( $this->default_query_vars, $this->query_vars );

		$clauses = [];

		//check args taxonomy is set and is rray or string
		if ( ! empty( $args['taxonomy'] ) ) {
			if ( is_array( $args['taxonomy'] ) ) {

				$args['taxonomy'] = array_map( 'esc_sql', $args['taxonomy'] );


				$clauses[] = " taxonomy IN (" . implode( ',', $args['taxonomy'] ) . ")";
			} else {
				$clauses[] = " taxonomy = " . esc_sql( $args['taxonomy'] );
			}
		}

		//check if hide_empty = true hide count = 0
		if ( $args['hide_empty'] ) {
			$clauses[] = " count > 0";
		}

		//cehck include term ids if is set any
		if ( is_array( $args['include'] ) && count( $args['include'] ) ) {

			$args['include'] = array_map( 'esc_sql', $args['include'] );

			$clauses[] = " id IN (" . implode( ',', $args['include'] ) . ")";
		}

		//cehck exclude term ids if is set any
		if ( is_array( $args['exclude'] ) && count( $args['exclude'] ) ) {

			$args['exclude'] = array_map( 'esc_sql', $args['exclude'] );


			$clauses[] = " id NOT IN (" . implode( ',', $args['exclude'] ) . ")";
		}

		//search for exact name
		if ( isset( $args['name'] ) && ! empty( $args['name'] ) ) {
			$value     = esc_sql( $args['name'] );
			$clauses[] = " name = '$value'";
		}

		//search for exact slug
		if ( isset( $args['slug'] ) && ! empty( $args['slug'] ) ) {
			$value     = esc_sql( $args['slug'] );
			$clauses[] = " slug = '$value'";
		}

		//seach in terms name and slug
		if ( isset( $args['search'] ) && ! empty( $args['seach'] ) ) {
			$value     = esc_sql( $args['name'] );
			$clauses[] = " name LIKE '%$value%' OR slug LIKE '%$value%'";
		}

		if(isset($args['parent']) && intval($args['parent']) > 0){
			$value = intval($args['parent']);

		}

		echo_pre( $clauses );
	}

	/**
	 * Utility method to retrieve the main instance of the class.
	 *
	 * The instance will be created if it does not exist yet.
	 *
	 * @return Nested_Term_Query The main instance.
	 * @since 5.0.0
	 *
	 */
	public static function get_instance( $args ) {
		if ( NULL === self::$instance ) {
			self::$instance = new self( $args );
		}

		return self::$instance;
	}


	/**
	 * Get max right index stored
	 *
	 * @return int|string
	 */
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
	public function insert( string $name, string $slug, string $taxonomy, int $parent = 0, string $description = NULL, int $term_group = 0, int $count = 0 ) {

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
	private function update_node( int $id, int $left, int $right, int $parent = 0 ) {
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
	private function make_taxonomy_root( string $taxonomy ): array {
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


}