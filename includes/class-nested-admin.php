<?php
/**
 * Developer : AmirMohammad Torkaman
 * Email : amirtorkaman5204@gmail.com
 * Author Uri : amirtorkaman.ir
 **/

class NEsted_Term_Admin {


	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ], 11 );
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