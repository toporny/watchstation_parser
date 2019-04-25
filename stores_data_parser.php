<?php

class StoresParser {
	private $db;

	function __CONSTRUCT() {
		$this->db = new DB\MySQLi(DB_WHMC_HOST, DB_WHMC_LOGIN, DB_WHMC_PASSWORD, DB_WHMC_DATABASE);
	}

	public function UpdataeWatchStationProductAndPrices() {
		$a = new WatchStationParser($this->db);
		if (!$a->areFreshPricesAlreadyInDb()) {
			$a->PrepareRawDataBasedOnFiles();
			$a->UpdateProducts();
			$a->UpdatePrices();
		} else {
			print "Prices are updated recently (".$a->last_update_datetime.").\n";
		}
	}
}

