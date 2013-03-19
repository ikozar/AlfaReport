<?
global $oReport, $arraySort, $configure;	//, $SQL;
//include_once('json/JSON.php');
//define('DEBUG_GROUP',			2);				// 2 - трассировка
//define('DEBUG_DATA',			1);				// 1 все, или запросы через ','
//define('DEBUG_BAND',			1);				// 2 - вывод массива
//define('DEBUG_PRINT',			1);
//define('DEBUG_EXEC',			1);
//define('DEBUG_TURN',			1);
//define('DEBUG_READMAIN',		1);
//define('DEBUG_SUBLEVEL',		1);
//define('TIME',						1);
//define('TIME_COMP_QUERY',	1);
if ( !$configure )
$configure['connect'] = array('driver'=>'pgsql', 'host'=>'192.168.100.102','user'=>'ikozar', 'password'=>'xiksx', 'dbname'=>'arbat-do-object');

include_once('f_print_r.php');
if (defined('TIME'))
time_start('TOTAL');
include_once('../probab_concl/misc.php');
include_once('Class.Query.php');
//include_once('../probab_concl/Query.class.php');

$oReport = null;

class cl_Report
{
var $ar_Query = array();		// массивы текстов запросов и разной лабудени
var $ar_Group = array();
var $ar_Band = array();			// массивы для секций
var $depth = 0;
var $lastTag = array();
var $sepKeys = '*';				// разделитель значений ключей
var $sepField = '*';				// разделитель значений составного поля
var $sepMacro = '%%';			// ограничитель МАКРО
var $Band;
var $iBand;
var $aConn;		// = array('DEFAULT'=>$SQL);
var $json;
var $type = 'HTML';
var $tags = array(
	'HTML'=>array('CELL'=>'TD', 'ROW'=>'TR', 'BR'=>'<BR/>', 'NBR'=>'&nbsp;', '('=>'<', ')'=>'>'),
	'XML'=> array('CELL'=>'C', 'ROW'=>'R', 'BR'=>"\n", '('=>'<', ')'=>'>'));

//Style Horizontal="Center" WrapText="1" Bold="1"

var $rootKeyUp = 'IS NULL';	// признак вершины дерева 

	function cl_Report()
	{
		global $_GET;
//		$this->json = new Services_JSON();
		global $configure;
	  	$this->aConn = array('DEFAULT'=>new CQuery($configure['connect']));
	  	$this->params = $_GET;
	}

//** Чтение групповых и прицепленных запросов по ключам ************************
	function getDataQuery($name, &$keyIn)
	{
//echo "\n getDataQuery $name";
if (defined('TIME'))
time_start('DATA-' . $name);
		$this->makeQuery($name, $keyIn);

if (defined('TIME_COMP_QUERY'))
{
if ($this->ar_Query[$name]['sqlSelectAlt'])
{
$sql = &$this->aConn[$this->ar_Query[$name]['CONN']];
$s1 = $sql->sql_mod($this->ar_Query[$name]['sqlSelect'], $this->params);
$s2 = $sql->sql_mod($this->ar_Query[$name]['sqlSelectAlt'], $this->params);
time_start('COMP_QUERY_ONE_READ');
$a = $sql->sql_query($s1);
time_end('COMP_QUERY_ONE_READ');
time_start('COMP_QUERY_MULTY_READ');
foreach($keyIn as $v)
	$a = $sql->sql_query($s2 . $v);
time_end('COMP_QUERY_MULTY_READ');
}
}

		$cnt = $this->execDataQuery($name);
if (defined('TIME'))
time_end('DATA-' . $name);
//		$i = 1;
		while($a=$this->getDataRow($name))
		{
//			$key_ = join($this->sepKeys, array_intersect_key($a, $aKey));
			$key_ = $a['key_field_'];
//echo " -$key_- ";
			$a['pNom'] = sprintf('%03d', ++$i);
			if ( ($keyUp=$this->ar_Query[$name]['fKeyUp']) )
			{
if (defined('TIME'))
time_start('DATA-' . $name . '-SUBLEVEL');

if (defined('DEBUG_SUBLEVEL'))
echo "\n<BR/>key_=$key_";
				if ( ($level=$ret[$a[$keyUp]]) )
				{
if (defined('DEBUG_SUBLEVEL'))
echo ", level=$level";
					$level = $level['level'] + 1;
if (defined('DEBUG_SUBLEVEL'))
echo "->$level, level_=$level_";
					if ( $level > $level_ )
						$subLevel[] = $a[$keyUp];
					else
					{
						while ( $level < $level_-- )
							array_pop($subLevel);
					}
if (defined('DEBUG_SUBLEVEL'))
echo ", subLevel=" . json_encode($subLevel);
				}
				else
				{
					$level = 1;
					$subLevel = array();
				}
				$level_ = $level;
				$a['level'] = $level;
				if ( $subLevel )
				{
					$a['subLevel'] = $subLevel;
if(defined('DEBUG_DATA'))
$a['cSubLevel'] = json_encode($subLevel);
				}
if (defined('TIME'))
time_end('DATA-' . $name . '-SUBLEVEL');
			}
			$ret[$key_] = $a;
		}
		return $ret;
	}

//** Чтение строки с убиранием пробелов ****************************************
	function getDataRow($name, $vid=PDO::FETCH_ASSOC)	//PGSQL_ASSOC)
	{
		$conn = $this->aConn[$this->ar_Query[$name]['CONN']];
		return $conn->getDataRow($vid);
	}

//** Выполнение запроса в БД на получение данных *******************************
	function prepareExecQuery($name, $sqlText, &$params)
	{
		$conn = $this->aConn[$this->ar_Query[$name]['CONN']];
		$arr = explode('%%', $sqlText);
		$aText[0] = $arr[0];
		$lText = 1;
		for($i=1; $i<count($arr); $i++)
		{
			$s = $arr[$i];
			if ( $s[0] == '|' )
			{
				if ( $s[1] == ' ' )
					$aText[$lText] = 'AND';
				$aText[$lText] .= substr($s, 1);
			}
			else
			{
				if ( isset($params[$s]) )
				{
//					$aText[$lText++] .= $params[$s] . $arr[++$i];
					$aText[$lText] .= '' . $params[$s];
					$aText[$lText] .= $arr[++$i];
					$lText++;
				}
				else
				{
					while( $arr[$i+1][0] != '|' )
						$i++;
				}
			}
		}

		$sqlText = join(' ', $aText);
if (defined('DEBUG_EXEC'))
echo "\n<BR>prepare <b>$name</b><BR>\n" . $sqlText;
		$index_result = $this->aConn[$this->ar_Query[$name]['CONN']]->prepare($sqlText);
		return $index_result;
	}

//** Выполнение запроса в БД на получение данных *******************************
	function execDataQuery($name)
	{
		$conn = $this->aConn[$this->ar_Query[$name]['CONN']];
		$sqlText = iconv('utf-8', 'cp1251', $this->ar_Query[$name]['sqlSelect']);
		return $conn->execQuery($sqlText, $this->params, $name);
	}

//** Суммирование	$tree - 0:суммирование строки MAIN, 1:секции MAIN, 2:вверх по дереву групп
	function SumTotal($tree, &$aSumFld, &$aSumDest, &$aSumSour)
	{
		foreach($aSumFld as $sFld)
			$aSumDest[$sFld] += $aSumSour[$sFld];
	}

//** Подготовка данных *********************************************************
	function prepareData()
	{
if (defined('TIME'))
time_start('DATA');

if (defined('TIME'))
time_start('DATA1');
//		$lastRows = !$this->execDataQuery('MAIN');		//-1;
		$lastRows = false;
		$this->execDataQuery('MAIN');
		$row = $this->getDataRow('MAIN');
		$this->ar_Query['MAIN']['FIELDS'] = array_keys($row);

if (defined('TIME'))
time_end('DATA1');
		if ( ($aKeyTurn=$this->ar_Query['MAIN']['TURN']['key']) )
		{
//			$aKeyTurn = explode(',', $aKeyTurn);
//			$kKeyTurn = count($aKeyTurn);
			$aKeyTurn = array_flip(explode(',', $aKeyTurn));
		}
		$aLink = &$this->ar_Query['MAIN']['LINK'];
		$aSumFldTurnTree = array();
		$aSumFldTurnList = array();
		if ( ($aFlOnTurn=$this->ar_Query['MAIN']['TURN']['on']) )
		{
			$aFlOnTurn = explode(',', $aFlOnTurn);
			$nmFstOnTurn = $aFlOnTurn[0];
			$aFlOnTurn = array_flip($aFlOnTurn);
			$this->ar_Query['MAIN']['TURN']['onLink'] = $aFlOnTurn;
			$nomFstOnTurn = array_search($nmFstOnTurn, $this->ar_Query['MAIN']['FIELDS']);
			if ( $nomFstOnTurn < 1 )
				die("\n<BR/>ERROR processMAIN: Don't find field ($nmFstOnTurn) for turn; Fields: " . json_encode($this->ar_Query['MAIN']['FIELDS']));
			foreach($aLink as $l=>&$k)
			{
				foreach($k['key'] as $f=>$v)
				{
//echo " ---Link $l $f (" . array_search($f, $this->ar_Query['MAIN']['FIELDS']) . ",$nomFstOnTurn)--- ";
					if ( array_search($f, $this->ar_Query['MAIN']['FIELDS']) >= $nomFstOnTurn )
					{
						$k['afterTurn'] = 1;
						if ( array_key_exists($f, $aFlOnTurn) )
						{
							$this->ar_Query['MAIN']['TURN']['onLink'][$f] = $l;
						}
					}
				}
			}
		}
		if ( ($aSumFld=$this->ar_Query['MAIN']['sum']) )
			$aSumFld = explode(',', $aSumFld);
		if ( ($aGroup=&$this->ar_Query['MAIN']['GROUP']) )
		{
			$aKeyGroup = array();
			$level = 1;
			foreach($aGroup as $g=>&$k)
			{
//				$aGroup[$g] = array('key'=>array_flip(explode(',', $k)), 'aKeys'=>array(), 'LevelGroup'=>$level++);
				$k['LevelGroup'] = $level++;
				$aKeyGroup = array_merge($aKeyGroup, $k['key']);
//:: Ибо порядок ключей д. совпадать с MAIN
				$this->ar_Query[$g]['key'] = array_intersect_key(array_flip($this->ar_Query['MAIN']['FIELDS']), $this->ar_Query[$g]['key']);
			}
		}
		else
			$aGroup = array('DUMMY'=>array('key'=>array('DUMMY'=>0), 'aKeys'=>array(), 'LevelGroup'=>1));
		unset($k);

//if (defined('DEBUG_READMAIN'))
//echo "\nMAIN-lastRows=$lastRows";
if (defined('TIME'))
time_start('DATA-MAIN');
//== Препарируем MAIN ==========================================================
		for($i=0, $lastRows=false; !$lastRows; $i++, $row = $this->getDataRow('MAIN'))
		{
if (defined('DEBUG_READMAIN'))
echo "\n<BR/>MAIN $i --$lastRows";
			if ( $row )
			{
				foreach($aLink as $l=>&$v)
				{
					if ( !$v['afterTurn'] )
						continue;
					$k = join($this->sepKeys, array_intersect_key($row, $v['key']));
if (defined('DEBUG_READMAIN'))
echo " Link l=$l, k=$k";
					if ( !array_key_exists($k, $v['aKeys']) )
						$aLink[$l]['aKeys'][$k] = 1;
				}
				unset($v);
			}
			else
			{
				$row = array();
				$lastRows = true;
				if ( !$aKeyTurn )
					break;
			}
			if ( $aKeyTurn )
			{
//-- Поворачиваем
//				if ( $i == $lastRows )
				if ( $lastRows )
				{
//echo "\nLastRow $i\n";
//print_r($row);
//print_r($turnRow);
					$newRow = $turnRow;
				}
				else
				{
					$keyRow_ = join($this->sepKeys, array_intersect_key($row, $aKeyTurn));
					if ( $keyRow != $keyRow_ )
					{
//-- Начинаем новую строку
//echo "\nNEW TURN $i $keyRow_\n";
						$newRow = $turnRow;
						$keyRow = $keyRow_;
						$turnRow = array_slice($row, 0, $nomFstOnTurn);
					}
					else
						unset($newRow);
					$aKeyTurn_ = array_intersect_key($row, $aFlOnTurn);
					$k = join($this->sepField, $aKeyTurn_);
					$turnRow[$k] = array_slice($row, $nomFstOnTurn);
					if ( !array_key_exists($k, $aSumFldTurnList) )
					{
						$aSumFldTurnList[$k] = $aKeyTurn_;
					}
					if ( $aSumFld )
					{
						for(array_pop($aKeyTurn_); $aKeyTurn_; array_pop($aKeyTurn_))
						{
							$keyTurn_ = join($this->sepKeys, $aKeyTurn_);
							if ( !array_key_exists($keyTurn_, $aSumFldTurnTree) )
							{
								$aSumFldTurnTree[$keyTurn_] = count($aSumFldTurnTree);
//echo "\n aSumFldTurn $keyTurn_\n";
							}
							$this->SumTotal(0, $aSumFld, $turnRow[$keyTurn_], $row);
						}
						$this->SumTotal(0, $aSumFld, $turnRow, $row);
					}
				}
				if ( !$newRow )
				{
if (defined('DEBUG_READMAIN'))
echo "\n<BR/>Continue $i";
					continue;
				}
			}
			else
				$newRow = &$row;

if (defined('DEBUG_READMAIN'))
echo "\n<BR/>Manage $i kt={$newRow['kt']}";
//print_r($newRow);
			foreach($aLink as $l=>&$v)
			{
				if ( $v['afterTurn'] )
					continue;
				$k = join($this->sepKeys, array_intersect_key($newRow, $v['key']));
				if ( !array_key_exists($k, $v['aKeys']) )
				{
					$v['aKeys'][$k] = 1;
				}
			}
			unset($v);
			$newRow['NomRow'] = ++$NomRow;
			if ( $aKeyGroup )
			{
//-- Подготавливаем группы
				$keyGroup_ = join($this->sepKeys, array_intersect_key($newRow, $aKeyGroup));
			}
			else
			{
				$keyGroup_ = 'DUMMY';
				$newRow['DUMMY'] = 'DUMMY';
			}
if (defined('DEBUG_READMAIN'))
echo " keyGroup_=$keyGroup_";
			if ( $keyGroup != $keyGroup_ )
			{
				$keyGroup = $keyGroup_;
				$aGroupItem[$keyGroup]['fstRowMain'] = count($this->ar_Data['MAIN']);
				foreach($aGroup as $g=>&$v)
				{
					$k = join($this->sepKeys, array_intersect_key($newRow, $v['key']));
					$aGroupItem[$keyGroup][$g] = $k;
if (defined('DEBUG_READMAIN'))
echo " aGroupItem-- keyGroup=$keyGroup, g=$g, k=$k";
					if ( !array_key_exists($k, $v['aKeys']) )
					{
						if ( $this->ar_Query[$g]['attachFld'] )
						{
							$v['aKeys'][$k] = array_intersect_key($newRow, $this->ar_Query[$g]['attachFld']);
							$v['aKeys'][$k]['pNom'] = sprintf('%03d', count($v['aKeys']));
						}
						else
							$v['aKeys'][$k] = 1;
					}
				}
				unset($v);
			}
			$newRow['keyGroup'] = $keyGroup;
			$aGroupItem[$keyGroup]['mCount']++;

			if ( $aSumFld )
			{
if (defined('TIME'))
time_start('DATA-MAIN-SUMM');
//-- Суммируем
				$this->SumTotal(1, $aSumFld, $aGroupItem[$keyGroup], $newRow);
//!!! т.к. array_merge($aSumFldTurnTree, $aSumFldTurnList) косячит
				foreach($aSumFldTurnTree as $k=>$v)
				{
//echo "\n $sFld==" . $newRow[$sFld];
					$this->SumTotal(1, $aSumFld, $aGroupItem[$keyGroup][$k], $newRow[$k]);
				}
				foreach($aSumFldTurnList as $k=>$v)
				{
//echo "\n $sFld==" . $newRow[$sFld];
					$this->SumTotal(1, $aSumFld, $aGroupItem[$keyGroup][$k], $newRow[$k]);
				}
if (defined('TIME'))
time_end('DATA-MAIN-SUMM');
			}
			$this->ar_Data['MAIN'][] = $newRow;
		}
if (defined('TIME')){
time_end('DATA-MAIN');
}
		foreach($aGroup as $g=>$v)
		{
			if ( $this->ar_Query[$g]['attachFld'] )
				$this->ar_Data[$g] = $v['aKeys'];
			else
			{
				$this->ar_Data[$g] = $this->getDataQuery($g, $v['aKeys']);
			}
		}

		foreach($aLink as $l=>$v)
		{
			$this->ar_Data[$l] = $this->getDataQuery($l, $v['aKeys']);
		}
if (defined('TIME')){
time_end('DATA');
time_start('GROUP_SORT');
}
//=================================================== сортировка aGroupItem
		foreach($aGroupItem as $kI=>&$rI)
		{
			$s = '';
			foreach($aGroup as $g=>$v)
				$s .= $this->ar_Data[$g][$rI[$g]]['pNom'];
			$rI['pNom'] = $s;
		}

		global $arraySort;
		$arraySort = $aGroupItem;
		uksort($aGroupItem, 'cmpPNom');
if (defined('TIME'))
time_end('GROUP_SORT');

//=================================================== сортировка aSumFldTurnAll
		if ( ($aTurnLink=$this->ar_Query['MAIN']['TURN']['onLink']) )
		{
			foreach($aSumFldTurnList as $k=>&$v)
			{
				$s = '';
				foreach($aTurnLink as $f=>$l)
				{
					$g = $v[$f];
					if ( !is_numeric($l) )
					{
						$s .= $this->ar_Data[$l][$g]['pNom'];
						if ( $this->ar_Query[$l]['fName'] )
							$v[$f . 'Name'] = $this->ar_Data[$l][$g][$this->ar_Query[$l]['fName']];
					}
					else
					{
						if ( is_numeric($g) )
							$s .= sprintf('%05d', $g);
						else
							$s .= str_pad(substr($g, 0, 10), 10, '_');
					}
				}
				$v['pNom'] = $s;
			}
		}
		unset($v);
		$arraySort = $aSumFldTurnList;
		uksort($aSumFldTurnList, 'cmpPNom');
		$this->ar_Turn['list'] = $aSumFldTurnList;
//		$this->ar_Turn['tree'] = transform_result(join(',', array_keys($aTurnLink)), '', $aSumFldTurnList);

//=================================================== Формирование ar_Group
if (defined('TIME'))
time_start('GROUP_PREPARE');
		if ( $aSumFldTurnTree )
		{
//!!! косяк
//		 	$aSumFldTurnList = array_merge($aSumFldTurnList, $aSumFldTurnTree);
		 	foreach($aSumFldTurnTree as $k=>$v)
		 		$aSumFldTurnList[$k] = $v;
//print_r($aSumFldTurnList);
		}
//		reset($aGroup);
//		$fstGroup = key($aGroup);

		$lastGroupLevel = count($aGroup);
//print_r($aGroup);
		$aGr[] = array('group'=>'TOTAL', 'LevelTotal'=>0);
		$nomGroupLevel = array(0);
		$aGroupItem['***'] = array();
		$aGroupCntLevel = array('1'=>'0');
		$lastGrNom = 0;

if (defined('DEBUG_GROUP')){
//print_r($aGroupItem);
f_print_tab($aGroupItem, array_merge(array_keys($aGroup), array('fstRowMain', 'mCount', 'pNom'), $aSumFld));
}
		foreach($aGroupItem as $kI=>&$rI)
		{
			$totalLevel = 1;
			$changeGroupLevel = 0;
if (DEBUG_GROUP > 1)
echo "\n\n<b>**** GroupItem</b>=$kI";
			$keyTotal = '';
			$razdTotal = '';
			foreach($aGroup as $g=>&$vG)
			{
				$keyGr = $rI[$g];
				$rowGr = $this->ar_Data[$g][$keyGr];
if (DEBUG_GROUP > 1)
echo " ???g=$g, changeGroupLevel=$changeGroupLevel, keyGr=$keyGr, prevKey={$vG['prevKey']}, lastGrNom=$lastGrNom, totalLevel=$totalLevel";
				if ( !$changeGroupLevel && $vG['prevKey'] != $keyGr )
				{
					$prevRowGr = $this->ar_Data[$g][$vG['prevKey']];
					$changeGroupLevel = $vG['LevelGroup'];
					$levelBand = 0;
					if ( $rowGr['subLevel'] )
					{
						for($levelBand=max(0,$aGroupCntLevel[$changeGroupLevel]-1); $levelBand>0; $levelBand--)
						{
if (DEBUG_GROUP > 1)
echo "\n     compare subLevel l=$levelBand, {$rowGr['subLevel'][$levelBand-1]}<>{$prevRowGr['subLevel'][$levelBand-1]}";
							if ( $rowGr['subLevel'][$levelBand-1] == $prevRowGr['subLevel'][$levelBand-1] )
								break;
						}
//						$totalLevel += $l+1;
					}
//					$aGroup[$g]['cntSubLevel'] = $l;
if (DEBUG_GROUP > 1){
echo "\n^^^^ g=$g, l=$levelBand, totalLevel=$totalLevel, changeGroupLevel=$changeGroupLevel, lastGrNom=$lastGrNom";
}
//					if ( $aSumFld )
					{
//						if ( $lastGrNom )
//							$aGr[$lastGrNom]['gCount'] = 1;
						for($i=$aGr[$lastGrNom]['nomUpGroup']; 
//							$aGr[$lastGrNom]['LevelTotal']>=$totalLevel;
							$aGr[$lastGrNom]['LevelGroup']>$changeGroupLevel 
								|| $aGr[$lastGrNom]['LevelGroup']==$changeGroupLevel && $aGr[$lastGrNom]['LevelBand']>$levelBand;
							$lastGrNom=$i, $i=$aGr[$i]['nomUpGroup'])
						{
if (DEBUG_GROUP > 1)
echo " |^^^->$i";
//							$aGr[$i]['gCount'] += $aGr[$lastGrNom]['gCount'];
							$aGr[$i]['mCount'] += $aGr[$lastGrNom]['mCount'];
							$aGr[$i]['gCount'] += $aGr[$lastGrNom]['gCount']+1;
							if ( $aSumFld )
							{
if (DEBUG_GROUP > 1)
echo " SUM";
								$this->SumTotal(2, $aSumFld, $aGr[$i], $aGr[$lastGrNom]);
								foreach($aSumFldTurnList as $k=>$v)
								{
									$this->SumTotal(2, $aSumFld, $aGr[$i][$k], $aGr[$lastGrNom][$k]);
								}
							}
						}
						$totalLevel = $aGr[$lastGrNom]['LevelTotal'] + 1;
					}
					if ( $kI == '***' )
					{
						break;
					}
					$aGroupCntLevel[$changeGroupLevel] = $levelBand;
if (DEBUG_GROUP > 1)
echo " ~~~~~~ changeGroupLevel=$changeGroupLevel, aGroupCntLevel=" . json_encode($aGroupCntLevel);
					$rubInGroupLevel[$totalLevel] = sprintf('%03d', ++$nomInGroupLevel[$totalLevel]);
//					$rubInGroupLevel = array_slice($rubInGroupLevel, 0, $totalLevel);
				}
				if ( $changeGroupLevel )
				{
					if ( !($subLevel=$rowGr['subLevel']) )
						$subLevel = array();
					array_push($subLevel, $keyGr);
					$cntSubLevel = count($subLevel);
if (DEBUG_GROUP > 1)
echo "\n++++ g=$g, subLevel=" . json_encode($subLevel);
//					for($i=$aGroup[$g]['cntSubLevel']; $i<$cntSubLevel; $i++)
					for($i=$aGroupCntLevel[$changeGroupLevel]; $i<$cntSubLevel; $i++)
					{
						$keyGr = $subLevel[$i];
						$aGroup[$g]['prevKey'] = $keyGr;
if (DEBUG_GROUP > 1)
echo " |+++[$lastGrNom] totalLevel=$totalLevel, i=$i, keyGr=$keyGr";
						$nomGroupLevel[$totalLevel] = $lastGrNom = count($aGr);
						$aGroupCntLevel[$changeGroupLevel]++;
						$aGr[] = array('group'=>$g, 'key'=>$keyGr, 'keyTotal'=>$keyTotal . $razdTotal . $keyGr, 'name'=> $this->ar_Data[$g][$keyGr][$this->ar_Query[$g]['fName']],
							'LevelGroup'=>$changeGroupLevel, 'nomUpGroup'=>$nomGroupLevel[$totalLevel-1], 'fstRowMain'=>$rI['fstRowMain'],
							'LevelBand'=>$aGroupCntLevel[$changeGroupLevel], 'rub'=>join(array_slice($rubInGroupLevel, 0, $totalLevel)), 'LevelTotal'=>$totalLevel++ );
						$nomInGroupLevel[$totalLevel] = 1;
						$rubInGroupLevel[$totalLevel] = '001';
					}
					if ( $changeGroupLevel == $lastGroupLevel )
					{
						$aGr[$lastGrNom]['mCount'] = $rI['mCount'];
						$aGr[$lastGrNom]['lastLevel'] = 1;
						if ( $aSumFld )
						{
							$this->SumTotal(2, $aSumFld, $aGr[$lastGrNom], $rI);
							foreach($aSumFldTurnList as $k=>$v)
							{
								$this->SumTotal(2, $aSumFld, $aGr[$lastGrNom][$k], $rI[$k]);
							}
						}
					}
					else
						$aGroupCntLevel[++$changeGroupLevel] = 0;
				}
				$keyTotal .= $razdTotal . $keyGr;
				$razdTotal = $this->sepKeys;
/*
				else
				{
//==== уровень не менялся
					if ( $rowGr['subLevel'] )
						$totalLevel += count($rowGr['subLevel']);
					else
						$totalLevel++;
if (DEBUG_GROUP > 1)
echo " |=== totalLevel=$totalLevel";
				}
*/
if (DEBUG_GROUP > 1){
echo "\n_______________ g=$g, totalLevel=$totalLevel";
//print_r($nomInGroupLevel);
}
			}
		}
		$this->ar_Group = &$aGr;
print_r($aSumFld);
if (defined('TIME'))
time_end('GROUP_PREPARE');

//print_r($aGr);
if (defined('DEBUG_GROUP')){
//f_print_tab($aGr, array_merge(array('group', 'nomUpGroup', 'key', 'name', 'LevelGroup', 'LevelTotal', 'fstRowMain', 'lastLevel', 'mCount', 'gCount', 'rub'), array('v1', '1.v1', '2.v1', '1.1.v1', '1.2.v1', '1.3.v1', '2.1.v1', '2.2.v1')));
$arr = array_merge(array('group', 'nomUpGroup', 'key', 'keyTotal', 'name', 'LevelBand', 'LevelGroup', 'LevelTotal', 'fstRowMain', 'lastLevel', 'mCount', 'gCount', 'rub'), $aSumFld);
foreach($aSumFld as $g)
	foreach($aSumFldTurnList as $l=>$v)
		$arr[] = "$l.$g";
f_print_tab($aGr, $arr);
}

if (defined('DEBUG_DATA')){
$g = DEBUG_DATA;
if ( $g == 1 )
	$g = array_keys($this->ar_Data);
else
	$g = explode(',', $g);
foreach($g as $v)
{
	echo "\n<BR/>Print DATA $v";
	f_print_tab($this->ar_Data[$v], array_merge(array_keys(reset($this->ar_Data[$v])), array('cSubLevel')));
}
}

//print_r($aGroupItem);
//=================================================== Формирование ar_Turn[all]
		if ( $aTurnLink )
		{
			$aTurnLink = array_keys($aTurnLink);
			$this->ar_Turn['fields'] = $aTurnLink;
			$this->ar_Turn['levels'] = count($this->ar_Turn['fields']);
			$countTurn = count($aTurnLink)-1;
			$aKeyTurnLevel = array();
			$g='***';
			$l = 0;
			$level = -1;
if (defined('DEBUG_TURN'))
{
//ksort($aSumFldTurnList, SORT_STRING);
//print_r(json_encode(array_keys($aSumFldTurnList)));
echo "\n<BR/>TURN field=" . json_encode($this->ar_Turn['fields']);
f_print_tab($this->ar_Turn['list']);
echo "\n<BR/>TURN make all, countTurn=$countTurn";
}
			foreach($this->ar_Turn['list'] as $k=>&$v)
			{
if (defined('DEBUG_TURN'))
echo "\nk=$k";
				$up = $g = '';
				for($i=0; $i<=$countTurn; $i++, $up = $g, $g.=$this->sepKeys)
				{
					$f = $aTurnLink[$i];
					$g .= $v[$f];
if (defined('DEBUG_TURN'))
echo " || i=$i, keyPrevLevel<>key ? ({$this->ar_Turn['all'][$aKeyTurnLevel[$i]]['key']}<>$g)";
					if ( $this->ar_Turn['all'][$aKeyTurnLevel[$i]]['key'] != $g )
					{
if (defined('DEBUG_TURN'))
echo " <b>!=</b> l=$l";
						$this->ar_Turn['all'][$l] = $v;
						$this->ar_Turn['all'][$l]['key'] = $g;
						$this->ar_Turn['all'][$l]['upKey'] = $up;
						$this->ar_Turn['all'][$l]['level'] = $i+1;
						if ( $i != $countTurn )
							for($s=$i-1; $s>=0; $s--)
								$this->ar_Turn['all'][$aKeyTurnLevel[$s]]['gCount']++;
						else
						{
							$this->ar_Turn['all'][$l]['mCount']=1;
							for($s=$i-1; $s>=0; $s--)
								$this->ar_Turn['all'][$aKeyTurnLevel[$s]]['mCount']++;
						}
						if ( $i <= $level )
							$this->ar_Turn['all'][$aKeyTurnLevel[$i]]['nextNode'] = $l;
						$aKeyTurnLevel[$i] = $l++;
						$level = $i;
					}
				}
			unset($v);
			}

if (defined('DEBUG_TURN')){
$this->ar_Turn['all'][0]['ppp'] = 'kkk';
//$v = array_merge(array_keys($this->ar_Turn['all'][$l-1]), array('gCount','nextNode','level')) // 'key', 'mCount',;
$v = array_merge(array_keys($this->ar_Turn['all'][0]));
f_print_tab($this->ar_Turn['all'], $v);
}
		}
	}

//** Формирование запроса ******************************************************
	function makeQuery($name, &$keyIn) 
	{
/*
select k.*
--, o.* 
from tkls_main k JOIN (
	select n, a.ar[n] as k from generate_series(1, 6) n 
	CROSS JOIN (select ARRAY[521888,511166,522014,520341,520389,143535] as ar
) a) o ON k.kod_kls = o.k 
ORDER BY o.n
*/
		if ( $this->ar_Query[$name]['sqlSelect'] )
			return;
//		if ( !($tab=$this->ar_Query[$name]['table']) )
//			die("Error makeQuery ($name). Missing sqlSelect and table");
		if ( !($fld=$this->ar_Query[$name]['fields']) )
			$fld = '*';
		$key = join('||\'' . $this->sepKeys . '\'||', array_keys($this->ar_Query[$name]['key']));
		$order = $this->ar_Query[$name]['order'];
		if ( !$order )
			$order = $this->ar_Query[$name]['fName'];
		if ( $order )
			$order = " ORDER BY $order";
		$rub = $this->ar_Query[$name]['fRubr'];
		$keyUp = $this->ar_Query[$name]['fKeyUp'];
		$sqlIn .= " IN ('" . join('\',\'', array_keys($keyIn)) . '\')';
		if ( $rub || $keyUp )
		{
			if ( !($tab=$this->ar_Query[$name]['table']) )
				die("Error makeQuery ($name). Missing sqlSelect and table");
if (defined('TIME_COMP_QUERY'))
	$this->ar_Query[$name]['sqlSelectAlt'] = "SELECT up.$key as key_field_, $fld FROM $tab up WHERE $key=";
			if ( $rub )
			{
				$fld = 'up.' . str_replace(',', ',up.', str_replace(' ', '', $fld));
				if ( $this->ar_Query[$name]['condition'] )
					$up = "(SELECT * FROM $tab %%|WHERE {$this->ar_Query[$name]['condition']}%%|)";
				else
					$up = $tab;
				if ( !$order )
					$order = " ORDER BY up.$rub";
				$sqlSelect = "SELECT DISTINCT up.$key as key_field_, $fld FROM $up up JOIN $tab dn ON dn.$rub LIKE rtrim(up.$rub)||'%' AND dn.$key $sqlIn WHERE up.$key > 0{$order}";
			}
			else
			{
				if ( !($start=trim($this->ar_Query[$name]['startWith'])) )
					$start = "$key = COALESCE($keyUp,$key)";
				else
				{
					if ( !strstr($start, '=') && !stristr($start, ' IN ') )
						$start = "$key = $start";
				}
				$sqlSelect = "SELECT * FROM (SELECT $key as key_field_, * FROM $tab CONNECT BY PRIOR $keyUp = $key START WITH $key $sqlIn{$order}) u CONNECT BY PRIOR $key = $keyUp START WITH $start";
			}
		}
		else
		{
			if ( !$sub=$this->ar_Query[$name]['sqlSubSelect'] )
			{
				if ( !($tab=$this->ar_Query[$name]['table']) )
					die("Error makeQuery ($name). Missing sqlSelect and sqlSubSelect and table");
				$sub = "SELECT $key as key_field_, $fld FROM $tab";
			}
			$sqlSelect = "SELECT * FROM ($sub) s WHERE key_field_ $sqlIn{$order}";
		}
		$this->ar_Query[$name]['sqlSelect'] = $sqlSelect;
	}

//** Обработка макросов ********************************************************
	function prepareMacro($sMacro, $isExpr=0)
	{
		$aMacs = explode($this->sepMacro, $sMacro);
		$i = 0;
		if ( substr($col[$text], 0, 2) != $this->sepMacro )
		{
			$i = 1;
			$str .= $aMacs[0];
		}
		for(; $i<count($aMacs); $i+=2)
		{
if (defined('DEBUG_MACRO'))
echo " sMac={$aMacs[$i]}";
			$aMac = explode('.', $aMacs[$i]);
			$vMac = '$row';
			do
			{
				foreach($aMac as $nm=>$sMac)
				{
					if ( $sMac[0] == '#' )
					{
						$sMac = substr($sMac, 1);
						$vMac = '$oReport->tags[$oReport->type][\'' . strtoupper($sMac) . '\']';
						break;
					}
					else
					{
						switch($sMac)
						{
							case 'NOMROW':
								$vMac = '$nomRow';
								break 2;
							case 'MAIN':
								$vMac .= 'MAIN';
								break;
							case 'CGROUP':
								$vMac = '$oReport->rowGr';
								break;
							case 'TCOUNT':
								$vMac = '(' . $vMac . '[\'gCount\']+' . $vMac . '[\'mCount\'])';
								if ( !$isExpr )
									$vMac = '".(' . $vMac . ')."';
								break 3;
							case 'TURNNAME':
								$vMac = '$aTURN_' . $this->iTurn . '[$oReport->ar_Turn[\'fields\'][$aTURN_' . $this->iTurn . '[\'level\']-1] . \'Name\']';
								break 2;
							case 'TURNINDEX':
								$vMac .= '[$aTURN_' . $this->iTurn . '[\'key\']]';
								break;
							case 'TURN':
//????????????? Заглушка
								$vMac = '$aTURN_' . $this->iTurn;
								break;
							default:
//								if ( $link=$this->ar_Query[$name]['LINK'][$sMac] )
								if ( $link=$this->ar_Query[$sMac] )
								{
//==== Link - $oReport->ar_Data['LT1'][$row['k1'].'*'.$row['k2']]
									$arr_ = array();
									foreach($link['key'] as $k=>$v)
										$arr_[] = $k;
									$vMac = '$oReport->ar_Data[\'' . $sMac . '\'][' . $vMac . '[\'' .
										join('].\'*\'.' . $vMac . '[\'', $arr_) . '\']]';
								}
								else
								{
//==== Поле - $row['t1']
									$vMac .= '[\'' . $sMac . '\']';
								}
						}
					}
				}
				if ( !$isExpr )
					$vMac = '{' . $vMac . '}';
			} while(0);
if (defined('DEBUG_MACRO'))
echo ", vMac=$vMac";
			$str .= $vMac . $aMacs[$i+1];
		}
		return $str;
	}

//** Подготовка ячеек строки или подсекции *************************************
	function prepareCells(&$Cells, $name, $iTurnLevel=0)
	{
		$cBreak = "\n";
		foreach($Cells as $nc=>$col)
		{
			switch($col['tag'])
			{
				case 'TurnCells':
					$str .= '";' . $cBreak . '';
					$iTurnLevel++;
					switch($col['on'])
					{
						case 'all':
							$str .= 'foreach($oReport->ar_Turn[\'all\']';
							if ( $iTurnLevel > 1 )
								die("Error parse Band: TurnCells on=\"all\" must bee outline");
							$this->iTurn = 'all';
							$str .= ' as $iTURN_all=>$aTURN_all) {' . $cBreak;
//							$level = strstr('last', $oReport->ar_Turn['levels'], $col['levels']);
							$level = str_replace('last', $this->ar_Turn['levels'], $col['levels']); 
							if ( $level )
								$str .= 'if (!strstr($aTURN_all[\'level\'] . \',\', \'' . ($level . ',') . '\')) continue;' . $cBreak;
							break;
						default:
							$this->iTurn = $iTurnLevel;
							if ( $iTurnLevel > 1 )
							{
								$t = $iTurnLevel;
								$tp = $iTurnLevel-1;
								$str .= "for(\$iTURN_$t=\$iTURN_$tp+1; " .
									"\$iTURN_$t; \$iTURN_$t=\$aTURN_{$t}['nextNode']) {\n".
									"\$aTURN_$t=\$oReport->ar_Turn['all'][\$iTURN_$t];" . $cBreak;
							}
							else
								$str .= "for(\$iTURN_1=0; \$iTURN_1!==null; \$iTURN_1=\$aTURN_1['nextNode']) {\n".
									'$aTURN_1=$oReport->ar_Turn[\'all\'][$iTURN_1];' . $cBreak;
							break;
					}
					$str .= '$str.="' . $this->prepareCells($col['cells'], $name, $iTurnLevel);
					$this->iTurn = --$iTurnLevel;
					$str .= '";' .  $cBreak;
					$str .= '}' . $cBreak . '$str.="';
					break;
				case 'switchBand':
					$skipCaseBand = 0;
					break;
				case 'caseBand':
					if ( $skipCaseBand || $col['for'] != $name && $col['for'] != 'other' )
						break;
					$skipCaseBand = 1;
					$str .= $this->prepareCells($col['cells'], $name, $iTurnLevel);
					break;
				case 'expr':
					$str .= '";' . $cBreak . $this->prepareMacro($col['text'], 1) . ' $str.="';
					break;
				case 'Cell':
					$sCell = '<' . $this->tags[$this->type]['CELL'];
//					if ( $s=$col['rowspan'] ) $sCell .= ' rowspan=\"' . $this->prepareMacro($s) . '\"';
//					if ( $s=$col['colspan'] ) $sCell .= ' colspan=\"' . $this->prepareMacro($s) . '\"';
//					if ( $s=$col['style'] ) $sCell .= ' style=\"' . $this->prepareMacro($s) . '\"';
					if ( $type=$col['type'] )
					{
						if ( $type == 'float' )
							$col['class'] .= ' r';
					}
					foreach(array('rowspan','colspan','style','class') as $v)
						if ( $s=$col[$v] )
							$sCell .= ' ' . $v . '=\"' . $this->prepareMacro($s) . '\"';
					$sCell .= '>';
					$sCell .= $this->prepareMacro(iconv('utf-8', 'cp1251', $col['text']));
					$sCell .= '</' . $this->tags[$this->type]['CELL'] . '>';
					if ( $s=$col['condition'] )
					{
						$str .= '";' . $cBreak . 
							'if (' . $this->prepareMacro($s, 1) . ') {' . 
							$cBreak . '$str.="' . $sCell . '";' . $cBreak . '}' . $cBreak .
							'$str.="';
					}
					else
						$str .= $cBreak . $sCell;
					break;
			}
		}
		return $str;
	}
//** Подготовка секции вывода **************************************************
	function prepareBand(&$Rows, $name)
	{
if (DEBUG_BAND > 0)
echo "\n<BR/>BAND-------------- name=$name   ";
		$cBreak = "\n";
		$str = '$str="';
		$iTurnLevel = -1;
		foreach($Rows as $nr=>&$row)
		{
			$str .= $cBreak . '<' . $this->tags[$this->type]['ROW'] . ' class=\"$classBand\">' . $this->prepareCells($row, $name) . $cBreak . '</' . $this->tags[$this->type]['ROW'] . '>' . $cBreak;
		}
		$str .= '";';
		return $str;
	}

//** Формирование отчета *******************************************************
	function printReport()
	{
if (defined('TIME'))
time_start('PRINT');
		if ( $this->type == 'HTML' )
			$text = '<html>
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
<body>
<table border="1" class="MainTable" style="empty-cells: show; width: 80%">
';

//print_r($this->ar_Band);
/*
if (defined('DEBUG_PRINT'))
{
$v = array_merge(array_keys($this->ar_Turn['all'][count($this->ar_Turn['all'])-1]), array('key', 'mCount','gCount','nextNode'));
f_print_tab($this->ar_Turn['all'], $v);
f_print_tab($this->ar_Group, array_merge(array('group', 'nomUpGroup', 'key', 'name', 'LevelBand', 'LevelGroup', 'LevelTotal', 'fstRowMain', 'lastLevel', 'mCount', 'gCount', 'rub'), array('v1', '1.v1', '2.v1', '1*1.v1', '1*2.v1', '1*3.v1', '2*1.v1', '2*2.v1')));
}
*/
		$lastTotalLevel = -1;
		array_push($this->ar_Group, array('LevelTotal'=>0));

if (defined('DEBUG_PRINT'))
echo "\nHEAD";
		if ( $f=$this->ar_Band['HEAD']['*']['header']['func'] )
		{
if (defined('DEBUG_PRINT'))
echo " <b>!!! $f</b>";
			$text .= $f($this, $this->ar_Group[0], 0, $this->ar_Data['MAIN'][0], 'ColCaption HEAD', $rowGr['rub']);
		}
		foreach($this->ar_Group as $nGr=>&$rowGr)
		{
			$totalLevel = max(0,$rowGr['LevelTotal']);
			for(; $lastTotalLevel>=$totalLevel; $lastTotalLevel-- )
			{
				$iGr = $aLevel[$lastTotalLevel];
				$nF = $this->ar_Group[$iGr]['group'];
if (defined('DEBUG_PRINT'))
echo "\nfooter {$this->ar_Group[$iGr]['group']}-{$nF}-{$this->ar_Group[$iGr]['LevelBand']}--{$this->ar_Group[$iGr]['key']}-{$this->ar_Group[$iGr]['rub']}";
				if ( $f=$this->ar_Band[$nF][$this->ar_Group[$iGr]['LevelBand']]['footer']['func'] ||
					$f=$this->ar_Band[$nF]['*']['footer']['func'] )
				{
if (defined('DEBUG_PRINT'))
echo " <b>!!! $f</b>";
					$text .= $f($this, $this->ar_Group[$iGr], $iGr, $this->ar_Data['MAIN'][$this->ar_Group['fstRowMain']], "RowGrpFoot_{$this->ar_Group[$iGr]['LevelGroup']} $nF {$nF}_{$this->ar_Group[$iGr]['LevelBand']}", $this->ar_Group[$iGr]['rub']);
				}
			}
			if ( $rowGr['group'] )
			{
				$nF = $rowGr['group'];
if (defined('DEBUG_PRINT'))
echo "\n<b>header</b> {$rowGr['group']}-{$nF}-{$rowGr['LevelBand']}--{$rowGr['key']}-{$rowGr['rub']}";	// . json_encode($this->ar_Band['G1']['*']['header']['func']);
				if ( ($f=$this->ar_Band[$nF][$this->ar_Group[$iGr]['LevelBand']]['header']['func']) ||
					($f=$this->ar_Band[$nF]['*']['header']['func']) )
				{
if (defined('DEBUG_PRINT'))
echo " <b>!!! $f</b>";
					$text .= $f($this, $rowGr, $nGr, $this->ar_Data['MAIN'][$rowGr['fstRowMain']], "RowGrpHead_{$rowGr['LevelGroup']} $nF {$nF}_{$rowGr['LevelBand']}", $rowGr['rub']);
				}
				$lastTotalLevel = $totalLevel;
				$aLevel[$lastTotalLevel] = $nGr;
			}
			if ( $rowGr['lastLevel'] )
			{
				$this->rowGr = $rowGr;
if (defined('DEBUG_PRINT'))
echo "\nMAIN";
				for($iMain=0; $iMain<$rowGr['mCount']; $iMain++)
				{
					if ( $f=$this->ar_Band['MAIN']['*']['']['func'] )
					{
if (defined('DEBUG_PRINT'))
echo ' <b>' . ($rowGr['fstRowMain']+$iMain) . '</b>';
						$text .= $f($this, $this->ar_Data['MAIN'][$rowGr['fstRowMain']+$iMain], $iMain+1, $this->ar_Data['MAIN'][$rowGr['fstRowMain']+$iMain], 'DataCell MAIN', $rowGr['rub']);
					}
				}
			}
		}
		if ( $this->type == 'HTML' )
			echo '
</table>
</body></html>';

if (defined('TIME'))
{
time_end('PRINT');
time_end('TOTAL');
time_print();
}

		return $text;
	}

//** Обработка стартовых ТЭГов *************************************************
	function startElement($parser, $tag, $attrs) 
	{
		$this->depth++;
//echo "\n$tag $this->depth";
//print_r($oReport->ar_Query);
		$attrs['tag'] = $tag;
		switch($tag)
		{
			case 'Query':
				$name = $attrs['name'];
				unset($attrs['name']);
				if ( $attrs['attachFld'] )
					$attrs['attachFld'] = array_flip(explode(',', $attrs['attachFld']));
				$this->ar_Query[$name] = $attrs;
				if ( !$attrs['CONN'] )
					$this->ar_Query[$name]['CONN'] = 'DEFAULT';
				$this->lastTag[$this->depth] = array('tag'=>$tag, 'name'=>$name, 'item'=>&$this->ar_Query[$name]);
				break;
			case 'Turn':
				$lastTag = $this->lastTag[$this->depth-1];
				if ( $lastTag['tag'] != 'Query' || $lastTag['name'] != 'MAIN' )
					die('<Turn> may be only in Query MAIN');
				$lastTag['item']['TURN'] = $attrs;
				break;
			case 'Group':
				$lastTag = $this->lastTag[$this->depth-1];
				if ( $lastTag['tag'] != 'Query' || $lastTag['name'] != 'MAIN' )
					die('<Group> may be only in Query MAIN');
				$t = 'GROUP';
				$n = $attrs['to'];
				$lastTag['item'][$t][$n] = array('key'=>array_flip(explode(',', $attrs['reference'])), 'aKeys'=>array());
				if ( !$this->ar_Query[$n] )
					die("startElement: Missing Query ($n) for $tag ({$lastTag['name']})");
				if ( !$this->ar_Query[$n]['key'] )
					$this->ar_Query[$n]['key'] = $lastTag['item'][$t][$n]['key'];
				break;
			case 'Link':
				$lastTag = $this->lastTag[$this->depth-1];
				if ( $lastTag['tag'] != 'Query' )
					die('<Link ...> may be only in Query');
				$t = 'LINK';
				$n = $attrs['to'];
				$lastTag['item'][$t][$n] = array('key'=>array_flip(explode(',', $attrs['reference'])), 'aKeys'=>array());
				if ( !$this->ar_Query[$n] )
					die("startElement: Missing Query ($n) for $tag ({$lastTag['name']})");
				if ( !$this->ar_Query[$n]['key'] )
					$this->ar_Query[$n]['key'] = $lastTag['item'][$t][$n]['key'];
				break;
			case 'Band':
//print_r($this->ar_Band);
				unset($this->Band);
//				$this->Band = null;
				$this->Band = array('attrs'=>$attrs, 'rows'=>array());

				$this->iBand = 0;
				$this->lastTag[$this->depth] = array('tag'=>$tag, 'cells'=>&$this->Band['rows'][$this->iBand]);
				break;
			case 'switchBand':
				$this->lastTag[$this->depth] = $tag;
				$this->lastTag[$this->depth-1]['cells'][] = $attrs;
				break;
			case 'caseBand':
				if ( $this->lastTag[$this->depth-1] != 'switchBand' )
						die('<caseBand ...> may be only in switchBand');
				$attrs['cells'] = array();
				$this->lastTag[$this->depth] = array('tag'=>$tag, 'cells'=>&$attrs['cells']);
				$this->lastTag[$this->depth-2]['cells'][] = $attrs;
				break;
			case 'TurnCells':
				$attrs['cells'] = array();
				$this->lastTag[$this->depth] = array('tag'=>$tag, 'cells'=>&$attrs['cells']);
				$this->lastTag[$this->depth-1]['cells'][] = $attrs;
				break;
			case 'expr':
				$this->lastTag[$this->depth-1]['cells'][] = $attrs;
				break;
			case 'Cell':
				$this->lastTag[$this->depth-1]['cells'][] = $attrs;
				break;
			case 'BROW':
//echo "++++++++++++ " . count($this->Band[$this->iBand]);
				$this->lastTag[$this->depth-1]['cells'] = &$this->Band['rows'][++$this->iBand];
				break;
			case 'Print':
				echo $this->printReport();
			default:
//echo "??????????? ==" . $tag . '== ' . json_encode($attrs);
		}
	}

	function endElement($parser, $tag) 
	{
		$this->depth--;
		switch($tag)
		{
			case 'QuerySections':
				$this->prepareData();
				break;
			case 'Band':
				$attrs = $this->Band['attrs'];
				if ( !($name=$attrs['name']) )
					$name = 'NONAME';
				if ( !($level=$attrs['level']) )
					$level = '*';
				if ( !($place=$attrs['place']) )
				{
//					$place = $name == 'HEAD' ? 'header' : 'footer';
					$place = 'header';
				}
				if ( !($for=$attrs['for']) )
				{
					$for = array($name);
				}
				else
				{
					if ( $for == 'all' )
					{
						$for = array_keys($this->ar_Query['MAIN']['GROUP']);
						$for[] = 'MAIN';
					}
					else
						$for = explode(',', $for);
				}
//print_r($for); 
				foreach($for as $fv)
				{
					$str = $this->prepareBand($this->Band['rows'], $fv);
					$str = '$nameBand="' . $fv . '"; ' . $str . ' return $str;';
if (DEBUG_BAND == 2)
print_r($this->Band['rows']);
if (DEBUG_BAND > 0)
echo "\nPARSE " . debug_parse($str);
					$pFunc = create_function('&$oReport, &$row, $nomRow, &$rowMAIN, $classBand=\'\', $rubr_group=\'\'', $str);
					if ( !$pFunc )
					{
						$this->print_text_section($str, $fv, $place, $level);
						die();
					}
					if ( $fv == 'MAIN' )
					{
						$this->ar_Band[$fv]['*']['']['func'] = $pFunc;
					}
					else
					{
						$this->ar_Band[$fv][$level][$place]['func'] = $pFunc;
					}
				}
				break;
//			case 'Report':
//				$this->printReport();
				break;
		}
	}

	function print_text_section($text, $name='', $footer=0, $level='*')
	{
		//echo "<pre>\n*********** Секция=$name FOOTER=$footer LEVEL=$level ***\n" . debug_parse($text) . "\n</pre>";
		echo "<pre>\n*********** Секция=$name FOOTER=$footer LEVEL=$level ***\n";
		$arr = split( "\n", debug_parse($text));
		$i = 1;
		if ( PRINT_SECTION == 2 )
			echo "create_function('','<br>";
		foreach($arr as $str)
		{
			if ( PRINT_SECTION == 2 )
				echo ereg_replace("'", "\'", $str) . "<br>";
			else
				echo "<b>" . ($i++) . "</b>).&nbsp;&nbsp;&nbsp;$str<br>";
		}
		if ( PRINT_SECTION == 2 )
			echo "');<br>";
		echo "\n</pre>";
	}

	function startData($parser, $text) 
	{
//echo '!!!=' . $text . '=!!!';
//		$this->Band['rows'][$this->iBand][count($this->Band['rows'][$this->iBand])-1]['text'] = $text;
		$this->lastTag[$this->depth-1]['cells'][count($this->lastTag[$this->depth-1]['cells'])-1]['text'] .= "$text\n";
	}

	function parse(&$rep, $xml_str)
	{
		global $oReport;
//		$oReport = &$this;
//		$aXml = domxml_open_mem($xml_str);
//		$aXml = domxml_xmltree($xml_str);
//print_r($aXml);
		$xml_parser = xml_parser_create();
		xml_set_element_handler($xml_parser, 'startElement', 'endElement');
		xml_set_character_data_handler($xml_parser, 'startData');
		xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
		if (!xml_parse($xml_parser, $xml_str)) {
		    die(sprintf("XML error: %s at line %d",
		                xml_error_string(xml_get_error_code($xml_parser)),
		                xml_get_current_line_number($xml_parser)));
		}
		xml_parser_free($xml_parser);
	}
	
	
}

//!!! Ф-я есть только в PHP 5. Обратить внимание на порядок, нужен как в a2 
/*
function array_intersect_key(&$a1, &$a2)
{
	$c = array();
	foreach($a2 as $k=>$v)
		if ( array_key_exists($k, $a1) )
			$c[$k] = $a1[$k];
	return $c;
}
*/
function startElement($parser, $name, $attrs) 
{
	global $oReport;
//echo " i+{$oReport}+ ";
//print_r($oReport);
	$oReport->startElement($parser, $name, $attrs);
}
function startData($parser, $data)
{
	global $oReport;
	$data = trim($data);
	if ( $data )
	{
		$oReport->startData($parser, $data);
	}
}
function endElement($parser, $name)  
{
	global $oReport;
	$oReport->endElement($parser, $name) ; 
}

//** Функция для сортировки ключевых массивов по pNom
function cmpPNom($ka, $kb)
{
	global $arraySort;
	return ( $arraySort[$ka]['pNom'] >= $arraySort[$kb]['pNom'] ) ? 1 : -1;
}

?>
