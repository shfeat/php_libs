<?php

function curl($url, $options=array())
{
	$result = array();
	if( is_callable('curl_init') )
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch, CURLOPT_ENCODING, "gzip");
			
		// 超时
		$options['timeout'] = isset($options['timeout'])? intval($options['timeout']) : 10;
		curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout']);
			
		// 返回
		if(isset($options['noreturn']) && $options['noreturn'])
		{
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
		}
		else
		{
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		}
			
		// 是否SSL
		if(isset($options['ssl']) && $options['ssl'])
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);  //这两行一定要加，不加会报SSL 错误
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}
			
		// http header
		if(isset($options['header']) && $options['header'])
		{
			if(!is_array($options['header']))
			{
				$options['header'] = array($options['header']);
			}
			$headers = array();
			foreach($options['header'] as $key=>$value)
			{
				if(is_int_ex($key))
				{
					$headers[] = $value;
				}
				else
				{
					$headers[] = $key.':'.$value;
				}
			}
			//curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		// 是否POST
		if( (isset($options['post']) && $options['post']) || (isset($options['get']) && !$options['get']) )
		{
			curl_setopt($ch, CURLOPT_POST, true);
			if(isset($options['data']) && $options['data'])
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options['data']));
			}
		}
			
		// 伪造来源
		if(isset($options['referer']) && $options['referer'])
		{
			curl_setopt($ch, CURLOPT_REFERER, $options['referer']);
		}
			
		// 用户代理
		if(isset($options['useragent']) && $options['useragent'])
		{
			curl_setopt($ch, CURLOPT_USERAGENT, $options['useragent']);
		}
			
		$resp = curl_exec($ch);
		$info = curl_getinfo($ch);
		$result = array('status' => $info, 'data' => $resp);
		$result['status']['flag'] = empty($resp)? false : true;
		$result['status']['flag'] = in_array($info['http_code'], array(0,400,401,403,404,408,410,500,502,503,504))? false : $result['status']['flag'];
		curl_close($ch);
	}
	else
	{
		if( (isset($options['post']) && $options['post']) || (isset($options['get']) && !$options['get']) )
		{
			$method = 'POST';
			$request['content'] = $data;
		}
		else
		{
			$method = 'GET';
		}
		$request['method'] = $method;
			
		if(isset($options['header']) && $options['header'])
		{

			if(!is_array($options['header']))
			{
				$options['header'] = array($options['header']);
			}
			$headers = '';
			foreach($options['header'] as $key=>$value)
			{
				if(is_int_ex($key))
				{
					$headers .= $value.';';
				}
				else
				{
					$headers .= $key.':'.$value.';';
				}
			}
			$headers = substr($headers, 0 , -1);
			$request['header'] = $headers;
		}
		$stream_context = stream_context_create(array('http'=>$request));
		$resp = @file_get_contents($url, FALSE, $stream_context);
		$result = array('status'=>array(), 'data'=>$resp);
		$result['status']['flag'] = empty($resp)? false:true;
	}

	return $result;
}

function curl_download($url, $filename, $options=array())
{
	$ch = curl_init();
	$fp = fopen($filename, 'wb');
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	//curl_setopt($hander,CURLOPT_RETURNTRANSFER,true);//以数据流的方式返回数据,当为false是直接显示出来

	// 超时
	$options['timeout'] = isset($options['timeout'])? intval($options['timeout']) : 60;
	curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout']);

	// 是否SSL
	if(isset($options['ssl']) && $options['ssl'])
	{
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);  //这两行一定要加，不加会报SSL 错误
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	}

	// http header
	if(isset($options['header']) && $options['header'])
	{
		if(!is_array($options['header']))
		{
			$options['header'] = array($options['header']);
		}
		$headers = array();
		foreach($options['header'] as $key=>$value)
		{
			if(is_int_ex($key))
			{
				$headers[] = $value;
			}
			else
			{
				$headers[] = $key.':'.$value;
			}
		}
		//curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}

	// 伪造来源
	if(isset($options['referer']) && $options['referer'])
	{
		curl_setopt($ch, CURLOPT_REFERER, $options['referer']);
	}

	// 用户代理
	if(isset($options['useragent']) && $options['useragent'])
	{
		curl_setopt($ch, CURLOPT_USERAGENT, $options['useragent']);
	}

	$resp = curl_exec($ch);
	$info = curl_getinfo($ch);
	$result = array('status' => $info, 'data' => $resp);
	$result['status']['flag'] = empty($resp)? false : true;
	$result['status']['flag'] = in_array($info['http_code'], array(0,400,401,403,404,408,410,500,502,503,504))? false : $result['status']['flag'];
	curl_close($ch);
	fclose($fp);
	return $result;
}

function curl_options($referer)
{
	$ip = random_ip();
	$header = array('CLIENT-IP' => $ip, 'X-FORWARDED-FOR' => $ip);
	return array(
		'referer' => $referer,
		'useragent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:42.0) Gecko/20100101 Firefox/42.0',
		'header' => $header
	);
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

// ip in china
function random_ip()
{
	$ip_long = array(
		array('607649792', '608174079'), //36.56.0.0-36.63.255.255
		array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
		array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
		array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
		array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
		array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
		array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
		array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
		array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
		array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
	);
	$rand_key = mt_rand(0, 9);
	return long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
}


function download_local($file, $filename)
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

function resume_broken_downloadlocal($file, $filename) 
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
