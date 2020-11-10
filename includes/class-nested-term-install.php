<?php
/**
 * Developer : AmirMohammad Torkaman
 * Email : amirtorkaman5204@gmail.com
 * Author Uri : amirtorkaman.ir
 **/

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

class Nested_Term_Install {

	private $table;

	/**
	 * left index name in table
	 *
	 * @var string $leftName
	 */
	private $leftName = "_lft";

	/**
	 * right index name in table
	 *
	 * @var string $rightName
	 */
	private $rightName = "_rgt";

	/**
	 * Nested_Term_Install constructor.
	 * Initialize table name
	 */
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . "taxonomy_lookup";


	}



	public function install() {

		$max_index_length = 191;
		$query            = "CREATE TABLE {$this->table} (
		term_id bigint(20) unsigned NOT NULL ,
 		name varchar(200) NOT NULL default '',
 		slug varchar(200) NOT NULL default '',
 		{$this->leftName} int(20) NOT NULL default 0,
 		{$this->rightName} int(20) NOT NULL default 0,
 		parent bigint(20) unsigned NOT NULL default 0,
 		taxonomy varchar(32) NOT NULL default '',
 		description longtext NOT NULL,
 		count bigint(20) NOT NULL default 0,
 		term_group bigint(10) NOT NULL default 0,
 		meta JSON default NULL,
		PRIMARY KEY id (id),
		KEY slug (slug($max_index_length)),
 		KEY name (name($max_index_length))
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		@dbDelta( $query );

		$this->move_terms();
	}

	/**
	 * Move terms from wordpress terms to nested set model
	 */
	public function move_terms() {
		global $wpdb;

		session_start();
		session_destroy();

		$url    = "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
		$limit  = 10;
		$offset = $_GET['offset'] ?? 0;

		$query = "SELECT * from {$wpdb->terms} as t inner join {$wpdb->term_taxonomy} as tt on t.term_id = tt.term_id ";

		$terms = $wpdb->get_results( $query );

		$nested = new Nested_Term_Query();

		/** @var WP_Term $term */
		foreach ( $terms as $term ) {

			$metas = [];
			$meta  = $wpdb->get_results( "SELECT meta_key, meta_value from {$wpdb->termmeta} where term_id  = {$term->term_id}" );
			foreach ( $meta as $item ) {
				$metas[ $item->meta_key ] = $item->meta_value;
			}

			$nested->insert( $term->term_id, $term->name, $term->slug, $term->taxonomy, $term->parent, $term->description, $term->term_group
				, $term->count, $metas );

		}

		foreach ( $terms as $term ) {
			$chidlren = $wpdb->get_results( "SELECT * from {$this->table} where parent = {$term->term_id}" );
			foreach ( $chidlren as $child ) {
				$nested->re_insert( $child->term_id, $child->parent );
			}
		}


	}

	/**
	 * empty table and fill it again with terms
	 */
	public function re_intsall() {
		global $wpdb;
		$query = "TRUNCATE TABLE `$this->table`";

		try {

			$wpdb->query( $query );
		} catch ( Exception $ex ) {
			echo $ex->getMessage();

			return false;
		}

		$this->move_terms();
	}


	public function fix_tree() {

		global $wpdb;
		$terms = $wpdb->get_results( "SELECT * FROM {$this->table}" );

		$nested = new Nested_Term_Query();
		foreach ( $terms as $term ) {
			$nested->re_insert( $term->term_id, $term->parent );
		}

	}

}
