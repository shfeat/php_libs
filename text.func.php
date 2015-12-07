<?php

function is_int_ex($var)
{
	if(is_numeric($var) && is_int($var + 0))  return true;
	return false;
}

function abslength($str)
{
	if(empty($str)) return 0;
	
	if(function_exists('mb_strlen'))
	{
		return mb_strlen($str,'utf-8');
	}
	else 
	{
		preg_match_all("/./u", $str, $arr);
		return count($arr[0]);
	}
}

function is_email($email)
{
	$pattern = '/^\w+@(\w+\.)+[a-z]{2,4}$/i';
	if(preg_match($pattern, $email)){
		return true;
	}
	else {
		return false;
	}
}

function info2array($infoStr, $semicolon='|', $semicolon2=',', $colon=':')
{
	$arr = array();
	$listArr = explode($semicolon, $infoStr);
	foreach($listArr as $val)
	{
		if($val != '') 
		{
			$arr[] = info2assoc($val, $semicolon2, $colon);
		}
	}
	return $arr;
}

function info2assoc($infoStr, $semicolon=';', $colon=':')
{
	$arr = array();
	$fieldArr = explode($semicolon, $infoStr);
	foreach($fieldArr as $val)
	{
		if($val != '') 
		{
			list($k, $v) = explode($colon, $val);
			$arr[$k] = $v;
		}
	}
	return $arr;
}

//中文字符串反转
function cn_strrev($str)
{
    if (is_string($str)) 
	{
        $len = strlen($str);
        $newstr = "";
        for ($i=$len-1; $i>=0; $i--) 
		{
			if(ord($str{$i})>160)
			{
				$newstr .= $str{$i-1}.$str{$i};
				$i--;
			}
			else
			{
				$newstr.=$str{$i};
			}
        }
        return $newstr;
    }
    else
	{
		return false;
    }
}

//UTF-8 js escape实现
function js_escape($str) 
{
    preg_match_all("/[\xc2-\xdf][\x80-\xbf]+|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}|[\x01-\x7f]+/e",$str,$r);
    $str = $r[0];
    $l = count($str);
    for($i=0; $i <$l; $i++) 
	{
        $value = ord($str[$i][0]);
        if($value < 223) {
            $str[$i] = rawurlencode(utf8_decode($str[$i]));
        }
        else {
            $str[$i] = "%u".strtoupper(bin2hex(iconv("UTF-8","UCS-2",$str[$i])));
        }
    }
    return join("",$str);
}
//UTF-8 js unescape实现
function js_unescape($str) 
{
    $ret = '';
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) 
	{
        if ($str[$i] == '%' && $str[$i+1] == 'u') 
		{
            $val = hexdec(substr($str, $i+2, 4));
            if ($val < 0x7f) $ret .= chr($val);
            else if($val < 0x800) $ret .= chr(0xc0|($val>>6)).chr(0x80|($val&0x3f));
            else $ret .= chr(0xe0|($val>>12)).chr(0x80|(($val>>6)&0x3f)).chr(0x80|($val&0x3f));
            $i += 5;
        }
        else if ($str[$i] == '%') 
		{
            $ret .= urldecode(substr($str, $i, 3));
            $i += 2;
        }
        else $ret .= $str[$i];
    }
    return $ret;
}

function asc2hex($str) 
{
	return '\x'.substr(chunk_split(bin2hex($str), 2, '\x'),0,-2);
}
//十六进制 转 ASCII
function hex2asc($str) 
{
	$str = join('',explode('\x',$str));
	$len = strlen($str);
	$data = '';
	for ($i=0;$i<$len;$i+=2) 
	{
		$data.= chr(hexdec(substr($str,$i,2)));
	}
	return $data;
}

function xml2array($xmlStr)
{
	$xml_parser = xml_parser_create();
	if(!xml_parse($xml_parser,$xmlStr,true))
	{
		xml_parser_free($xml_parser);
		return false;
	}
	else
	{
		return json_decode(json_encode(simplexml_load_string($xmlStr,'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS)),true);
	}
}

function array2xml($array) 
{   
	if(is_array($array))
	{
		$xml = '';
        foreach ($array as $key=>$val) 
		{
            if (is_array($val)) {
                $xml .= "<$key>".array2xml($val)."</$key>"; 
            } 
			else { 
                $xml .= "<$key>".$val."</$key>"; 
            } 
        } 
        return $xml; 
    }
	else
	{
		return '';
	}
}

function cutstr($str,$cutleng){
	mb_internal_encoding("UTF-8");
	return mb_substr($str,0,$cutleng);
}

function cn_substr($str, $start=0, $length, $charset="utf-8", $suffix=false)
{
	if(function_exists("mb_substr"))
	{
		if(mb_strlen($str, $charset) <= $length) return $str;
		$slice = mb_substr($str, $start, $length, $charset);
	}
	else
	{
		$re['utf-8']	= "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312']	= "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk']		= "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5']		= "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset], $str, $match);
		if(count($match[0]) <= $length) return $str;
		$slice = join("",array_slice($match[0], $start, $length));
	}
	if($suffix) return $slice."…";
	return $slice;
}

function info2mix($str)
{
	$list = array();
	$arr = explode(';', $str);
	$pattern = '/(\w+):\[(.*?)\]/';
	foreach($arr as $val)
	{
		if($val != '')
		{
			$attrStr = preg_replace($pattern, '',$val);
			$t = info2assoc($attrStr, ',', ':');
			preg_match_all($pattern, $val, $match, PREG_SET_ORDER);
			foreach($match as $k=>$v)
			{
				$t[$v[1]] = info2array($v[2],'|', ',', ':');
			}
			$list[] = $t;
		}
	}
	return $list;
}

// 闭合标签
function closetags($html) 
{
	preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
	$openedtags = $result[1];

	preg_match_all('#</([a-z]+)>#iU', $html, $result);
	$closedtags = $result[1];
	$len_opened = count($openedtags);

	if (count($closedtags) == $len_opened) {
		return $html;
	}
	$openedtags = array_reverse($openedtags);

	for ($i=0; $i < $len_opened; $i++) {
		if (!in_array($openedtags[$i], $closedtags)){
			$html .= '</'.$openedtags[$i].'>';
		} else {
			unset($closedtags[array_search($openedtags[$i], $closedtags)]);
		}
	}
	return $html;
}

function filter_style($content, $div=true)
{
	if($div) $content = preg_replace('/<div[^>]*>|<\/div>/i','',$content);//去<div>
	$content = preg_replace("/style=.+?['|\"]/i",'',$content);//去除样式
	$content = preg_replace("/class=.+?['|\"]/i",'',$content);//去除样式
	$content = preg_replace("/id=.+?['|\"]/i",'',$content);//去除样式
	$content = preg_replace("/width=.+?['|\"]/i",'',$content);//去除样式
	$content = preg_replace("/height=.+?['|\"]/i",'',$content);//去除样式
	$content = preg_replace("/border=.+?['|\"]/i",'',$content);//去除样式
	return $content;
}

/*
id,article_id,position,title,url,cover,desc
				(空行，不参与解析)
# 热门推荐		(#开头为注释，不解析)
1001,null,hot,hot_1,http://www.wanmei.com,null,null
1002,null,hot,hot_2,http://www.wanmei.com,null,null
1003,null,hot,hot_3,http://www.wanmei.com,null,null
1004,null,hot,hot_4,http://www.wanmei.com,null,null
1005,null,hot,hot_5,http://www.wanmei.com,null,null
1006,null,hot,hot_6,http://www.wanmei.com,/uploads/1510/26/4009-15102611560b34.jpg,'摘要'
*/
function listtext2array($str)
{
	$array = $headers = array();
	$rows = explode("\n", str_replace("\r" , "", $str));
	
	if(isset($rows[0]) && substr($rows[0], 0, 2) == '#,')
	{
		$headers = explode(',', $rows[0]);
	}
	
	foreach($rows as $key=>$row)
	{
		if($key == 0) continue;
		$trim = trim($row);
		if(empty($trim)) continue;
		if(substr($trim, 0, 1) == '#') continue;
		
		$t = array();
		$row = explode(',', $row);
		foreach($headers as $k=>$header)
		{
			$t[$header] = isset($row[$k])? trim($row[$k]) : '';
		}
		$array[] = $t;
	}
	return $array;
}

//短地址
function short_encode($url)
{
	$key = 'sh';
	$chars = array('a','b','c','d','e','f','g','h','i','j',
		'k','l','m','n','o','p','q','r','s','t','u','v','w',
		'x','y','z','0','1','2','3','4','5','6','7','8','9',
		'A','B','C','D','E','F','G','H','I','J','K','L','M',
		'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
	$short = array();
	$hex = md5($url.$key);
	for($i=0; $i<4; $i++)
	{
		$sub_hex = substr($hex, $i*8, 8);
		//3FFFFFFF = 30位1 = 6*5
		$int = 0x3FFFFFFF & ('0x'.$sub_hex-0);//字符串转换成float型的10进制
		$sub_short = '';
		for($j=0; $j<6; $j++)
		{
			$sub_short .= $chars[0x0000003D & $int];
			$int = $int >> 5;
		}
		exit;
		$short[] = $sub_short;
	}
	return $short;
}

function short_encode2($in)
{
    $chars = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z",
		"0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
    $hex8 = substr(md5($in), 0, 8);
    $hex_int = base_convert($hex8, 16, 10);
    $hex = 0x3FFFFFFF & $hex_int;
    $out = '';
    for($i=6; $i>0; --$i)
    {
        $index = 0x0000003D & $hex;
        $out .= $chars[$index];
        $hex = $hex >> 5; 
    }
    return $out;
}