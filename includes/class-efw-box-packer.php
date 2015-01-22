<?php if ( ! defined( 'ABSPATH' ) ) exit;

class EFWBoxPacker {
	private $boxes = array();
	private $items = array();
	private $packages = array();
	private $un_packages = array();

	public function __construct() {
	}


	public function add_item( $item ) {
		$item['volume'] = $item['width'] * $item['height'] * $item['length'];

		//push down item to fit box
		$dimensions = array( $item['length'], $item['width'], $item['height'] );
		sort( $dimensions );
		$item['length'] = $dimensions[2];
		$item['width']  = $dimensions[1];
		$item['height'] = $dimensions[0];

		$this->items[] = $item;
	}

	public function add_box( $box ) {
		$new_box = new EFWBox( $box );
		$this->boxes[] = $new_box;
	}

	public function packer() {
		$this->packages = array();

		$this->boxes = $this->sort_boxes( $this->boxes );
		if ( ! $this->boxes ) {
			$this->un_packages = $this->items;
			$this->items       = array();
		}

		while ( count( $this->items ) > 0 ) {
			$this->items       = $this->sort_items( $this->items );
			$packages = array();
			$best_package      = '';

			foreach ( $this->boxes as $box ) {
				$packages[] = $box->packer( $this->items );
			}

			// Find the best success rate
			$best_rate = 0;

			foreach ( $packages as $package ) {
				if ( $package->rate > $best_rate ) {
					$best_rate = $package->rate;
				}
			}

			if ( $best_rate == 0 ) {
				$this->un_packages = $this->items;
				$this->items       = array();
			} else {
				// Get smallest box with $best_rate
				$packages = array_reverse( $packages );

				foreach ( $packages as $package ) {
					if ( $package->rate == $best_rate ) {
						$best_package = $package;
						break;
					}
				}

				// Update items array
				$this->items = $best_package->unpacked;

				// Store package
				$this->packages[] = $best_package;
			}
		}

		// Items we cannot pack (by now) get packaged individually
		if ( count($this->un_packages) ) {
			foreach ( $this->un_packages as $item ) {
				$package           = new stdClass();
				$package->id       = false;
				$package->name       = '';
				$package->weight   = $item['weight'];
				$package->length   = $item['length'];
				$package->width    = $item['width'];
				$package->height   = $item['height'];
				$this->packages[]  = $package;
			}
		}
	}

	public function get_packages() {
		return $this->packages;
	}

	private function sort_boxes( $sort ) {
		if ( is_array( $sort ) ) {
			uasort( $sort, array( $this, 'box_sorting' ) );
		}
		return $sort;
	}

	public function box_sorting( $a, $b ) {
		if ( $a->volume == $b->volume ) {
			if ( $a->max_weight == $b->max_weight ) {
				return 0;
			}
			return ( $a->max_weight < $b->max_weight ) ? 1 : -1;
		}
		return ( $a->volume < $b->volume ) ? 1 : -1;
	}


	private function sort_items( $sort ) {
		if ( is_array( $sort ) ) {
			uasort( $sort, array( $this, 'item_sorting' ) );
		}
		return $sort;
	}


	public function item_sorting( $a, $b ) {
		if ( $a['volume'] == $b['volume'] ) {
			if ( $a['weight'] == $b['weight'] ) {
				return 0;
			}
			return ( $a['weight'] < $b['weight'] ) ? 1 : -1;
		}
		return ( $a['volume'] < $b['volume'] ) ? 1 : -1;
	}

}