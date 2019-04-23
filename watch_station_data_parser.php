<?php


class WatchStationParser implements ParseStore {
	private $rawDataToParse = "";
	private $productsAndPricesArray = array();
	public $db;

	function __CONSTRUCT($db) {
		//parent::__construct();
		$this->db = $db;
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


	public function PrepareRawDataBasedOnFiles($file1, $file2) {
		$rawDataToParse1 = file_get_contents($file1);
		$rawDataToParse2 = file_get_contents($file2);

		$array1 = $this->prepareArrayBasedOnRawData($rawDataToParse1);
		$array2 = $this->prepareArrayBasedOnRawData($rawDataToParse2);


		$only_sku_1 = $this->getOnlySku($array1);
		$only_sku_2 = $this->getOnlySku($array2);

 		$intersect = array_intersect($only_sku_2, $only_sku_1);

		$final_array = array();

		foreach ($array1 as $item) {
			$final_array[] = $item + array('for_whom'=>'man');
		}

		foreach ($array2 as $item) {
			$needle = $item['brand']."-".$item['imagePath'];
			if (!in_array ($needle, $intersect)) {
				$final_array[] = $item + array('for_whom' => 'woman');
			}
		}

		foreach ($final_array as &$item) {
			$needle = $item['brand']."-".$item['imagePath'];
			if (in_array ($needle , $intersect)) {
				$item['for_whom'] = "man,woman";
			}
		}		

	}


	public function UpdataeWatchStationPrices() {
		//$watchstation = new WatchStationParser($this->db);
		//$this->CreateRawBasedOnFiles();

		// $watchstation->PrepareProductsAndPricesArray();
		// $watchstation->UpdateProducts('man');


		// $watchstation->UpdatePrices();

		// $watchstation->InitDataToParse("woman.html");
		// $watchstation->UpdateProducts();
		// $watchstation->UpdatePrices();
	}

	public function UpdateProducts($for_whom) {

		$sql  = "SELECT DISTINCT concat(brand,'/',  model,'/', for_man,'/', for_woman) as brand_n_model ";
		$sql .= " FROM products ";
		if ($for_whom == 'man') {
			$sql .= " WHERE for_man = 1 ";
		}
		if ($for_whom == 'woman') {
			$sql .= " WHERE for_woman = 1 ";
		}

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


		foreach ($this->productsAndPricesArray as $item) {

			$value_to_find = $item['brand_n_model'];

			if (!in_array($value_to_find, $aaa )) {
				$insert_data[] = $item;
			} else {
				$item['brand_n_model'] = "ZZZZZZZZZZZZZZZZ";
			}
		}

		if (count($insert_data) > 0) {
			foreach ($insert_data as $insert) {

				if (strlen($insert['brand']) > 1) {
					$sql  = "INSERT INTO products  SET ";
//					$sql  .= "id = '".$this->db->escape($insert['brand']."-".$insert['model'])."', ";
					$sql  .= "brand = '".$this->db->escape($insert['brand'])."', ";
					$sql  .= "model = '".$this->db->escape($insert['model'])."', ";
					if ($for_whom == 'man') {
						$sql .= " for_man = 1, ";
					}
					if ($for_whom == 'woman') {
						$sql .= " for_woman = 1, ";
					}
					$sql  .= "product_name = '".$this->db->escape($insert['name'])."', ";
					$sql  .= "tmp = '".$this->db->escape($insert['brand'])."'  ";
	//print "<br>".$sql."<br>";
					$result = $this->db->query($sql);
				} 
			}
		}
	}




	public function PrepareProductsAndPricesArray($rawDataToParse) {
 	
		$lines = explode("\n", $this->rawDataToParse);
		$array = array();
		foreach ($lines as $line) {
			if( ($a = strpos( $line, 'var productsJSON =' )) !== false) {
				$json = substr($line, $a+19);
				$json = rtrim( rtrim( $json),";");
				$array  = json_decode($json, true);
				break;
			}
		}

 
		if (count($array['products']) > 0) {
			foreach ($array['products'] as $item) {

				/*Array (
					[imagePath] => MK3977
					[salePrice] => 134.99
					[details] => n\/a
					[newLabel] => 
					[exclusiveLabel] => 
					[brand] => Michael Kors
					[productId] => 22768816
					[listPrice] => 225.0
					[name] => Michael Kors Women's Jaryn Three-Hand Gold-Tone Stainless Steel Watch
					[baseSKU] => MK3977P
					[description] => n\/a
				) */
				$for_man = ($for_whom == 'man') ? 1 : 0; 
				$for_woman = ($for_whom == 'woman') ? 1 : 0;

				$this->productsAndPricesArray[] = array(
				"brand" => trim($item['brand']),
				"model" => trim($item['imagePath']),
				"brand_n_model" => trim($item['brand'])."/".trim($item['imagePath'])."/".$for_man."/".$for_woman,
				"name" => trim($item['name']),
				"for_man" => ($for_whom == 'man') ? 1 : 0,
				"for_woman" => ($for_whom == 'woman') ? 1 : 0,
				"name" => trim($item['name']),
				"list_price" => $item['listPrice'],
				"sales_price" => $item['salePrice'],
				);
			}
		}

	}




	public function UpdatePrices() {
		// wziac wszystkie z array
		// zrob cala liste array produktow
// brand/model

		 // i porownac czy co te rekordy takze w bazie
		$sql = "SELECT DISTINCT id, concat(brand,'/',  model) as brand_n_model FROM products ";
		$database_data = array();
		$result = $this->db->query($sql);
		if ($result->num_rows > 0) {
			foreach ($result->rows as $item) {
				$database_data[$item['brand_n_model']] = $item['id'];
			}
		}

 // print "<pre>";
 // print_r( $this->productsAndPricesArray );
 // print "</pre>";
 // exit;



		foreach ($this->productsAndPricesArray as $item) {
			if (isset($database_data[$item['brand_n_model']])) {
				$product_id = $database_data[$item['brand_n_model']];
				$sql  = "INSERT INTO prices  SET ";
				$sql  .= "product_id = ".$product_id.", ";
				$sql  .= "sales_price = '".$item['sales_price']."', ";
				$sql  .= "list_price = '".$item['list_price']."', ";
				$sql  .= "discount = '".(100-($item['sales_price']*100/$item['list_price']))."', ";
				$sql  .= "store = 'watchstation.com'  ";
				$result = $this->db->query($sql);
			} else {
				// log error: there is no product defined $item['brand_n_model']
			}

		} 
	}
}


