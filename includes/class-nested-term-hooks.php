<?php
/**
 * Developer : AmirMohammad Torkaman
 * Email : amirtorkaman5204@gmail.com
 **/

class Nested_Term_Hooks {

	public function __construct() {


		add_action( 'created_term', [ $this, 'create_term' ], 10, 3 );
		add_action( 'edited_term', [ $this, 'edit_term' ], 10, 3 );
		add_action( 'delete_term', [ $this, 'delete_term' ], 10, 5 );

		add_filter( 'woocommerce_get_product_subcategories_cache_key', [
			$this,
			'woocommerce_get_product_subcategories_cache_key',
		], 10, 2 );

	}

	/**
	 * calls after new term created
	 *
	 * @param int    $term_id
	 * @param int    $tt_id
	 * @param string $taxonomy
	 */
	public function create_term( int $term_id, int $tt_id, string $taxonomy ) {
		global $wpdb;

		$term = get_term( $term_id );

		$metas = [];
		$meta  = $wpdb->get_results( "SELECT meta_key, meta_value from {$wpdb->termmeta} where term_id  = {$term->term_id}" );
		foreach ( $meta as $item ) {
			$metas[ $item->meta_key ] = $item->meta_value;
		}

		$nested = new Nested_Term_Query();
		$resutl = $nested->insert( $term->term_id, $term->name, $term->slug, $term->taxonomy, $term->parent, $term->description, $term->term_group, 0, $metas );
		unset( $nested );
	}

	/**
	 * @param int    $term_id
	 * @param int    $tt_id
	 * @param string $taxonomy
	 */
	public function edit_term( int $term_id, int $tt_id, string $taxonomy ) {
		$term = get_term( $term_id );

		nested_update_term( $term_id, (array) $term );
	}

	public function delete_term( $term_id, $tt_id, $taxonomy, $deleted_term, $object_ids ) {

		nested_delete_term( $term_id );
	}



	public function woocommerce_get_product_subcategories_cache_key( $key, $parent_id ) {

		$data = mcache()->get( $key );

		if ( $data === false ) {

			$nested = new Nested_Term();
			$data   = apply_filters('woocommerce_product_subcategories_args',$nested->get_all_children( $parent_id ));

//			$data = get_categories(
//				apply_filters(
//					'woocommerce_product_subcategories_args',
//					array(
//						'parent'       => $parent_id,
//						'hide_empty'   => 0,
//						'hierarchical' => 1,
//						'taxonomy'     => 'product_cat',
//						'pad_counts'   => 1,
//					)
//				)
//			);

			mcache()->set( $key, $data, DAY_IN_SECONDS );
		}

		wp_cache_set( $key, $data, 'product_cat' );

		return $key;
	}


}


new Nested_Term_Hooks();

