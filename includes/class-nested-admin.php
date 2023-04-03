<?php
/**
 * Developer : AmirMohammad Torkaman
 * Email : amirtorkaman5204@gmail.com
 **/

class NestedTermAdmin {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ], 11 );

		add_action( 'admin_init', [ $this, 'installNestedTerms' ] );
		add_action( 'admin_init', [ $this, 'fixTree' ] );
	}

	/**
	 * Set page headers to run in background
	 * @return void
	 */
	private function setHeaders(): void {
		ignore_user_abort( true );

		set_time_limit( 0 );
		nocache_headers();
	}

	/**
	 * Fix Nested terms tree
	 * @return void
	 */
	public function fixTree(): void {
		if ( isset( $_POST['nested_term_action'] ) && $_POST['nested_term_action'] == 'fixtree-nested-term' ) {
			$this->setHeaders();

			$nested_term = new Nested_Term_Install();
			$nested_term->fix_tree();

			wp_redirect( $_SERVER['HTTP_REFERER'] );
			exit();
		}
	}

	/**
	 * Created table and insert terms
	 * @return void
	 */
	public function installNestedTerms(): void {
		if ( isset( $_POST['nested_term_action'] ) && $_POST['nested_term_action'] == 'install-nested-term' ) {
			$this->setHeaders();

			$nested_term = new Nested_Term_Install();
			$nested_term->install();

			wp_redirect( $_SERVER['HTTP_REFERER'] );
			exit;
		}
	}

	/**
	 * Add menu pages to admin sidebar
	 * @return void
	 */
	public function add_menu_page(): void {
		add_menu_page( 'nested terms',
			'nested-terms',
			'manage_options',
			'nested-terms',
			[ $this, 'include_menu_page' ],
			'dashicons-image-filter' );

		add_submenu_page( 'nested-terms',
			'nested terms',
			'nested terms',
			'manage_options',
			'nested-terms',
			[ $this, 'include_menu_page' ] );
	}

	/**
	 * @return void
	 */
	public function include_menu_page(): void {
		include 'template/nested-admin.php';
	}

}

new NestedTermAdmin();

