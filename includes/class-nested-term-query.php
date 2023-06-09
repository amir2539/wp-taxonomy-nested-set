<?php

defined( 'ABSPATH' ) || exit;

class Nested_Term_Query {

	/**
	 * Container for the main instance of the class.
	 *
	 * @since 5.0.0
	 * @var Nested_Term_Query|null
	 */
	private static $instance = null;

	/**
	 * nested set table name
	 * wpdb->prefix . nested_set
	 *
	 * @var string $table
	 */
	private $table;

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

	private $field_set = [];

	/**
	 * Nested_Term_Query constructor.
	 *
	 * setup the term queries passed
	 *
	 * @param array $args {
	 *
	 * Optional. Array or query string of term query parameters. Default empty.
	 *
	 * @type string|array $taxonomy Taxonomy name, or array of taxonomies, to which results should
	 *                                                be limited.
	 * @type string $orderby Field(s) to order terms by. Accepts:
	 *                                                - term fields ('name', 'slug', 'term_group', 'term_id', 'id',
	 *                                                  'description', 'parent', 'term_order'). Unless `$object_ids`
	 *                                                  is not empty, 'term_order' is treated the same as 'term_id'.
	 *                                                - 'count' for term taxonomy count.
	 *                                                - 'include' to match the 'order' of the $include param.
	 *                                                - 'slug__in' to match the 'order' of the $slug param.
	 *                                                - 'meta_value', 'meta_value_num'.
	 *                                                - the value of `$meta_key`.
	 *                                                - the array keys of `$meta_query`.
	 *                                                - 'none' to omit the ORDER BY clause.
	 *                                                Defaults to 'name'.
	 * @type string $order Whether to order terms in ascending or descending order.
	 *                                                Accepts 'ASC' (ascending) or 'DESC' (descending).
	 *                                                Default 'ASC'.
	 * @type bool|int $hide_empty Whether to hide terms not assigned to any posts. Accepts
	 *                                                1|true or 0|false. Default 1|true.
	 * @type array|string $include Array or comma/space-separated string of term IDs to include.
	 *                                                Default empty array.
	 * @type array|string $exclude Array or comma/space-separated string of term IDs to exclude.
	 *                                                If $include is non-empty, $exclude is ignored.
	 *                                                Default empty array.
	 * @type int|string $number Maximum number of terms to return. Accepts ''|0 (all) or any
	 *                                                positive number. Default ''|0 (all). Note that $number may
	 *                                                not return accurate results when coupled with $object_ids.
	 *                                                See #41796 for details.
	 * @type int $offset The number by which to offset the terms query. Default empty.
	 * @type string $fields Term fields to query for. Accepts:
	 *                                                - 'all' Returns an array of complete term objects (`WP_Term[]`).
	 *                                                - 'all_with_object_id' Returns an array of term objects
	 *                                                  with the 'object_id' param (`WP_Term[]`). Works only
	 *                                                  when the `$object_ids` parameter is populated.
	 *                                                - 'ids' Returns an array of term IDs (`int[]`).
	 *                                                - 'tt_ids' Returns an array of term taxonomy IDs (`int[]`).
	 *                                                - 'names' Returns an array of term names (`string[]`).
	 *                                                - 'slugs' Returns an array of term slugs (`string[]`).
	 *                                                - 'count' Returns the number of matching terms (`int`).
	 *                                                - 'id=>parent' Returns an associative array of parent term IDs,
	 *                                                   keyed by term ID (`int[]`).
	 *                                                - 'id=>name' Returns an associative array of term names,
	 *                                                   keyed by term ID (`string[]`).
	 *                                                - 'id=>slug' Returns an associative array of term slugs,
	 *                                                   keyed by term ID (`string[]`).
	 *                                                Default 'all'.
	 * @type bool $count Whether to return a term count. If true, will take precedence
	 *                                                over `$fields`. Default false.
	 * @type string|array $name Optional. Name or array of names to return term(s) for.
	 *                                                Default empty.
	 * @type string|array $slug Optional. Slug or array of slugs to return term(s) for.
	 *                                                Default empty.
	 * @type int|array $term_id Optional. Term taxonomy ID, or array of term taxonomy IDs,
	 *                                                to match when querying terms.
	 * @type string $search Search criteria to match terms. Will be SQL-formatted with
	 *                                                wildcards before and after. Default empty.
	 * @type string $name__like Retrieve terms with criteria by which a term is LIKE
	 *                                                `$name__like`. Default empty.
	 * @type string $description__like Retrieve terms where the description is LIKE
	 *                                                `$description__like`. Default empty.
	 * @type bool $pad_counts Whether to pad the quantity of a term's children in the
	 *                                                quantity of each term's "count" object variable.
	 *                                                Default false.
	 * @type string $get Whether to return terms regardless of ancestry or whether the
	 *                                                terms are empty. Accepts 'all' or empty (disabled).
	 *                                                Default empty.
	 * @type int $child_of Term ID to retrieve child terms of. If multiple taxonomies
	 *                                                are passed, $child_of is ignored. Default 0.
	 * @type int|string $parent Parent term ID to retrieve direct-child terms of.
	 *                                                Default empty.
	 * @type bool $childless True to limit results to terms that have no children.
	 *                                                This parameter has no effect on non-hierarchical taxonomies.
	 *                                                Default false.
	 * @type array $meta_query Optional. Meta query clauses to limit retrieved terms by.
	 *                                                See `WP_Meta_Query`. Default empty.
	 * @type string $meta_key Limit terms to those matching a specific metadata key.
	 *                                                Can be used in conjunction with `$meta_value`. Default empty.
	 * @type string $meta_value Limit terms to those matching a specific metadata value.
	 *                                                Usually used in conjunction with `$meta_key`. Default empty.
	 * @type string $meta_type MySQL data type that the `$meta_value` will be CAST to for
	 *                                                comparisons. Default empty.
	 * @type string $meta_compare Comparison operator to test the 'meta_value'. Default empty.
	 *
	 * }
	 */
	public function __construct( array $args = [] ) {
		global $wpdb;

		$this->table = $wpdb->prefix . "taxonomy_lookup";

		$this->default_query_vars = [
			'taxonomy'          => null,
			'orderby'           => 'name',
			'order'             => 'ASC',
			'hide_empty'        => true,
			'include'           => [],
			'exclude'           => [],
			'nmae'              => '',
			'slug'              => '',
			'name__like'        => '',
			'search'            => '',
			'description__like' => '',
			'parent'            => 0,
			'child_of'          => 0,
			'childless'         => false,
			'number'            => 0,
			'offset'            => 0,
			'fields'            => 'all',
			'meta_key'          => '',
			'meta_value'        => '',
			'meta_compare'      => '',
			'count'             => false,
		];

		//define table fields that when fiekds is set should be in this
		$this->field_set = [
			'term_id',
			'name',
			'slug',
			'description',
			$this->leftName,
			$this->rightName,
			'parent',
			'taxonomy',
			'count',
			'term_group',
		];

		$this->query_vars = array_merge( $this->default_query_vars, $args );
	}


	/**
	 * @param array $args
	 */
	public function get_terms( $args = [] ) {
		global $wpdb;

		$args = count( $args ) ? $args : array_merge( $this->default_query_vars, $this->query_vars );

		// init clauses and specify to don't get the taxonomy root
		$clauses = [
			'parent <> 0',
		];

		$args = apply_filters( 'nested_pre_get_terms', $args );

		// Check args taxonomy is set and is array or string
		if ( ! empty( $args['taxonomy'] ) ) {
			if ( is_array( $args['taxonomy'] ) ) {
				$args['taxonomy'] = array_map( 'esc_sql', $args['taxonomy'] );
			} else {
				$args['taxonomy'] = array_map( 'esc_sql', explode( ',', $args['taxonomy'] ) );
			}

			$clauses[] = " taxonomy IN ('" . implode( "','", $args['taxonomy'] ) . "')";
		}

		// Check if hide_empty = true hide count = 0
		if ( $args['hide_empty'] ) {
			$clauses[] = " count > 0";
		}

		// Check include term ids if is set any
		if ( is_array( $args['include'] ) ) {
			if ( is_array( $args['include'] && count( $args['include'] ) ) ) {
				$args['include'] = array_map( 'esc_sql', $args['include'] );

				$clauses[] = " term_id IN (" . implode( ',', $args['include'] ) . ")";
			} elseif ( ! empty( $args['include'] ) ) {
				$args['include'] = esc_sql( $args['include'] );

				$clauses[] = " term_id IN (" . $args['include'] . ")";
			}
		}

		// Check exclude term ids if is set any
		if ( is_array( $args['exclude'] ) && count( $args['exclude'] ) ) {
			if ( is_array( $args['exclude'] ) && count( $args['include'] ) ) {
				$args['exclude'] = array_map( 'esc_sql', $args['exclude'] );

				$clauses[] = " term_id NOT IN (" . implode( ',', $args['exclude'] ) . ")";
			} elseif ( ! empty( $args['exclude'] ) ) {
				$args['exclude'] = esc_sql( $args['exclude'] );

				$clauses[] = " term_id NOT IN (" . $args['exclude'] . ")";
			}
		}

		//search for exact name
		if ( isset( $args['name'] ) ) {
			if ( is_array( $args['name'] ) ) {
				$args['name'] = array_map( 'esc_sql', $args['name'] );
				$clauses[]    = " name IN (' " . implode( "','", $args['name'] ) . "')";
			} elseif ( ! empty( $args['name'] ) ) {
				$value     = esc_sql( $args['name'] );
				$clauses[] = " name = '$value'";
			}
		}

		//search for exact slug
		if ( isset( $args['slug'] ) && ! empty( $args['slug'] ) ) {
			if ( is_array( $args['slug'] ) ) {
				$args['slug'] = array_map( 'esc_sql', $args['slug'] );
				$clauses[]    = " slug IN (' " . implode( "','", $args['slug'] ) . "')";
			} elseif ( ! empty( $args['slug'] ) ) {
				$value     = esc_sql( $args['slug'] );
				$clauses[] = " slug = '$value'";
			}
		}

		// Search in terms name and slug
		if ( isset( $args['name__like'] ) && ! empty( $args['name__like'] ) ) {
			$value     = esc_sql( $args['name__like'] );
			$clauses[] = " name LIKE '%$value%'";
		} elseif ( isset( $args['search'] ) && ! empty( $args['search'] ) ) {
			// Search in terms name and slug
			$value     = esc_sql( $args['search'] );
			$clauses[] = " name LIKE '%$value%' OR slug LIKE '%$value%'";
		}


		//search for description
		if ( isset( $args['description__like'] ) && ! empty( $args['description__like'] ) ) {
			$value     = esc_sql( $args['description__like'] );
			$clauses[] = " description LIKE '%$value%' ";
		}

		//get children of given parent if found
		$parent_set = false;
		if ( isset( $args['parent'] ) && intval( $args['parent'] ) > 0 ) {
			$value  = intval( $args['parent'] );
			$parent = nested_get_term( $value );
			//check term found
			if ( $parent ) {
				$clauses[] = "{$parent->leftName} between {$parent->left} and {$parent->right} 
				and {$parent->rightName} between {$parent->left} and {$parent->right}";

				$parent_set = true;
			}
		}

		// Parent overrides child_of then use parent_set flag
		// Child_of means the adjacent parent of term should be this
		if ( isset( $args['child_of'] ) && ! $parent_set && intval( $args['child_of'] ) > 0 ) {
			$value     = intval( $args['child_of'] );
			$clauses[] = " parent = $value";
		}

		// Check if want a leaf term with no child
		// If difference between left and right is 1 then its leaf
		if ( isset( $args['childless'] ) && $args['childless'] ) {
			$clauses[] = " {$this->leftName} = {$this->rightName}-1 ";
		}


		// Determine order of terms
		$order_by = ' order by name';
		if ( isset( $args['orderby'] ) && ! empty( $args['orderby'] ) ) {
			$order_by = " order by " . esc_sql( $args['orderby'] );
		}

		$order = 'ASC';
		if ( isset( $args['order'] ) && ! empty( $args['order'] ) ) {
			$order = esc_sql( $args['order'] );
		}

		// Determine how many terms to return
		$limit = '';
		if ( isset( $args['number'] ) && intval( $args['number'] ) > 0 ) {
			$limit = " limit " . intval( $args['number'] );
		}
		$offset = '';
		if ( isset( $args['offset'] ) && intval( $args['offset'] ) > 0 ) {
			$offset = "offset " . intval( $args['offset'] );
		}

		// Determine which field(s) to return
		$field = '*';
		if ( isset( $args['fields'] ) && ! empty( $args['fields'] ) ) {
			$value = esc_sql( $args['fields'] );

			if ( $value == 'all' ) {
				$field = '*';
			} elseif ( $value == 'ids' || $value == 'tt_ids' ) {
				$field = 'term_id';
			} elseif ( $value == 'names' ) {
				$field = 'name';
			} elseif ( $value == 'slugs' ) {
				$field = 'slug';
			} elseif ( $value == 'count' ) {
				$field = 'count';
			} elseif ( in_array( $value, $this->field_set ) ) {
				$field = $value;
			}
		}


		// Check for metas
		if ( isset( $args['meta_key'], $args['meta_value'] ) && ! empty( $args['meta_key'] ) && ! empty( $args['meta_value'] ) ) {
			$compare = "=";
			$key     = esc_sql( $args['meta_key'] );
			$value   = esc_sql( $args['meta_value'] );

			if ( isset( $args['meta_compare'] ) && ! empty( $args['meta_compare'] ) ) {
				$operands = [ '=', '<>', '>=', '<=', '>=', '>', '<' ];

				if ( in_array( $args['meta_compare'], $operands ) ) {
					$compare = esc_sql( $args['meta_compare'] );
				}
			}

			$clauses[] = " JSON_EXTRACT(`meta`, '$.{$key}') {$compare} {$value} ";
		}


		// If fields not equal to all the get col
		$get_function = "get_results";
		if ( $field != '*' ) {
			$get_function = "get_col";
		}
		// Count arg override fields
		if ( isset( $args['count'] ) && $args['count'] ) {
			$field = "count";
		}

		$clause = implode( ' AND ', $clauses );
		$query  = "SELECT {$field} from {$this->table} where {$clause} {$order_by} {$order} {$limit} {$offset} ";


		// Execute query
		$terms = $wpdb->$get_function( $query );

		// Check if meta is on result decode it
		if ( $field == '*' ) {
			foreach ( $terms as &$term ) {
				$term->meta = json_decode( $term->meta );
			}
		}

		// Check if fields is one of associative fields change result array
		$associative_fields = [
			'id=>parent' => 'parent',
			'id=>name'   => 'name',
			'id=>slug'   => 'slug',
		];

		if ( array_key_exists( $args['field'], $associative_fields ) ) {
			$result = [];


			/** @var Nested_Term $term */
			foreach ( $terms as $term ) {
				$result[ $term->term_id ] = $term->{$associative_fields[ $args['field'] ]};
			}
			$terms = $result;
		}

		if ( $args['field'] == "all_with_object_id" ) {
			foreach ( $terms as &$term ) {
				$term_id = $term->term_id;
				$term    = (array) $term;

				$object_ids = $wpdb->get_col( "SELECT object_id from {$wpdb->term_relationships} where term_taxonomy_id = {$term_id}" );

				$term['object_ids'] = $object_ids;
				$term               = (object) $term;
			}
		}

		do_action( 'nested_after_get_terms', $terms );

		return $terms;
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
	public static function get_instance( $args ): ?Nested_Term_Query {
		if ( null === self::$instance ) {
			self::$instance = new self( $args );
		}

		return self::$instance;
	}

	/**
	 * Get max right index stored
	 *
	 * @return int
	 */
	private function get_max(): int {
		global $wpdb;

		$max = $wpdb->get_var( "SELECT max({$this->rightName}) as aggregate from {$this->table}" );

		return $max ?? 0;
	}

	/**
	 * @param string $taxonomy taxonomy name
	 *
	 * @return bool false when taxonomy does not exist
	 *              true when taxonomy exists
	 */
	public function taxonomy_exists( string $taxonomy ): bool {
		global $wpdb;

		$taxonomy = $wpdb->get_var( "SELECT taxonomy from {$this->table} where taxonomy = '{$taxonomy}' LIMIT 1" );

		return ! is_null( $taxonomy );
	}

	private function term_exists( int $term_id ): bool {
		global $wpdb;

		$term = $wpdb->get_row( "SELECT term_id from {$this->table} where term_id = {$term_id}" );

		return ! is_null( $term );
	}

	/**
	 * @param int $term_id
	 * @param string $name
	 * @param string $slug
	 * @param string $taxonomy
	 * @param int $parent
	 *
	 * @param string|null $description
	 * @param int $term_group
	 *
	 * @param int $count
	 *
	 * @param array|null $meta
	 *
	 * @return bool|false|int returns id of inserted node
	 *                        if there is error in database returns false
	 */
	public function insert(
		int $term_id,
		string $name,
		string $slug,
		string $taxonomy,
		int $parent = 0,
		string $description = null,
		int $term_group = 0,
		int $count = 0,
		array $meta = null
	): bool|int {
		global $wpdb;

		if ( $this->term_exists( $term_id ) ) {
			return false;
		}

		$max = $this->get_max();

		$left  = $max + 1;
		$right = $max + 2;

		if ( ! is_null( $meta ) ) {
			$meta = json_encode( $meta );
		}

		$wpdb->insert( $this->table, [
			'term_id'        => $term_id,
			'name'           => $name,
			'slug'           => $slug,
			'taxonomy'       => $taxonomy,
			'parent'         => $parent,
			$this->leftName  => $left,
			$this->rightName => $right,
			'description'    => $description,
			'term_group'     => $term_group,
			'meta'           => $meta,
			'count'          => $count,
		] );


		//get parent left and update it
		$parent_left = $this->get_parent_left( $parent );

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
			file_put_contents( __DIR__ . '/logs/query.log',
				json_encode( [
					'query' => $query,
					'error' => $wpdb->last_error,
				], JSON_PRETTY_PRINT ) . PHP_EOL,
				FILE_APPEND );

			return false;
		}

		// update node
		$this->update_node( $term_id, ( $left - $diff ), ( $right - $diff ), $parent );

		return $term_id;
	}


	/**
	 * @param int $id
	 * @param int $left
	 * @param int $right
	 *
	 * @param int $parent
	 *
	 * @return void
	 */
	private function update_node( int $id, int $left, int $right, int $parent = 0 ): void {
		global $wpdb;

		$wpdb->update( $this->table, [
			$this->leftName  => $left,
			$this->rightName => $right,
			'parent'         => $parent,
		], [
			'term_id' => $id,
		] );
	}

	/**
	 * @param int $parent
	 *
	 * @return int left index of parent
	 */
	private function get_parent_left( int $parent ): int {
		global $wpdb;

		$left = $wpdb->get_var( "SELECT {$this->leftName} from {$this->table} where term_id = {$parent}" );

		return $left ?? 0;
	}


	/**
	 * when update parent node we have to soft_delete (remove left and right) node and re-insert it
	 *
	 * @param int $term_id
	 * @param int|null $new_parent
	 *
	 * @return bool|int
	 */
	public function re_insert( int $term_id, int $new_parent ): bool|int {
		global $wpdb;
		nested_get_term( $term_id );

		$max = $this->get_max();

		$left  = $max + 1;
		$right = $max + 2;

		//make left and right 0
		$wpdb->update( $this->table, [
			$this->leftName  => $left,
			$this->rightName => $right,
		], [
			'term_id' => $term_id,
		] );

		if ( $new_parent ) {
			//get parent left and update it
			$parent_left = $this->get_parent_left( $new_parent );

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
				file_put_contents( __DIR__ . '/logs/query.log',
					json_encode( [
						'query' => $query,
						'error' => $wpdb->last_error,
					], JSON_PRETTY_PRINT ) . PHP_EOL,
					FILE_APPEND );

				return false;
			}

			// update node
			$this->update_node( $term_id, ( $left - $diff ), ( $right - $diff ), $new_parent );

			//update all children
			$children = $wpdb->get_results( "SELECT * from {$this->table} where parent = {$term_id}" );

			foreach ( $children as $child ) {
				$this->re_insert( $child->term_id, $term_id );
			}
		}

		return $term_id;
	}

	/**
	 * Delete node and fix left and right indexes
	 *
	 * @param int $term_id
	 *
	 * @return int|bool
	 * @global    $wpdb
	 */
	public function delete_node( int $term_id ): int|bool {
		global $wpdb;

		$term        = nested_get_term( $term_id );
		$term_parent = $term->parent;

		// If it's a parent node make it's children parent to it's parent
		$wpdb->delete( $this->table, [
			'term_id' => $term_id,
		] );

		return $wpdb->update( $this->table, [
			'parent' => $term_parent,
		], [
			'parent' => $term_id,
		] );
	}

	/**
	 * get ancestors of giver term id
	 *
	 * @param int $term_id
	 *
	 * @return array return array of Nested_term objects
	 *
	 * @global    $wpdb
	 */
	public function get_ancestors( int $term_id ): array {
		global $wpdb;

		$terms = new Nested_Term();

		$term = $terms->get_instance( $term_id );

		$query     = "SELECT * FROM {$this->table} where {$this->leftName} < {$term->left} AND {$this->rightName} > {$term->right}";
		$ancestors = $wpdb->get_results( $query );

		$result = [];
		foreach ( $ancestors as $ancestor ) {
			$ancestor          = (array) $ancestor;
			$ancestor['left']  = $ancestor[ $this->leftName ];
			$ancestor['right'] = $ancestor[ $this->rightName ];

			$ancestor  = (object) $ancestor;
			$result[] = $ancestor;
		}

		return $result;
	}

}