<?php
/*
http://fossil.scene7.com/is/image/FossilPartners/MK2732_main?wsiAsset_pdpdetail&id=O78if2&scl=2&req=tile&rect=768,256,256,256&fmt=jpg

http://fossil.scene7.com/is/image/FossilPartners/MK2749_main?wsiAsset_pdpdetail&id=wqCi_0&scl=1&req=tile&rect=1280,768,256,256&fmt=jpg

http://fossil.scene7.com/is/image/FossilPartners/MK5701_main?wsiAsset_pdpdetail&id=j3D6c3&scl=1&req=tile&rect=2560,2560,256,256&fmt=jpg

http://fossil.scene7.com/is/image/FossilPartners/MK5701_main?wsiAsset_pdpdetail&id=j3D6c3&scl=1&req=tile&rect=1280,768,256,256&fmt=jpg



http://fossil.scene7.com/is/image/FossilPartners/MK5701_main?wsiAsset_pdpdetail&id=j3D6c2&scl=1&req=tile&rect=0,0,3000,3000&fmt=jpg

http://fossil.scene7.com/is/image/FossilPartners/MK3990_2?wsiAsset_pdpdetail&id=2RIjl2&scl=1&req=tile&rect=1280,2304,256,256&fmt=jpg


http://fossil.scene7.com/is/image/FossilPartners/MK3990_2?wsiAsset_pdpdetail&id=2RIjl2&scl=1&req=tile&rect=0,0,3000,3000&fmt=jpg

http://www.watchstation.com/webapp/wcs/stores/servlet/ProductDisplay?storeId=34054&catalogId=23503&productId=22105685
*/
require_once("parser_config.php");
require_once("libs/mysqli.php");
require_once("stores_data_parser.php");
require_once("watch_station_data_parser.php");


interface ParseStore {
	public function UpdateProducts();
	public function UpdatePrices();
}

$dataUpdater = new StoresParser();
$dataUpdater->UpdataeWatchStationProductAndPrices();
