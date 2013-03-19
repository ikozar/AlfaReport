<?php
function f_print_tab(&$arr, $head=null, $with_key=0)
{
	if ( !$head )
	{
		if ( !is_array($arr) )
			return false;
		$row = reset($arr);
		if ( !is_array($row) )
			return false;
		$head = array_keys($row);
	}
	echo "<table border=1 cellspacing=0 cellpadding=0 style='empty-cells: show; '><tr>";
	if ( $with_key )
		echo "<td>FST_KEY</td>";
	echo "<td>KEY</td>";
	foreach($head as $k)
		echo "<td>$k</td>";
	echo "</tr>";
	if ( $with_key )
	{
		foreach($arr as $key=>$arr_ )
		{
			echo "<tr><td rowspan=" . count($arr_) . ">$key</td>";
			foreach($arr_ as $key=>$row )
			{
				if ( $key )
					echo "</tr><tr>";
				echo "<td>$key</td>";
				foreach($head as $k)
				{
					$kk = explode('.', $k);
					$v = $row;
					foreach($kk as $k)
						$v = $v[$k];
					echo "<td>$v</td>";
				}
			}
			echo "</tr>";
		}
	}
	else
		foreach($arr as $key=>$row )
		{
			echo "<tr><td>$key</td>";
			foreach($head as $k)
			{
				$kk = explode('.', $k);
				$v = $row;
				foreach($kk as $k)
					$v = $v[$k];
				echo "<td>$v</td>";
			}
			echo "</tr>";
		}
	echo "</table>";
	return true;
}

function f_print_r(&$arr, $str='-----')
{
	echo "<br>$str<pre>";
	print_r($arr);
	echo "</pre>";
}

function debug_parse($text)
{
	return preg_replace(array('/</', '/>/'), array('&lt;', '&gt;'), $text);
}

function getmicrotime()
{
   list($usec, $sec) = explode(" ",microtime());
   return ((float)$usec + (float)$sec);
}
$time_start = null;
function time_start($vid = '***', $subvid = '')
{
		global $time_start;
		if ( $subvid )
			$time_start[$vid][$subvid]['start'] = getmicrotime();
		else
			$time_start[$vid]['start'] = getmicrotime();
}
function time_end($vid = '***', $subvid = '')
{
		global $time_start;
		if ( $subvid )
			$aStart = &$time_start[$vid][$subvid];
		else
			$aStart = &$time_start[$vid];
		if ( !$aStart['start'] )
			return;
		$time = getmicrotime();
		$ret = $time - $aStart['start'];
//		if ( $subvid )
//			$time_start["$vid-$subvid"]['time'] += $ret;
		if ( $subvid )
		{
			$time_start[$vid]['time'] += $ret;
			$time_start[$vid][$subvid]['time'] += $ret;
		}
		else
			$time_start[$vid]['time'] += $ret;
		$aStart['start'] = $time;
		return $ret;
}

function time_print($round = 5)
{
	global $time_start;
	function cmp($a, $b)
	{
	    if ( $a['time'] == $b['time'] )
	        return 0;
	    return ( $a['time'] < $b['time'] ) ? 1 : -1;
	}

	uasort($time_start, "cmp");
	$total = $time_start['TOTAL']['time'];
	echo '<table>';
	foreach($time_start as $key=>$arr)
	{
		echo "<tr><td>time_$key</td><td>" . number_format($arr['time'], $round, '.', ' ') . 
			($total ? ' (' . number_format($arr['time']*100/$total, 2, '.', '') . '%)' : '') . "</td></tr>";
		if ( count($arr) > 2 )
		{
			uasort($arr, "cmp");
			foreach($arr as $key_=>$arr_)
				if ( !ereg('^(time|start)$', $key_) )
					echo "<tr><td>&nbsp;&nbsp;&nbsp;=&nbsp;$key_</td><td>" . number_format( $arr_['time'], $round, '.', ' ' ) . "</td></tr>";
		}
	}
	echo '<table>';
}
?>
