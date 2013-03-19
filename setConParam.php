<?php
global $configure;
define('DB_SERVER',	'POSTGRES');
//define('DB_SERVER',	'ARBAT');
if ( DB_SERVER == 'ARBAT' )
{
$configure['connect'] = 
	array(
		'driver'=>'pgsql', 
		'host'=>'192.168.1.85',
		'user'=>'ikozar', 
		'dbname'=>'arbat-do-obj-act',
	);
//$configure['iniConnect'] = "SET search_path TO org,public; SET client_encoding to win;";
}
elseif ( DB_SERVER == 'POSTGRES' )
{
$configure['connect'] = 
	array(
		'driver'=>'pgsql', 
		'host'=>'localhost',
		'user'=>'postgres', 
		'password'=>'qqq', 
		'dbname'=>'Naklad',
		'iniConnect'=>array('SET search_path TO realiz,public; SET client_encoding TO win;')
	);
//$configure['iniConnect'] = "SET search_path TO org,public; SET client_encoding to win;";
}
else
{
$configure['connect'] = 
	array(
		'driver'=>'mysql', 
		'host'=>'localhost',
		'user'=>'report', 
		'password'=>'', 
		'dbname'=>'realiz',
		'dopparam'=>';charset=cp1251',
		'iniConnect'=>array('USE realiz;', 'SET CHARACTER SET cp1251;')
	);
//$configure['iniConnect'] = array('USE realiz;', 'SET CHARACTER SET cp1251;');
}
?>
