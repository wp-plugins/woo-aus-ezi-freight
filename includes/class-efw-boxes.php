<?php if ( ! defined( 'ABSPATH' ) ) exit;

class EFWBoxes {
	private static $table_name = 'ezihosting_freight_tick_boxes';

	public static function find( $args = '' ) {
		global $wpdb, $table_prefix;

		$sql = 'select * from '.$table_prefix.self::$table_name.' where is_deleted = 0';

		$page = absint($args['paged']);
		if ( !$page )
			$page = 1;

		$args['per_page'] = $args['per_page'] ? $args['per_page'] : 20;
		if ( empty($q['offset']) ) {
			$pgstrt = ($page - 1) * $args['per_page'] . ', ';
		} else { // we're ignoring $page and using 'offset'
			$q['offset'] = absint($args['offset']);
			$pgstrt = $args['offset'] . ', ';
		}

		if($args['orderby'] && in_array($args['orderby'], array('height', 'weight', 'width', 'length'))) {
			$sort_type = $args['order'] == 'desc' ? 'desc' : 'asc';
			$sql .= ' order by '.$args['orderby'].' '.$sort_type;
		}

		$sql .= ' LIMIT ' . $pgstrt . $args['per_page'];
		return $wpdb->get_results($sql);
	}

	public static function findAll($condition = '') {
		global $wpdb, $table_prefix;

		return $wpdb->get_results('select * from '.$table_prefix.self::$table_name.' where is_deleted = 0 '.$condition);
	}

	public static function count() {
		global $wpdb, $table_prefix;

		$sql = 'select count(id) count_id from '.$table_prefix.self::$table_name.' where is_deleted = 0';

		$result = $wpdb->get_row($sql);
		return $result->count_id;
	}

	public static function create($data) {
		global $wpdb, $table_prefix;
		return $wpdb->insert($table_prefix.self::$table_name, $data);
	}

	public static function delete($id) {
		global $wpdb, $table_prefix;
		return $wpdb->update($table_prefix.self::$table_name, array('is_deleted' => 1), array('id' => $id));
	}

	public static function update($id, $data) {
		global $wpdb, $table_prefix;
		return $wpdb->update($table_prefix.self::$table_name, $data, array('id' => $id));
	}
}