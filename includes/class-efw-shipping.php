<?php
class EFWShipping extends WC_Shipping_Method {
	private $config;
	public function __construct() {
		$this->id                 = 'efw_shipping';
		$this->method_title       = __('Woo Aus EZi Freight', 'ezihosting_wc' );
		$this->method_description = '';


		$this->init();
	}

	public function init() {

		$this->config = EFWHelper::get_config();
		$this->title              = $this->config->efw_method_title;
		$this->enabled = $this->config->efw_enable_tb ? 'yes' : 'no';
		if($this->config->efw_ship_to_countries == 'specific') {
			$this->availability = 'including';
			$this->countries = $this->config->efw_countries ? $this->config->efw_countries : array();
		} else {
			$this->availability = '';
			$this->countries = array();
		}

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}


	public function admin_options() {
		$wc_countries = new WC_Countries();
		$countries = $wc_countries->get_countries();

		$config = $this->config;

		wp_enqueue_script( 'default', EFWHelper::plugin_url() . '/assets/admin.js', array( 'jquery', 'jquery-ui-dialog' ), EFWHelper::assets_version(), true );

		$list_table = new EFW_Boxes_List_Table();
		$list_table->prepare_items();


		include EFW_PLUGIN_DIR . '/admin/views/html-setting.php';
	}

	public function process_admin_options() {
		if ( ! empty( $_POST ) ) {
			$post = $_POST['efw_config'];
			$post['efw_enable_tb'] = $post['efw_enable_tb'] == 1 ? true : false;
			$post['efw_show_tb'] = $post['efw_show_tb'] == 1 ? true : false;
			$post['efw_debug'] = $post['efw_debug'] == 1 ? true : false;
			$post['efw_receipts'] = $post['efw_receipts'] == 1 ? true : false;
			$post['efw_services_limit'] = intval($post['efw_services_limit']);
			$post['efw_countries'] = $post['efw_ship_to_countries'] == 'all' ? array() : $post['efw_countries'];

			EFWHelper::set_config($post);

			unset($_POST);
		}
	}

	public function calculate_shipping( $package ) {
		$valid_license = EFWHelper::validLicense($this->config->efw_license_key);
		if($valid_license !== true) {
			$valid_tip = 'Woo Aus EZi Freight trial version! Please visit  <a href="http://www.ezihosting.com/woocommerce-australian-freight-extension/">EZiHosting</a> to obtain your license. Licensed plugins won’t have this message plus you will receive 1 year priority support and free updates.<br />';
			wc_add_notice( $valid_tip , 'notice' );
			$this->debug_info("License valid error message: {$valid_license}<br />", 'notice');
		}


		$this->debug_info( 'calculate_shipping' );
		try {
			$response = $this->shipping_package($package);
			$rates = $response['rates'];
		} catch(EFWException $e) {
			$this->debug_info( $e->getMessage(), 'error' );
			return false;
		}

		$this->debug_info( 'Response from Interparcel<br /><pre>'.htmlspecialchars($response['original']).'</pre>' );

		if(!$rates) {
			$this->debug_info( 'There is no available services' );
			return false;
		}

		$service = '';
		$cheapest = $rates[0];
		foreach($rates as $r) {
			if($r['total'] <= $cheapest['total'])
				$cheapest = $r;

			$service .= 'service : '.$r['name'].', price :'.$r['price'].', tax :'.$r['tax'].', total :'.$r['total'].'<br />';
		}
		$this->debug_info( 'All available services: <br />'.$service );

		if($this->config->efw_rate_offer == 'all') {

			foreach($rates as $k=>$r) {

				if($this->config->efw_services_limit && $k>($this->config->efw_services_limit - 1))
					break;

				$name = $r['transit_cover'] ? $r['name'].'(Transit Cover: $'.$r['transit_cover'].')' : $r['name'];
				$rate = array(
					'id'        => md5($name),          // ID for the rate
					'label'     => $name,          // Label for the rate
					'cost'      => $r['total'] + $this->config->efw_handling_cost,         // Amount or array of costs (per item shipping)
					//'taxes'     => 20,        // Pass taxes, nothing to have it calculated for you, or 'false' to calc no tax
					'calc_tax'  => 'per_order'  // Calc tax per_order or per_item. Per item needs an array of costs
				);
				$this->add_rate( $rate );
			}
		} else {
			//show cheapest
			$name = $cheapest['transit_cover'] ? $cheapest['name'].'(Transit Cover: $'.$cheapest['transit_cover'].')' : $cheapest['name'];
			$rate = array(
				'id' => $this->id,
				'label' => $name,
				'cost' => $cheapest['total'] + $this->config->efw_handling_cost,
				//'taxes' => false,
				'calc_tax' => 'per_order'
			);
			$this->add_rate( $rate );
			$this->debug_info( 'cheapest services: <br />service : '.$cheapest['name'].', price :'.$cheapest['price'].', tax :'.$cheapest['tax'].', total :'.$cheapest['total'] );
		}

		if($this->config->efw_handling_cost)
			$this->debug_info( 'The handling cost $'.$this->config->efw_handling_cost.' you set in admin panel will auto add into rate' );
	}


	private function shipping_package( $package ) {
		switch($this->config->efw_packing_method) {
			case 'all':
				$shipping_package = $this->all_shipping($package);
				break;

			case 'individual':
				$shipping_package = $this->individual_shipping($package);
				break;

			case 'box':
				$this->debug_info( 'use box packing method' );

				$shipping_package = $this->box_shipping($package);

				if(strtolower($package['destination']['country']) == 'au' && count($shipping_package) == 1 && $shipping_package[0]->id && $shipping_package[0]->flat_rate) {
					$this->debug_info( 'use box flat rate' );
					return array(
						'original' => 'This information is got from tick box\'s flat rate',
						'rates' => array(
							array(
								'name' => 'Flat Rate Shipping',
								'type' => 'flat_rate',
								'carrier' => '',
								'price' => $shipping_package[0]->flat_rate,
								'tax' => 0,
								'total' => $shipping_package[0]->flat_rate,
								'transit_cover' => 0,
							)
						)
					);
				}

				break;
		}

		return EFWHelper::get_au_rates($shipping_package, $this->config, $package['destination']);
	}

	private function all_shipping($package) {
		$shipping_package = array();

		foreach ( $package['contents'] as $k => $v ) {
			if ( ! $v['data']->needs_shipping() ) {
				$this->debug_info( sprintf( __( 'Product #%d is missing virtual.', 'ezihosting_wc' ), $k ), 'error' );
				continue;
			}

			if ( ! $v['data']->get_weight() ) {
				$this->debug_info( sprintf( __( 'Product #%d do not have height attribute', 'ezihosting_wc' ), $k ), 'error' );
				return;
			}

			for($i=1;$i<=$v['quantity'];$i++) {
				$shipping_package[] = (object) array(
					'weight' => wc_get_weight( $v['data']->get_weight(), 'kg' ),
					'length' => wc_get_dimension( $v['data']->length, 'cm' ),
					'height' => wc_get_dimension( $v['data']->height, 'cm' ),
					'width' => wc_get_dimension( $v['data']->width, 'cm' ),
				);
			}
		}

		return $shipping_package;
	}

	private function individual_shipping($package) {
		//there is no difference between all shipping and individual shipping
		return $this->all_shipping($package);
	}

	private function box_shipping($package) {
		//$condition = strtolower($package['destination']['country']) == 'au' ? '' : 'AND flat_rate = 0';

		//$boxes = EFWBoxes::findAll($condition);
		$boxes = EFWBoxes::findAll();

		if(count($boxes) <= 0)
			return array();

		$box_packer = new EFWBoxPacker();
		foreach ( $boxes as $box ) {
			$box_packer->add_box( $box );
		}

		foreach ( $package['contents'] as $k => $v ) {

			if ( ! $v['data']->needs_shipping() ) {
				$this->debug_info( sprintf( __( 'Product #%d is missing virtual.', 'ezihosting_wc' ), $k ), 'error' );
				continue;
			}

			if ( $v['data']->length && $v['data']->height && $v['data']->width && $v['data']->weight ) {
				for($i=1;$i<=$v['quantity'];$i++) {
					$item = array(
						'weight' => wc_get_weight( $v['data']->get_weight(), 'kg' ),
						'length' => wc_get_dimension( $v['data']->length, 'cm' ),
						'height' => wc_get_dimension( $v['data']->height, 'cm' ),
						'width' => wc_get_dimension( $v['data']->width, 'cm' ),
					);
					$box_packer->add_item($item);
				}
			} else {
				$this->debug_info( sprintf( __( 'Product #%d do not have shipping attribute.', 'ezihosting_wc' ), $k ), 'error' );
				return;
			}
		}

		$box_packer->packer();

		$packages = $box_packer->get_packages();
		$un_packed = 0;
		$package_tips = '';
		foreach($packages as $p) {
			if($p->id) {
				$package_tips .= sprintf( __( 'Tick box id is #%d, box name is %s', 'ezihosting_wc' ), $p->id, $p->name ).'<br />';
			} else {
				$un_packed++;
			}
		}

		if($package_tips)
			$this->debug_info( $package_tips );

		if($un_packed)
			$this->debug_info( sprintf( __( '%d product(s) un packed', 'ezihosting_wc' ), $un_packed ) );

		return $packages;
	}

	private function debug_info($txt, $type = 'notice') {
		if ( $this->config->efw_debug ) {
			wc_add_notice( $txt, $type );
		}
	}
}