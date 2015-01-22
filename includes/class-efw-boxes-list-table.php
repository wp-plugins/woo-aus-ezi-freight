<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) )
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

class EFW_Boxes_List_Table extends WP_List_Table {


	public function __construct() {
		parent::__construct( array(
			'singular'  => 'box',     //singular name of the listed records
			'plural'    => 'boxes',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
	}

	public function column_default($item, $column_name){
		switch($column_name) {
			case 'operation':
				return '<a href="javascript:;" class="efw-box-delete" data-id="'.$item->id.'">Delete</a>';
				break;

			case 'flat_rate':
			case 'name':
				$text = '';
				break;

			case 'max_weight':
			case 'weight':
				$text = 'kg';
				break;

			default:
				//return $this->editable()
				$text = 'cm';
				break;
		}

		return $this->editable($item, $column_name, $text);
	}

	public function prepare_items() {
		$per_page = 20;
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$args = array(
			'per_page' => $per_page,
			'page' => $_GET['paged'],
			'orderby' => $_GET['orderby'],
			'order' => $_GET['order'],
		);
		$this->items = EFWBoxes::find( $args );

		$total_items = EFWBoxes::count();
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page' => $per_page ) );
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'height'     => array('height',false),     //true means it's already sorted
			'length'    => array('length',false),
			'width'  => array('width',false),
			'weight'  => array('weight',false),
		);
		return $sortable_columns;
	}

	public function get_columns(){
		$columns = array(
			//'cb'        => '', //Render a checkbox instead of text
			'name'     => 'Name',
			'length'  => 'Length',
			'width'  => 'Width',
			'height'    => 'Height',
			'weight'  => 'Weight',
			'max_weight'  => 'Max Weight',
			'flat_rate'  => 'Flat Rate(ex GST)',
			'operation'  => 'Operation',
		);
		return $columns;
	}

	private function editable($item, $column_name, $text = '') {
		$value = ($column_name == 'name') ? $item->$column_name : floatval($item->$column_name);

		if($column_name == 'flat_rate') {
			$html = '<span class="efw-editable-value"><em>'.($value ? $item->rate : '---').'</em>'.$text.'</span>';
			$html .= '<p class="efw-editable-form">';
			$html .= '<input type="checkbox" name="" value="1" data-key="'.$column_name.'" data-id="'.$item->id.'" style="width:16px" '. ($value ? 'checked="checked"' : '') .' />';
			$html .= '<input type="text" name="" value="'.$item->rate.'" data-key="rate" data-id="'.$item->id.'" style="width:60px;'. ($value ? '' : 'display:none') .'" />';
			$html .= '<br /><a href="javascript:;" class="efw-editable-update" data-type="rate">Update</a> | <a href="javascript:;" class="efw-editable-cancel">Cancel</a>';
			$html .= '</p>';
		} else {
			$html = '<span class="efw-editable-value"><em>'.($value ? $value : 'click to edit').'</em>'.$text.'</span>';
			$html .= '<p class="efw-editable-form">';
			$html .= '<input type="text" name="" value="'.$value.'" data-key="'.$column_name.'" data-id="'.$item->id.'" style="width:80px" />';
			$html .= '<br /><a href="javascript:;" class="efw-editable-update">Update</a> | <a href="javascript:;" class="efw-editable-cancel">Cancel</a>';
			$html .= '</p>';
		}


		return $html;
	}
}