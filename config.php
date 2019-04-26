<?php

if (isset($_SERVER['SERVER_NAME'] ) && ($_SERVER['SERVER_NAME'] == "alltic.home.pl")) {
	define("DB_WHMC_HOST" , "localhost");
	define("DB_WHMC_LOGIN" , "08171730_watchst");
	define("DB_WHMC_PASSWORD" , "#&g_Phb#3JAn");
	define("DB_WHMC_DATABASE" , "08171730_watchst"); 

} else {
	define("DB_WHMC_HOST" , "localhost");
	define("DB_WHMC_LOGIN" , "root");
	define("DB_WHMC_PASSWORD" , "");
	define("DB_WHMC_DATABASE" , "watchstation"); 
}
