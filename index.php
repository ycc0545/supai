<?php
header("Content-type: text/html; charset=utf-8");
if (get_magic_quotes_gpc()) {
	function stripslashes_deep($value){
		$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value); 
		return $value; 
	}
	$_POST = array_map('stripslashes_deep', $_POST);
	$_GET = array_map('stripslashes_deep', $_GET);
	$_COOKIE = array_map('stripslashes_deep', $_COOKIE); 
}
define('APP_DEBUG',true);
define('APP_NAME', 'zuicity');
define('CONF_PATH','./zuicitydata/conf/');
define('RUNTIME_PATH','./zuicitydata/logs/');
define('TMPL_PATH','./themes/');
define('HTML_PATH','./zuicitydata/html/');
define('APP_PATH','./zuicity/');
define('CORE','./zuicity/_Core');
require(CORE.'/zuicity.php');
?>