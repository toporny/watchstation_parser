<?php



class StoresParser {
	private $db;

	function __CONSTRUCT() {
		$this->db = new DB\MySQLi(DB_WHMC_HOST, DB_WHMC_LOGIN, DB_WHMC_PASSWORD, DB_WHMC_DATABASE);
		// $sql  = "truncate table prices ";
		// $this->db->query($sql);
		// $sql  = "truncate table products ";
		// $this->db->query($sql);
		// exit;
	}


	public function UpdataeWatchStationProductAndPrices() {
		$a = new WatchStationParser($this->db);
		$a->PrepareRawDataBasedOnFiles("source_data_files/man.html", "source_data_files/woman.html");
		$a->UpdateProducts();

	}

}



// $dataUpdater = new WatchStationParser();
// $dataUpdater->UpdataeWatchStationPrices();
