<?php
class installerDbUpdaterGmp {
	static public function runUpdate() {
		self::update_105();
		self::update_109();
		self::update_117();
		self::update_192();
	}
	public static function update_105() {
		if(!dbGmp::exist('gmp_modules', 'code', 'csv')) {
			global $wpdb;
			$tableName = $wpdb->prefix . "gmp_modules";
			$wpdb->insert($tableName, array(
					'code' => 'csv',
					'active' => 1,
					'type_id' => 1,
					'params' => '',
					'has_tab' => 0,
					'label' => 'csv',
					'description' => 'csv',
			));
		}
	}
	public static function update_109() {
		if(!dbGmp::exist('gmp_modules', 'code', 'maps_widget')) {
			global $wpdb;
			$tableName = $wpdb->prefix . "gmp_modules";
			$wpdb->insert($tableName, array(
					'code' => 'gmap_widget',
					'active' => 1,
					'type_id' => 1,
					'params' => '',
					'has_tab' => 0,
					'label' => 'gmap_widget',
					'description' => 'gmap_widget',
			));
		}
	}
	public static function update_117() {
		global $wpdb;
		$tableName = $wpdb->prefix . "gmp_options";
		$data_update = array(
			 'value_type' => 'array',
		);
		$data_where = array(
			 'code' => 'infowindow_size'
		);
		$wpdb->update($tableName, $data_update, $data_where);
	}

	public static function update_192() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix;

		$row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$wpdb->prefix}gmp_markers' AND column_name = 'period_from'"  );
		if(empty($row)) {
			$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}gmp_markers ADD COLUMN `period_from` DATE NULL");
		}

		$row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$wpdb->prefix}gmp_markers' AND column_name = 'period_to'"  );
		if(empty($row)) {
			$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}gmp_markers ADD COLUMN `period_to` DATE NULL");
		}

		$row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$wpdb->prefix}gmp_markers' AND column_name = 'hash'"  );
		if(empty($row)) {
			$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}gmp_markers ADD COLUMN `hash` varchar(32) DEFAULT NULL");
		}
	}
}
