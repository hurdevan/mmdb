<?php
class home extends SWAF_C
	{
	public $defaultRequest = "main";
	
	public function init()
		{
		}
	
	public function main()
		{
		
		}
	public function onpage()
		{
		$this->site->HTML->head->insertJSFile("https://raw.github.com/HenrikJoreteg/ICanHaz.js/master/ICanHaz.min.js");
		$this->site->HTML->head->insertJSFile($this->cdir."/js/json2.js");
		$this->html = $this->site->C->html->ldHTML($this->dir."/html.php");
		$this->site->HTML->head->insertCSSFile($this->cdir."/styles.css");
		$this->site->HTML->head->insertJSFile($this->cdir."/views/jstable/table.js");
		$this->site->HTML->head->insertJSFile($this->cdir."/main.js");
		$this->site->HTML->head->insertJSFile($this->cdir."/js/views.js");
		$this->site->HTML->content->push($this->html);
		
		
		$this->loadTables();
		}
	public function loadTables()
		{
		$tables = $this->site->C->tables->listTables();
		for($x = 0;$x < sizeof($tables);$x++)
			{
			$name= $tables[$x]['TABLE_NAME'];
			$this->html->table_list->push("<option value='$name'>$name</option>");	
			}
		}
	
	public function getTableData($columns,$name,$start=1,$end=40,$where=array())
		{
		$colConfig = $this->site->C->tables->getColumnConfig($name);
		
		return array(
			"config"=>$this->site->C->tables->getTableConfig($name),
			"columnConfig"=>$colConfig,
			"rows"=>$this->site->C->tables->getTableRows($columns,$name,$start,$end,$where),
			"columns"=>$this->site->C->tables->getTableColumns($name),
			"define_lists"=>$this->getDefinedLists($colConfig)
			);
		}
	public function getDefinedLists($confs){
		$lists = "";
		for($x = 0;$x < sizeof($confs);$x++){
			if($confs[$x]['type']='enum'){
				$lists[$confs[$x]['column_name']] = $this->site->C->tables->getDefineList($confs[$x]['arg1']);
				}
			}//
		return $lists;
		}
	}
?>