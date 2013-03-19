<?
include_once('Class.Report.php');

function makeMAIN($param)
{
//echo json_encode($param);
	$arrType = array(
		'id_quarter'=>array('n'=>'Кварталы', 'f'=>'EXTRACT(quarter FROM r.date_realiz)', 'fName'=>'naim', 'next'=>'id_month',
			'table'=>'quarter', 'sqlSelect'=>"SELECT n as key_field_, concat(cast(n as char),'й кв.','') as naim FROM generate_series WHERE n &lt;= 4"),
		'id_month'=>array('n'=>'Месяца', 'f'=>'EXTRACT(month FROM r.date_realiz)', 'fName'=>'naim', 'next'=>'id_vendor',
			'table'=>'month', 'sqlSelect'=>"SELECT n as key_field_, monthname(cast(concat('1990-',cast(n as char(2)),'-01') as date)) as naim 
				FROM generate_series"),
		'id_store'=>array('n'=>'Магазины', 'f'=>'id_store', 't'=>'personal,subdivision',
			'table'=>'store', 'fName'=>'naim_store', 'next'=>'id_subdiv'),
		'id_ter'=>array('n'=>'Территория продаж', 'f'=>'id_ter', 't'=>'personal,subdivision,store',
			'tc'=>'personal,subdivision,store,v_terit_reg', 'wc'=>'level_ter=3', 'order'=>'rub_terit',
			'table'=>'teritory', 'fName'=>'naim_ter', 'fKeyUp'=>'id_ter_parent', 'fRubr'=>'rub_terit'),
		'id_subdiv'=>array('n'=>'Подразделение', 'f'=>'id_subdiv', 't'=>'personal',
			'tc'=>'personal,v_subdiv', 'wc'=>'level_subdiv=2', 'next'=>'id_pers',
			'table'=>'subdivision', 'fName'=>'naim_subdiv', 'fKeyUp'=>'id_subdiv_parent', 'fRubr'=>'rub_subdiv'),
		'id_type_wares'=>array('n'=>'Вид товара', 'f'=>'id_type_wares', 't'=>'wares', 'next'=>'id_wares',
			'tc'=>'wares,v_type_wares v', 'wc'=>'level_type_wares=%%level%%',
			'table'=>'type_wares', 'fName'=>'naim_type_wares', 'fKeyUp'=>'id_type_wares_parent', 'fRubr'=>'rub_type_wares'),
		'id_vendor'=>array('n'=>'Фирмы', 'f'=>'id_vendor', 't'=>'wares',
			'table'=>'vendor', 'fName'=>'naim_vendor'),
		//'9'=>array('n'=>'Кварталы', 'f'=>'id_ter', 't'=>'wares,vendor'),
		'id_wares'=>array('n'=>'Товар', 'f'=>'id_wares',
			'table'=>'wares', 'fName'=>'naim_wares'),
		'id_pers'=>array('n'=>'Продавец', 'f'=>'id_pers', 'next'=>'id_type_wares',
			'table'=>'personal', 'fName'=>'fio_pers'),
		);
	$arrTab = array(
		'realiz'=>			array('schema'=>'realiz', 'alias'=>'r'),
		'personal'=>		array('schema'=>'realiz', 'alias'=>'ps', 'on'=>'id_pers'),
		'subdivision'=>	array('schema'=>'realiz', 'alias'=>'sd', 'on'=>'id_subdiv'),
		'v_subdiv'=>		array('schema'=>'realiz', 'alias'=>'vsd', 'on'=>'id_subdiv'),
		'store'=>			array('schema'=>'realiz', 'alias'=>'st', 'on'=>'id_store'),
		'teritory'=>		array('schema'=>'realiz', 'alias'=>'tr', 'on'=>'id_ter'),
		'v_terit_reg'=>	array('schema'=>'realiz', 'alias'=>'vtr', 'on'=>'id_ter'),
		'wares'=>			array('schema'=>'realiz', 'alias'=>'wr', 'on'=>'id_wares'),
		'type_wares'=>		array('schema'=>'realiz', 'alias'=>'tw', 'on'=>'id_type_wares'),
		'v_type_wares'=>	array('schema'=>'realiz', 'alias'=>'vtw', 'on'=>'id_type_wares'),
		'vendor'=>			array('schema'=>'realiz', 'alias'=>'vd', 'on'=>'id_vendor'),
		);

	$arrRej = array();
	$arrGen = array();
	$sql = '';
	$nf = 0;

//	foreach($param['R'] as $nDim)
	for($i=0; $i<count($param['R']); $i++)
	{
		$nDim = $param['R'][$i];
		if ( $arrRej[$nDim] )
			throw('Повторное использование "' + $arrType[$nDim] + '"');
		$alias = '';
		if ( $arrType[$nDim]['t'] )
		{
			$arr = explode( ',', $arrType[$nDim]['t']);
			foreach($arr as $t)
				$arrGen['table'][$t] = 1;
			$alias = $arrTab[$t]['alias'] . '.';
		}
		$f = $arrType[$nDim]['f'];
		if ( $_GET['where'][$f] )
		{
			$arrGen['where'][] = "$alias{$f}={$_GET['where'][$nDim]}";
			if ( $arrType[$nDim]['next'] )
				$param['R'][] = $arrType[$nDim]['next'];
		}
//		else
		$arrGen['keyG'][$nDim] = $alias . $f;
	}

//	foreach($param['C'] as $nDim)
	for($i=0; $i<count($param['C']); $i++)
	{
		$nDim = $param['C'][$i];
		if ( $arrRej[$nDim] )
			throw('Повторное использование "' + $arrType[$nDim] + '"');
		if ( $arrType[$nDim]['tc'] )
		{
			$f = $arrType[$nDim]['f'] . '_level';
			$t = $arrType[$nDim]['tc'];
		}
		else
		{
			$f = $arrType[$nDim]['f'];
			$t = $arrType[$nDim]['t'];
		}
		$alias = '';
		if ( $t )
		{
			$arr = explode( ',', $t);
			$t = 'realiz';
			foreach($arr as $t)
				$arrGen['table'][$t] = 1;
			if ( $arrType[$nDim]['t'] )		// ??????????????
				$alias = $arrTab[$t]['alias'] . '.';
		}
		if ( $arrType[$nDim]['wc'] )
			$arrGen['where'][] = $arrType[$nDim]['wc'];
//f_print_r($_GET['where'], "-----------$alias---$nDim");
		if ( $_GET['where'][$nDim] )
		{
			$arrGen['where'][] = "$alias{$f}={$_GET['where'][$nDim]}";
			if ( $arrType[$nDim]['next'] )
			{
				$param['C'][] = $arrType[$nDim]['next'];
				continue;
			}
		}
		$arrGen['keyC'][$nDim] = $alias . $f;
	}
//echo "\n ||" . json_encode($arrGen) . '|| ';

	$sql = 'SELECT ';
	foreach($arrGen['keyG'] as $k=>$f)
		$sql .= "$f as $k,";
	$arr = array_values($arrGen['keyG']);
	$arrGen['group'] = $arr;
	$keyFld = join(',', array_keys($arrGen['keyG']));
	$arrGen['order'] = $arr;
//	if ( $_GET['order'] )
//		$arrGen['order'][] = $_GET['order'];
//echo ' ||' . json_encode($arrGen) . '|| ';

	if ( $arrGen['keyC'] )
	{
		$kolTurn = count($arrGen['keyC']);
		$arrGenKey = array_keys($arrGen['keyC']);
		$fstTurn = reset($arrGenKey);
		$turnFld = join(',', $arrGenKey);
		foreach($arrGen['keyC'] as $k=>$f)
			$sqlTurn .= "$f as $k,";
		$arrGen['group'] = array_merge($arrGen['group'], array_values($arrGen['keyC']));
	}
//echo ' ||' . json_encode($arrGen) . '|| ';

	end($arrGen['keyG']);
	$lastGroupKey = key($arrGen['keyG']);
	if ( !$arrGen['keyG']['id_wares'] || !$arrGen['keyG']['id_pers'] )
	{
		$naimMAIN = $arrType[$lastGroupKey]['table'] . '.' . $arrType[$lastGroupKey]['fName'];
		if ( $arrType[$lastGroupKey]['next'] )
			$naimMAIN = "<a href=\"javascript:detal('{$lastGroupKey}', {\$row['$lastGroupKey']});\">%%$naimMAIN%%</a>";
		else
			$naimMAIN = '%%' . $naimMAIN . '%%';
		$sql .= $sqlTurn . 'sum(num_realiz) as num_realiz, sum(sum_realiz) as sum_realiz';
		$arrGen['group'] = ' GROUP BY ' . join(',', $arrGen['group']);
		$arrGen['order'] = ' ORDER BY ' . join(',', $arrGen['order']);
	}
	else
	{
		$naimMAIN = $arrType[$lastGroupKey]['fName'];
		$sql .= $naimMAIN . ',' . $sqlTurn . 'num_realiz, sum_realiz';
		$naimMAIN = '%%' . $naimMAIN . '%%';
		$arrGen['group'] = '';
		$arrGen['order'] = ' ORDER BY ' . join(',', $arrGen['order']);
	}

	$sql .= "\nFROM realiz.realizations r";
	foreach($arrGen['table'] as $t=>$i)
	{
		$sql .= "\n	JOIN realiz.$t {$arrTab[$t]['alias']} USING({$arrTab[$t]['on']})";
	}
	if ( $arrGen['where'] )
		$sql .= "\nWHERE " . join(' AND ', $arrGen['where']);
//	$sql .= "\n{$arrGen['group']}\n%%ORDER%%\nLIMIT 100";
	$sql .= "\n{$arrGen['group']}\n{$arrGen['order']}";

	$sql .= "\n--LIMIT 100";
//echo $sql;

	$xml_str = "
<Report>
	<QuerySections>";
//echo "\n 1||" . json_encode($arrType) . '|| ';
	foreach(array_merge($arrGen['keyG'], $arrGen['keyC']) as $f=>$i)
	{
		$arr = $arrType[$f];
		$xml_str .= "
		<Query name=\"{$arr['table']}\"";
		foreach(array('table', 'fName', 'fKeyUp', 'fRubr', 'order', 'sqlSelect') as $k)
		{
			if ( $arr[$k] )
				$xml_str .= " $k=\"{$arr[$k]}\"";
		}
		$xml_str .= '/>';
	}
	$xml_str .= "
		<Query name=\"MAIN\" sqlSelect=\"$sql\"
			sum=\"num_realiz,sum_realiz\">";
//	$bands = 'MAIN';
/*
	end($arrGen['keyG']);
	$lastGroupKey = key($arrGen['keyG']);
*/
	foreach($arrGen['keyG'] as $nDim=>$arr)
	{
		$t = $arrType[$nDim]['table'];
		if ( !$fstGroup )
			$fstGroup = $t;
		$bands .= ( $bands? ',' : '' ) . $t;
		if ( $nDim == $lastGroupKey )
			$xml_str .= "
				<Link to=\"{$t}\" reference=\"{$nDim}\"/>";
		else
			$xml_str .= "
				<Group to=\"{$t}\" reference=\"{$nDim}\"/>";
	}

	foreach($arrGen['keyC'] as $nDim=>$arr)
		$xml_str .= "
			<Link to=\"{$arrType[$nDim]['table']}\" reference=\"{$nDim}\"/>";
//echo "\n 2||" . json_encode($arrType) . '|| ';

	$kolTurn_ = $kolTurn + 1;
	$xml_str .= "
			<Turn key=\"$keyFld\" on=\"$turnFld\"/>
		</Query>
	</QuerySections>

	<BandSections>
		<Band name=\"HEAD\">
			<Cell text=\"Наименование\" rowspan=\"{$kolTurn_}\"/>
			<Cell text=\"Кол. продаж\" rowspan=\"{$kolTurn_}\"/>
			<Cell text=\"Сумма продаж\" rowspan=\"{$kolTurn_}\"/>";
	foreach($arrGenKey as $n=>$f)
	{
		$xml_str .= '
			<TurnCells on="level">';
			for($i=0; $i<$n; $i++)
			{
				$xml_str .= '
				<TurnCells>';
			}
			$xml_str .= '
					<Expr>
						$colSpan = %%TURN.mCount%%*2;
					</Expr>';
			if ( $f == $fstTurn )
			{
				if ( $arrType[$f]['next'] )
					$xml_str .= '
					<Cell colspan="$colSpan">
						<![CDATA[
							<a href="javascript:detal(\'' . $f . '\', {$aTURN_1[\'key\']});">%%TURNNAME%%</a>
						]]>
						<CheckVisibility mask="01"/>
					</Cell>';
				else
					$xml_str .= '
					<Cell colspan="$colSpan" text="%%TURNNAME%%">
						<CheckVisibility mask="01"/>
					</Cell>';
			}
			else
				$xml_str .= '
					<Cell text="%%TURNNAME%%" colspan="$colSpan"/>';
			for($i=0; $i<$n; $i++)
				$xml_str .= '
				</TurnCells>';
			$xml_str .= '
			</TurnCells>
			<BROW/>
';
	}

	if ( $kolTurn )
		$xml_str .= "
			<TurnCells on=\"all\" levels=\"last\">
				<Cell text=\"Кол. продаж\"/>
				<Cell text=\"Сумма продаж\"/>
			</TurnCells>";

	$xml_str .= "
		</Band>

		<Band place=\"header\" for=\"MAIN,$bands\">
			<SwitchBand>
				<CaseBand for=\"MAIN\">
					<!--Cell text=\"%%{$arrType[$lastGroupKey]['table']}.{$arrType[$lastGroupKey]['fName']}%%\" /-->
					<Cell>
						<![CDATA[
							$naimMAIN
						]]>
					</Cell>
				</CaseBand>
				<CaseBand for=\"$fstGroup\">
					<Cell text=\"%%name%%\">
						<CheckVisibility/>
					</Cell>
				</CaseBand>
				<CaseBand for=\"other\">
					<Cell text=\"%%name%%\" style=\"padding-left: %%PAD_LEVEL%%;\" />
				</CaseBand>
			</SwitchBand>
			<Cell text=\"%%num_realiz%%\" type=\"float\" />
			<Cell text=\"%%sum_realiz%%\" type=\"float\" />
			<TurnCells on=\"all\" levels=\"last\">
				<Cell text=\"%%TURNINDEX.num_realiz%%\" type=\"float\" />
				<Cell text=\"%%TURNINDEX.sum_realiz%%\" type=\"float\" />
			</TurnCells>
			<BROW/>
		</Band>
	</BandSections>
	<Print/>
</Report>";
	return $xml_str;
}

$xml_str = makeMAIN($_GET);

//echo '<pre> ' . debug_parse($xml_str);

$xml_str = iconv('cp1251', 'utf-8', $xml_str);

$oReport = new ReportBuilder();
//$oReport->type = CRP_TYPE_JSFM;
$oReport->type = CRP_TYPE_HTML;
//$oReport->aConn['DEFAULT']->connect->exec("SET search_path to realiz;");

$oReport->parse($oReport, $xml_str);

echo '
<!--script type="text/javascript" src="DOMInspector.js"></script-->
<script>
function detal(f, val)
{
	document.location.href += "&where[" + f + "]=" + val
//	alert(f + "=" + val);
}
</script>
';
?>
