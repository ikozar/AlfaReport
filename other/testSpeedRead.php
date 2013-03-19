<?
include_once('f_print_r.php');
include_once('Class.Query.php');

$configure['connect'] = array('driver'=>'pgsql', 'host'=>'localhost','user'=>'postgres', 'password'=>'100', 'dbname'=>'Naklad');

$oQuery = new CQuery($configure['connect']);

$oQuery->execQuery('SELECT kod_kls FROM tkls_main WHERE kod_qualifier=819 LIMIT 50');
$resKls = $oQuery->getData();

time_start('LIST_VALUE');
$s = 0;
foreach($resKls as $row)
{
	$oQuery->execQuery('SELECT * FROM tkls_main WHERE kod_kls=' . $row['kod_kls']);
	$res = $oQuery->getDataRow();
	$s += $res['kod_kls'];
}
time_end('LIST_VALUE');
echo " -$s- ";

time_start('LIST_VALUE_BIND');
$s = 0;
foreach($resKls as $row)
{
	$oQuery->execQuery('SELECT * FROM tkls_main WHERE kod_kls=%%kod_kls%%', $row);
	$res = $oQuery->getDataRow();
	$s += $res['kod_kls'];
}
time_end('LIST_VALUE_BIND');
echo " -$s- ";

/*
time_start('LIST_BIND');
$s = 0;
$oQuery->connect->prepare('SELECT * FROM tkls_main WHERE kod_kls=:kod_kls');
foreach($resKls as $row)
{
//	$oQuery->stmt->execute($row);
	$oQuery->stmt->bindParam(1, $row['kod_kls'], PDO::PARAM_INT);
	$oQuery->stmt->execute();
	$res = $oQuery->getDataRow();
	$s += $res['kod_kls'];
}
time_end('LIST_BIND');
echo " -$s- ";
*/

foreach($resKls as $row)
{
	$apar[] = $row['kod_kls'];
}
$spar = join(',', $apar);
time_start('LAZY_INT');
$s = 0;
$oQuery->execQuery("SELECT kod_kls FROM tkls_main WHERE kod_kls IN ($spar)");
while($res=$oQuery->getDataRow())
	$s += $res['kod_kls'];
//$res = $oQuery->getData();
time_end('LAZY_INT');
echo " -$s- ";

$spar = '\'' . join('\',\'', $apar) . '\'';
time_start('LAZY_STR');
$s = 0;
$oQuery->execQuery("SELECT kod_kls FROM tkls_main WHERE kod_kls IN ($spar)");
while($res=$oQuery->getDataRow())
	$s += $res['kod_kls'];
//$res = $oQuery->getData();
time_end('LAZY_STR');
echo " -$s- ";

/*
time_start('LAZY_BIND');
$s = 0;
$oQuery->execQuery("SELECT kod_kls FROM tkls_main WHERE kod_kls IN (%%kods%%)", array('kods'=>$apar));
while($res=$oQuery->getDataRow())
	$s += $res['kod_kls'];
//$res = $oQuery->getData();
time_end('LAZY_BIND');
echo " -$s- ";
*/

time_print();

?>
