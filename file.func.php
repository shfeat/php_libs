<?php

//�Ż���require_once
function sh_require($filename) 
{
    static $_importFiles = array();
    if( !isset($_importFiles[$filename]) ) 
	{
		require $filename;
		$_importFiles[$filename] = true;
    }
}

//�Ľ��Ĵ���Ŀ¼
function mkdir_ex($path, $mod='0777')
{
    if(!is_dir($path))
	{
        mkdir_ex(dirname($path), $mod);
        mkdir($path, $mod);
    }
}

//�ݹ��ȡĿ¼�µ��ļ�
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

//����ļ�������Ч�ʽϸ�
function get_file_line($file)
{
	$line = 0;
	$fp = fopen($file, 'r') or die("���ļ�ʧ��!"); 
	if($fp)
	{
		//��ȡ�ļ���һ�����ݣ�php5
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

//��ȡĿ¼�������ļ���Ŀ¼(��������Ŀ¼���ļ���Ŀ¼��)
function get_dir_file()
{
    $handler = opendir($dir);
    while (($filename = readdir($handler)) !== false) 
	{//���ʹ��!==����ֹĿ¼�³��������ļ���"0"�����
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

function get_file_ext($filename)
{
	$ext = substr(strrchr($filename, '.'), 1);
	return $ext? $ext : '';
}
