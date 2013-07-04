<?php
class tables extends SWAF_C
	{
	public $db_name = "MMS";
	
	public function listTables()
		{
		$sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='{$this->db_name}'";
		return $this->M->config->__dbFetchAll($sql,"Failed to get table list:$sql");
		}
	
	public function getTableRows($columns,$name,$start=1,$end=40,$where=array())
		{
		//$sql = "SELECT $columns FROM {$this->db_name}.$name LIMIT $start, $end";
		return $this->M->blank->tbl($name)->SELECT($columns)->where($where)->query($start,$end);
		//return $this->M->config->__dbFetchAll($sql,"Failed to get table list:$sql");
		}
	public function getTableConfig($table)
		{
		return $this->M->config->SELECT("*")->where(array("TABLE_NAME"=>$table))->query();
		}
	public function getColumnConfig($table)
		{
		return $this->M->blank->tbl('tconfig')->SELECT("*")->where(array("table_name"=>$table))->query();	
		}
	public function getTableColumns($table)
		{
		$sql = "SELECT COLUMN_NAME,COLUMN_COMMENT as LABEL,EXTRA,DATA_TYPE,COLUMN_TYPE, ORDINAL_POSITION FROM information_schema.columns WHERE table_name='$table' AND TABLE_SCHEMA='{$this->db_name}'";
		return $this->M->config->__dbFetchAll($sql,"Failed to get table Columns:$sql");
		}
	
	public function searchTable($table,$whereObject,$columns)
		{
		return $this->M->blank->tbl($table)->select($columns)->where($whereObject)->query();	
		}
	public function deleteTableRow($table,$where){
		return $this->M->blank->tbl($table)->delete()->where($whereObject)->query();
		}
	public function insertIntoTable($table,$values)
		{
		return $this->M->blank->tbl($table)->insert()->set($values)->query();
		}
	public function updateTable($table,$setObject,$whereObject)
		{
		return $this->M->blank->tbl($table)->update()->set($setObject)->where($whereObject)->query();	
		}
	public function getDefineList($list_name){
			return $this->M->blank->tbl('db_definelists')->SELECT('*')->where(array('list_name'=>$list_name))->query();	
		}
	}
?>