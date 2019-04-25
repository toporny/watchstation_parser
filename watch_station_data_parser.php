<?php


class WatchStationParser implements ParseStore {
	private $rawDataToParse = "";
	private $productsAndPricesArray = array();
	public $db;
	public $last_update_datetime;


	function __CONSTRUCT($db) {
		//parent::__construct();
		$this->db = $db;
	}


	public function areFreshPricesAlreadyInDb() {

		$sql  = "SELECT datetime ";
		$sql .= " FROM prices ";
		$sql .= " WHERE store = 'watchstation.com' ";
		$sql .= " AND DATE_FORMAT(NOW(), '%Y-%m-%d %H') = DATE_FORMAT(DATETIME, '%Y-%m-%d %H') ";
		$sql .= " ORDER BY id desc ";
		$sql .= " LIMIT 1";

		$result = $this->db->query($sql);

		if ($result->num_rows > 0) {
			$this->last_update_datetime = $result->row['datetime'];
			return true;
		} else {
			return false;
		}
	}


	
	private function prepareArrayBasedOnRawData($rawDataToParse) {	
		$lines = explode("\n", $rawDataToParse);
		$array = array();
		foreach ($lines as $line) {
			//print $line. "\n";
			if( ($a = strpos( $line, 'var productsJSON =' )) !== false) {
				$json = substr($line, $a+19);
				$json = rtrim( rtrim( $json),";");
				$array  = json_decode($json, true);
				break;
			}
		}
		return $array['products'];
	}		


	private function getOnlySku($array) {
		$only_sku = array();
		if (count($array) > 0) {
			foreach ($array as $item)  {
				if (isset($item['brand'])) {
					$only_sku[] = $item['brand']."-".$item['imagePath'];
				}
			}
		}
		return $only_sku;
	}


	public function PrepareRawDataBasedOnFiles() {

$file1 = "http://www.watchstation.com/webapp/wcs/stores/servlet/FSAJAXService?service=getProductList&langId=-1&storeId=34054&catalogId=23503&categoryId=445605&parent_category_rn=288102&departmentCategoryId=287584&&Nf=p_min_price|BTWN+0+795&maxRecPerPg=99999";

$file2 = "http://www.watchstation.com/webapp/wcs/stores/servlet/FSAJAXService?service=getProductList&langId=-1&storeId=34054&catalogId=23503&categoryId=445606&parent_category_rn=288124&departmentCategoryId=287583&&Nf=p_min_price|BTWN+0+1995&maxRecPerPg=99999";

		$rawDataToParse1 = file_get_contents($file1);
		$rawDataToParse2 = file_get_contents($file2);

		$array1 = $this->prepareArrayBasedOnRawData($rawDataToParse1);
		$array2 = $this->prepareArrayBasedOnRawData($rawDataToParse2);


		$only_sku_1 = $this->getOnlySku($array1);
		$only_sku_2 = $this->getOnlySku($array2);

 		$intersect = array_intersect($only_sku_2, $only_sku_1);

		foreach ($array1 as $item) {
			$this->productsAndPricesArray[] = $item + array('brand_n_model'=>$item['brand']."/".$item['imagePath'] ,'for_whom' => 'women');
		}

		foreach ($array2 as $item) {
			$needle = $item['brand']."-".$item['imagePath'];
			if (!in_array ($needle, $intersect)) {
				$this->productsAndPricesArray[] = $item + array('brand_n_model'=>$item['brand']."/".$item['imagePath'] ,'for_whom' => 'men');
			}
		}

		foreach ($this->productsAndPricesArray as &$item) {
			$needle = $item['brand']."-".$item['imagePath'];
			if (in_array ($needle , $intersect)) {
				$item['for_whom'] = "men,women";
			}
		}
	}



	public function UpdateProducts() {
		$sql  = "SELECT DISTINCT concat(brand,'/',  model) as brand_n_model ";
		$sql .= " FROM products ";


		$result = $this->db->query($sql);

		$aaa  = array();
		if ($result->num_rows > 0) {
			$aaa = array_reduce(
				$result->rows, function ($one_dimensional, $value) {
					return array_merge($one_dimensional, array_values($value));
				}, array()
			);
			$insert_data = array();
		}

	
		$insert_data = array();
		foreach ($this->productsAndPricesArray as $item) {
			$value_to_find = $item['brand_n_model'];
			if (!in_array($value_to_find, $aaa )) {
				$insert_data[] = $item;
			}
		}

		if (count($insert_data) > 0) {
			foreach ($insert_data as $insert) {

				if (strlen($insert['brand']) > 1) {
					$sql  = "INSERT INTO products  SET ";
					$sql  .= "brand = '".$this->db->escape($insert['brand'])."', ";
					$sql  .= "model = '".$this->db->escape($insert['imagePath'])."', ";
					$sql  .= "product_name = '".$this->db->escape($insert['name'])."', ";
					$sql  .= "for_whom = '".$this->db->escape($insert['for_whom'])."', ";
					$sql  .= "tmp = '".$this->db->escape($insert['brand'])."'  ";
					$result = $this->db->query($sql);
				} else {}
			}
		}
	}



// ==============================================================================
	public function UpdatePrices() {
		//print "update prices";
		$sql = "SELECT DISTINCT id, concat(brand,'/',  model) as brand_n_model FROM products ";
		$database_data = array();
		$result = $this->db->query($sql);
		if ($result->num_rows > 0) {
			foreach ($result->rows as $item) {
				$database_data[$item['brand_n_model']] = $item['id'];
			}
		}

		foreach ($this->productsAndPricesArray as $item) {
			if (isset($database_data[$item['brand_n_model']])) {
				$product_id = $database_data[$item['brand_n_model']];

				$sql  = "INSERT INTO prices  SET ";
				$sql  .= "product_id = ".$product_id.", ";
				$sql  .= "sales_price = '".$item['salePrice']."', ";
				$sql  .= "list_price = '".$item['listPrice']."', ";
				$sql  .= "discount = '".(100-($item['salePrice']*100/$item['listPrice']))."', ";
				$sql  .= "store_product_id = '".$item['productId']."', ";
				$sql  .= "store = 'watchstation.com'  ";

				$result = $this->db->query($sql);
			} else {
				// log error: there is no product defined $item['brand_n_model']
			}

		} 


	}


}


