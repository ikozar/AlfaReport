<?
include_once('setConParam.php');
include_once('Class.Query.php');

$metaData = array(
	'kod_org'=>array(
		'table'=>'torg_ref'
		,'fName'=>'naimp_org'
	)
	,'kod_kls'=>array(
		'table'=>'tkls_main'
		,'fName'=>'naimk_kls'
	)
	,'kod_kls_dolz'=>array(
		'fKey'=>'kod_kls'
		,'type'=>'COMBO'
		,'condition'=>'kod_qualifier = 92'
	)
	,'kod_kls_okato'=>array(
		'fKey'=>'kod_kls'
		,'condition'=>'kod_qualifier = 55'
	)
	,'kod_kls_svyaz'=>array(
		'fKey'=>'kod_kls'
		,'type'=>'COMBO'
		,'condition'=>'kod_qualifier = 22'
	)
	,'kod_pers'=>array(
		'table'=>'torg_personal'
		,'fName'=>'fio_pers'
	)
);

$aFlds = $_REQUEST['req_combo'];
for($i=0; $i<count($aFlds); $i++)
{
	$fld = $aFlds[$i];
	if ( !$cQry )
		$cQry = new CQuery($configure['connect']);
	$cQry->execQuery("SET client_encoding TO utf8;");
	$param = $metaData[$fld];
	if ( $metaData[$fld]['type'] != 'COMBO' )
		$param['LIMIT'] = 30;

	if ( ($key=$param['fKey']) )
		$param = array_merge($param, $metaData[$param['fKey']]);
	else
		$key = $fld;
	$name = $param['table'];
	if ( $_REQUEST['filt'] )
		$param['filt'] = $_REQUEST['filt'];
	$sqlText = $cQry->makeQuery($name, $param);
	if ( $_REQUEST['l_trace'] )
		echo $sqlText;
	$cQry->execQuery($sqlText, $params, $name);
	while($row=$cQry->getDataRow())
	{
//			$res[$row[$key]] = $row;
$row['kod_kls_dolz'] = $row['kod_kls'];
		$res[] = $row;
	}
//		$text .= '
//HRC.Manager.setData("CMB_' . $name . '", ' . 
//makeJSON($res) . ');';
	$text .= makeJSON($res);
}
if ( $_REQUEST['l_trace'] )
{
	echo '<pre>';
	print_r($res);
	echo $text;
}
else
{
	if ( !$text )
		$text = 'null';
	header('Content-Type: application/xml; charset=utf-8');
	echo $text;
}
?>
