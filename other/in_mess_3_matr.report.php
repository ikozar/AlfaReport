<?
global $configure, $limitSQL;
$configure['connect'] = array('host'=>'192.168.100.102','user'=>'ikozar', 'password'=>'xiksx', 'dbname'=>'arbat-do-object');
include_once('Class.Report.php');

//define('DEBUG_PRINT',			1);

$oReport = new cl_Report();
//$oReport->ar_Data['FLDS'] = array(in_stock,for_peace,in_come,go_out,nz,current_portion,count_nz,cat_2_3,zc,cat_4,in_repair,cat_5,write_off,period_before);

switch($pr_tpl)
{
	case 1:
		$bands = "PART,TPL";
		break;
	case 2:
		$bands = "PART,MAIN";
		break;
	default:
		$bands = "all";
		break;
}

$xml_str = '
<Report>
	<QuerySections>
		<Query name="MEAS" table="unit_measure" fields="short_name" fName="short_name"/>
		<Query name="PART" table="temp_kozar.partition_num_template" fKeyUp="parent_id" fRubr="rub_part" fName="partition_name"/>
		<Query name="TPL" attachFld="nomtemp_id,nomtemp_name" fName="nomtemp_name"/>
		<Query name="DOC"  sqlSubSelect="SELECT r.in_whs_rep_id as key_field_, u.short_name as name_org FROM in_whs_report r JOIN kcbc u USING(unit_id)" order="name_org"/>
		<Query name="MAIN" sqlSelect="SELECT 
n.nomen_id,n.short_name,m.meas_id,t.*,tp.partition_id,m.in_whs_rep_id,m.for_peace,m.period_before   
FROM in_mess_3 m
JOIN temp_kozar.nomen_list n USING(nomen_id)
JOIN temp_kozar.nomen_template t USING(nomtemp_id)   
JOIN temp_kozar.nomen_template_partition tp USING(nomtemp_id) 
WHERE in_whs_rep_id IN (SELECT in_whs_rep_id FROM in_whs_report WHERE date_part(\'year\', report_date_from)=2007) AND rep_type_id=@rep_type_id@
ORDER BY tp.partition_id,t.nomtemp_id,n.short_name --LIMIT 200" 
			sum="for_peace,period_before">
			<Group to="PART" reference="partition_id"/>
			<Group to="TPL" reference="nomtemp_id"/>
			<Link to="MEAS" reference="meas_id"/>
			<Link to="DOC" reference="in_whs_rep_id"/>
			<Turn key="partition_id,nomtemp_id,nomen_id" on="in_whs_rep_id"/>
		</Query>
	</QuerySections>

	<BandSections>
		<Band name="HEAD">
			<Cell text="Усл.%%#BR%%номер" rowspan="2" />
			<Cell text="Наименование%%#BR%%средств связи" rowspan="2" />
			<Cell text="Номенкла-%%#BR%%турный номер,%%#BR%%код КВТ" rowspan="2"/>
			<Cell text="Всего" colspan="2" style="border-left-width: 2px;"/>
			<TurnCells on="all">
				<Cell text="%%TURN.DOC.name_org%% ( %%TURN.DOC.key_field_%%)" colspan="2" style="border-left-width: 2px;"/>
			</TurnCells>
		<BROW/>
			<Cell text="потреб-%%#BR%%ность" style="border-left-width: 2px;"/>
			<Cell text="состоя-%%#BR%%ло"/>
			<TurnCells on="all">
				<Cell text="потреб-%%#BR%%ность" style="border-left-width: 2px;"/>
				<Cell text="состоя-%%#BR%%ло"/>
			</TurnCells>
		</Band>

		<Band place="header" for="' . $bands . '">
			<switchBand>
				<caseBand for="MAIN">
					<Cell/>
					<Cell text="%%short_name%%" />
					<Cell/>
				</caseBand>
				<caseBand for="TPL">
					<Cell/>
					<Cell text="%%name%%" colspan="2" />
				</caseBand>
				<caseBand for="other">
					<expr>
						$padding = 10 * (%%LevelTotal%%-1);
					</expr>
					<Cell text="%%#(%%b%%#)%%%%name%%%%#(%%b%%#)%%" colspan="3" style="padding-left: $padding;" />
				</caseBand>
			</switchBand>

			<Cell text="%%for_peace%%" type="float" style="border-left-width: 2px;" />
			<Cell text="%%period_before%%" type="float" />
			<TurnCells on="all">
				<expr type="dynamic">
					$comp_color = \'\';
					if ( %%TURNINDEX.for_peace%% &lt; %%TURNINDEX.period_before%% )
						$comp_color = \'color: blue;\';
					else if ( %%TURNINDEX.for_peace%% &gt; %%TURNINDEX.period_before%% )
						$comp_color = \'color: red;\';
				</expr>
				<Cell text="%%TURNINDEX.for_peace%%" type="float" style="{$comp_color}border-left-width: 2px;" />
				<Cell text="%%TURNINDEX.period_before%%" type="float" />
			</TurnCells>
		</Band>
	</BandSections>
	<!--Print/-->
</Report>
';

echo '
<html>
<head>
<title>Report</title>

<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<link rel="stylesheet" type="text/css" href="ReportPrint.css">
<style>
.TPL { background-color: linen; }
.PART_1 { background-color: #C1B05C; }
.PART_2 { background-color: #EEDC82; }
.PART_3 { background-color: #FFEC8B; }
.float { align: right; }
</style>

</head>

<pre>
';

$xml_str = iconv('cp1251', 'utf-8', $xml_str);
$oReport->parse($oReport1, $xml_str);

//print_r($oReport->ar_Query);
/*
foreach($oReport->ar_Data as $n=>$v)
{
echo "<br>!!!! $n<br>";
if ( !f_print_tab($v) )
	print_r($v);
}
*/
//f_print_tab($oReport->ar_Group, array('group', 'nomUpGroup', 'key', 'name', 'LevelBand', 'LevelGroup', 'LevelTotal', 'fstRowMain', 'lastLevel', 'mCount', 'gCount', 'rub'));

//print_r($oReport->ar_Data['TPL']);

$oReport->printReport();
//print_r($oReport->ar_Group);
//print_r($oReport->ar_Band);

?>
