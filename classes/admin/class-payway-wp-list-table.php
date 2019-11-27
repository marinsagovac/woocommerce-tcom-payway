<?php

class Payway_Wp_List_Table {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_data_list_payway_menu' ) );
	}

	public function add_data_list_payway_menu() {
		add_submenu_page( 'woocommerce', 'PayWay Response Status', 'PayWay Data List', 'manage_options', 'payway-response-data.php', array( $this, 'list_payway_page' ) );
	}

	public function list_payway_page() {
		$listtable = new PayWayData_List_Table();
		$listtable->prepare_items();
		?>
			<div class="wrap">
				<h2>PayWay Data Lists</h2>
				<?php $listtable->display(); ?>
			</div>
		<?php
	}
}

require_once plugin_basename( 'classes/admin/class-paywaydata-list-table.php' );
