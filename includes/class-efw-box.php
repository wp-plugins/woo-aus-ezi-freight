<?php if ( ! defined( 'ABSPATH' ) ) exit;

class EFWBox {
	public $id;
	public $name;
	public $length;
	public $width;
	public $height;
	public $weight;
	public $volume;
	public $max_weight;
	public $flat_rate;

	public function __construct( $box ) {
		$this->id = $box->id;
		$this->name = $box->name;

		$dimensions = array( $box->length, $box->width, $box->height );
		sort( $dimensions );
		$this->length = $dimensions[2];
		$this->width  = $dimensions[1];
		$this->height = $dimensions[0];

//		$this->length = $box->length;
//		$this->height = $box->height;
//		$this->width = $box->width;
		$this->weight = $box->weight;
		$this->max_weight = $box->max_weight;
		$this->flat_rate = $box->flat_rate ? $box->rate : 0;
		$this->volume = $box->length * $box->height * $box->width;
	}


	public function packer( $items ) {
		$packed        = array();
		$unpacked      = array();
		$packed_weight = $this->weight;
		$packed_volume = 0;

		foreach($items as $item) {
			$can_not_packed = ( ! $this->can_packed( $item ) || ( $packed_weight + $item['weight'] ) > $this->max_weight || ( $packed_volume + $item['volume'] ) > $this->volume );
			if ( $can_not_packed ) {
				$unpacked[] = $item;
				continue;
			}

			$packed[]      = $item;
			$packed_volume += $item['volume'];
			$packed_weight += $item['weight'];
		}

		$unpacked_weight = 0;
		$unpacked_volume = 0;
		foreach ( $unpacked as $item ) {
			$unpacked_weight += $item['weight'];
			$unpacked_volume += $item['volume'];
		}

		$package           = new stdClass();
		$package->id       = $this->id;
		$package->name       = $this->name;
		$package->flat_rate       = $this->flat_rate;
		$package->packed   = $packed;
		$package->unpacked = $unpacked;
		$package->weight   = $packed_weight;
		$package->volume   = $packed_volume;
		$package->length   = $this->length;
		$package->width    = $this->width;
		$package->height   = $this->height;

		$package->rate = ( $packed_weight / ( $packed_weight + $unpacked_weight ) ) * ( $packed_volume / ( $packed_volume + $unpacked_volume ) ) * 100;

		return $package;
	}

	private function can_packed( $item ) {
		return (
			$this->length >= $item['length']
			&& $this->width >= $item['width']
			&& $this->height >= $item['height']
			&& $item['volume'] < $this->volume
		);

	}
}