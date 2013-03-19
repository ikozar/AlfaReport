<?
global $oReport, $arraySort, $configure;	//, $SQL;
//include_once('json/JSON.php');
//define('DEBUG_GROUP',			2);				// 2 - трассировка
//define('DEBUG_DATA',			1);				// 1 все, или запросv через ','
//define('DEBUG_LINK',			1);				// 1 все, или запросv через ','
//define('DEBUG_LINK',			'tkls_main');				// 1 все, или запросv через ','
//define('DEBUG_MACRO',			1);
//define('DEBUG_PRINT',			1);
//define('DEBUG_EXEC',			1);
//define('DEBUG_TURN',			1);					// 2 - формирование ar_Turn
//define('DEBUG_READMAIN',		1);
//define('DEBUG_SUBLEVEL',		1);
//define('DEBUG_IDXCOLUMN',	1);
if ( $_REQUEST['urp_print_query'] )
	define('DEBUG_EXEC',			$_REQUEST['urp_print_query']);
if ( $_REQUEST['urp_time'] )
	define('TIME',					$_REQUEST['urp_time']);
if ( $_REQUEST['urp_print_data'] )
	define('DEBUG_DATA',			$_REQUEST['urp_print_data']);				// 1 все, или запросv через ','
if ( $_REQUEST['urp_print_band'] )
	define('DEBUG_BAND',			$_REQUEST['urp_print_band']);				// 1 все, или запросv через ','

//define('TIME_COMP_QUERY',	1);
//define('CRP_INF_GROUP',			0x0001);
define('CRP_INF_SORT',			0x0002);			// готовить информациі для сортировки
define('CRP_TQRY_DATA',			0x0010);			// typeQuery
define('CRP_TQRY_SET',			0x0020);
define('CRP_TQRY_DATASET',		0x0030);
define('CRP_TQRY_ALL',			0x0040);
define('CRP_CMP_DESC',			0x0100);			// сортировать по убvваниі
define('CRP_ORD_CALC',			0x0400);			// сортировать по вvчисляемому полу
define('CRP_QRY_PRINT',			0x1000);			// QUERY MAIN или GROUP
define('CRP_QRY_GROUP',			0x2000);			// QUERY MAIN или GROUP

define('CRP_OUT_BODY',			0x1000);			// вvводить HTML и BODY
define('CRP_OUT_TABLE',			0x2000);			// вvводить TABLE
define('CRP_OUT_ALL',			0x3000);
define('CRP_OUT_DATA',			0x4000);			// вvводить даннvе в JSON
define('CRP_OUT_TABS',			0x8000);			// вvводить даннvе в DIV и TABS

//define('RP_TYPE_PHP_CHAR',	0x0001);			// символv HTML
define('CRP_TYPE_FMT_CHAR',	0x0002);			// символv форматирования вvвода
define('CRP_TYPE_TXT_CHAR',	0x0004);			// символv простого вvвода
define('CRP_TYPE_CHAR',			0x000F);

define('CRP_TYPE_HTML_OUT',	0x0010);			// Tі+и для HTML
define('CRP_TYPE_XML_OUT',		0x0020);			// Tі+и для XML
define('CRP_TYPE_JSFM_OUT',	0x0100);			// вид JSON для форм
define('CRP_TYPE_OUT',			0x01F0);

define('CRP_TYPE_HTML',			0x3012);			// Tі+и для HTML
define('CRP_TYPE_XML',			0x0024);			// Tі+и для XML
define('CRP_TYPE_JSFM',			0x3102);			// размерка секций JS ф-я

if ( !$configure )
	include_once('setConParam.php');
include_once('f_print_r.php');
include_once('Class.Query.php');

$oReport = null;

class ReportBuilder
{
var $ar_Query = array();							// массивv текстов запросов и разной лабудени
var $ar_Group = array();
var $aGroupList = array();							// массив групп в MAIN
var $aGroupCalcOrder = array();					// массив полей сортировки групп
var $ar_Turn = array();								// массив значений поворота
var $ar_Band = array();								// массивv для секций
var $ar_Style = array();							// массив стилей
var $ar_Const = array();							// массив констант
var $aLinks = array();								// массив ведомvх запросов
var $aKeys = array();								// массив клічей требуемvх LINKов
var $aCntRef = array();								// массив числа ссvлок 
var $aGroupFld = array(								// массив полей группv
'group'=>1, 'nomUpGroup'=>1, 'key'=>1, 'keyTotal'=>1, 'name'=>1, 'LevelBand'=>1, 'LevelGroup'=>1, 'LevelTotal'=>1, 'lastLevel'=>1, 'mCount'=>1, 'gCount'=>1, 'rub'=>1
);
var $aSumTurn = array();							// массив суммирования
var $aFormat = array(								// массив форматирования
	'dec'=>2, 'point'=>'%A%.%A%', 'thousand'=>"\xA0",
	'float'=>array('func'=>'number_format', 'args'=>array('dec','point','thousand')),
);
var $aSettings = array();
var $depth = 0;
var $lastTag = array();
var $sepKeys = '*';									// разделитель значений клічей
var $sepField = '*';									// разделитель значений составного поля
var $sepMacro = '%%';								// ограничитель іLіі+
var $sepID = '/';										// разделитель полей
var $Band;
var $aConn;		// = array('DEFAULT'=>$SQL);
//var $json;
var $type = CRP_TYPE_HTML;
var $assocData = false;								// вvвод в JSON в ASSOC массив
var $tags = array(
//	RP_TYPE_PHP_CHAR=>array('LT'=>'<', 'GT'=>'>', 'AND'=>'&&'),
	CRP_TYPE_FMT_CHAR=>array('BR'=>'<BR/>', 'NBR'=>'&nbsp;', 'LT'=>'<', 'GT'=>'>', 'AND'=>'&&', 'SUM'=>'&sum;'),
	CRP_TYPE_TXT_CHAR=>array('BR'=>"\n", 'LT'=>'<', 'GT'=>'>', 'AND'=>'&&', 'SUM'=>'Sum:'),
	CRP_TYPE_HTML_OUT=>array('CELL'=>'TD', 'ROW'=>'TR'),
	CRP_TYPE_XML_OUT=>array('CELL'=>'C', 'ROW'=>'R'),
	CRP_TYPE_JSFM_OUT=>array('CELL'=>'TD', 'ROW'=>'TR')
	);

//Style Horizontal="Center" WrapText="1" Bold="1"

var $rootKeyUp = 'IS NULL';	// признак вершинv дерева 

	function ReportBuilder()
	{
//		$this->json = new Services_JSON();
		global $configure;
	  	$this->aConn = array('DEFAULT'=>new CQuery($configure['connect']));
	  	if ( $configure['iniConnect'] )
	  	{
//echo $configure['iniConnect'];
	  		$this->aConn['DEFAULT']->connect->exec($configure['iniConnect']);
		}
	  	$this->params = $_REQUEST;
	  	if ( $_REQUEST['assocData'] )
	  		$this->assocData = true;

	}

//** +тение групповvх и ведомvх запросов по клічам *****************************
	function getDataQuery($name, &$keyIn)
	{
//echo "\n<BR> getDataQuery $name";
if (defined('TIME'))
time_start('DATA', $name);
		$this->makeQuery($name);

if (defined('TIME_COMP_QUERY')) {
//***
if ($this->ar_Query[$name]['sqlSelectAlt']) {
$sql = &$this->aConn[$this->ar_Query[$name]['CONN']];
$s1 = $sql->sql_mod($this->ar_Query[$name]['sqlSelect'], $this->params);
$s2 = $sql->sql_mod($this->ar_Query[$name]['sqlSelectAlt'], $this->params);
time_start('COMP_QUERY_ONE_READ');
$a = $sql->sql_query($s1);
time_end('COMP_QUERY_ONE_READ');
time_start('COMP_QUERY_MULTY_READ');
foreach($keyIn as $v)
	$a = $sql->sql_query($s2 . $v);
time_end('COMP_QUERY_MULTY_READ'); }
//***
}
		$procData = $this->ar_Query[$name]['typeQuery'];
//		$this->params['KEYS_LINK'] = " IN ('" . join('\',\'', array_keys($keyIn)) . '\')';
		if ( !$keyIn && !($this->ar_Query[$name]['typeQuery'] & CRP_TQRY_ALL) )
		{
//echo "\n<BR>+++++++++$name-" . ($this->ar_Query[$name]['typeQuery'] & CRP_TQRY_ALL);
//f_print_r($keyIn, '$keyIn');
			return;
		}
		if ( $this->ar_Query[$name]['typeQuery'] & CRP_ORD_CALC )
		{
			if ( $this->ar_Query[$name]['typeQuery'] & CRP_CMP_DESC )
				arsort($keyIn);
			else
				asort($keyIn);
//f_print_r($keyIn, $name);
			foreach($keyIn as $k=>$v)
				$keyIn[$k] = sprintf('%03d', ++$i);
//f_print_r($keyIn, $name);
			$procData = CRP_ORD_CALC;
		}
		if ( count($this->ar_Query[$name]['fKey']) > 1 
			|| $this->ar_Query[$name]['FIELDS_TYPE'][$this->ar_Query[$name]['fKey'][0]] == 2 )
			$this->params['KEYS_LINK'] = '\'' . join('\',\'', array_keys($keyIn)) . '\'';
		else
			$this->params['KEYS_LINK'] = join(',', array_keys($keyIn));

		if ( $this->ar_Query[$name]['typeQuery'] & CRP_TQRY_DATA )
		{
//==== parentData для суммирования SETов в родительскуі вvборку, а ее в группv
			$parentData = $this->ar_Query[$name]['parentData'];
if (DEBUG_GROUP > 2)
echo "\n<BR/>SUMM name=$name, TO parentData=$parentData";
			if ( $parentData )
				$this->processDataQuery($name, $this->ar_Data[$parentData]);
			else
			{
				$tmpArrSum = array();
				$this->processDataQuery($name, $tmpArrSum);			// Tипа ссvлка от MAIN
				if ( ($aSumFld=$this->ar_Query[$name]['sum'])
					&& ($k=$this->ar_Query['MAIN']['Link'][$name]['key']) )
				{
if (DEBUG_GROUP > 2)
echo "\n<BR/>Sum TO MAIN from $name";
					$fGeyKey = $this->fKeyCreator($k);
					$aSumFld = explode(',', $aSumFld);
					$aSumTurnCutALL = $this->aSumTurn[$name]['turnCutALL'];
					foreach($this->ar_Data['MAIN'] as $g=>&$gArr)
					{
						foreach($gArr as &$row)
						{
							$k = $fGeyKey($row);
							if ( $tmpArrSum[$k] )
							{
								$row = array_merge($row, $tmpArrSum[$k]);
								$this->SumTotal($this->aGroupList[$g], $tmpArrSum[$k], $aSumFld, $aSumTurnCutALL, $name);
							}
						}
					}
					unset($gArr);
				}
				unset($tmpArrSum);
			}
			return;
		}

		$collapsedLevel = $this->ar_Query[$name]['collapseLevel'];

		$cnt = $this->execDataQuery($name);
		$newRow = &$this->ar_Data[$name];
		$fKeyUp = $this->ar_Query[$name]['fKeyUp'];
//		$i = 1;
		while($a=$this->getDataRow($name))
		{
			$key_ = $a['key_field_'];
			if ( $this->ar_Query[$name]['appendFld'] )
				$a = array_merge($a, $this->aKeys[$name][$key_]);
//echo " -$key_- ";
//==== pNom - ном іі для сортировки групп и TURNов
			if ( $procData & CRP_INF_SORT )
				$a['pNom'] = sprintf('%03d', ++$i);
			elseif ( $procData & CRP_ORD_CALC )
				$a['pNom'] = $keyIn[$key_];

//==== для "деревяннvх" групп формирование subLevel
			if ( $fKeyUp )
			{
//if (defined('TIME'))
//time_start('DATA', $name . '_SUBLEVEL');
if (defined('DEBUG_SUBLEVEL'))
echo "\n<BR/>key_=$key_";
				if ( ($level=$newRow[$a[$fKeyUp]]) )
				{
//==== есть батя
if (defined('DEBUG_SUBLEVEL'))
echo ", level=$level";
					$level = $level['level'] + 1;
if (defined('DEBUG_SUBLEVEL'))
echo "->$level, level_=$level_";
					if ( $level > $level_ )
						$subLevel[] = $a[$fKeyUp];
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
				if ( $collapsedLevel && $level >= $collapsedLevel )
					$a['isCollapsed'] = 1;
				if ( $subLevel )
				{
					$a['subLevel'] = $subLevel;
if(defined('DEBUG_DATA'))
$a['cSubLevel'] = json_encode($subLevel);
				}
//if (defined('TIME'))
//time_end('DATA', $name . '_SUBLEVEL');
			}
			elseif ( $collapsedLevel )
				$a['isCollapsed'] = 1;

			$newRow[$key_] = $a;
/*
			if ( $this->typeOUT == CRP_TYPE_JSFM_OUT )
			{
				$newRow['pCount'] = 0;
				$newRow['pCountSet'] = 0;
			}
*/
			if ( $this->ar_Query[$name]['Link'] )
				foreach($this->ar_Query[$name]['Link'] as $l=>&$v)
				{
if (DEBUG_LINK==1 || DEBUG_LINK==$v['baseName'])
$d = "\n<BR/>Link=$l";
					$l = $v['baseName'];
					$k = $v['GetKey']($a);
					if ( $k !== '' && !array_key_exists($k, $this->aKeys[$l]) )
					{
if (DEBUG_LINK==1 || DEBUG_LINK==$l)
echo "$d, baseName=$l, key=$k";	//, type=" . gettype($k);
						if ( $this->ar_Query[$l]['appendFld'] )
							$this->aKeys[$l][$k] = array_intersect_key_($newRow, $this->ar_Query[$l]['appendFld']);
						else
							$this->aKeys[$l][$k] = 1;
					}
				}
		}

if (defined('TIME'))
time_end('DATA', $name);
//		return $newRow;
	}

//** +тение строки с убиранием пробелов ****************************************
	function getDataRow($name, $vid=PDO::FETCH_ASSOC)	//PGSQL_ASSOC)
	{
		$conn = $this->aConn[$this->ar_Query[$name]['CONN']];
		$ret = $conn->getDataRow($vid);
		if ( $ret )
		{
			if ( !$this->ar_Query[$name]['FIELDS'] )
			{
				$this->ar_Query[$name]['FIELDS'] = array_keys($ret);
				foreach($this->ar_Query[$name]['FIELDS'] as $n=>$f)
				{
					$a = $conn->stmt->getColumnMeta($n);
					$this->ar_Query[$name]['FIELDS_TYPE'][$f] = $a['pdo_type'];
				}
//f_print_r($this->ar_Query[$name]['FIELDS_TYPE'], $name);
			}
			foreach($ret as $f=>&$v)
			{
				if ( $this->ar_Query[$name]['FIELDS_TYPE'][$f] == 2 )
				{
					if ( $this->aCode['data'] )
						$v = iconv($this->aCode['data'], 'utf-8', rtrim($v));
					else
						$v = rtrim($v);
				}
				elseif ( $v === null )
					$v = '';
			}
//f_print_r($ret, $name);
		}
		return $ret;
	}

//** Tvполнение запроса в +- на получение даннvх *******************************
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
		$index_result = $this->aConn[$this->ar_Query[$name]['CONN']]->prepare($sqlText);
		return $index_result;
	}

//** Tvполнение запроса в +- на получение даннvх *******************************
	function execDataQuery($name)
	{
		$conn = $this->aConn[$this->ar_Query[$name]['CONN']];
		$sqlText = $this->ar_Query[$name]['sqlSelect'];
		if ( $this->aCode['db'] )
			$sqlText = iconv('utf-8', $this->aCode['db'], $sqlText);
		return $conn->execQuery($sqlText, $this->params, $name);
	}

//** Tуммирование	из в по полям и осям поворота
	function SumTotal(&$aSumDest, &$aSumSour, &$aSumFld, &$aCutSum, $from/*, &$aDCutSum=null*/)
	{
if (DEBUG_GROUP > 2)
echo "\n<BR><b>+++!!! </b>";
		foreach($aSumFld as $sFld)
		{
			$aSumDest[$sFld] += $aSumSour[$sFld];
if (DEBUG_GROUP > 2)
echo " [$sFld]+$aSumSour[$sFld]={$aSumDest[$sFld]}";
		}
		foreach($aCutSum as $sCuts)
		{
//echo " <b>$sCuts</b>:";
			foreach($aSumFld as $sFld)
			{
				$aSumDest[$sCuts][$sFld] += $aSumSour[$sCuts][$sFld];
if (DEBUG_GROUP > 2)
echo " [$sCuts][$sFld]+{$aSumSour[$sCuts][$sFld]}={$aSumDest[$sCuts][$sFld]}";
			}
		}
	}

//** іолучение ф-ции формирования кліча ****************************************
	function fKeyCreator(&$aKeyGroup)
	{
		foreach($aKeyGroup as $k)
			$arr_[] = '$row[\'' . $k . '\']';
		$tGetKey = 'return ' . join('.\'' . $this->sepKeys . '\'.', $arr_) . ';';
		return create_function('&$row', $tGetKey);
	}

//** іодготовка наборов даннvх *************************************************
	function processDataQuery($name, &$aDestSumm)
	{
		global $arraySort;

if (defined('DEBUG_EXEC'))
echo "\n<BR>process DataSet <b>$name</b>";

if (defined('TIME'))
time_start('DATA', $name . '_EXEC');
//		$lastRows = !$this->execDataQuery($name);		//-1;
		$lastRows = false;
if (defined('TIME'))
time_start('QUERY', $name . '_EXEC');
		$this->execDataQuery($name);
if (defined('TIME'))
time_end('QUERY', $name . '_EXEC');
		if ( !$row=$this->getDataRow($name) )
			return;
		$this->ar_Query[$name]['FIELDS'] = array_keys($row);
if (defined('TIME'))
time_end('DATA', $name . '_EXEC');

		if ( ($aKeyTurn=$this->ar_Query[$name]['TURN']['key']) )
		{
//			$aKeyTurn = explode(',', $aKeyTurn);
//			$kKeyTurn = count($aKeyTurn);
			$aKeyTurn = array_flip(explode(',', $aKeyTurn));
		}
		$aLink = &$this->ar_Query[$name]['Link'];
		$aSumCutsTurnTree = array();
		$aSumCutsTurnTRVal = array();
		$aSumCutsTurnList = array();
		$aSumCutsTurnALLVal = array();
		$aPrintFld = $this->ar_Query[$name]['FIELDS'];
		$aTurnFld = array();
		if ( ($aFlOnTurn=$this->ar_Query[$name]['TURN']['on']) )
		{
			$aFlOnTurn = explode(',', $aFlOnTurn);
			$this->ar_Query[$name]['TURN']['onArray'] = $aFlOnTurn;
			$nmFstOnTurn = $aFlOnTurn[0];
			$nmLstOnTurn = end($aFlOnTurn);
			$aFlOnTurn = array_flip($aFlOnTurn);
			$this->ar_Query[$name]['TURN']['onLink'] = $aFlOnTurn;
			$nomFstOnTurn = array_search($nmFstOnTurn, $this->ar_Query[$name]['FIELDS']);
			if ( ($nomLastOnTurn=array_search($nmLstOnTurn, $this->ar_Query[$name]['FIELDS'])) )
			{
				$aTurnFld = array_slice($this->ar_Query[$name]['FIELDS'], $nomLastOnTurn+1);
				$aPrintFld = array_slice($this->ar_Query[$name]['FIELDS'], 0, $nomFstOnTurn);
			}
			if ( $nomFstOnTurn < 1 )
				die("\n<BR/>ERROR processDATA: Don't find field ($nmFstOnTurn) for turn; Fields: " . json_encode($this->ar_Query[$name]['FIELDS']));
//f_print_r($aLink);
			foreach($aLink as $l=>&$k)
			{
				$l = $k['baseName'];
				foreach($k['key'] as $f)
				{
//echo " ---Link l=$l, f=$f (" . array_search($f, $this->ar_Query[$name]['FIELDS']) . ",$nomFstOnTurn)--- ";
					if ( array_search($f, $this->ar_Query[$name]['FIELDS']) >= $nomFstOnTurn )
					{
//==== +сли key после TURNа - готовим информациі для сортировки для упорядочения TURNа
						$k['afterTurn'] = 1;
						$this->ar_Query[$l]['typeQuery'] |= CRP_INF_SORT;
						if ( array_key_exists($f, $aFlOnTurn) )
						{
							$this->ar_Query[$name]['TURN']['onLink'][$f] = $l;
						}
					}
				}
			}
		}
		$fKeyUp = $this->ar_Query[$name]['fKeyUp'];
		$collapsedLevel = $this->ar_Query[$name]['collapseLevel'];

//f_print_r($this->ar_Query[$name]);
		if ( ($aSumFld=$this->ar_Query[$name]['sum']) )
			$aSumFld = explode(',', $aSumFld);
		else
			$aSumFld = array();
		if ( ($aGroup=&$this->ar_Query[$name]['Group']) )
		{
			$aKeyGroup = array();
			$level = 1;
			foreach($aGroup as $g=>&$k)
			{
				$k['LevelGroup'] = $level++;
				$aKeyGroup = array_merge($aKeyGroup, $k['key']);
			}
		}
		else
		{
			$aGroup = array();
			if ( $this->ar_Query[$name]['fKey'] )
				$aKeyGroup = $this->ar_Query[$name]['fKey'];
			else
				$aKeyGroup = array('key_field_');
		}
		unset($k);
		if ( $aKeyGroup )
		{
			$GetKey = $this->fKeyCreator($aKeyGroup);
		}

if (defined('DEBUG_READMAIN'))
echo "\n<BR><b>processDataQuery $name</b>";
if (defined('TIME'))
time_start('DATA', $name . '_FETCH');
//== ірепарируем MAIN ==========================================================
		for($i=0, $lastRows=false; !$lastRows; $i++, $row = $this->getDataRow($name))
		{
if (defined('TIME'))
time_start('DATA_PROCESS', $name);
if (defined('DEBUG_READMAIN'))
echo "\n<BR/>$name $i --$lastRows";
			if ( $row )
			{
				foreach($aLink as $l=>&$v)
				{
if (DEBUG_LINK==1 || DEBUG_LINK==$v['baseName'])
$d = "\n<BR/>Link=$l";
//--Linkи до осей поворота
					if ( !$v['afterTurn'] )
						continue;
					$l = $v['baseName'];
					$k = $v['GetKey']($row);
if (defined('DEBUG_READMAIN'))
echo " Link l=$l, k=$k";
//					if ( !array_key_exists($k, $v['aKeys']) )
//						$aLink[$l]['aKeys'][$k] = 1;
					if (  $k !== '' && !array_key_exists($k, $this->aKeys[$l]) )
					{
if (DEBUG_LINK==1 || DEBUG_LINK==$l)
echo "$d, baseName=$l, key=$k";
						$this->aKeys[$l][$k] = 1;
					}
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
//-- іоворачиваем
//				if ( $i == $lastRows )
				if ( $lastRows )
				{
if (defined('DEBUG_READMAIN'))
echo "\nLastRow $i\n";
//print_r($row);
//print_r($turnRow);
					$newRow = $turnRow;
				}
				else
				{
					$keyRow_ = join($this->sepKeys, array_intersect_key_($row, $aKeyTurn));
					if ( $keyRow != $keyRow_ )
					{
//-- =ачинаем новуі строку
//echo "\nNEW TURN $i $keyRow_\n";
						$newRow = $turnRow;
						$keyRow = $keyRow_;
						$turnRow = array_slice($row, 0, $nomFstOnTurn);
					}
					else
						unset($newRow);
					$aKeyTurn_ = array_intersect_key_($row, $aFlOnTurn);
					$k = join($this->sepField, $aKeyTurn_);
					$turnRow[$k] = array_slice($row, $nomFstOnTurn);
					if ( !array_key_exists($k, $aSumCutsTurnList) )
					{
						$aSumCutsTurnList[$k] = $aKeyTurn_;
						$aSumCutsTurnALLVal[] = $k;
					}
					if ( $aSumFld )
					{
//echo "\n<BR><b>MAIN</b>" . json_encode($aSumFld);
if (DEBUG_GROUP > 2)
echo "\n<BR/>Sum1: name=$name";
						$a_ = array();
						for(array_pop($aKeyTurn_); $aKeyTurn_; array_pop($aKeyTurn_))
						{
							$keyTurn_ = join($this->sepKeys, $aKeyTurn_);
							if ( !array_key_exists($keyTurn_, $aSumCutsTurnTree) )
							{
								$aSumCutsTurnTree[$keyTurn_] = 1;
								$aSumCutsTurnTRVal[] = $keyTurn_;
								$aSumCutsTurnALLVal[] = $keyTurn_;
//echo "\n aSumFldTurn $keyTurn_\n";
							}
//==== суммирование полей из вvборки в NewRow в разрез turnTREE (т.к. turnLIST - само значение)
//echo "\n<BR/>Cut: $keyTurn_";
							$this->SumTotal($turnRow[$keyTurn_], $row, $aSumFld, $a_, $name);
						}
//==== суммирование полей из вvборки в NewRow
						$this->SumTotal($turnRow, $row, $aSumFld, $a_, $name);
					}
if (DEBUG_GROUP > 2)
f_print_r($turnRow);
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
if (DEBUG_LINK==1 || DEBUG_LINK==$v['baseName'])
$d = "\n<BR/>Link=$l";
				if ( $v['afterTurn'] )
					continue;
				$l = $v['baseName'];
				$k = $v['GetKey']($newRow);
				if (  $k !== '' && !array_key_exists($k, $this->aKeys[$l]) )
				{
if (DEBUG_LINK==1 || DEBUG_LINK==$l)
echo "$d, baseName=$l, key=$k";
//					$this->aKeys[$l][$k] = 1;
					if ( $this->ar_Query[$l]['appendFld'] )
						$this->aKeys[$l][$k] = array_intersect_key_($newRow, $this->ar_Query[$l]['appendFld']);
					else
						$this->aKeys[$l][$k] = 1;
				}
			}
			unset($v);
			$newRow['NomRow'] = ++$NomRow;
			if ( $aKeyGroup )
			{
//-- іодготавливаем группv
//				$keyGroup_ = join($this->sepKeys, array_intersect_key_($newRow, $aKeyGroup));
				$keyGroup_ = $GetKey($newRow);
			}
			else
			{
				$keyGroup_ = 'DUMMY';
				$newRow['DUMMY'] = 'DUMMY';
				$aKeyGroup = $keyGroup_;
			}
			if (!$keyGroup_) {
				$keyGroup_ = 'DUMMY';
				$newRow['DUMMY'] = 'DUMMY';
				$aKeyGroup = $keyGroup_;
			}
if (defined('DEBUG_READMAIN'))
echo " keyGroup=$keyGroup, keyGroup_=$keyGroup_";

			if ( $keyGroup != $keyGroup_ )
			{
				$keyGroup = $keyGroup_;
				foreach($aGroup as $g=>&$v)
				{
if (DEBUG_LINK==1 || DEBUG_LINK==$v['baseName'])
$d = "\n<BR/>Group=$g";
					$g = $v['baseName'];
					$k = $v['GetKey']($newRow);
//					$this->aGroupList[$keyGroup][$g] = $k;
					$aDestSumm[$keyGroup][$g] = $k;
if (defined('DEBUG_READMAIN'))
echo " aGroupItem-- keyGroup=$keyGroup, g=$g, k=$k";

//					if ( $v['lastKey'] != $k && !array_key_exists($k, $v['aKeys']) )
					if ( $v['lastKey'] != $k && !array_key_exists($k, $this->aKeys[$g]) )
					{
if (DEBUG_LINK==1 || DEBUG_LINK==$g)
echo "$d, baseName=$g, key=$k";
						if ( $this->ar_Query[$g]['attachFld'] )
						{
//							$v['aKeys'][$k] = array_intersect_key_($newRow, $this->ar_Query[$g]['attachFld']);
//							$v['aKeys'][$k]['pNom'] = sprintf('%03d', count($v['aKeys']));
							$this->aKeys[$g][$k] = array_intersect_key_($newRow, $this->ar_Query[$g]['attachFld']);
							$this->aKeys[$g][$k]['pNom'] = sprintf('%03d', count($v['aKeys']));
							
						}
						else
						{
//							$v['aKeys'][$k] = 0;
							$this->aKeys[$g][$k] = 0;
						}
						$v['lastKey'] = $k;
					}
				}
				unset($v);
			}
//			foreach($this->aGroupCalcOrder as $g=>$v)
			foreach($aGroup as $g=>$v)
			{
				$g = $v['baseName'];
				if ( ($v=$this->aGroupCalcOrder[$g]) )
//					$aGroup[$g]['aKeys'][$aGroup[$g]['lastKey']] += $newRow[$v];			// sum_realiz
					$this->aKeys[$g][$aGroup[$g]['lastKey']] += $newRow[$v];			// sum_realiz
			}
			$newRow['keyGroup'] = $keyGroup;
/*
			if ( $this->typeOUT == CRP_TYPE_JSFM_OUT )
			{
				$newRow['pCount'] = 0;
				$newRow['pCountSet'] = 0;
			}
*/
//			$this->aGroupList[$keyGroup]['mCount']++;
			$aDestSumm[$keyGroup]['mCount']++;

			if ( $aSumFld )
			{
if (defined('TIME'))
time_start('DATA_SUMM', $name);
//echo "\n<BR><b>NEW ROW</b>" . json_encode($aSumFld);
//==== суммирование полей из newRow в aGroupList
				$this->SumTotal($aDestSumm[$keyGroup], $newRow, $aSumFld, $aSumCutsTurnALLVal, $name);
if (DEBUG_GROUP > 2) {
echo "\n<BR/>SumGROUP: name=$name, keyGroup=$keyGroup";
}
if (defined('TIME'))
time_end('DATA_SUMM', $name);
			}

//			if ( ($k=$newRow['key_field_']) )
			$k = $newRow['key_field_'];
			if ( $name != 'MAIN' )
			{
//echo "\n<BR><b>NEW ROW</b> name=$name, k=$k";
				if ( $fKeyUp )
				{
					if ( isset($k) )
					{
						$nom = count($this->ar_Data[$name][$k]);
					}
					else
						$nom = count($this->ar_Data[$name]);
					if ( !$nom )
					{
						$aLevels = array();
						$LevelTotal = 0;
					}
					$keyUp = $newRow[$fKeyUp];
					if ( $aUps[$keyUp] )
					{
						$LevelTotal = $aUps[$keyUp];
					}
					else
					{
						$aUps[$keyUp] = ++$LevelTotal;
					}
					if ( $LevelTotal > 1 )
					{
						$newRow['nomUpGroup'] = $aLevels[$LevelTotal-1];
//echo "+" . $aLevels[$LevelTotal-1];
					}
					$aLevels[$LevelTotal] = $nom;
					$newRow['LevelTotal'] = $LevelTotal;
//echo " +$name-" . count($this->ar_Data[$name][$k]) . '-'  . $LevelTotal . '=' . $collapsedLevel;

					if ( $collapsedLevel && $LevelTotal >= $collapsedLevel )
						$newRow['isCollapsed'] = 1;
				}
				elseif ( $collapsedLevel )
					$newRow['isCollapsed'] = 1;

				if ( $this->ar_Query[$name]['appendFld'] )
					$newRow = array_merge($newRow, $this->aKeys[$name][$k]);
				if ( $this->ar_Query[$name]['typeQuery'] & CRP_TQRY_SET )
				{
					if ( isset($k) )
						$this->ar_Data[$name][$k][] = $newRow;
					else
						$this->ar_Data[$name][] = $newRow;
				}
				else
					$this->ar_Data[$name][$k] = $newRow;
			}
			else
//				$this->ar_Data[$name][] = $newRow;
				$this->ar_Data[$name][$keyGroup][] = $newRow;
if (defined('TIME'))
time_end('DATA_PROCESS', $name);
		}
if (defined('TIME')){
time_end('DATA', $name . '_FETCH');
}

		if ( $aSumCutsTurnALLVal )
		{
			$this->aSumTurn[$name] = array('sumFld'=>$aSumFld, 'turnFld'=>$aTurnFld
				,'printFld'=>$aPrintFld , 'turnCutALL'=>$aSumCutsTurnALLVal);
			if ( $aSumFld && $name != 'MAIN' )
			{
				$this->ar_Group[$this->ar_Query[$name]['parentData']]['summFrom'][] = $name;
			}
		}
		else
			$this->aSumTurn[$name] = array('sumFld'=>$aSumFld, 'turnFld'=>$aTurnFld
				,'printFld'=>$aPrintFld , 'turnCutALL'=>$aSumCutsTurnALLVal);

		if ( $aSumCutsTurnList )
			$this->ar_Turn[$name]['list'] = $aSumCutsTurnList;

//		return array($aTurnLink);
	}

//** Tортировка ar_Data по набору полей ****************************************
	function sortData($name, $fld, $type=0)
	{
		global $arraySort;
		
//		if ( $this->ar_Query[$name]['typeQuery'] & CRP_TQRY_SET )
//		else
		$type = ($type & CRP_CMP_DESC) == CRP_CMP_DESC;
		$arraySort = array('key'=>explode(',',$fld), 'type'=>$type);
//echo "\n</BR>arraySort=" . json_encode($arraySort);
//		usort($this->ar_Data['MAIN'], 'fCompSetField');
		foreach($this->ar_Data['MAIN'] as &$v)
			usort($v, 'fCompSetField');
	}

//** іодготовка даннvх *********************************************************
	function prepareData()
	{
		global $arraySort;

//if (defined('TIME'))
//time_start('DATA_ALL');
		$this->processDataQuery('MAIN', $this->aGroupList);
		foreach(array_merge($this->ar_Query['MAIN']['Link'], $this->ar_Query['MAIN']['Group']) as $k=>$v)
		{
			if ( $k != 'DUMMY' )
			{
//-- +сли Link уже есть в очереди, то передвигаем назад
				$k = $v['baseName'];
//				$this->aCntRef[$k] = 1;
				$this->aCntRef[$k] = count($this->aLinks);
				$this->aLinks[] = $k;
			}
		}
//f_print_r($this->aLinks);
//f_print_r($this->aCntRef, "********************* aCntRef");

		for(reset($this->aCntRef);;)
		{
			if ( !($l=key($this->aCntRef)) )
				break;
			foreach($this->ar_Query[$l]['Link'] as $n=>$v)
			{
				$n = $v['baseName'];
//echo "\n<BR> ADD $l-$n";
				if ( isset($this->aCntRef[$n]) ) {
//echo " REPEAT " . $this->aCntRef[$n];
					if ( $this->ar_Query[$n]['sum'] )
						$this->printError('To the Query ' . $n . ' with sum fields may be only one link');
					$i=$this->aCntRef[$n];
					for(; $i<$this->aCntRef[$l]; $i++) {
						$v_ = $this->aLinks[$i+1];
						$this->aCntRef[$v_]=$i;
						$this->aLinks[$i] = $v_;
					}
					$this->aLinks[$i] = $n;
					$this->aCntRef[$n]=$i;
				}
				else {
					$this->aCntRef[$n] = count($this->aLinks);
					$this->aLinks[] = $n;
//echo "\n<BR/>name=$name, parentData={$this->ar_Query[$name]['parentData']}";
					if ( $this->ar_Query[$n]['sum'] )
						$this->ar_Query[$n]['parentData'] = $l;
				}
			}
			next($this->aCntRef);
		}
//f_print_r($this->aLinks);
//f_print_r($this->aCntRef);

		if ( $this->ar_Query['MAIN']['typeQuery'] & CRP_ORD_CALC )
			$this->sortData('MAIN', $this->aGroupCalcOrder['MAIN'], $this->ar_Query['MAIN']['typeQuery'] & CRP_CMP_DESC);

//=================================================== подгрузка групп и линков
		for(reset($this->aLinks);;)
		{
			if ( !($l=current($this->aLinks)) )
				break;
			$this->getDataQuery($l, $this->aKeys[$l]);
			next($this->aLinks);
		}

if (defined('TIME')){
//time_end('DATA_ALL');
time_start('GROUP_SORT');
}
//=================================================== сортировка aGroupItem
		$aGroup = $this->ar_Query['MAIN']['Group'];
		$this->ar_GroupLevel = $aGroup;
		$v = array_keys($aGroup);
		foreach($v as $g)
			$this->ar_Query['MAIN']['GROUP_LEVEL'][$g] = ++$n;
		$this->lastGroup = $g;
		foreach($this->aGroupList as $kI=>&$rI)
		{
			$s = '';
			foreach($aGroup as $g=>$v)
				$s .= $this->ar_Data[$g][$rI[$g]]['pNom'];
			$rI['pNom'] = $s;
		}

		$arraySort = $this->aGroupList;
		uksort($this->aGroupList, 'cmpPNom');
if (defined('TIME'))
time_end('GROUP_SORT');

//=================================================== Lормирование ar_Group
if (defined('TIME'))
time_start('GROUP_PREPARE');

		$lastGroupLevel = count($aGroup);
//print_r($aGroup);
		$nomGroupLevel = array(0);
		$this->aGroupList['***'] = array();
		$aGr[] = array('group'=>'TOTAL', 'LevelTotal'=>0, 'keyMAIN'=>key($this->aGroupList));	//, 'pCount'=>0);
		$aGroupCntLevel = array('1'=>'0');
		$lastGrNom = 0;

if (defined('DEBUG_GROUP') || defined('DEBUG_DATA')){
$arr_D = array();
foreach($this->aSumTurn as $n=>$v)
{
	$v['turnFld'];
	foreach($v['turnFld'] as $g)
	{
		if ( $v['sumFld'] )
			$this->aSumTurn[$n]['printFld'][] = $g;
		$arr_D[] = $g;
		foreach($v['turnCutALL'] as $l)
		{
			$this->aSumTurn[$n]['printFld'][] = "$l.$g";
			$arr_D[] = "$l.$g";
		}
	}
}
sort($arr_D);
//f_print_r($this->aSumTurn, '$this->aSumTurn');
//f_print_r($arr_D, 'arr_D');
}
if (defined('DEBUG_GROUP')) {
$arr = array_merge(array_keys($aGroup), array('mCount', 'pNom'), $arr_D);
}

if (DEBUG_GROUP > 1) {
echo '<br/>Print for DEBUG_GROUP - aGroupList';
f_print_tab($this->aGroupList);
}


		foreach($this->aGroupList as $kI=>&$rI)
		{
			$totalLevel = 1;
			$changeGroupLevel = 0;
if (DEBUG_GROUP > 1)
echo "\n\n<br/><b>**** GroupItem</b>=$kI";
			$keyTotal = '';
			$razdTotal = '';
			if ( !$aGroup )
			{
				$changeGroupLevel = $lastGroupLevel = 1;
				$aGroup['DUMMY'] = array('prevKey'=>'***', 'LevelGroup'=>1);
				$this->aGroupList['DUMMY']['DUMMY'] = 'DUMMY';
				$rubInGroupLevel[1] = '001';
//f_print_r($this->aGroupList, "------------------------aGroupList-changeGroupLevel=$changeGroupLevel-");
			}
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
echo "\n<br/>     compare subLevel l=$levelBand, {$rowGr['subLevel'][$levelBand-1]}<>{$prevRowGr['subLevel'][$levelBand-1]}";
							if ( $rowGr['subLevel'][$levelBand-1] == $prevRowGr['subLevel'][$levelBand-1] )
								break;
						}
//						$totalLevel += $l+1;
					}
//					$aGroup[$g]['cntSubLevel'] = $l;
if (DEBUG_GROUP > 1){
echo "\n<br/>^^^^ g=$g, l=$levelBand, totalLevel=$totalLevel, changeGroupLevel=$changeGroupLevel, lastGrNom=$lastGrNom";
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
							if ( $this->aSumTurn )
							{
if (DEBUG_GROUP > 1)
echo " SUM_GROUP_UP";
//==== суммирование полей из ar_Group в верх
								foreach($this->aSumTurn as $n=>$v)
								{
									$this->SumTotal($aGr[$i], $aGr[$lastGrNom], $v['sumFld'], $v['turnCutALL'], 'GROUP_UP');
if (DEBUG_GROUP > 2) {
f_print_r($v, "--- from $n ---");
f_print_r($aGr[$lastGrNom], "++++++++++++");
f_print_r($aGr[$i], "===========");
}
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
echo "\n<br/>++++ g=$g, subLevel=" . json_encode($subLevel);
//					for($i=$aGroup[$g]['cntSubLevel']; $i<$cntSubLevel; $i++)
					for($i=$aGroupCntLevel[$changeGroupLevel]; $i<$cntSubLevel; $i++)
					{
						$keyGr = $subLevel[$i];
						$aGroup[$g]['prevKey'] = $keyGr;
if (DEBUG_GROUP > 1)
echo " |+++[$lastGrNom] totalLevel=$totalLevel, i=$i, keyGr=$keyGr";
						$nomGroupLevel[$totalLevel] = $lastGrNom = count($aGr);
						$aGroupCntLevel[$changeGroupLevel]++;
						$aGr[] = array('group'=>$g, 'key'=>$keyGr, 'keyMAIN'=>$kI, 'keyTotal'=>$keyTotal . $razdTotal . $keyGr
							, 'name'=>$this->ar_Data[$g][$keyGr][$this->ar_Query[$g]['fName']]
							, 'LevelGroup'=>$changeGroupLevel, 'nomUpGroup'=>$nomGroupLevel[$totalLevel-1]
							, 'LevelBand'=>$aGroupCntLevel[$changeGroupLevel], 'rub'=>join(array_slice($rubInGroupLevel, 0, $totalLevel)), 'LevelTotal'=>$totalLevel++
							, 'pCount'=>0, 'pCountSet'=>0 );
						$nomInGroupLevel[$totalLevel] = 1;
						$rubInGroupLevel[$totalLevel] = '001';
						if ( $this->ar_Data[$g][$keyGr]['isCollapsed'] )
							$aGr[$lastGrNom]['isCollapsed'] = $this->ar_Data[$g][$keyGr]['isCollapsed'];
						if ( $this->ar_Group[$g]['summFrom'] )
						{
							foreach($this->ar_Group[$g]['summFrom'] as $n)
							{
								if ( $this->ar_Data[$n][$keyGr] )
								{
if (DEBUG_GROUP > 1)
echo " summFrom $n";
									$v = $this->aSumTurn[$n];
									$this->SumTotal($aGr[$lastGrNom], $this->ar_Data[$n][$keyGr], $v['sumFld'], $v['turnCutALL'], $n);
								}
							}
						}
					}
					if ( $changeGroupLevel == $lastGroupLevel )
					{
						$aGr[$lastGrNom]['mCount'] = $rI['mCount'];
						$aGr[$lastGrNom]['gCount'] = 0;
						$aGr[$lastGrNom]['lastLevel'] = 1;
//						$aGr[$lastGrNom]['pCountSet'] = 0;
						if ( $this->aSumTurn['MAIN']['sumFld'] )
						{
//==== суммирование полей из aGroupList в ar_Group
if (DEBUG_GROUP > 1)
echo "\n<br/>SUM_LAST_GROUP";
							foreach($this->aSumTurn as $n=>$v)		// іаскоментировал ибо не считала сумму Link DATA AllQuery
//							$v = $this->aSumTurn['MAIN'];
							{
								$this->SumTotal($aGr[$lastGrNom], $rI, $v['sumFld'], $v['turnCutALL'], 'GROUP_ITEM');
if (DEBUG_GROUP > 2) {
f_print_r($v, "--- from=$n ---");
f_print_r($rI, "++++++++++++");
f_print_r($aGr[$lastGrNom], "===========");
}
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
if (defined('TIME'))
time_end('GROUP_PREPARE');

//print_r($aGr);
if (defined('DEBUG_GROUP')){
//f_print_tab($aGr, array_merge(array('group', 'nomUpGroup', 'key', 'name', 'LevelGroup', 'LevelTotal', 'lastLevel', 'mCount', 'gCount', 'rub'), array('v1', '1.v1', '2.v1', '1.1.v1', '1.2.v1', '1.3.v1', '2.1.v1', '2.2.v1')));
$arr = array_merge(array('group', 'nomUpGroup', 'key', 'keyTotal', 'keyMAIN', 'name', 'LevelBand', 'LevelGroup', 'LevelTotal', 'lastLevel', 'mCount', 'gCount', 'rub'), $arr_D);
echo '<br/>Print for DEBUG_GROUP - aGr.allData';
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
/*
	$arr = $this->ar_Query[$v]['FIELDS'];
	if ( $this->ar_Query[$v]['sum'] && $this->ar_Query[$v]['TURN']['onArray'] )
	{
		$arr = array_merge(array_diff($arr, $this->ar_Query[$v]['TURN']['onArray']), $arr_D);
	}
*/
//	f_print_tab($this->ar_Data[$v], array_merge(array_keys(reset($this->ar_Data[$v]))));
	if ( $this->ar_Query[$v]['typeQuery'] & CRP_TQRY_SET && !($this->ar_Query[$v]['typeQuery'] & CRP_TQRY_ALL ) )
	{
		echo " DATASET";
		$arr = $this->aSumTurn[$v]['printFld'];
		if ( $this->ar_Query[$v]['fKeyUp'] )
			$arr = array_merge($arr, array('LevelTotal', 'nomUpGroup'));
		f_print_tab($this->ar_Data[$v], $arr, 1);
	}
	else
		f_print_tab($this->ar_Data[$v], $this->aSumTurn[$v]['printFld']);
}}

		foreach($this->ar_Turn as $nTurn=>&$aTurn)
		{
//========================== если есть Link после Turn, то постанов имени в TURN
			if ( ($aTurnLink=$this->ar_Query[$nTurn]['TURN']['onLink']) )
			{
//print_r($aTurnLink);
				foreach($aTurn['list'] as $k=>&$v)
				{
					$s = '';
					foreach($aTurnLink as $f=>$l)
					{
						$g = $v[$f];
						if ( !is_numeric($l) )
						{
							$s .= $this->ar_Data[$l][$g]['pNom'];
							if ( $this->ar_Query[$l]['fName'] )
							{
//echo " +++++NAME_TURN f=$f, l=$l, g=$g, fName={$this->ar_Query[$l]['fName']}, Name=" . $this->ar_Data[$l][$g][$this->ar_Query[$l]['fName']];
								$v[$f . 'Name'] = $this->ar_Data[$l][$g][$this->ar_Query[$l]['fName']];
							}
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
//=================================================== сортировка ar_Turn['list']
			$arraySort = $aTurn['list'];
			uksort($aTurn['list'], 'cmpPNom');

//=================================================== формирование ar_Turn[all]
			$aTurnLink = array_keys($aTurnLink);
			$aTurn['fields'] = $aTurnLink;
			$aTurn['levels'] = count($aTurn['fields']);
			$countTurn = count($aTurnLink)-1;
			$aKeyTurnLevel = array();
			$g='***';
			$l = 0;
			$level = -1;
if (defined('DEBUG_TURN')){
echo "\n<BR/>TURN <b>$nTurn</b> field=" . json_encode($aTurn['fields']);
f_print_tab($aTurn['list']);
echo "\n<BR/>TURN make all, countTurn=$countTurn";
}
			foreach($aTurn['list'] as $k=>&$v)
			{
if (DEBUG_TURN==2)
echo "\n<BR/>k=$k";
				$up = $g = '';
				for($i=0; $i<=$countTurn; $i++, $up = $g, $g.=$this->sepKeys)
				{
					$f = $aTurnLink[$i];
					$g .= $v[$f];
if (DEBUG_TURN==2)
echo " || i=$i, keyPrevLevel<>key ? ({$aTurn['all'][$aKeyTurnLevel[$i]]['key']}<>$g)";
					if ( $aTurn['all'][$aKeyTurnLevel[$i]]['key'] != $g )
					{
if (DEBUG_TURN==2)
echo " <b>!=</b> l=$l";
						$aTurn['all'][$l] = $v;
						$aTurn['all'][$l]['key'] = $g;
						$aTurn['all'][$l]['upKey'] = $up;
						$aTurn['all'][$l]['level'] = $i+1;
						if ( $i != $countTurn )
							for($s=$i-1; $s>=0; $s--)
								$aTurn['all'][$aKeyTurnLevel[$s]]['gCount']++;
						else
						{
							$aTurn['all'][$l]['mCount']=1;
							for($s=$i-1; $s>=0; $s--)
								$aTurn['all'][$aKeyTurnLevel[$s]]['mCount']++;
						}
						if ( $i <= $level )
							$aTurn['all'][$aKeyTurnLevel[$i]]['nextNode'] = $l;
						$aKeyTurnLevel[$i] = $l++;
						$level = $i;
					}
				}
			unset($v);
			}

if (defined('DEBUG_TURN')){
//$v = array_merge(array_keys($this->ar_Turn['all'][$l-1]), array('gCount','nextNode','level')) // 'key', 'mCount',;
$v = array_merge(array_keys($aTurn['all'][0]));
f_print_tab($aTurn['all'], $v);
//f_print_r($aTurn);
}
		}
	}

//** Lормирование запроса ******************************************************
	function makeQuery($name) 
	{
		$param = &$this->ar_Query[$name];
		if ( $param['sqlSelect'] )
			return;
		if ( !($fld=$param['fields']) )
			$fld = '*';
//echo "\n<br>$name-$key-" . json_encode($param['fKey']);
		$key = join('||\'' . $this->sepKeys . '\'||', $param['fKey']);
//f_print_r($param, "$name--$key");
		$order = $param['order'];
		$rub = $param['fRubr'];
		if ( !$order )
			$order = $rub;
		if ( !$order )
			$order = $param['fName'];
		if ( $order )
			$order = " ORDER BY $order";
		$keyUp = $param['fKeyUp'];

//		$sqlIn .= " IN ('" . join('\',\'', array_keys($keyIn)) . '\')';
		if ( $rub || $keyUp )
		{
			if ( $param['typeQuery'] & CRP_ORD_CALC )
				die("makeQuery: Error ($name). Use Sort ({$param['order']}) for hierarhical query not possible");
			if ( !($tab=$param['table']) )
				die("makeQuery: Error ($name). Missing sqlSelect and table");
if (defined('TIME_COMP_QUERY'))
	$param['sqlSelectAlt'] = "SELECT $fld, up.$key as key_field_ FROM $tab up WHERE $key=";
			if ( $rub )
			{
				$fld = 'up.' . str_replace(',', ',up.', str_replace(' ', '', $fld));
				if ( ($cond=$param['condition']) )
				{
					$up = "(SELECT * FROM $tab %%|WHERE {$cond}%%|)";
					$cond = '';
				}
				else
				{
					$up = $tab;
					if ( $param['typeQuery'] & CRP_TQRY_SET )
						$cond = " AND up.$key=dn.$key";
				}
				if ( !$order )
					$order = " ORDER BY up.$rub";
				$sqlSelect = "SELECT DISTINCT $fld, up.$key as key_field_ FROM $up up JOIN $tab dn ON dn.$rub LIKE rtrim(up.$rub)||'%' AND dn.$key IN(%%KEYS_LINK%%) WHERE up.$key > 0{$cond}{$order}";
			}
			else
			{
				if ( !($start=trim($param['startWith'])) )
					$start = "$key = COALESCE($keyUp,$key)";
				else
				{
					if ( !strstr($start, '=') && !stristr($start, ' IN ') )
						$start = "$key = $start";
				}
				$sqlSelect = "SELECT * FROM (SELECT *, $key as key_field_ FROM $tab CONNECT BY PRIOR $keyUp = $key START WITH $key IN (%%KEYS_LINK%%){$order}) u CONNECT BY PRIOR $key = $keyUp START WITH $start";
			}
		}
		else
		{
			if ( !$sub=$param['sqlSubSelect'] )
			{
				if ( !($tab=$param['table']) )
					die("makeQuery: Error ($name). Missing sqlSelect and sqlSubSelect and table");
				$sub = "SELECT $fld, $key as key_field_ FROM $tab";
			}
			$sqlSelect = "SELECT * FROM ($sub) s ";
/*
			if ( $param['typeQuery'] & CRP_ORD_CALC )
				$sqlSelect .= "JOIN (
	SELECT gn as c_num_, (ARRAY[%%KEYS_LINK%%])[gn] as c_key_ from generate_series(1, 6) gn) o ON s.key_field_ = o.c_key_ 
ORDER BY c_num_";
			else
*/
				$sqlSelect .= "WHERE key_field_ IN (%%KEYS_LINK%%){$order}";

		}
		$param['sqlSelect'] = $sqlSelect;
	}

//** +бработка макросов ********************************************************
	function prepareLink($keyLink, $vMac, $onlyKey=0)
	{
		if ( !$keyLink )
		{
			if ( $onlyKey )
				return '\'\'';
			return '';
		}
		foreach($keyLink as $f)
		{
			if ( is_numeric($f) )
				$arr[] = "%A%$f%A%";
			else
				$arr[] = "{$vMac}[%A%$f%A%]";
		}
		$f = join('%.%%A%*%A%%.%', $arr);
		if ( !$onlyKey )
			$f = "[$f]";
		return $f;
	}

//** +бработка макросов ********************************************************
	function prepareMacro($name, $sMacro, $isExpr=0, $defRow='$row')
	{
if (defined('DEBUG_MACRO'))
echo "\n<BR/>sMac=<b>{$sMacro}</b>, defRow=$defRow";
		$aMacs = explode($this->sepMacro, $sMacro);
		$i = 0;
		if ( substr($col[$text], 0, 2) != $this->sepMacro )
		{
			$i = 1;
			$str .= $aMacs[0];
		}
		for(; $i<count($aMacs); $i+=2)
		{
			$contLink = 0;
if (defined('DEBUG_MACRO'))
echo "\n<BR/>  | iMac={$aMacs[$i]}, Expr=$isExpr";
			$aMac = explode('.', $aMacs[$i]);
			$vMac = $defRow;
			$nData = $name;
			do
			{
				foreach($aMac as $nm=>$sMac)
				{
//echo " $sMac-$this->type, " . ($this->type & CRP_TYPE_CHAR) . ': ' . json_encode($this->tags[($this->type & CRP_TYPE_CHAR)]);
if (defined('DEBUG_MACRO'))
echo " --sMac=$sMac";
					if ( ($sQMac=$this->tags[($this->type & CRP_TYPE_CHAR)][$sMac]) )
					{
						$vMac = $sQMac;
						break 2;
					}
					else
					{
						switch($sMac)
						{
							case '(':
								if ( !$isExpr )
								{
									$isExpr = 1;
									$vMac = '".(';
									break 3;
								}
							case ')':
								if ( $isExpr )
								{
									$isExpr = 0;
									$vMac = ')."';
									break 3;
								}
								die("parseMacro: Error use \"$sMac\" in {$aMacs[$i]}");
/*
							case 'mCount':
							case 'gCount':
								if ( $this->typeOUT == CRP_TYPE_MARK_OUT && $name != 'HEAD' )
								{
									$vMac = '%%' . $sMac . '%%';
									break 2;
								}
*/
							case 'PAD_LEVEL':
								$vMac = '$LevelShift';
								break 2;
							case 'RCOUNT1':
								$vMac = '(' . $vMac . '[%A%gCount%A%]+' . $vMac . '[%A%mCount%A%]+1)';
								$isExpr = 1;
								break 2;
							case 'rIndex':
								$vMac = '$oReport->rIndex';
								break 2;
							case 'NOMROW':
								$vMac = '$nomRow';
								break 2;
							case 'MAIN':
//								$vMac .= 'MAIN';
								$vMac = '$oReport->ar_Data[%A%MAIN%A%][$oReport->ar_Data[%A%RP_PARAM%A%][%A%keyMAIN%A%]][0]';
								break;
							case 'GROUP':
								if ( $name == 'MAIN' )
									$vMac = '$oReport->rowGr';
								break;
							case 'TCOUNT':
								$vMac = '(' . $vMac . '[%A%gCount%A%]+' . $vMac . '[%A%mCount%A%])';
/*
								if ( !$isExpr )
									$vMac = '".(' . $vMac . ')."';
								break 3;
*/
								break 2;
							case 'TURNNAME':
//								$vMac = '$aTURN_' . $this->iTurn . '[$oReport->ar_Turn[%A%fields%A%][$aTURN_' . $this->iTurn . '[%A%level%A%]-1] . %A%Name%A%]';
								if ( $this->iTurn == 'all' )
									$fld = end($this->ar_Turn[$this->Band[$this->iTurn]['nTurn']]['fields']);
								else
									$fld = $this->ar_Turn[$this->Band[$this->iTurn]['nTurn']]['fields'][$this->iTurn-1];
								$vMac = '$aTURN_' . $this->iTurn . '[%A%' . $fld . 'Name%A%]';
								break 2;
							case 'TURNINDEX':
								if ( count($aMac) > 1 )
								{
									$vMac .= '[$aTURN_' . $this->iTurn . '[%A%key%A%]]';
									$contLink = 2;
								}
								else
								{
									$vMac = '$aTURN_' . $this->iTurn . '[%A%key%A%]';
									break 2;
								}
								break;
							case 'TURN':
//????????????? іаглушка
								$vMac = '$aTURN_' . $this->iTurn;
								break;
							case 'COUNT':
								if ( count($aMac) == 2 )
								{
									$vMac = 'count($oReport->ar_Data[\'' . $aMac[++$nm] . '\'])';
									break 2;
								}
								if ( $vMac == '$row' )
								{
									$vMac = '$oReport->ar_Data[%A%RP_PARAMS%A%][%A%COUNT%A%][%A%' . $name . '%A%]';
								}
								else
									$vMac = 'count(' . $vMac . ')';
								break;
							case 'STRLEN':
								$vMac = 'strlen(' . $vMac . ')';
								break;
							case 'GROUP_DATA':
								$sMac = $name;
							case 'DATA':
								$vMac = '$oReport->ar_Data[\'' . $aMac[++$nm] . '\']';
								break 2;
							default:
								$sQMac = $sMac;
								if ( $sQMac[0] == '#' )
								{
									$sQMac = substr($sQMac, 1);
									if ( isset($this->ar_Const['GLOBAL'][$sQMac]) )
									{
										$vMac = $this->ar_Const['GLOBAL'][$sQMac];
									}
									elseif ( isset($this->ar_Const['DATA'][$sQMac]) )
									{
										$vMac = '$row.' . $sQMac;
									}
									else
										$vMac = '$' . $sQMac;
									break;
								}
								if ( $sQMac[0] != '$' )
									$sQMac = '%A%' . $sQMac . '%A%';

								if ( $this->ar_Query[$sMac] )
								{
if (defined('DEBUG_MACRO'))
echo " --LINK cont=$contLink, nDataLink=$nData";
									if ( ($keyLink=$this->ar_Query[$nData]['Link'][$sMac]['key']) )
									{
if (defined('DEBUG_MACRO'))
echo ", LINK";
										$nData = $this->ar_Query[$nData]['Link'][$sMac]['baseName'];
									}
									else if ( ($keyLink=$this->ar_Query[$nData]['Group'][$sMac]['key']) )
									{
if (defined('DEBUG_MACRO'))
echo ", GROUP";
										$nData = $this->ar_Query[$nData]['Group'][$sMac]['baseName'];
									}
									else
									{
										if ( !($keyLink=$this->ar_Query[$sMac]['fKey']) )
											$keyLink = null;
										$nData = $sMac;
									} 
//									$sQMac = '%A%' . $nData . '%A%';
//$$$$$$$$$$
									$sQMac = '"' . $nData . '"';
//==== Link - $oReport->ar_Data['LT1'][$row['k1'].'*'.$row['k2']]
if (defined('DEBUG_MACRO'))
echo ", nData=$nData, keyLink=" . json_encode($keyLink);

//									if ( $name != 'MAIN' && !$contLink )
									if ( ($this->ar_Query[$name]['typeQuery'] & CRP_QRY_GROUP) && !$contLink )
									{
										if ( $vMac == '$row' )
											$k = '[' . $vMac . '[%A%key%A%]]';
										else
											$k = $this->prepareLink($keyLink, $vMac);
										$vMac = '$oReport->ar_Data[' . $sQMac . ']' . $k;
										$contLink = 1;
										continue;
									}
									if ( $contLink != 2 )
										$vMac = '$oReport->ar_Data[' . $sQMac . ']' . 
											$this->prepareLink($keyLink, $vMac);
									else
									{
//==== TURNINDEX.link.field
if (defined('DEBUG_MACRO'))
echo " --TURNINDEX cont=$contLink";		// . json_encode($link['fKey']);
										if ( $name == 'MAIN' )
											$k = $this->prepareLink($keyLink, $vMac);
										else
											$k = '[$row[%A%key%A%]]';
										$vMac = '$oReport->ar_Data[' . $sQMac . ']' . $k . 
											'[$aTURN_' . $this->iTurn . '[%A%key%A%][%A%' . 
											$this->ar_Turn[$this->Band[$this->iTurn]['nTurn']]['fields'][$this->iTurn-1] . '%A%]]';
									}
//echo " ====vMac=$vMac";
								}
								else
								{
//==== іоле - $row['t1']
									$vMac .= '[' . $sQMac . ']';
								}
						}
					}
if (defined('DEBUG_MACRO'))
echo " [=>$vMac=]";	// . json_encode($aMacs);
				}
				if ( !$isExpr )
				{
					if ( $this->typeOUT == CRP_TYPE_JSFM_OUT )
						$vMac = '"+' . $vMac . '+"';
					else
						$vMac = '{' . $vMac . '}';
				}
if (defined('DEBUG_MACRO'))
echo " [=>$vMac=]";	// . json_encode($aMacs);
			} while(0);
if (defined('DEBUG_MACRO'))
echo ", vMac=<span style='font-weight: bold; color: blue;'>$vMac</span>";	// . json_encode($aMacs);
			$str .= $vMac . $aMacs[$i+1];
		}
		return $str;
	}

//** іроверка условий вvвода строк *********************************************
	function testForRows($s, $nMac)
	{
		if ( $s == 'none' )
			return false;
		if ( !$s || $s == 'all' )
			return true;
		$arr = explode('..', $s);
		if ( count($arr) == 2 )
		{
			if ( $arr[0] === '' )
				$arr[0] = '1';
			$str = 'if ( ' . $nMac . ' >= ' . $arr[0];
			if ( $arr[1] )
				$str .= ' && ' . $nMac . ' <= ' . $arr[1];
			return $str . ')';
		}
		else
		{
			return 'if ( ' . $nMac . ' == ' . $arr[0] . ')';
		}
		return false;
	}

//** іодготовка ячеек строки или подсекции *************************************
	function prepareCells(&$Cells, $name, $iTurnLevel=0, $iniArray='$row')
	{
if (DEBUG_BAND > 2)
echo "\n<BR/><b>prepareCells</b> iTurnLevel=$iTurnLevel, iniArray=$iniArray" . json_encode($Cells);
		$cBreak = "\n";
//echo "\n<BR/><b>$name</b> " . json_encode($Cells);
		foreach($Cells as $nc=>$col)
		{
//echo "\n<BR/> --- <b>$nc</b> " . json_encode($col);
if (DEBUG_BAND > 2)
echo "\n<BR/><b>{$col['tag']}</b>";
			switch($col['tag'])
			{
				case 'SetTabCtrl':
					$str .= '$sBand += "</tbody><tbody id=%Q%ctrlTAB/' . $col['for'] . '%Q%>";';
if (DEBUG_BAND > 2)
echo ", str=$str";
					break;
				case 'SetBand':
					$n = $col['for'];
					$nr = str_replace('.', '_', $n);
					$iter = '$nomRow_' . $nr;
					if ( $col['cells'] )
					{
						$sBand = "\nif (%%$n%%)\nforeach(%%$n%% as $iter=>\$rowSet_$nr) {\n";
/*
						if ( $col['iterate'] )
						{
							$aRep = explode('..', $col['iterate']);
							if ( count($aRep) == 1 )
								array_unshift($aRep, '1');
							$sBand = "\nfor($iter={$aRep[0]}; $iter<={$aRep[1]}; $iter++) {\n";
						}
*/
						$str .= $this->prepareMacro($name, $sBand . $cBreak, 1, $iniArray);
						$str .= $this->prepareCells($col['cells'], $n, $iTurnLevel, "\$rowSet_$nr") . '}';
					}
					else
					{
						if ( !($band=$col['alias']) )
							$band = $n;
						$str .= '$sBand%.%=$oReport->printSetBand(%A%' . $name . '%A%, %A%' . $band . '%A%, %A%' . $n . '%A%, ' .
							$this->prepareLink($this->ar_Query[$name]['Link'][$n]['key'], $iniArray, 1) .
							', ' . $iniArray . ', $rubr_group);';
					}
					$str .= $cBreak;
if (DEBUG_BAND > 2)
echo ", str=$str";
					break;
				case 'TurnCells':
//					$str .= '";' . $cBreak;
					if ( !($for=$col['for']) )
						 $for = 'MAIN';
					$iTurnLevel++;
					if ( $this->type & CRP_TYPE_JSFM_OUT )
						$var = 'var ';
					switch($col['on'])
					{
						case 'all':
//??????????????????????????? JS несовместимо
							$str .= 'foreach($oReport->ar_Turn[%A%' . $for . '%A%][%A%all%A%]';
							if ( $iTurnLevel > 1 )
								die("Error parse Band: TurnCells on=\"all\" must bee outline");
							$this->iTurn = 'all';
							$str .= ' as $iTURN_all=>$aTURN_all) {' . $cBreak;
							$level = str_replace('last', $this->ar_Turn[$for]['levels'], $col['levels']); 
							if ( $level )
								$str .= 'if (!strstr($aTURN_all[%A%level%A%] . %A%,%A%, %A%' . ($level . ',') . '%A%)) continue;' . $cBreak;
							break;
						case '':
						case 'level':
							$this->iTurn = $iTurnLevel;
							if ( $iTurnLevel > 1 )
							{
								$t = $iTurnLevel;
								$tp = $iTurnLevel-1;
								$str .= "\nfor({$var}\$iTURN_$t=\$iTURN_$tp+1; " .
									"\$iTURN_$t; \$iTURN_$t=\$aTURN_{$t}[%A%nextNode%A%]) {\n".
									"{$var}\$aTURN_$t=\$oReport->ar_Turn[%A%$for%A%][%A%all%A%][\$iTURN_$t];" . $cBreak;
							}
							else
								$str .= "\nfor({$var}\$iTURN_1=0; \$iTURN_1!==%N%; \$iTURN_1=\$aTURN_1[%A%nextNode%A%]) {\n".
									$var . '$aTURN_1=$oReport->ar_Turn[%A%' . $for . '%A%][%A%all%A%][$iTURN_1];' . $cBreak;
							break;
						default:
							$on = $col['on'];
//							if ( strstr('..', $on) )
							if ( $col['iterate'] )
							{
								$aRep = explode('..', $this->prepareMacro($name, $col['iterate'], 1, $iniArray));
								if ( count($aRep) == 1 )
									array_unshift($aRep, '1');
								$str .= "\nfor({$var}\$iTURN_$on={$aRep[0]}; \$iTURN_$on<={$aRep[1]}; \$iTURN_$on++) {\n" . $cBreak;
							}
							else
							{
								$str .= $this->prepareMacro($name, "\n%FOREACH%(%%DATA.$on%% as \$iTURN_$on=>\$aTURN_$on) {\n", 1, $iniArray) . $cBreak;
							}
							break;
					}
					$this->Band[$this->iTurn]['nTurn'] = $for;
					$str .= $this->prepareCells($col['cells'], $name, $iTurnLevel, $iniArray);
					$this->iTurn = --$iTurnLevel;
					$str .= '}' . $cBreak;
if (DEBUG_BAND > 2)
echo ", str=$str";
					break;
				case 'SwitchBand':
					$skipCaseBand = 0;
					$typeCaseBand = $col['type'];
					break;
				case 'CaseBand':
if (DEBUG_BAND > 2)
echo ", for={$col['for']}, type=$typeCaseBand";
					switch($typeCaseBand)
					{
						case 'COUNT':
							$ts = $col['for'];
							if ( $ts == 'empty' )
								$ts = '0';
							if ( $skipCaseBand )
								$str .= 'else ';
							if ( $ts != 'other' )
								$str .= $this->testForRows($name, '$nomRow');
							$str .= $cBreak . '{' . 
								$this->prepareCells($col['cells'], $name, $iTurnLevel) . 
								$cBreak . '}';
							$skipCaseBand = 1;
							break;
						default:
						if ( $skipCaseBand || $col['for'] != $name && $col['for'] != 'other' )
							break 2;
						$skipCaseBand = 1;
						$str .= $this->prepareCells($col['cells'], $name, $iTurnLevel);
					}
if (DEBUG_BAND > 2)
echo ", str=$str";
					break;
				case 'Expr':
					$str .= $this->prepareMacro($name, $col['text'], 1, $iniArray) . $cBreak;
					if ( $col['cells'] )
						$str .= $this->prepareCells($col['cells'], $name, $iTurnLevel, $iniArray);
if (DEBUG_BAND > 2)
echo ", str=$str";
					break;
				case 'BROW':
					$row .= $str . $cBreak;
					$str = '';
					$ts = $this->testForRows($col['begin'], '$nomRow');
					do {
						if ( $ts === false )
							break;
						if ( $ts !== true )
							$row .= $ts . $cBreak;
						$row .= '$sBand%.%="<' . $this->tags[$this->typeOUT]['ROW'];
						$classBROW = '$classBand';
						if ( $this->type & CRP_TYPE_JSFM_OUT )
						{
							$row .= ' id=%Q%';
							if ( $this->ar_Query[$name]['typeQuery'] & CRP_QRY_GROUP )
								$row .= 'GROUP' . $this->sepID . 'TREE"';
							else
								$row .= '" + $name + $oReport.sepID + $oReport.ar_Data["RP_PARAMS"]["keySET"][$name]';
							$row .= ' + $oReport.sepID + ($nomRow-1) + "%Q%';
						}
if (defined('CHECK_VISIB_COL')) {
						if ( $this->aSettings['HIDDEN_ROW'] == 1 )
						{
							if ( $this->ar_Query[$name]['fKeyUp'] && !($this->ar_Query[$name]['typeQuery'] & CRP_QRY_GROUP) )
								$classBROW .= '%.% ($row.LevelTotal>$oReport.ar_Data["RP_PARAMS"].stopLEVEL? " HIDDEN": "")';
							$row .= ' hidd_level=%Q%" + $oReport.getHiddLevel($name, $row) + "%Q%'; 
							$this->aSettings['HIDDEN_ROW'] = 2;
						}
}

//						if ( $name != 'MAIN' )		//??? =е попадает класс для MAIN
							$row .= ' class=%Q%"%.%' . $classBROW . '%.%"%Q%';
						$row .= '>";'
						. $cBreak;
					} while(false);

					$row .= '$sBand%.%=$sCells; $sCells=\'\'; $indexCell=0;' . $cBreak;
//if ( $name == 'HEAD' )
//$row .= 'f_print_r($aRSpan);' . $cBreak;
if (defined('DEBUG_IDXCOLUMN') && $name == 'HEAD')
$sCell .= 'echo "\n<BR/>";' . $cBreak;

					$ts = $this->testForRows($col['end'], '$nomRow');
					do {
						if ( $ts === false )
							break;
						if ( $ts !== true )
							$row .= $ts . $cBreak;
						$row .= '$sBand%.%="</' . 
							$this->tags[$this->typeOUT]['ROW'] . '>";' . $cBreak;
						
						if ( $this->typeOUT == CRP_TYPE_JSFM_OUT && $name != 'HEAD' )
						{
//							if ( $this->ar_Query[$name]['typeQuery'] & CRP_QRY_GROUP )
							$row .= '$row.pCount++;' . $cBreak;
//else
//MAIN, SubMAIN 
						}

					} while(false);

					if ( $name == 'HEAD' )
						$this->countRowsHead++;
					$str = '';
					break;
//------------------------------------------------------------------------------
				case 'Cell':
					$text = '';
					$sCell = '';
//					$cellExpr = 0;
					if ( $col['cellNumber'] ) {
						$cellNumber = $this->prepareMacro($name, $col['cellNumber'], 1, $iniArray);
						$sCell .= '$indexCell='. ($cellNumber-1) . ';' . $cBreak;
					}
					if ( $col['colspan'] )
						$colspan = $this->prepareMacro($name, $col['colspan'], 1, $iniArray);
					else
						$colspan = 1;
					if ( $col['rowspan'] )
					{
						$rowspan = $this->prepareMacro($name, $col['rowspan'], 1, $iniArray);
						$sCell .= '$rowspan='. $rowspan . ';' . $cBreak;
					}
					else
						$rowspan = 1;
					$sCell .= '$colspan='. $colspan . ';' . $cBreak;


if (defined('CHECK_VISIB_COL')) {
					if ( $col['CheckVisibility'] )
					{
						$text .= '<DIV onClick=%Q%HRC.Manager.';
						if ( $name == "HEAD"  )
						{
							if ( !($s=$col['CheckVisibility']['mask']) )
								$s = '01';
							$text .= 'switchCol(event, " + $indexCell + ",\'" + \'' . $s . '\' + "\',' . $colspan . ')%Q% class=%Q%visibCol';
							$col['class'] .= ' " + HRC.Manager.getSwitchImg(!HRC.Manager.isColVisib($indexCell, ' . $colspan . ')) + "';
						}
						else
						{
//??????????????????????? $hiddenRow = $this->prepareMacro($name, $col['CheckVisibility']['level'], 1, $iniArray);
// $hiddCond = $this->prepareMacro($name, $col['CheckVisibility']['condition'], 1, $iniArray);
							if ( $col['CheckVisibility']['level'] )
							{
								if ( $this->typeOUT != CRP_TYPE_JSFM_OUT )
//									$sCell .= 'HRC.Manager.setHiddLevel(' . $this->prepareMacro($name, $col['CheckVisibility']['level'], 1, $iniArray) . 
									$sCell .= 'HRC.Manager.setHiddLevel(' . $col['CheckVisibility']['level'] . 
										'99);';
								else
									$this->setHiddLevel = $col['CheckVisibility']['level'];
							}
							$text .= 'switchRow(event)%Q% class=%Q%visibRow';
							$this->aSettings['HIDDEN_ROW'] = 1;
							if ( $col['class'][0] != '(' )
								$col['class'] = '("' . $col['class'] . '"';
//							$sCell .= 'if ( HRC.Manager.getHiddLevel("' . $name . '", $row) == $oReport.ar_Data["RP_PARAMS"].hiddLEVEL )
							$sCell .= 'if ( $row.isCollapsed && $oReport.ar_Data["RP_PARAMS"].stopLEVEL == $oReport.ALLOW_LEVEL ) {
	HRC.Manager.getHiddLevel("' . $name . '", $row);
	$oReport.ar_Data["RP_PARAMS"].stopLEVEL = $row.LevelTotal; }
';
//							$col['class'] .= '%.%" "%.%HRC.Manager.getSwitchImg($oReport.ar_Data["RP_PARAMS"].ctrlLEVEL >= $oReport.ar_Data["RP_PARAMS"].hiddLEVEL))';
							$col['class'] .= '%.%" "%.%HRC.Manager.getSwitchImg($row.isCollapsed))';
						}

						$text .= '%Q%></DIV>';
					}
}


					if ( $col['field'] )
					{
						$col['text'] = $col['field'];
						if ( substr($col['field'], 0, 2) != '%%' )
							$col['text'] = '%%' . $col['text'] . '%%';
						else
							$col['field'] = substr($col['field'], 2, -2);
						$col['field'] = str_replace('TURNINDEX.', '%%TURNINDEX%%' . $this->sepID, $col['field']);
					}

					if ( ($type=$col['type']) == 'float' )
					{
						if ( !($t=$col['expr']) )
							$t = $col['text'];
						$t = $this->prepareMacro($name, $t, 1, $iniArray);
//						if ( $this->typeOUT != CRP_TYPE_JSON_OUT )
						{
							$text .= "{$this->aFormat['float']['func']}($t";
							foreach($this->aFormat['float']['args'] as $v)
								$text .= ",{$this->aFormat[$v]}"; 
							$text .= ')';
						}
						$col['class'] .= ' r';
					}
					else
					{
						if ( $col['expr'] )
							$text = '"%.%' . $this->prepareMacro($name, $col['expr'], 1, $iniArray) . ';' . $cBreak . '$sCells.="';
						else
						{
							if ( $col['textBefore'] )
								$text .= $this->prepareMacro($name, $col['textBefore'], 0, $iniArray);	// . '";';
							if ( $col['SetBand'] )
							{
								$text .= '";' . $cBreak;
								$n = $col['SetBand']['for'];
								$iter = '$nomRow_' . $n;
								$text .= $this->prepareMacro($name, 
									"for($iter=0; $iter<count(%%$n%%); $iter++) {\n".
									"\$rowSet_$n=%%$n.$iter%%;" . $cBreak, 1, $iniArray);
								if ( $col['SetBand']['separate'] )
									$text .= 'if (' . $iter . ') $sCells%.%="' . 
										$this->prepareMacro($name, $col['SetBand']['separate'], 0, $iniArray) . '";' . $cBreak;
								$text .= '$sCells%.%="' . $this->prepareMacro($n, $col['SetBand']['text'], 0, "\$rowSet_$n");
								$text .= '";' . $cBreak . '}' . $cBreak . '$sCells%.%="';
							}
							$text .= $this->prepareMacro($name, $col['text'], 0, $iniArray);
						}
						if ( $col['Expr'] )
						{
							$aCells = array($col['Expr']);
							$text .= '";' . $cBreak . $this->prepareCells($aCells, $name, 0) . $cBreak . '$sCells%.%="';
						}
					}

if (defined('DEBUG_IDXCOLUMN') && $name == 'HEAD')
$sCell .= 'echo " =1-$indexCell= ";' . $cBreak;
//					if ( $name != 'HEAD' && $this->aSettings['HIDDEN_COL'] )
					if ( $this->aSettings['HIDDEN_COL'] )
					{
						if ( $col['indexCell'] )
							$sCell .= '$indexCell=' . $col['indexCell'] . $cBreak . ';';
						$sCell .= 'while($aRSpan[$indexCell]>0) { ';
if (defined('DEBUG_IDXCOLUMN') && $name == 'HEAD')
$sCell .= 'echo " +$indexCell-{$aRSpan[$indexCell]}+ ";' . $cBreak;
						$sCell .= '$aRSpan[$indexCell++]--; }' . $cBreak;
						if ( $colspan == 1 )
							$sCell .= 'if ( $oReport->hiddenColumns[$indexCell] != 1 ) {' . $cBreak;
						else
							$sCell .= 'for(' . $var . ' $iIC=0; $iIC<' . $colspan . '; $iIC++)' . $cBreak .
								'	if ( $oReport->hiddenColumns[$indexCell+$iIC] == 1 ) $colspan--;' . $cBreak . 
								'if ( $colspan > 0 ) {' . $cBreak;
if (defined('DEBUG_IDXCOLUMN') && $name == 'HEAD')
$sCell .= 'echo " =2-$indexCell= ";' . $cBreak;
					}

					$sCell .= '$sCells%.%="<' . $this->tags[$this->typeOUT]['CELL'];

					foreach(array('rowspan','colspan','style','class','field') as $v)
						if ( $s=$col[$v] )
						{
							$expr = $s[0] == '(';
							$quoted = true;
							switch ($v) 
							{
								case 'colspan':
									$expr = 1;
									$quoted = false;
									$s = '$colspan';
									break;
								case 'rowspan':
									$expr = 1;
									$quoted = false;
									$s = '$rowspan';
									$rowspan = $s;
									break;
								case 'field':
									if ( ($i=strpos($s, '.')) > 0 )
									{
										$t = substr($s, 0, $i);
										if ( $t != $name && $this->ar_Query[$t] )
										{
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ TLTL і+і++
											$n = substr($s, $i+1);
											if ( strpos($n, '.') > 0 )
												$this->printError('Edit field must contain only one "." (' . $s . ')' . $i . '-' . $n);
											$keyLink = $this->ar_Query[$name]['Link'][$t]['key'];
											if ( count($keyLink) > 1 )
												$this->printError('Combo field reference: several fields not supported');
											$k = $keyLink[0];
											if ( !$this->ar_Data['CMB_LIST'][$k] )
											{
												$this->ar_Data['CMB_LIST'][$k] = array('data'=>$t,
													'fName'=>$n, 'fKey'=>$this->ar_Query[$t]['fKey'][0]
												);
											}
//f_print_r()
											$s = $k;
//echo "--$name--$t--$k-- ";
/*
//$this->ar_Query[$t]['fKey'][0]
											$k = $this->prepareLink($keyLink, $iniArray, 1);
//f_print_r($keyLink, "--$name--$t--$iniArray--");
											$s = substr($s, $i+1) . '%Q% id=%Q%' . $t . 
												$this->sepID . '"%.%' . $k . 
												'%.%$oReport.sepID%.%($nomRow-1)%.%"';
*/
										}
									}
									break;
								default:
									$s = $this->prepareMacro($name, $s, $expr, $iniArray);
									break;
							}
							if ( $expr )
							{
								$s = '"%.%(' . $s . ')%.%"';
								if ( $quoted )
									$s = '%Q%' . $s . '%Q%';
							}
							else
								$s = '%Q%' . $s . '%Q%';
							$sCell .= ' ' . $v . '=' . $s;
						}
					if ( $type == 'float' )
						$text = '"%.%' . $text . '%.%"'; 
					$sCell .= '>';

if (defined('DEBUG_IDXCOLUMN'))
$sCell .= '{$indexCell}== ';
					$sCell .= $text . '</' . $this->tags[$this->typeOUT]['CELL'] . '>";' . $cBreak;
					if ( $name == 'HEAD' && !$this->countRowsHead )
						$sCell .= '$oReport->countCells+=$colspan;' . $cBreak;

//					if ( $name != 'HEAD' && $this->aSettings['HIDDEN_COL'] )
					if ( $this->aSettings['HIDDEN_COL'] )
					{
						$sCell .= '}' . $cBreak;
//====! Lменно изначальнvй $colspan
						if ( $rowspan != 1 )
						{
							$sCell .= 'for(' . $var . ' $iIC=0; $iIC<' . $colspan . '; $iIC++)' . $cBreak .
								'	$aRSpan[$indexCell++]=' . $rowspan . '-1;' . $cBreak;
						}
						else
							$sCell .= '$indexCell+=' . $colspan . ';'  . $cBreak;
if (defined('DEBUG_IDXCOLUMN') && $name == 'HEAD')
$sCell .= 'echo " =3-$indexCell= ";' . $cBreak;
					}

					if ( $s=$col['condition'] )
					{
//						$str .= '";' . $cBreak . 
						$str .= $cBreak . 
							'if (' . $this->prepareMacro($name, $s, 1, $iniArray) . ') {' . 
							$cBreak . $sCell . '}' . $cBreak;
					}
					else
						$str .= $sCell;
					break;
			}
		}
		$row .= $str;
if (DEBUG_BAND > 2)
echo "\n<BR/><b>RETURN-</b>" . debug_parse($row);
//		return $str;
		return $row;
	}
//** іодготовка секции вvвода **************************************************
	function prepareBand($textFunc, $name, $nFunc, $level, $place, $pad)
	{
if (DEBUG_BAND>=1 || strstr(DEBUG_BAND . ',', $name))
{
echo "\n<BR/>BAND-------------- name=<b>$nFunc</b>   ";
$debugThisBand = 1;
}
		$cBreak = "\n";
		$iTurnLevel = -1;
		if ( $nFunc == 'MAIN' )
		{
			$level = '*';
			$place = '';
		}
		$this->aSettings['HIDDEN_ROW'] = 0;

		if ( $this->type & CRP_TYPE_JSFM_OUT )
		{
			$str = 'var ';
			if ( $this->ar_Query[$name]['typeQuery'] & CRP_QRY_GROUP )
				$str .= '$classBand = $name + " " + $name + "_" + $row.LevelBand;';	//" RowGrpHead_" + $row.LevelTotal +  
			elseif ( $this->ar_Query[$name]['fKeyUp'] )
				$str .= '$classBand = $name + " " + $name + "_" + $row.LevelTotal;';
			else
				$str .= '$classBand = $name;';
			$str .= $cBreak . 'var $oReport=this.$oReport; var $nameBand="' . $nFunc . '";
$row.pCount = $row.pCountSet = 0; 
var $sBand="", $sCells="", $indexCell=0, $aRSpan=new Array(100), $colspan, $rowspan;' . $cBreak;
		}
		else
			$str = '$nameBand="' . $nFunc . '"; $indexCell=0;' . $cBreak;

//		if ( $nFunc != 'MAIN' && $nFunc != 'HEAD' )
		if ( $this->ar_Query[$name]['typeQuery'] & CRP_QRY_GROUP ) // GRP
		{
			$str .= $this->prepareMacro($name, '%%PAD_LEVEL%%=0; if ( %%LevelTotal%% > 1 ) %%PAD_LEVEL%% = ' . $pad . ' * (%%LevelTotal%%-1);', 1, '$row') . $cBreak;
		}
		$str .= $textFunc;

		if ( $this->type & CRP_TYPE_JSFM_OUT )
		{
			$str = str_replace(
				array('%Q%',	'%A%',	'%.%',	'\'\'', 	"\xA0",		'->',		'%N%'
				),
				array('\"',		'"',		'+',		'""',		'"\xA0"',	'.',		'undefined'
				),
				$str);
			if ( strstr('%FOREACH%(', $str) )
				$str = preg_replace('/%FOREACH%\(([^ ]*) as ([^=]*)=>([^)]*)\) {/', 'for(var \\2 in \\1) { var \\3=\\1[\\2];', $str);
			if ( $name != 'HEAD' )
			{
				if ( $this->ar_Query[$name]['typeQuery'] & CRP_QRY_GROUP )
					$str .= '$oReport.countPrintChild($row, $oReport.ar_Data.GROUP.TREE);' . $cBreak;
//else
//MAIN, SubMAIN 
			}

			if ( $this->lastGroup == $name )
				$str .= $cBreak . '$oReport.ar_Data.RP_PARAMS.keyMAIN=$row["keyMAIN"];' . $cBreak .
					'$sBand+=$oReport.printSetBand("GROUP", "MAIN", "MAIN", $row["keyTotal"], $row, $rubr_group);' . $cBreak;

if ($debugThisBand)
echo $this->print_text_section($str, $nFunc, $place, $level);
			$pFunc = $str;
			$place = 'header';
		}
		else
		{
			$str .= $cBreak . ' return $sBand;';
			$str = str_replace(
				array('%Q%',	'%A%',	'%.%',	'%N%',	'%FOREACH%'),
				array('\"',		'\'',		'.',		'null',	'foreach'),
				$str);

			$pFunc = create_function('&$oReport, &$row, $nomRow, $classBand=\'\', $rubr_group=\'\'', $str);

			if ( !$pFunc )
			{
				$this->print_text_section($str, $nFunc, $place, $level, true);
				die("<BR>CreateFunction ERROR");
			}
if ($debugThisBand)
echo $this->print_text_section($str, $nFunc, $place, $level, true);
		}
		$this->ar_Band[$nFunc][$level][$place]['func'] = $pFunc;
	}

//** Lормирование теста секции *************************************************
	function printSetBand($callName, $band, $name, $key, $rowMain, $rub)
	{
		if ( $f=$this->ar_Band[$band]['*']['header']['func'] )
		{
if (defined('DEBUG_PRINT'))
echo ' printSetBand <b>' . $name . '</b>';
			if ( $key )
				$arData = &$this->ar_Data[$name][$key];
			else
				$arData = &$this->ar_Data[$name];
			$this->ar_Data['RP_PARAMS']['COUNT'][$name] = count($arData);
//echo " $key-$arData={$this->ar_Data['COUNT'][$name]} ";
			if ( $arData )
				foreach($arData as $nom=>&$row)
					$text .= $f($this, $row, ++$nom, 'MAIN ' . $name, $rub . sprintf('%03d', $nom));
//echo $text;
			return $text;
		}
	}

//** Lормирование теста секции *************************************************
	function printBand($name, &$row, $nom, &$rowMain, $rub)
	{
		if ( $f=$this->ar_Band[$name]['*']['header']['func'] )
		{
if (defined('DEBUG_PRINT'))
echo ' <b>' . $name . '</b>';
			return $f($this, $row, $nom, 'MAIN ' . $name, $rub . sprintf('%03d', $nom));
		}
	}

//** Lормирование отчета *******************************************************
	function printReport()
	{
if (defined('TIME'))
time_start('PRINT');

//$this->hiddenColumns = array(0,1,0,0,0,1,0,0,1);
		if ( !$this->lastGroup )
		{
			$this->lastGroup = 'DUMMY';
			$this->prepareBand('', 'DUMMY', 'DUMMY', '*', 'header', 0);
		}

		if ( $this->type & CRP_OUT_BODY )
		{
			$text = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Report</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
<!--meta http-equiv="Content-Type" content="application/vnd.ms-excel; charset=windows-1251"/-->
';

		if ( $this->params['urp_out'] )
			$text .= '
<style type="text/css">
'
. file_get_contents('ReportPrint.css') .
'
</style>
';
		else
			$text .= '
<link rel="stylesheet" type="text/css" href="ReportPrint.css"/>
';

$text .= "<!-- ******************** {$this->type} (" . ( $this->type & CRP_TYPE_JSFM_OUT ) . ") ************** -->";

		if ( $this->type & CRP_TYPE_JSFM_OUT )
		{
//if (0)
			$text .= '<script type="text/javascript" src="Report.Ctrl.js"></script>
';
			$this->ar_Style['SEL_TR'] = array('background-color'=>'Lavender');
			$this->ar_Style['SEL_TD'] = array('background-color'=>'LavenderBlush');
			$this->ar_Style['HIDDEN'] = array('display'=>'none');
		}

		if ( $this->ar_Style )
		{
			$text .= '
<style type="text/css">
';
			foreach($this->ar_Style as $f=>$s)
			{
				if ( $f == 'Style' )
					continue;
				$text .= ".$f {\n";
				foreach($s as $sN=>$sV)
					$text .= "$sN: $sV;\n";
				$text .= "}\n";
			}

		if ( $this->type & CRP_OUT_TABS )
			$text .= '
table.MainTable {
	width: 100%;
}
table.MainTable tbody {
	z-height: 100%;
	z-overflow-y: auto;
	z-overflow-x: hidden;
}
.x-form-text {
	width: 100%;
	color: black;
}
.x-form-field-wrap .x-form-trigger {
	z-left:-18px;
	z-position:relative;
	z-top:5pt;
	right: 0px;
	top: 1px;
	border-left: 1px solid lightgray;
}
.x-form-field-wrap {
	width: 100%;
}

.x-find { background-color: lightcyan; text-decoration: underline !important; padding: 0px !important; }

.x-check-inline { display: inline; padding-right: 10px; }

.x-combo-custom { position: absolute; right: 20px; top: 3px; }

.x-combo-inbody { border-bottom-width: 0px !important; width: 100%; height: 100% }

.x-panel-custom { margin: 4 2 2 2; }

.x-disabled-item { color: gray; }

.x-custom-collapsed .x-tool-toggle{background-position:0 -75px;}
';

			$text .= '
</style>
';
		}

			$text .= '
</head>
<body>
';
		}

		if ( $this->type & CRP_OUT_TABS )
			$text .= '
<div id="regTABS-OUTER" style="margin:15px 10px;"><div id="regTABS" style="height: 100%; position:relative; overflow: auto; overflow-x: hidden;" onClick="return preventDefaultEvent(event);">
';

		if ( $this->type & CRP_OUT_TABLE )
			$text .= '
<table id="MainTable" border="1" class="MainTable MAIN" style="empty-cells: show;" onClick="HRC.Manager.selectCell(event)">
';

		$lastTotalLevel = -1;
		array_push($this->ar_Group, array('LevelTotal'=>0));

//f_print_tab($this->ar_Group, array('group', 'nomUpGroup', 'key', 'keyTotal', 'keyMAIN', 'name', 'LevelBand', 'LevelGroup', 'LevelTotal', 'lastLevel', 'mCount', 'gCount', 'rub'));


if (defined('DEBUG_PRINT'))
echo "\nHEAD \$this->type=" . sprintf('%x', $this->type);
		if ( !($this->type & CRP_TYPE_JSFM_OUT) )
		{
if (defined('DEBUG_PRINT'))
echo ' -> CRP_TYPE_HTML';
			if ( $f=$this->ar_Band['HEAD']['*']['header']['func'] )
			{
if (defined('DEBUG_PRINT'))
echo " <b>!!! $f</b>";
				$text .= $f($this, $this->ar_Group[0], 0, 'HEAD', $rowGr['rub']);
			}
			$this->rIndex = 0;
			foreach($this->ar_Group as $nGr=>&$rowGr)
			{
				$totalLevel = max(0,$rowGr['LevelTotal']);
//==== вvвод footerов	??? $this->aSettings['HIDDEN_ROW']
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
						$text .= $f($this, $this->ar_Group[$iGr], $iGr, "RowGrpFoot_{$this->ar_Group[$iGr]['LevelGroup']} $nF {$nF}_{$this->ar_Group[$iGr]['LevelBand']}", $this->ar_Group[$iGr]['rub']);
					}
				}
				if ( $keyMAIN != $this->ar_Group[0]['keyMAIN'] )
					$this->ar_Data['RP_PARAMS']['keyMAIN'] = $this->ar_Group[0]['keyMAIN'];

				if ( $rowGr['group'] )
				{
					$nF = $rowGr['group'];
if (defined('DEBUG_PRINT'))
echo "\n<b>header</b> {$rowGr['group']}-{$nF}-{$rowGr['LevelBand']}--{$rowGr['key']}-{$rowGr['rub']}";	// . json_encode($this->ar_Band['G1']['*']['header']['func']);
					if ( ($f=$this->ar_Band[$nF][$this->ar_Group[$iGr]['LevelBand']]['header']['func']) ||
						($f=$this->ar_Band[$nF]['*']['header']['func']) )
					{
						$cls_ = "{$nF}_{$rowGr['LevelBand']}";
						if ( !$this->ar_Style[$cls_] )
							$cls_ = $nF;
						if ( !$this->ar_Style[$cls_] )
							$cls_ = 'RowGrpHead';
						$text .= $f($this, $rowGr, $nGr, $cls_, $rowGr['rub']);	//RowGrpHead_{$rowGr['LevelGroup']} 
					}
					$lastTotalLevel = $totalLevel;
					$aLevel[$lastTotalLevel] = $nGr;
				}
				if ( $rowGr['lastLevel'] )
				{
					$this->rowGr = $rowGr;
					$keyMAIN = $rowGr['keyMAIN'];
if (defined('DEBUG_PRINT'))
echo "\nMAIN {$keyMAIN}";

					$this->ar_Data['RP_PARAMS']['COUNT']['MAIN'] = count($this->ar_Data['MAIN'][$keyMAIN]);
					foreach($this->ar_Data['MAIN'][$keyMAIN] as $iMain=>&$arMainPrint)
					{
						if ( $f=$this->ar_Band['MAIN']['*']['']['func'] )
						{
if (defined('DEBUG_PRINT'))
echo " <b>$iMain</b>";
							$text .= $f($this, $arMainPrint, $iMain+1, 'MAIN', $rowGr['rub']);
						}
					}
				}
			}
		}

		if ( $this->type & CRP_OUT_TABLE )
			$text .= '
</table><table id="tabTmpCreate" style="display: none;"></table>';
		if ( $this->type & CRP_OUT_TABS )
			$text .= '
</div></div>';

		if ( $this->type & CRP_TYPE_JSFM_OUT )
		{
//f_print_r($this->ar_Res, '$this->ar_Res');
//			$this->ar_Res[] = array('band'=>'DUMMY', 'LevelTotal'=>0, 'mCount'=>0, 'gCount'=>0, 'pCount'=>0);
//HRC.Manager.setData("GROUP", ' . str_replace(',{', ",\n{", json_encode($this->ar_Res)) . ');
			$text .= '
<script>
';
			if ( $this->assocData )
			$text .= '
HRC.Manager.assocData = true;
';

			if ( $this->setHiddLevel )
			$text .= '
HRC.Manager.setHiddLevel(' . $this->setHiddLevel . '99);
';
			$text .= '
HRC.Manager.setData("GROUP", ' . makeJSON(array('TREE'=>$this->ar_Group)) . ');';
			foreach($this->ar_Data as $n=>&$v)
			{
				$text .= '
HRC.Manager.setData("' . $n . '", ' . makeJSON($v) . ');';
			}
			if ( $this->hiddenColumns )
				$text .= '
HRC.Manager.setData("hiddenColumns", ' . makeJSON($this->hiddenColumns) . ');';

			foreach($this->ar_Band as $n=>&$v)
			{
				$text .= '
//------------------------------------------------------------------------------
HRC.Manager.arMark["' . iconv('utf-8', $this->aCode['target'], $n) . '"] = function($name, $nomRow, $row, $rubr_group) {
' . $v['*']['header']['func'];

				$text .= '
return $sBand;
};
';
				
			}
			if ( $this->type & CRP_OUT_TABS )
				$text .= '
HRC.Manager.ar_Data.RP_PARAMS.SETTINGS.IS_TABBED = 1;
';
			$text .= '
HRC.Manager.setData("ar_Turn", ' . makeJSON($this->ar_Turn) . ');
HRC.Manager.assocData = true;
HRC.Manager.makeFRM();
';
			$text .= '
</script>';
		}
		if ( $this->type & CRP_OUT_BODY )
			$text .= '
</body>
</html>';

		return $text;
	}

	function printError($sErr)
	{
		die(preg_replace(array('/</', '/>/'), array('&lt;', '&gt;'), $sErr));
	}

//** +бработка стартовvх Tі+ов *************************************************
	function startElement($parser, $tag, $attrs) 
	{
		$cBreak = "\n";
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
				if ( $attrs['appendFld'] )
					$attrs['appendFld'] = array_flip(explode(',', $attrs['appendFld']));
				if ( $attrs['fKey'] )
					$attrs['fKey'] = explode(',', $attrs['fKey']);
				$this->ar_Query[$name] = $attrs;
				if ( !($this->ar_Query[$name]['table']) )
					$this->ar_Query[$name]['table'] = $name;
				switch($attrs['type'])
				{
					case 'SET':
						$this->ar_Query[$name]['typeQuery'] |= CRP_TQRY_DATA | CRP_TQRY_SET;
						break;
					case 'DATA':
						$this->ar_Query[$name]['typeQuery'] |= CRP_TQRY_DATA;
						break;
					case '':
						if ( $name == 'MAIN' )
							$this->ar_Query[$name]['typeQuery'] |= CRP_TQRY_DATA | CRP_QRY_PRINT | CRP_TQRY_SET;
						break;
				}
				if ( $name != 'MAIN' && $attrs['sqlSelect'] && strstr($attrs['sqlSelect'], '%%KEYS_LINK%%') >= 0 )
					$this->ar_Query[$name]['typeQuery'] |= CRP_TQRY_ALL;
				if ( !$attrs['CONN'] )
					$this->ar_Query[$name]['CONN'] = 'DEFAULT';
				$this->ar_Query[$name]['Link'] = array();
				$this->lastTag[$this->depth] = array('tag'=>$tag, 'name'=>$name, 'item'=>&$this->ar_Query[$name]);
				break;
			case 'Turn':
				$lastTag = $this->lastTag[$this->depth-1];
//				if ( $lastTag['tag'] != 'Query' || $lastTag['name'] != 'MAIN' )
//					$this->printError('<Turn> may be only in Query MAIN');
				$lastTag['item']['TURN'] = $attrs;
				break;
			case 'Group':
			case 'Link':
				$lastTag = $this->lastTag[$this->depth-1];
				if ( $tag == 'Group' )
				{
					if ( $lastTag['tag'] != 'Query' || $lastTag['name'] != 'MAIN' )
						$this->printError('<Group> may be only in Query MAIN');
				}
				else
				{
					if ( $lastTag['tag'] != 'Query' )
						$this->printError('<Link ...> may be only in Query');
				}
				$t = $tag;
				if ( !($n=$attrs['alias']) )
					$n = $attrs['to'];
				if ( $attrs['reference'] )
				{
					$arr_keys = explode(',', $attrs['reference']);
					foreach($arr_keys as $k)
					{
						if ( is_numeric($k) )
							$arr_[] = "'$k'";
						else
							$arr_[] = '$row[\'' . $k . '\']';
					}
					$tGetKey = 'return ' . join('.\'' . $this->sepKeys . '\'.', $arr_) . ';';
				}
				else
				{
					$arr_keys = array();
					$tGetKey = 'return \'\';';
				}
				if ( !$this->ar_Query[$n]['fKey'] )
					$this->ar_Query[$n]['fKey'] = $arr_keys;
				if ( $lastTag['item'][$t][$n] )
					$this->printError("For <Query name=\"{$lastTag['name']}\" exist <Link to=\"$n\"...>");
				$lastTag['item'][$t][$n] = array('key'=>$arr_keys, 'baseName'=>$attrs['to']
					, 'GetKey'=>create_function('&$row', $tGetKey)
				);
				if ( $tag == 'Group' )
				{
					$this->ar_Query[$n]['typeQuery'] |= CRP_INF_SORT | CRP_QRY_PRINT | CRP_QRY_GROUP;
				}
				$this->aKeys[$n] = array();
				if ( !$this->ar_Query[$n] )
					$this->printError("startElement: Missing Query ($n) for $tag ({$lastTag['name']})");
				$this->lastTag[$this->depth] = $attrs;
				break;
			case 'Sort':
				$lastTag = $this->lastTag[$this->depth-1];
				if ( $lastTag['tag'] == 'Group' )
					$n = $lastTag['to'];
				else
				{
					$n = $lastTag['name'];
					if ( $lastTag['tag'] != 'Query' || !($this->ar_Query[$n]['typeQuery'] & CRP_TQRY_DATA) )
						$this->printError($n .$this->ar_Query[$n]['typeQuery'].'<Sort> may be appear only within <Group> or <Query name="MAIN" OR type=DATA|SET>');
				}
				$this->ar_Query[$n]['order'] = '';		//$attrs['field'];
				$this->ar_Query[$n]['typeQuery'] |= CRP_ORD_CALC;
				$this->aGroupCalcOrder[$n] = $attrs['order'];
				if ( $attrs['reverse'] )
					$this->ar_Query[$n]['typeQuery'] |= CRP_CMP_DESC;
				break;
			case 'Band':
//print_r($this->ar_Band);
				unset($this->Band);
				$this->Band = array('attrs'=>$attrs, 'rows'=>array());
				$this->lastTag[$this->depth] = array('tag'=>$tag, 'cells'=>&$this->Band['rows']);
				break;
			case 'SwitchBand':
				$this->lastTag[$this->depth] = array('tag'=>$tag);
				$this->lastTag[$this->depth-1]['cells'][] = $attrs;
				break;
			case 'CaseBand':
				if ( $this->lastTag[$this->depth-1]['tag'] != 'SwitchBand' )
						$this->printError('<caseBand ...> may be only in switchBand, not in "' . $this->lastTag[$this->depth-1] . '"');
				$attrs['cells'] = array();
				$this->lastTag[$this->depth] = array('tag'=>$tag, 'cells'=>&$attrs['cells']);
				$this->lastTag[$this->depth-2]['cells'][] = $attrs;
				break;
			case 'TurnCells':
				$attrs['cells'] = array();
				$this->lastTag[$this->depth] = array('tag'=>$tag, 'cells'=>&$attrs['cells']);
				$this->lastTag[$this->depth-1]['cells'][] = $attrs;
				break;
			case 'StyleSections':
				$this->ar_Style['RowGrpHead'] = array(
						'font-family'=>'Times New Roman', 'font-size'=>'small', 'font-weight'=>'bold', 'padding'=>'5px'
					);
			case 'Defines':
				$this->lastTag[$this->depth] = array('tag'=>$tag);
				break;
			case 'Style':
				if ( $this->lastTag[$this->depth-1]['tag'] != 'StyleSections' )
						$this->printError('<Style ...> may be only in StyleSections, not in "' . $this->lastTag[$this->depth-1]['tag'] . '"');
				$aF = explode(',', $attrs['for']);
				unset($attrs['for']);
				unset($attrs['Style']);
				foreach($aF as $n)
				{
					if ( $this->ar_Query[$n]['typeQuery'] & CRP_QRY_GROUP )
						$this->ar_Style[$n] = array_merge($this->ar_Style['RowGrpHead'], $attrs);
					if ( $this->ar_Style[$n] )
						$this->ar_Style[$n] = array_merge($this->ar_Style[$n], $attrs);
					else
						$this->ar_Style[$n] = $attrs;
				}
				$this->lastTag[$this->depth] = array('tag'=>$tag, 'cells'=>&$attrs['cells']);
				$this->lastTag[$this->depth-2]['cells'][] = $attrs;
				break;
			case 'ifCondition':
				$attrs['tag'] = 'Expr';
				if ( !($text=$attrs['text']) )
					$text = 'if ( ' . $attrs['for'];
				if ( isset($attrs['value']) )
				{
					if ( $this->aCode['target'] )
						$attrs['value'] = iconv('utf-8', $this->aCode['target'], $attrs['value']);
					$text .= '==' . $attrs['value'];
				}
				$text .= ' )' . $cBreak;
				if ( $this->aCode['target'] )
					$text = iconv('utf-8', $this->aCode['target'], $text);
				$attrs['text'] = $text;
				$attrs['text'] .= '{' . $cBreak;
				$attrs['cells'] = array();
				$this->lastTag[$this->depth] = array('tag'=>$tag, 'cells'=>&$attrs['cells']);
/*
				if ( $this->Band['openCell'] )
				{
					$lastCell = &$this->lastTag[$this->depth-2]['cells'][count($this->lastTag[$this->depth-2]['cells'])-1];
					$lastCell['cells'] = $attrs;
				}
				else
*/
					$this->lastTag[$this->depth-1]['cells'][] = $attrs;
				break;
			case 'Define':
				if ( $this->aCode['target'] )
					$attrs['text'] = iconv('utf-8', $this->aCode['target'], $attrs['text']);
				if ( $this->lastTag[$this->depth-1]['tag'] == 'Defines' )
					$this->ar_Const['GLOBAL'][$attrs['name']] = $attrs['text'];
				elseif ( $this->lastTag[$this->depth-1]['tag'] == 'Band' )
				{
					$attrs['tag'] = 'Expr';
					if ( $attrs['scope'] == 'data' )
					{
						$this->ar_Const['DATA'][$attrs['name']] = $attrs['text'];
						$text .= '$row.' . $attrs['name'] . '=';
					}
					else
					{
						if ( $this->type & CRP_TYPE_JSFM_OUT )
							$text = 'var ';
						$text .= '$' . $attrs['name'] . '=';
					}
					if ( isset($attrs['min']) )
					{
						if ( $this->type & CRP_TYPE_JSFM_OUT )
							$text .= 'Math.';
							$text .= 'max(' . $attrs['min'] . ',' . $attrs['text'] . ')';
					}
					elseif ( isset($attrs['max']) )
					{
						if ( $this->type & CRP_TYPE_JSFM_OUT )
							$text .= 'Math.';
							$text .= 'min(' . $attrs['max'] . ',' . $attrs['text'] . ')';
					}
					else
						$text .= $attrs['text'];
					$attrs['text'] = $text . ';';
					$this->lastTag[$this->depth-1]['cells'][] = $attrs;
				}
				else
					$this->printError('<Define ...> may be only in <Defines> or <Band>, not in "' . $this->lastTag[$this->depth-1]['tag'] . '"');
				$this->lastTag[$this->depth] = $attrs;
				break;
			case 'SetTabCtrl':
//				$this->aSettings['TAB_CTRL'] = 1;
				if ( $this->type & CRP_TYPE_JSFM )
				{
					$this->type |= CRP_OUT_TABS;
					$this->lastTag[$this->depth-1]['cells'][] = $attrs;
					$this->depth--;
				}
				else
				{
					startElement($parser, 'Cell', array_merge($attrs, array('tag'=>'Cell', 'colspan'=>'%%#colAll%%')));
					endElement($parser, 'Cell');
					startElement($parser, 'BROW', array('tag'=>'BROW'));
					endElement($parser, 'BROW');
				}
				startElement($parser, 'SetBand', array_merge($attrs, array('tag'=>'SetBand')));
				endElement($parser, 'SetBand');
				$this->depth++;
				break;
			case 'CheckVisibility':
				$this->aSettings['CHECK_VISIBILITY'] = 1;
				if ( strpos($this->Band['attrs']['for'], 'HEAD') >= 0 )
					$this->aSettings['HIDDEN_COL'] = 1;
//f_print_r($this->Band['attrs'], '---' . strpos($this->Band['attrs']['for'], 'HEAD') . '+++' . $this->aSettings['HIDDEN_COL']);
				if ( $this->Band['openCell'] )
				{
					$lastCell = &$this->lastTag[$this->depth-2]['cells'][count($this->lastTag[$this->depth-2]['cells'])-1];
					$lastCell['CheckVisibility'] = $attrs;
//					$this->lastTag[$this->depth] = $attrs;
				}
				else
				{
					$this->printError('Use <CheckVisibility> only in Cell');
				}
				break;
			case 'SetBand':
				if ( !$attrs['for'] )
					$this->printError('Must bee evaluate parameters "for" in tag <SetBand>');
				if ( $this->aCode['target'] )
				{
					$attrs['text'] = iconv('utf-8', $this->aCode['target'], $attrs['text']);
					$attrs['separate'] = iconv('utf-8', $this->aCode['target'], $attrs['separate']);
				}
				if ( $this->Band['openCell'] )
				{
					$lastCell = &$this->lastTag[$this->depth-2]['cells'][count($this->lastTag[$this->depth-2]['cells'])-1];
					if ( $lastCell['SetBand'] )
						$this->printError('Use included <SetBand> not acceptably');
					$text = $lastCell['text'];
					$lastCell['textBefore'] = $text;
					$lastCell['text'] = '';
					$lastCell['SetBand'] = $attrs;
					$this->lastTag[$this->depth] = $attrs;
				}
				else
				{
					$n = $attrs['for'];
					$attrs['cells'] = array();
					$this->lastTag[$this->depth] = array('tag'=>$tag, 'cells'=>&$attrs['cells'], 'text'=>$attrs['text']);
					$this->lastTag[$this->depth-1]['cells'][] = $attrs;
//					if ( $attrs['alias'] )
//						$n = $attrs['alias'];
					$this->ar_Query[$n]['typeQuery'] |= CRP_QRY_PRINT;
				}
				break;
			case 'Expr':
				if ( $this->aCode['target'] )
					$attrs['text'] = iconv('utf-8', $this->aCode['target'], $attrs['text']);
				if ( $this->Band['openCell'] )
				{
					$lastCell = &$this->lastTag[$this->depth-2]['cells'][count($this->lastTag[$this->depth-2]['cells'])-1];
					$lastCell['Expr'] = $attrs;
					$this->lastTag[$this->depth-1]['cells'][] = &$lastCell['Expr'];
				}
				else
				{
					$this->lastTag[$this->depth-1]['cells'][] = $attrs;
				}
				break;
			case 'Cell':
				if ( $this->aCode['target'] )
					$attrs['text'] = iconv('utf-8', $this->aCode['target'], $attrs['text']);
				$this->lastTag[$this->depth-1]['cells'][] = $attrs;
				$this->Band['openCell'] = 1;
				break;
			case 'BROW':
				$this->lastTag[$this->depth-1]['cells'][] = $attrs;
				break;
			case 'Print':
				echo $this->printReport();
				break;
			case 'Report':
			case 'QuerySections':
			case 'BandSections':
				break;
			default:
				$this->printError('Undefined tag "' . $tag . '"');
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
			case 'ifCondition':
				$this->Band['rows'][] = array('tag'=>'Expr', 'text'=>'}');
				break;
			case 'Band':
				$attrs = $this->Band['attrs'];
//??????????????????
//				if ( $this->Band['rows'][count($this->Band['rows'])-1]['tag'] == 'SetBand' )
				if ( $this->Band['rows'][count($this->Band['rows'])-1]['tag'] != 'BROW' )
					$this->Band['rows'][] = array('tag'=>'BROW', 'begin'=>'none', 'end'=>'none');
//				elseif ( $this->Band['rows'][count($this->Band['rows'])-1]['tag'] != 'BROW' )
//					$this->Band['rows'][] = array('tag'=>'BROW');
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
						$for = array_keys($this->ar_Query['MAIN']['Group']);
						$for[] = 'MAIN';
					}
					else
						$for = explode(',', $for);
				}
//print_r($for); 
				$cBreak = "\n";
				if ( !($pad=$attrs['paddingLevel']) )
					$pad = '10';
				foreach($for as $fv)
				{
//					if ( !($nF=$attrs['alias']) )
//						$nF = $fv;
					$arr = explode(':', $fv);
					$nF = $arr[0];
					if ( !($fv=$arr[1]) )
						$fv = $nF;
					if ( ($this->ar_Query[$fv]['typeQuery'] & CRP_QRY_PRINT) || $fv == 'HEAD' || $fv == 'TOTAL' )
					{
if (DEBUG_BAND>1)
{
echo "\n<BR/>BAND-------------- name=<b>$nF</b>   ";
f_print_r($Rows);
}
						$str = $this->prepareCells($this->Band['rows'], $fv);
						$this->prepareBand($str, $fv, $nF, $level, $place, $pad);
					}
				}
				break;
			case 'Cell':
				$this->Band['openCell'] = 0;
				break;
			case 'Report':
//				$this->printReport();
if (defined('TIME'))
{
time_end('PRINT');
time_end('TOTAL');
time_print();
}
				break;
		}
	}

	function print_text_section($text, $name='', $footer=0, $level='*', $printNRow=false)
	{
		//echo "<pre>\n*********** Tекция=$name FOOTER=$footer LEVEL=$level ***\n" . debug_parse($text) . "\n</pre>";
		echo "<pre>\n*********** Tекция=$name FOOTER=$footer LEVEL=$level ***\n";
//echo debug_parse($text) . "\n";
		$arr = split( "\n", debug_parse($text));
		$i = 1;
		if ( PRINT_SECTION == 2 )
			echo "create_function('','<br>";
		foreach($arr as $str)
		{
			if ( PRINT_SECTION == 2 )
				echo ereg_replace("'", "\'", $str) . "<br>";
			else
			{
				if ( $printNRow )
					echo "<b>" . sprintf('% 3d', ($i++)) . "</b>).&nbsp;&nbsp;&nbsp;";
				echo $str . '<br>';
			}
		}
		if ( PRINT_SECTION == 2 )
			echo "');<br>";
		echo "\n</pre>";
	}

	function startData($parser, $text) 
	{
//echo '!!!=' . $text . '=!!!';
//		$this->lastTag[$this->depth-1]['cells'][count($this->lastTag[$this->depth-1]['cells'])-1]['text'] .= iconv('utf-8', 'cp1251', str_replace('"', '\"', $text));	//. "\n";
		if ( $this->aCode['target'] )
			$text = iconv('utf-8', $this->aCode['target'], $text);
		$this->lastTag[$this->depth-1]['cells'][count($this->lastTag[$this->depth-1]['cells'])-1]['text'] .= str_replace('"', '\"', $text);	//. "\n";
	}

	function parse(&$rep, $xml_str)
	{
		global $oReport;
//$$$$$$$$$$$$
//$this->type = CRP_TYPE_JSFM_OUT;
		switch($oReport->type)
		{
			case CRP_TYPE_JSFM:
				$oReport->aCode['db'] = 'cp1251';
				$oReport->aCode['data'] = 'cp1251';
				$oReport->aCode['target'] = 'cp1251';
				break;
			case CRP_TYPE_HTML:
			case CRP_TYPE_XML:
				$oReport->aCode['db'] = 'cp1251';
				$oReport->aCode['target'] = 'cp1251';
				break;
		}
		$oReport->typeOUT = $oReport->type & CRP_TYPE_OUT;
		if ( $this->typeOUT == CRP_TYPE_JSFM_OUT )
			define('CHECK_VISIB_COL',		1);


if (defined('TIME'))
time_start('TOTAL');
//		$oReport = &$this;
//		$aXml = domxml_open_mem($xml_str);
//		$aXml = domxml_xmltree($xml_str);
//print_r($aXml);
//		$xml_str = iconv('utf-8', 'cp1251', $xml_str);
		if ( $_REQUEST['urp_print_tpl'] )
		{
			header('Content-Type: text/xml');
			echo $xml_str;
//			if ( $_REQUEST['urp_print_tpl'] == 'only' )
			die(0);
		}
//xml_set_object()
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

//!!! L-я есть только в PHP 5. +братить внимание на порядок, нужен как в a2 
/**/
function array_intersect_key_(&$a1, &$a2)
{
	$c = array();
	foreach($a2 as $k=>$v)
		if ( array_key_exists($k, $a1) )
			$c[$k] = $a1[$k];
	return $c;
}
/**/
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

//** Lункция для сортировки клічевvх массивов по pNom
function cmpPNom($ka, $kb)
{
	global $arraySort;
	return ( $arraySort[$ka]['pNom'] >= $arraySort[$kb]['pNom'] ) ? 1 : -1;
}

//** Lункция для производьной сортировки массивов вvборок
function fCompSetField($ka, $kb)
{
	global $arraySort;
//echo "\n<BR/>{$ka['NomRow']}<>{$kb['NomRow']} == $ret";
	if ( $ka['keyGroup'] != $kb['keyGroup'] )
		return ($ka['NomRow'] > $kb['NomRow']);
	foreach($arraySort['key'] as $f)
	{
		if ( $ka[$f] == $kb[$f] )
			continue;
		$ret = ( ($ka[$f] > $kb[$f]) ^ ($arraySort['type']) );
//echo " == $ret";
		return $ret;
	}
	return 0;
}

?>
