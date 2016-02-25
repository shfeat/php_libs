<?php
// url 参数中是否有
// 还需完善
function has_get($array, $url='')
{
	if(!$array) return false;
	$url OR $url = (isset($_SERVER['REQUEST_SCHEME'])? $_SERVER['REQUEST_SCHEME'] : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$info = parse_url($url);

	$queries = explode('&', $info['query']);
	$params = array();
	foreach ($queries as $query)
	{
		$item = explode('=', $query);
		$params[$item[0]] = urldecode($item[1]);
	}
	foreach($array as $key=>$value)
	{
		if(is_array($value))
		{
			if(!isset($params[$key]) || !in_array($params[$key], $value))
				return false;
			else 
				return true;
		}
		else
		{
			if($value)
			{
				if(isset($params[$key]) && $params[$key] == $value)
				{
					return true;
				}
				return false;
			}
			else
			{
				if(isset($params[$key]))
				{
					if($params[$key])
						return false;
				}
				return true;
			}
		}
	}
}

// 添加 url 参数，返回新 url
function add_url_params($params, $url='')
{
	$url OR $url = (isset($_SERVER['REQUEST_SCHEME'])? $_SERVER['REQUEST_SCHEME'] : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$info = parse_url($url);
	if(!$info) return '';
	$url_new = '';
	$params_old = array();
	if(isset($info['query']))
	{
		$queries = explode('&', $info['query']);

		foreach ($queries as $query)
		{
			$item = explode('=', $query);
			$params_old[$item[0]] = urldecode($item[1]);
		}
	}
	$params = array_merge($params_old, $params);
	$url = '';
	if(isset($info['scheme']))
	{
		$url .= $info['scheme'].'://';
	}
	if(isset($info['host']))
	{
		$url .= $info['host'];
	}
	return $url.$info['path'].'?'.http_build_query($params);
}

//获得 url 参数，可用于web服务器重写
function get_url_param($key, $url='')
{
	$url OR $url = (isset($_SERVER['REQUEST_SCHEME'])? $_SERVER['REQUEST_SCHEME'] : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$info = parse_url($url);
	if(!isset($info['query'])) return '';
	
	$queries = explode('&', $info['query']);
	foreach ($queries as $query)
	{
		$item = explode('=', $query);
		if($item[0] == $key) return urldecode($item[1]);
	}
	return '';
}

function parse_query($str, $del='&', $del2='=')
{
	if(empty($str)) return false;
	$arr = explode($del, $str);
	$arr2 = array();
	foreach($arr as $val)
	{
		$t = explode($del2, $val, 2);
		$arr2[$t[0]] = $t[1];
	}
	return $arr2;
}