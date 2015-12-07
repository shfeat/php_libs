<?php
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













