<?php
/**
 * Developer : AmirMohammad Torkaman
 * Email : amirtorkaman5204@gmail.com
 * Author Uri : amirtorkaman.ir
 **/

class NEsted_Term_Admin {


	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ], 11 );

		add_action( 'admin_init', [ $this, 'install_nested_terms' ] );
		add_action( 'admin_init', [ $this, 'fixtree' ] );
	}

	public function fixtree() {
		if ( isset( $_POST['nested_term_action'] ) && $_POST['nested_term_action'] == 'fixtree-nested-term' ) {
			$this->set_headers();

			$nested_term = new Nested_Term_Install();
			$nested_term->fix_tree();

			wp_redirect( $_SERVER['HTTP_REFERER'] );
			exit();
		}
	}

	public function install_nested_terms() {
		if ( isset( $_POST['nested_term_action'] ) && $_POST['nested_term_action'] == 'install-nested-term' ) {
			$this->set_headers();

			$nested_term = new Nested_Term_Install();
			$nested_term->install();

			wp_redirect( $_SERVER['HTTP_REFERER'] );
			exit;
		}
	}

	private function set_headers() {
		ignore_user_abort( true );

		set_time_limit( 0 );
		nocache_headers();
	}

	public function add_menu_page() {
		add_menu_page( 'nested terms', 'nested-terms', 'manage_options', 'nested-terms',
			[
				$this,
				'include_menu_page',
			], 'dashicons-image-filter' );
		add_submenu_page( 'nested-terms', 'nested terms', 'nested terms', 'manage_options', 'nested-terms', [
			$this,
			'include_menu_page',
		] );


	}


	public function include_menu_page() {
		include "termlpate/nested-admin.php";
	}

}

new NEsted_Term_Admin();

