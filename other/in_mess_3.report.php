<?
global $limitSQL;
include_once('Class.Report.php');

//define('DEBUG_PRINT',			1);

$oReport = new cl_Report();
//$oReport->ar_Data['FLDS'] = array(in_stock,for_peace,in_come,go_out,nz,current_portion,count_nz,cat_2_3,zc,cat_4,in_repair,cat_5,write_off,period_before);

//$oReport->spec['BR'] = '&#xD;';
//$oReport->br = '&#10;';
//$oReport->nbr = '&#A0;';

switch($pr_tpl)
{
	case 1:
		$bands = "PART,TPL";
		break;
	case 2:
		$bands = "PART,MAIN";
		break;
	case 3:
		$bands = "NONE";
		break;
	default:
		$bands = "all";
		break;
}

//WHERE in_whs_rep_id=@in_whs_rep_id@::bigint #rep_type_id=@rep_type_id@::bigint

$xml_str = '
<Report>
	<QuerySections>
		<Query name="MEAS" table="unit_measure" fields="short_name" fName="short_name"/>
		<Query name="PART" table="temp_kozar.partition_num_template" fKeyUp="parent_id" startWith="parent_id=1" fRubr="rub_part" 
			fName="partition_name" condition="rep_type_id=%%rep_type_id%%" condition_="rep_type_id=%%rep_type_id%%" order="rep_type_id,rub_part"/>
		<!--Query name="TPL" table="temp_kozar.nomen_template" fName="nomtemp_name"/-->
		<Query name="TPL" attachFld="nomtemp_id,nomtemp_name" fName="nomtemp_name"/>
		<Query name="MAIN" sqlSelect="SELECT m.*
,COALESCE(nz, 0) - COALESCE(in_stock, 0) AS diff_war
,COALESCE(nz, 0) + COALESCE(cat_4, 0) - COALESCE(for_peace, 0) AS diff_peace
,n.nomen_id,n.short_name,m.meas_id,t.*,tp.partition_id   
FROM in_mess_3 m
JOIN temp_kozar.nomen_list n USING(nomen_id)
JOIN temp_kozar.nomen_template t USING(nomtemp_id)   
JOIN temp_kozar.nomen_template_partition tp USING(nomtemp_id) 
WHERE in_whs_rep_id=%%in_whs_rep_id%%::bigint %%| rep_type_id=%%rep_type_id%%::bigint
ORDER BY tp.partition_id,t.nomtemp_id,n.short_name 
LIMIT 20" 
			sum="in_stock,for_peace,in_come,go_out,nz,current_portion,count_nz,cat_2_3,zc,cat_4,in_repair,cat_5,write_off,period_before,diff_war,diff_peace">
			<Group to="PART" reference="partition_id"/>
			<Group to="TPL" reference="nomtemp_id"/>
			<Link to="MEAS" reference="meas_id"/>
		</Query>
	</QuerySections>

	<StyleSections>
		<Style for="TPL" background-color="linen"/>
		<Style for="PART_1" background-color="#C1B05C"/>
		<Style for="PART_2" background-color="#EEDC82"/>
		<Style for=".PART_3" background-color="#FFEC8B"/>
	</StyleSections>

	<BandSections>
		<Band name="HEAD">
			<Cell text="Усл.%%BR%%номер" rowspan="3" />
			<Cell text="Наименование%%BR%%средств связи" rowspan="3" />
			<Cell text="Номенкла-%%BR%%турный номер,%%BR%%код КВТ" rowspan="3"/>
			<Cell text="ед.%%BR%%учета" rowspan="3"/>
			<Cell text="потребность" colspan="2"/>
			<Cell text="состоя-%%BR%%ло" rowspan="3"/>
			<Cell text="движение за отчетный период" colspan="2"/>
			<Cell text="состоит в наличии%%BR%%1, 2, 3 категории" colspan="4"/>
			<Cell text="кроме того" colspan="5"/>
			<Cell text="недостает или излишествует%%BR%%( -/+ )" colspan="2" rowspan="2"/>
		<BROW/>
			<Cell text="на%%BR%%особый%%#BR%%период" rowspan="2"/>
			<Cell text="на%%#BR%%обычный%%#BR%%период" rowspan="2"/>
			<Cell text="прибы-%%#BR%%ло" rowspan="2"/>
			<Cell text="убыло" rowspan="2"/>
			<Cell text="всего" rowspan="2"/>
			<Cell text="в НЗ" rowspan="2"/>
			<Cell text="на теку-%%#BR%%щем дово-%%#BR%%льствии" rowspan="2"/>
			<Cell text="подмен-%%#BR%%ный фонд%%#BR%%2-3 кат." rowspan="2"/>
			<Cell text="в ЗЦ" rowspan="2"/>
			<Cell text="4%%#NBR%%категории" colspan="2"/>
			<Cell text="5%%#NBR%%категории" colspan="2"/>
		<BROW/>
			<Cell text="всего"/>
			<Cell text="в т.ч.%%#BR%%кап. рем."/>
			<Cell text="всего"/>
			<Cell text="в т.ч.%%#BR%%списано"/>
			<Cell text="на особый%%#BR%%период"/>
			<Cell text="на обыч.%%#BR%%период"/>
		<BROW/>
			<Cell text="-1-"/>
			<Cell text="-2-"/>
			<Cell text="-3-"/>
			<Cell text="-4-"/>
			<Cell text="-5-"/>
			<Cell text="-6-"/>
			<Cell text="-7-"/>
			<Cell text="-8-"/>
			<Cell text="-9-"/>
			<Cell text="-10-"/>
			<Cell text="-11-"/>
			<Cell text="-12-"/>
			<Cell text="-13-"/>
			<Cell text="-14-"/>
			<Cell text="-15-"/>
			<Cell text="-16-"/>
			<Cell text="-17-"/>
			<Cell text="-18-"/>
			<Cell text="-19-"/>
			<Cell text="-20-"/>
		</Band>

		<Band place="header" for="' . $bands . '">
			<switchBand>
				<caseBand for="MAIN">
					<Cell/>
					<Cell text="%%short_name%%" />
					<Cell/>
					<Cell text="%%MEAS.short_name%%" />
				</caseBand>
				<caseBand for="TPL">
					<Cell/>
					<Cell text="%%name%%" colspan="3" />
				</caseBand>
				<caseBand for="other">
					<expr><![CDATA[
						$padding = 0;
						if ( %%LevelTotal%% > 1 )
							$padding = 10 * (%%LevelTotal%%);
					]]></expr>
					<Cell text="%%name%%" colspan="4" style="padding-left: $padding;" />
				</caseBand>
			</switchBand>

			<Cell text="%%in_stock%%" type="float" />
			<Cell text="%%for_peace%%" type="float" />
			<Cell text="%%period_before%%" type="float" />
			<Cell text="%%in_come%%" type="float" />
			<Cell text="%%go_out%%" type="float" />
			<Cell text="%%nz%%" type="float" />
			<Cell text="%%count_nz%%" type="float" />
			<Cell text="%%current_portion%%" type="float" />
			<Cell text="%%cat_2_3%%" type="float" />
			<Cell text="%%zc%%" type="float" />
			<Cell text="%%cat_4%%" type="float" />
			<Cell text="%%in_repair%%" type="float" />
			<Cell text="%%cat_5%%" type="float" />
			<Cell text="%%write_off%%" type="float" />
			<Cell text="%%diff_war%%" type="float" />
			<Cell text="%%diff_peace%%" type="float" />
		</Band>
	</BandSections>
	<!--Print/-->
</Report>
';

$xml_str = iconv('cp1251', 'utf-8', $xml_str);
$oReport->type = 'XML';
$oReport->parse($oReport1, $xml_str);

$ss = $oReport->printReport();

if ( $oReport->type != 'HTML' )
	echo '<?xml version="1.0" encoding="cp1251"?>
<?xml-stylesheet type="text/xsl" href="test_r_xls.xsl"?>
<REPORT>
';

echo '1111111111111111111' . $ss;

//print_r($oReport->ar_Band);

if ( $oReport->type != 'HTML' )
{
//	$tt = file_get_contents('test_r_xls.xsl');
/*
	$doc = new DOMDocument('1.0'); //, 111 );	//'cp1251'

//	$element = $doc->createElementNS('http://www.example.com/XFoo', 'xfoo:test', 'This is the root element!');
//	$doc->appendChild($element);
//	echo $doc->saveXML();

	echo json_encode($doc);

	$doc->load('test_r_xls.xsl');
	$xsl = new XSLTProcessor();
	$xsl->importStyleSheet($doc);
	
//	$doc->loadXML($ss);
	$doc->load('in_mess_3.report.xml');
	$dd = $xsl->transformToXML($doc);
	echo '-----------------------------------------------------' . $dd;
*/
/**/
	$arguments = array('/_xml' => $ss);
	$xsltproc = xslt_create();
	xslt_set_encoding($xsltproc, 'cp1251');
	$dd = xslt_process($xsltproc, 'arg:/_xml', 'test_r_xls.xsl', NULL, $arguments);
	
	xslt_free($xsltproc);
echo '-----------------------------------------------------' . $dd;
/**/
	file_put_contents('dest.xml', $dd);
echo '
</REPORT>
';
}

?>
