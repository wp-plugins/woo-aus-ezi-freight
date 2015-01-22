<?php if ( ! defined( 'ABSPATH' ) ) exit;

class EFW_settings {
	public $id;
	public $label;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'efw_freight';
		$this->label = __( 'Tick Boxes', 'ezihosting_wc' );


		//add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 21);
		//add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		//add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
//		add_action( 'woocommerce_admin_field_shipping_methods', array( $this, 'shipping_methods_setting' ) );
//		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );

		//ajax
		add_action( 'wp_ajax_efw_create_box', array($this, 'create_box') );
		add_action( 'wp_ajax_efw_update_box', array($this, 'update_box') );
		add_action( 'wp_ajax_efw_delete_box', array($this, 'delete_box') );
	}

	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;

		return $pages;
	}

	/**
	 * Output sections
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}

	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			//'tick_boxes' => __( 'tick boxes', 'ezihosting_wc' ),
		);

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Settings page.
	 *
	 * Handles the display of the main woocommerce settings page in admin.
	 *
	 * @return void
	 */
	public static function output() {
		global $current_section, $current_tab;

		do_action( 'woocommerce_settings_start' );
		wp_enqueue_script( 'default', EFWHelper::plugin_url() . '/assets/admin.js', array( 'jquery', 'jquery-ui-dialog' ), EFWHelper::assets_version(), true );


		// Get current tab/section
		$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

		$list_table = new EFW_Boxes_List_Table();
		$list_table->prepare_items();

		$GLOBALS['hide_save_button'] = true;

		include EFW_PLUGIN_DIR . '/admin/views/html-tick-boxes.php';
	}


	public static function create_box() {
		$response = array(
			'error' => true,
			'msg' => 'invalid request',
		);

		if (  $_POST['name'] ) {
			$box = array(
				'name' => trim($_POST['name']),
				'length' => abs(floatval($_POST['length'])),
				'width' => abs(floatval($_POST['width'])),
				'height' => abs(floatval($_POST['height'])),
				'weight' => abs(floatval($_POST['weight'])),
				'max_weight' => abs(floatval($_POST['max_weight'])),
			);

			if($rate = abs(floatval($_POST['rate']))) {
				$box['flat_rate'] = 1;
				$box['rate'] = $rate;
			}

			EFWBoxes::create($box);

			$response = array(
				'error' => false,
				'msg' => 'create success',
			);
		}


		header( "Content-Type: application/json" );
		echo json_encode($response);
		exit;
	}

	public static function delete_box() {
		$response = array(
			'error' => true,
			'msg' => 'invalid request',
		);

		$id = intval($_POST['id']);
		if (  $id ) {

			EFWBoxes::delete($id);

			$response = array(
				'error' => false,
				'msg' => 'delete success',
			);
		}


		header( "Content-Type: application/json" );
		echo json_encode($response);
		exit;
	}


	public static function update_box() {
		$response = array(
			'error' => true,
			'msg' => 'invalid request',
		);

		$id = intval($_POST['id']);
		if ( $id && $_POST['key']) {
			$key = $_POST['key'];
			if(in_array($key, array('name', 'length', 'width', 'height', 'weight', 'max_weight', 'rate', 'flat_rate'))) {
				$value = $key == 'name' ? trim($_POST['value']) : abs(floatval($_POST['value']));

				$update =  array( $key => $value );
				if($key == 'rate') {
					$update['flat_rate'] = $value ? 1 : 0;
				}
				EFWBoxes::update($id, $update);
			}

			$response = array(
				'error' => false,
				'msg' => 'update success',
			);
		}


		header( "Content-Type: application/json" );
		echo json_encode($response);
		exit;
	}
}
