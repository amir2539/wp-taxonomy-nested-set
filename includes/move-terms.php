<?php
/**
 * Developer : AmirMohammad Torkaman
 * Email : amirtorkaman5204@gmail.com
 * Author Uri : amirtorkaman.ir
 **/

defined( 'ABSPATH' ) || exit;

function nested_move_terms() {
	$install = new Nested_Term_Install();
	$install->move_terms();
	unset($install);
}