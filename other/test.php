<?php
/*
function array_intersect_key($a1, $a2)
{
	$c = array();
	foreach($a2 as $k=>$v)
		if ( array_key_exists($k, $a1) )
			$c[$k] = $a1[$k];
	return $c;
}

$a = array('a'=>1, 'b'=>2, 'c'=>3);
$b = array('c'=>3, 'a'=>1);
$c = array_intersect_key($a, $b);
print_r($c);
$c = array_intersect_assoc($a, $b);
print_r($c);
*/

echo "<pre>";



print_r(get_loaded_extensions());
print_r(get_extension_funcs("xsl"));

$doc = new DOMDocument();
$xsl = new XSLTProcessor();

$doc->load('test_r_xls.xsl');
$xsl->importStyleSheet($doc);

$doc->load('in_mess_3.report.xml');
echo iconv('utf-8', 'cp1251', $xsl->transformToXML($doc));


$s = 'SELECT (11%%xx%%%%zzz%%11111-2222%|yyy%%22222%%), %%3333333%% FROM %%44444%%, (SELECT 5555 FROM 666666 %|77777), (
   SELECT 888888) %|WHERE %%999999%%%| (000000) aaaaaa';
//$s = '1111111-222222222<3333333>44444<5555>666666=77777-888888<999999>000000=aaaaaa';
//echo "!!!!!!!!!!!!!!<pre>" . preg_match_all('/(-([^-<]*)((<[^>]*>)+[^-<>]*)+([^-<>]*)-)/', $s, $a);
echo "!!!!!!!!!!!!!!" . $s . "\n" . 
//preg_match_all('/((-|<)([^-<>]*)(>|-))/', $s, $a);
//preg_match_all('/(-([^<]*)(((<[^<]*>)([^>=]*))*)=)/', $s, $a);
//preg_match_all('/(-)|(=)|(<[^>]*>)/', $s, $a, PREG_OFFSET_CAPTURE);
preg_match_all('/(\(?\s*SELECT\b|\bFROM\b|%\||\(|\)|%%[^%]+%%)/', $s, $a, PREG_OFFSET_CAPTURE);
print_r($a);
?>
