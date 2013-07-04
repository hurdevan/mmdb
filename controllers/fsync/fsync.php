<?php
class fsync extends SWAF_C
	{
	public $ACD_Class = "fsync_ACD";
	public $defaultRequest = "main";
	
	public function init()
		{	
		}
	
	protected function main()
		{
		}
	
	public function onpage()
		{
		$this->site->HTML->head->insertJSFile("controllers/fsync/fsync.js");
		}
	public function ondata()
		{
		}	
	public function onexit()
		{
		}
	
	protected function call()
		{
		$args = array();
		$call = $_REQUEST['c'];
		$request = explode("/",$call);
		$class = $this->site;
		//echo $call;
		for($x = 0;$x < (sizeof($request)-1);$x++)
			{
			echo $x;
			$step = $request[$x];
			$class = $class->$step;
			}
		
		if(isset($_REQUEST['a']))
			{
			echo $_REQUEST['a'];
			$args = json_decode($_REQUEST['a'],true);
			}else{
			$args = array();	
			}
		
		$method = $request[sizeof($request)-1];
		$ret = SWAF_Call(array($class,$method),$args);
		echo "Fsync_Data".json_encode(array($ret))."Fsync_Data";
		}
	}
	
class fsync_ACD extends SWAF_ACD
	{//G:w, O:r
	public $main = array(
						"owner"=>1,
						"group"=>array(array(0),ACD_Access),
						"others"=>ACD_Access,
						"restricted"=>array(),
						'db'=>array('localhost','root','victinosh'),
						"runas"=>array('id'=>2,'name'=>'admin','group'=>array())
						);
	public $call = array(
						"owner"=>1,
						"group"=>array(array(0),ACD_Access),
						"others"=>ACD_Access,
						"restricted"=>array(),
						'db'=>array('localhost','root','victinosh')
						);
	}
?>