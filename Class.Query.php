<?
class CQuery
{
const SEND_PARAM_BY_VALUE		= 0x0001;
const SEND_PARAM_BY_BIND		= 0x0002;
var $connect;
var $stmt;
var $sendParam = SEND_PARAM_BY_VALUE;
var $nullArray = array();
//var $name;

	function CQuery($param, $name='')
	{
		$strConn = "{$param['driver']}:host={$param['host']};dbname={$param['dbname']}{$param['dopparam']}";
//		$this->name = $name;
//echo $strConn;
		try {
			$this->connect = new PDO($strConn, $param['user'], $param['password']);
		}
		catch (PDOException $e) {
//			die("<BR>CreateConnect ($strConn) ERROR: " . $e->getMessage());
			throw new Exception("<BR>CreateConnect ($strConn) user='{$param['user']}' ERROR: " . $e->getMessage());
		}
//		$this->connect->exec("SET client_encoding to win;");	//utf8
		if ( $param['iniConnect'] )
			foreach($param['iniConnect'] as $iniConn)
			{
//echo " iniConnect ($iniConn) ";
				$this->connect->exec($iniConn);
			}
		$this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

//** Приведение запроса в БД на получение данных *******************************
	function reductionQuery($sqlText, &$params=null)
	{
		$arr = explode('%%', $sqlText);
		$aText[0] = $arr[0];
		$lText = 1;

		for($i=1; $i<count($arr); $i++)
		{
			$s = $arr[$i];
			if ( $s[0] == '|' )
			{
//echo "\n<br>SPLIT - <b>$s</b>";
				if ( $s[1] == ' ' )
					$aText[$lText] = 'AND';
				$aText[$lText++] .= substr($s, 1);
			}
			else
			{
//echo "\n<br>PARAM - <b>$s</b>";
				if ( isset($params[$s]) )
				{
					if ( $this->sendParam == SEND_PARAM_BY_VALUE )
						$aText[$lText] .= '' . $params[$s];
					else
					{
						$aText[$lText] .= ':' . $s;
						$bParam[$s] = $params[$s];
					}
					$aText[$lText] .= $arr[++$i];
					$lText++;
				}
				else
				{
					while( $arr[$i+1][0] != '|' && $i < count($arr) )
						$i++;
				}
			}
		}

		return join(' ', $aText);
	}

//** Выполнение запроса в БД на получение данных *******************************
	function execQuery($sqlText, &$params=null, $name='')
	{
if (defined('DEBUG_EXEC')) //???????????????????? $name
echo "\n<BR>execDataQuery <b>$name</b><BR>\n" . $sqlText . ' (param=' . json_encode($params);

	$sqlText = $this->reductionQuery($sqlText, $params);
if (defined('DEBUG_EXEC'))
echo "\n<BR>===> " . $sqlText;

		try {
			$this->stmt = $this->connect->prepare($sqlText);
			$this->stmt->execute($bParam);
		}
		catch (PDOException $e) {
//			die("<br>Query ($name) <br>$sqlText<br><br>Ошибка чтения из БД: " . $e->getMessage());
			throw new Exception("<br>Query ($name) <br>$sqlText<br><br>Ошибка чтения из БД: " . $e->getMessage());
		}
		return $bParam;
	}

//** Чтение строки с убиранием пробелов ****************************************
	function getDataRow($vid=PDO::FETCH_ASSOC)	//PGSQL_ASSOC)
	{
//		$ret = $this->stmt->fetch($vid);
//		return $ret;
		return $this->stmt->fetch($vid);
	}

//** Чтение результата *********************************************************
	function getData($vid=PDO::FETCH_ASSOC)	//PGSQL_ASSOC)
	{
		return $this->stmt->fetchAll($vid);
	}

//** Формирование запроса ******************************************************
	function makeQuery($name, $param) 
	{
		$sWher = '%%|WHERE';
		if ( $param['sqlSelect'] )
			return $param['sqlSelect'];
		if ( !($tab=$param['table']) )
			$tab = $name;
		$key = $param['fKey'];
		if ( !($fld=$param['fields']) )
			$fld = '*';
		elseif ( !strstr($fld . ',', $key . ',') )
			$fld .= ',' . $key;

		$order = $param['order'];
		$rub = $param['fRubr'];
		if ( !$order )
			$order = $rub;
		if ( !$order )
			$order = $param['fName'];
		if ( $order )
			$order = $order;
		elseif ( $param['fName'] )
			$order = $param['fName'];

		$sqlSelect = "SELECT $fld FROM $tab ";
		if ( ($cond=$param['condition']) )
		{
			$sqlSelect .= "$sWher {$cond}%%|";
			$sWher = '';
		}
		if ( ($cond=$param['filt']) )
		{
//			$sqlSelect .= "$sWher {$param['fName']} LIKE '%$cond%'%%|";
			$sqlSelect .= "$sWher {$param['fName']} ~* '$cond'%%|";
			$sWher = '';
		}

		if ( $order )
			$sqlSelect .= "ORDER BY $order ";
		if ( $param['LIMIT'] )
			$sqlSelect .= "LIMIT {$param['LIMIT']}";

//		$param['sqlSelect'] = $sqlSelect;
		return $sqlSelect;
	}

}

//** Формирование JSON *********************************************************
function makeJSON($data)
{
	return str_replace(',{', ",\n{", json_encode($data));
} 

?>
