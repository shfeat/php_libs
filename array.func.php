<?php

//将混合型数组换成以$key为键的新数组，以便其他操作
function array_index($arr, $key)
{
	if( empty($arr) ) {
		return array();
	}
	$new_arr = array();
	foreach($arr as $val)
	{
		$new_arr[$val[$key]] = $val;
	}
	return $new_arr;
}

function array_sort2($arr,$keys,$type='asc')
{
	if( empty($arr) ) return false;
		
	$keysvalue = $new_array = array();
	foreach ($arr as $k=>$v)
	{
		$keysvalue[$k] = $v[$keys];
	}
	if ($type == 'asc')
	{
		asort($keysvalue);
	}
	else
	{
		arsort($keysvalue);
	}
	reset($keysvalue);
	$count = count($keysvalue);
	foreach ($keysvalue as $k=>$v)
	{
		if ($type == 'asc')
		{
			$new_array[$k] = $arr[$k];
		}
		else 
		{
			$new_array[$count-1 - $k] = $arr[$k];
		}
	}
	return $new_array; 
}

function array2object($d) 
{
	if (is_array($d)) {
		return (object) array_map(__FUNCTION__, $d);
	}
	else {
		return $d;
	}
}

function object2array($d) 
{ 
	if (is_object($d)) { 
		$d = get_object_vars($d); 
	} 
	if (is_array($d)) { 
		return array_map(__FUNCTION__, $d); 
	} 
	else { 
		return $d; 
	} 
}