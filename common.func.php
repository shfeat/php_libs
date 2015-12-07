<?php
/**
 * 公共函数
 * @file common.php
 * @author sh
 * @date 2014-4-11
 */

/***** 字符串 *****/
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

function dump($var)
{
	$vars = func_get_args();
	foreach($vars as $param)
	{
		var_dump($param);
	}
	exit;
}

//debug_print_backtrace()
function dump_trace()
{
	$e = new Exception;
	var_dump($e->getTraceAsString());exit;
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

/***** 数组 *****/
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

/***** 时间相关 *****/
function get_month_stamp($ym, $offset=0)
{
    $y = substr($ym, 0, 4);
    $m = substr($ym, 4);
    $first_stamp = mktime(0, 0, 0, $m, 1, $y) - $offset*3600;
    $last_stamp = mktime(0, 0, 0, $m+1, 1, $y) - 1 - $offset*3600;
    return array('first'=>$first_stamp, 'last'=>$last_stamp);
}
function month_increment($ym)
{
    $y = substr($ym,0,2);
    $m = substr($ym,2);
    return date('ym', mktime(0,0,0,$m+1,2,$y));
}

/***** 文件 *****/
//优化的require_once
function sh_require($filename) 
{
    static $_importFiles = array();
    if( !isset($_importFiles[$filename]) ) 
	{
		require $filename;
		$_importFiles[$filename] = true;
    }
}

//改进的创建目录
function sh_mkdir($path,$mod='0777')
{
    if(!is_dir($path))
	{
        sh_mkdir(dirname($path), $mod);
        mkdir($path, $mod);
    }
}

//递归获取目录下的文件
function tree($dir, $filter = '', &$result = array(), $deep = false)
{
	$files = new DirectoryIterator($dir);
	foreach ($files as $file) 
	{
		$filename = $file->getFilename();
		
		if ($filename[0] === '.') {
			continue;
		}
		
		if ($file->isDir()) 
		{
			tree($dir . DS . $filename, $filter, $result, $deep);
		} 
		else 
		{
			if(!empty($filter) && !\preg_match($filter,$filename))
			{
				continue;
			}
			if ($deep) {
				$result[$dir] = $filename;
			} 
			else {
				$result[] = $dir . DS . $filename;
			}
		}
	}
	return $result;
}

//获得文件行数，效率较高
function get_file_line($file)
{
	$line = 0;
	$fp = fopen($file, 'r') or die("打开文件失败!"); 
	if($fp)
	{
		//获取文件的一行内容，php5
		while(stream_get_line($fp,8192,"\n"))
		{
			$line++;
		}
		return $line;
	}
	else
	{
		return 0;
	}
}

//获取目录下所有文件、目录(不包括子目录下文件、目录名)
function get_dir_file()
{
    $handler = opendir($dir);
    while (($filename = readdir($handler)) !== false) 
	{//务必使用!==，防止目录下出现类似文件名"0"等情况
		if ($filename != '.' && $filename != '..' && $filename != '.svn') 
			$files[] = $filename;
	}
    closedir($handler);
	return $file;
}

function get_dir_allfiles($path, &$files) 
{
    if(is_dir($path))
	{
        $dp = dir($path);
        while ($file = $dp->read())
		{
            if($file !="." && $file !="..") {
                get_allfiles($path.'/'.$file, $files);
            }
        }
        $dp->close();
    }
    if(is_file($path)){
        $files[] =  $path;
    }
}
   
function get_filenamesbydir($dir){
    $files =  array();
    get_allfiles($dir,$files);
    return $files;
}

//获得文件行数，效率较高
function getFileLine($file)
{
	$line = 0;
	$fp = fopen($file, 'r') or die("打开文件失败!"); 
	if($fp)
	{
		//获取文件的一行内容，php5
		while(stream_get_line($fp,8192,"\n"))
		{
			$line++;
		}
		return $line;
	}
	else
	{
		return 0;
	}
}

//补全url
function geturl($url, $param)
{
	if(is_array($param))
	{
		$param = http_build_query($param);
	}
	
	if(strpos($url, '?'))
	{
		$url .= '&'.$param;
	}
	else
	{
		$url .= '?'.$param;
	}
	return $url;
}

// 添加url参数，重复的会覆盖
function add_url_params($params, $url='')
{
	if(!$url)
	{
		$url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}
	
	$info = parse_url($url);
	$url_new = '';
	$params_old = array();
	if(isset($info['query']))
	{
		$queries = explode('&', $info['query']);

		foreach ($queries as $query)
		{
			$item = explode('=', $query);
			$params_old[$item[0]] = $item[1];
		}
	}
	$params = array_merge($params_old, $params);
	return $info['scheme'].'://'.$info['host'].$info['path'].'?'.http_build_query($params);
}

// url 参数中是否有
// 还需完善
function has_get($array, $url='')
{
	if(!$array) return false;
	$url OR $url = (isset($_SERVER['REQUEST_SCHEME'])? $_SERVER['REQUEST_SCHEME'] : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$info = parse_url($url);
	if(!isset($info['query'])) return false;

	$queries = explode('&', $info['query']);
	foreach ($queries as $query)
	{
		$item = explode('=', $query);
		$params[$item[0]] = $item[1];
	}
	foreach($array as $key=>$value)
	{
		if(is_array($value))
		{
			if(!isset($params[$key]) || !in_array($params[$key], $value))
				return false;
		}
		else
		{
			if(!isset($params[$key]) || $params[$key] != $value)
				return false;
		}
	}
	return true;
}

/**
 * curl模拟请求，代替file_get_contents
 * @param string $url
 * @param bool $get
 * @param assoc $data
 * @return string
 */
function curl($url, $get=false, $data=false, $header=false, $time_out=5, $ssl=false)
{
	if( is_callable('curl_init') )
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if($ssl)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);  //这两行一定要加，不加会报SSL 错误
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
		}
		if( !empty($header) )
		{
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, $time_out);
		//curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if($get == false)
		{//post方式请求
			curl_setopt($ch, CURLOPT_POST, true);
			is_array($data) AND $data = http_build_query($data);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		$resp = curl_exec($ch);
		curl_close($ch);
	}
	else
	{
		if($get == false)
		{
			$method = 'POST';
			$request['content'] = $data;
		}
		else
		{
			$method = 'GET';
		}
		$request['method'] = $method;
		if( !empty($header) )
		{
			$headerStr = '';
			foreach($header as $val)
			{
				$headerStr .= $val.';';
			}
			$headerStr = substr($headerStr, 0 , -1);
			$request['header'] = $headerStr;
		}
		$stream_context = stream_context_create(array('http'=>$request));
		$resp = @file_get_contents($url,FALSE,$stream_context);
	}
	return $resp;
}

//请求https
function ssl_curl($url, $post)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);  //这两行一定要加，不加会报SSL 错误
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
	
	$resp = curl_exec($ch);
	$errno = curl_errno($ch);
	$errmsg = curl_error($ch);
	curl_close($ch);
	return array('data'=>$resp, 'errno'=>$errno, 'errmsg'=>$errmsg);				
}

function download($file, $filename)
{
	header('Content-Description: File Transfer');  
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$filename);  
    header("Accept-Ranges: bytes");
	//header('Content-Transfer-Encoding: binary');  
	header('Expires: 0'); 
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');  
	header('Pragma: public');  
	header('Content-Length: '.filesize($file));  
	ob_clean();  
	flush();
	readfile($file);  
	exit;  
}

function resume_broken_download($file, $filename) 
{
    if(!file_exists($file)) return false;
	
    $file_size = filesize($file);
    $file_size2 = $file_size-1;
    $range = 0;
		
    if(isset($_SERVER['HTTP_RANGE']) && $_SERVER['HTTP_RANGE']!='' && 
		preg_match("/^bytes=([0-9]+)-/i", $_SERVER['HTTP_RANGE'], $match) && $match[1] < $file_size) 
	{
        header('HTTP /1.1 206 Partial Content');
        $range = trim($match[1]);
        header('Content-Length:'.$file_size);
        header("Content-Range: bytes {$range}-{$file_size2}/{$file_size}");
		echo 1;exit;
    } 
	else
	{
        header('Content-Length:'.$file_size);
        header("Content-Range: bytes 0-{$file_size2}/{$file_size}");
    }

	header('Content-Description: File Transfer');  
	header("Content-Type: application/octet-stream"); 
    header('Accenpt-Ranges: bytes');
    header("Cache-control: public");
    header("Pragma: public");
    //解决在IE中下载时中文乱码问题
    $ua = $_SERVER['HTTP_USER_AGENT'];
    if(preg_match('/MSIE/',$ua))
	{
        $ie_filename = str_replace('+','%20',urlencode($filename));
        header('Content-Disposition: attachment; filename='.$ie_filename);
    }  
	else 
	{
        header('Content-Disposition: attachment; filename='.$filename);
    }
	
	set_time_limit(0);
    $fp = fopen($file,'rb+');
    fseek($fp, $range);
	fpassthru($fp);
	fclose($fp);
}

function get_client_ip() 
{
	if (isset($_SERVER))
	{
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		else if (isset($_SERVER["HTTP_CLIENT_IP"]))
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		else 
			$ip = $_SERVER["REMOTE_ADDR"];
	}
	else 
	{
		if(getenv("HTTP_X_FORWARDED_FOR"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if(getenv("HTTP_CLIENT_IP")) 
			$ip = getenv("HTTP_CLIENT_IP");
		else 
			$ip = getenv("REMOTE_ADDR");
	}
    // IP地址合法验证
    $ip = (false !== ip2long($ip)) ? $ip:'0.0.0.0';
    return $ip;
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
function textlist2array($str)
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
	return $info['scheme'].'://'.$info['host'].$info['path'].'?'.http_build_query($params);
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