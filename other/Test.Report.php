<?
include_once('../Class.Report.php');
//define('DEBUG_GROUP',			1);				// 2 - трассировка
//define('DEBUG_BAND',				1);			// 2 - вывод массива
//define('DEBUG_DATA',				1);

class cl_TestReport extends ReportBuilder
{
  var $testData = array(       //);           // массив всех тестовых данных
    'MAIN' => array(
        'k1;k2;k3;kt;t0;t1;t2;v1',
        '10;1;100;0;a;1;1;3',
        '10;1;100;1;a;2;1;8',
        '10;1;100;2;b;1;2;2',
        '10;1;101;3;b;2;1;1',
        '10;1;101;4;a;1;3;7',
        '11;2;100;5;a;2;2;5',
        '11;2;100;6;b;1;3;3',
        '11;2;101;7;b;1;2;4',
        '11;2;101;8;a;2;1;3',
        '11;2;101;9;b;1;1;9',
      ),
      'G1' => array(
        'key_field_;k2;k1;kl1;n1',
        '11*2;1;11;1;A',
        '10*1;1;10;2;B',
      ),
      'G2' => array(
        'key_field_;k3;k3v;n3;r3',
        '102;102;0;a;1',
        '103;103;102;a1;11',
        '101;101;103;a11;111',
        '104;104;102;a2;12',
        '100;100;104;a21;121',
      ),
      'LT1' => array(
        't1;nt1',
        '2;NT12',
        '1;NT21',
      ),
      'L1' => array(
        'key_field_;kl1;nL1;k1_L;k2_L',
        '1;1;Xxx;33;1',
        '2;2;Zzz;44;2',
      ),
      'L2' => array(
        'key_field_;k1_L;k2_L;n_L',
        '33*1;33;1;X-11',
        '44*2;44;2;Z-22',
      ),
    );

	function cl_TestReport()
	{
		global $_GET;
	  	$this->params = $_GET;
	}

	function getDataQuery($name, &$keyIn, $procData=0)
	{
		$this->makeQuery($name, $keyIn);
		$aKey = explode(';', $this->testData[$name][0]);
//		array_shift($aKey);
		$ret = &$this->ar_Data[$name];
		for($i=1; $i<count($this->testData[$name]); $i++)
		{
			$aVal = explode(';', $this->testData[$name][$i]);
			foreach($aKey as $nom=>$key)
					$a[$key] = $aVal[$nom];		//+1];
			if ( $procData & RP_INF_SORT )
				$a['pNom'] = sprintf('%03d', $i);
			if ( ($keyUp=$this->ar_Query[$name]['fKeyUp']) )
			{
				if ( ($level=$ret[$a[$keyUp]]) )
				{
					$level = $level['level'] + 1;
					if ( $level > $level_ )
						$subLevel[] = $a[$keyUp];
					elseif ( $level < $level_ )
						array_pop($subLevel);
				}
				else
				{
					$level = 1;
					$subLevel = array();
				}
				$level_ = $level;
				$a['level'] = $level;
				if ( $subLevel )
					$a['subLevel'] = $subLevel;
			}
			$ret[$aVal[0]] = $a;
		}
//		return $ret;
	}
	function getDataRow($name, $vid=PDO::FETCH_ASSOC)
	{
		$numRow = ++$this->ar_Query[$name]['numGetRow'];
		$arr = $this->testData[$name][$numRow];
		if ( $arr )
		{
			$aVal = explode(';', $arr);
			foreach($this->ar_Query[$name]['FIELDS'] as $nom=>$key)
					$ret[$key] = $aVal[$nom];
		}
		else
			$ret = array();
		return $ret;
	}
	function execDataQuery($name)
	{
		$this->ar_Query[$name]['FIELDS'] = explode(';', $this->testData[$name][0]);
		return null;
	}
} 

$oReport = new cl_TestReport();

$xml_str = '
<Report>
	<QuerySections>
		<Query name="LT1" table="lt1" fields="*" order="nt1" fName="nt1"/>
		<Query name="L1" table="l1" fields="*" order="nL1" fName="nL1"/>
		<Query name="L2" table="l2" fields="*" order="n_L" fName="n_L"/>
		<Query name="G1" table="g1" order="n1" fName="n1">
			<Link to="L1" reference="k2"/>
		</Query>
		<Query name="G2" table="g2" fKeyUp="k3v" fRubr="r3" order="r3" fName="n3"/>
		<Query name="MAIN" sqlSelect="SELECT * FROM gmain" sum="v1" style="sM">
			<Group to="G1" reference="k1,k2" style="sG1"/>
			<Group to="G2" reference="k3" style="sG2"/>
			<Turn key="k1,k2,kt" on="t1,t2" jj="^kt"/>
			<Link to="LT1" reference="t1"/>
			<Link to="L1" reference="k2"/>
			<Link to="L2" reference="k1_L,k2_L"/>
		</Query>
	</QuerySections>

	<BandSections>
		<Band name="HEAD">
			<Cell text="K1,K2" colspan="2"/>
			<Cell text="K3" rowspan="2"/>
			<Cell text="Sum V" rowspan="2"/>
			<TurnCells on="level">
				<Expr>
					$colSpan = %%TURN.TCOUNT%%+1;
				</Expr>
				<Cell text="%%TURN.LT1.nt1%%" colspan="$colSpan"/>
			</TurnCells>
		<BROW/>
			<Cell text="K1"/>
			<Cell text="K2"/>
			<TurnCells on="level">
				<Cell text="Sum"/>
				<TurnCells>
					<Cell text="%%TURN.t2%%"/>
				</TurnCells>
			</TurnCells>
		</Band>

		<!--Band place="header" for="all"-->
		<Band place="header" for="G1,G2,MAIN">
			<SwitchBand>
				<CaseBand for="MAIN">
					<Cell text="%%k3%% +++(%%k2%%-%%L1.k1_L%%-%%L1.k2_L%%==%%L1.L2.n_L%%)" rowspan="%%GROUP.mCount%%" condition="$nomRow == 1" />
				</CaseBand>
				<CaseBand for="G1">
					<Expr>
						$rowSpan = %%mCount%%+%%gCount%%+1;
					</Expr>
					<Cell text="%%name%% (%%MAIN.L1.nL1%%-%%MAIN.k2%%)" colspan="2" rowspan="$rowSpan" />
					<Cell/>
				</CaseBand>
				<CaseBand for="other">
					<Cell text="%%name%%"/>
				</CaseBand>
			</SwitchBand>
	
			<Cell text="%%v1%%" style="float" />
			<TurnCells on="all">
				<Cell text="%%TURNINDEX.v1%%" style="float" />
			</TurnCells>
		</Band>
	</BandSections>
	<Print/>
</Report>
';

$oReport->parse($oReport1, $xml_str);
echo '<pre>------------' . "\n";
echo "+++++++++++++++";
//print_r($oReport->ar_Query['MAIN']['TURN']['onLink']);
echo '=================' . "\n</pre>";

//print_r($oReport->ar_Data['MAIN']);

/*
1. TURN. Как в OLAP, по комбинации значений, а не по декартовому произведению
2. Сумма по иерархии поворотов: TURN a,b,c - [=ai]=s1, [=ai*bj]=s2, [=*bj]=s3, [=**ck]
	(!!! значение ai, bj и ck могут пересекаться, т.о. *bj, **ck)
3. ??? Вложенные отчеты инициализацию пропускаем, запрос данных и вывод
4. ??? Поворот в группах ? если в группе не все поля поворота ?
*/
?>
