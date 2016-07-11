<?php

class lib_mysql_sql_builder
{
	public static $pre = '';
	public static $table = '';
	//public static $sql;
	
	public static function set_table($table, $pre='')
	{
		if(empty($pre)) {
			self::$table = self::$pre.$table;
		}
		else {
			self::$table = $pre.$table;
		}
	}
	
	public static function select($where, $field='*', $group_by=null, $order_by=null, $limit=null)
	{
		$field OR $field = '*';
		$table = self::$table;
		$where = self::handle_where($where);
		$sql = "SELECT {$field} FROM {$table} WHERE {$where}";
		if(!empty($group_by)) {
			$sql .= ' GROUP BY '.$group_by;
		}
		if(!empty($order_by)) {
			$sql .= ' ORDER BY '.$order_by; 
		}
		$sql .= self::handle_limit($limit);
		return $sql;
	}

	public static function select_one($where, $field='*')
	{
		$field OR $field = '*';
		$table = self::$table;
		$where = self::handle_where($where);
		$sql = "SELECT {$field} FROM {$table} WHERE {$where} LIMIT 1";
		return $sql;
	}
	
	public static function insert($insert)
	{
		!empty($insert) OR exit('Param $insert empty'); 
		is_array($insert) OR exit('Param $insert must be array');
		
		$table = self::$table;
		$field = '';
		$value = '';
		foreach($insert as $key=>$val)
		{
			$field .= '`'.$key.'`,';
			$value .= "'".self::escape_string($val)."'".',';
		}
		$field = substr($field , 0, -1);
		$value = substr($value, 0, -1);
		$sql = "INSERT INTO {$table}({$field}) VALUES({$value})";
		return $sql;
	}
	
	/**
	 * @param assoc $insert
	 * @param assoc $not_exist
	 * @param string $insert_id
	 */
	public static function insert_not_exist($insert, $not_exist)
	{
		$table = self::$table;
		$field = '';
		$value = '';
		foreach($insert as $key=>$val)
		{
			$field .= $key.',';
			$value .= "'".self::escape_string($val)."'".',';
		}
		$field = substr($field , 0, -1);
		$value = substr($value, 0, -1);
		$where = self::handle_where($not_exist);
		$sql = "insert into {$table}({$field}) select {$value} from temp where not exists(select * from {$table} where {$where})";
		return $sql;
	}
	
	public static function insert_batch()
	{}
	
	public static function update($where, $set, $limit=null)
	{
		$table = self::$table;
		$where = self::handle_where($where);
		$set = self::handle_set($set);
		$limit = self::handle_limit($limit);
		$sql = "UPDATE {$table} SET {$set} WHERE {$where}".$limit;
		return $sql;
	}

	public static function delete($where, $limit=null, $affected_rows=false)
	{
		$table = self::$table;
		$where = self::handle_where($where);
		$limit = self::handle_limit($limit);
		$sql = "DELETE FROM {$table} WHERE {$where}".$limit;
		return $sql;
	}
	
	/**
	 * 转化数组where -> 字符串where
	 * @param mix $where
	 */
	public static function handle_where($where)
	{
		$whereStr = '';
		if(empty($where)) 
		{
			$whereStr = ' 1';
		}
		else 
		{
			if(is_array($where))
			{
				foreach($where as $key=>$val)
				{
					$keyArr = explode(' ', $key);
					if(isset($keyArr[1])) {
						$op = $keyArr[1];
					}
					else {
						$op = '=';
					}
					if(in_array($op, array('in'))) 
					{
						$in = "(";
						foreach($val as $v)
						{
							$in .= "'".self::escape_string($v)."',";
						}
						$in = substr($in, 0, -1).')';
						$whereStr .= '`'.$keyArr[0]."` ".$op." ".$in." AND ";
					}
					else 
					{
						$whereStr .= '`'.$keyArr[0]."` ".$op." '".self::escape_string($val)."' AND ";
					}
				}
				$whereStr = substr($whereStr, 0, -5);
			}
			else 
			{
				$whereStr = $where;
			}
		}
		return $whereStr;
	}
	
	public static function handle_set($set)
	{
		$setStr = '';
		if(empty($set)) 
		{
			exit('Param $set error');
		}
		if(is_array($set))
		{
			foreach($set as $key=>$val)
			{
				$setStr .= ' `'.self::escape_string($key).'` = "'.self::escape_string($val).'",';
			}
			$setStr = substr($setStr, 0, -1);
		}
		else 
		{
			$setStr = self::escape_string($set);
		}
		return $setStr;
	}
	
	public static function handle_limit($limit)
	{
		if(empty($limit)) {
			$limit = '';
		}
		else {
			$limit = ' LIMIT '.$limit;
		}
		return $limit;
	}
	
	public static function escape_string($str, $like = FALSE)
	{
		return $str;
		if (is_array($str))
		{
			foreach ($str as $key => $val)
	   		{
				$str[$key] = self::escape_str($val, $like);
	   		}
	   		return $str;
	   	}

		if (function_exists('mysql_real_escape_string') AND is_resource(self::$conn_set[self::$default_config]))
		{
			$str = mysql_real_escape_string($str, self::$conn_set[self::$default_config]);
		}
		elseif (function_exists('mysql_escape_string'))
		{
			$str = @mysql_escape_string($str);
		}
		else
		{
			$str = addslashes($str);
		}
		
		// escape LIKE condition wildcards
		if ($like === TRUE)
		{
			$str = str_replace(array('%', '_'), array('\\%', '\\_'), $str);
		}
		return $str;
	}
	
}
