<?php
class blank extends SWAF_M
	{
	public $route = "collumn";
	public $table = false;
	public $dbName = "MMS";
	
	public function collumn()
		{
		return true;
		}

	public function tbl($table)
		{$this->table = $table;return $this;}
		
	}
?>