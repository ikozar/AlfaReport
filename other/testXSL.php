<?php
//header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
//header('Content-Type: application/vnd.ms-excel');

/*
$doc = new DOMDocument();
$xsl = new XSLTProcessor();

$doc->load('test_r_xls.xsl');
$xsl->importStyleSheet($doc);

$doc->load('in_mess_3.report.xml');
$xslt = $xsl->transformToXML($doc);

file_put_contents('out.xml', $xslt);
echo iconv('utf-8', 'cp1251', $xslt);
*/

echo '<pre>';
preg_match_all('/(\[|\]|-|,|[^[\],-]*)/', 'teritory[2-3,5],store,subdivision,MAIN', $a);	//, PREG_OFFSET_CAPTURE);
print_r($a);

?>
