<?php
class WatchWatcher {

	private $db = null;

	public function __construct() {
		$this->db = new DB\MySQLi(DB_WHMC_HOST, DB_WHMC_LOGIN, DB_WHMC_PASSWORD, DB_WHMC_DATABASE);
	}

	public function getTitle() {
		return "Watch watcher";
	}

	public function getWebsiteContent() {
		return "Website Content";
	}

	public function showTables() {
		$return = "";
		$sql  = "SELECT brand, model, list_price, sales_price, discount, product_name  FROM biggest_discounts ";
		
		$result = $this->db->query($sql);

		if ($result->num_rows > 0) {

			print '<div class="container">'."\n";
			foreach ($result->rows as $item) {
				$return .= '  <div  class="row"> '."\n";
				$return .= '    <div style="border:1px solid red;" class="col"> '."\n";
				$return .= '<img style="float:right" src="http://fossil.scene7.com/is/image/FossilPartners/'.$item['model'].'_main?wsiAsset_pdpdetail&id=2RIjl2&scl=16&req=tile&rect=0,0,187,187&fmt=jpg" width="175" height="175">';
				$return .= "Brand: ".$item['brand']."<br>\n";
				$return .= "Model: ".$item['model']."<br>\n";
				$return .= "List_price:".$item['list_price']."<br>\n";
				$return .= "Sales_price: <b>".$item['sales_price']."$</b><br>\n";
				$return .= "Discount: ".$item['discount']."%<br>\n";
				$return .= "Name: ".$item['product_name']."<br>\n";
				$return .= '    </div> '."\n";
				$return .= '  </div> '."\n";
			}
			print "</div>";
			print "</div>";
			return $return ;
		} else {
			return "no results";
		}
	}

	

}
