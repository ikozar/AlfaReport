<?php
//define('DEBUG_PRINT',			1);
//define('DEBUG_READMAIN',		1);
//define('DEBUG_DATA',				'teritory,delivery');
//define('DEBUG_GROUP',			1);
//define('DEBUG_TURN',				1);
//define('DEBUG_MACRO',			1);
//define('DEBUG_BAND',				1);
//define('DEBUG_EXEC',				1);
//define('TIME',						1);
if ( $argv )
{
	foreach($argv as $a)
	{
		$arr = split('=', $a);
		$_REQUEST[$arr[0]] = $arr[1];
	}
}

include_once('Class.Report.php');

$oReport = new ReportBuilder();

$urp_tpl = $_REQUEST['urp_tpl'];

try {
	if ( !$urp_tpl )
		throw new Exception("=е указано имя файла");
	$xml_str = file_get_contents($urp_tpl);
}
catch(Exception $e)
{
	die("+шибка чтения шаблона отчета \"$urp_tpl\" (\$_REQUEST['urp_tpl']):\n" . $e->getMessage());
}
//$xml_str = iconv('cp1251', 'utf-8', $xml_str);
//$oReport->type = 'HTML';
switch($_REQUEST['urp_type'])
{
	case 'jsfm':
		$oReport->type = CRP_TYPE_JSFM;
		break;
	case 'xml':
		$oReport->type = CRP_TYPE_XML;
		break;
	default:
		if ( substr($urp_tpl,-7) == 'ufm.xml' )
			$oReport->type = CRP_TYPE_JSFM;
}

echo $oReport->type . '---------------------------------------';

if ( $_REQUEST['urp_out'] == 'oo_text' )
	header("Content-Type: application/vnd.oasis.opendocument.text");
elseif ( $_REQUEST['urp_out'] == 'oo_calc' )
	header("Content-Type: application/vnd.oasis.opendocument.calc");
elseif ( $_REQUEST['urp_out'] == 'oo_xls' )
	header("Content-Type: application/ms-excel");

$oReport->parse($oReport1, $xml_str);

//f_print_r($oReport->ar_Data['type_wares_sum']);

//if ( $_REQUEST['urp_gzip'] )
	if ( $_SERVER['HTTP_ACCEPT_ENCODING'] && extension_loaded('gzip') )
	{
		gzip_output($_REQUEST['urp_gzip']);
	}
?>
