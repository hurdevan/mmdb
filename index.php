<?php	
include "../SWAF/SWAF_3.0.php";
class c extends SWAF
	{
	public $serverPath = "/var/www/";
	public $rootPath ="MMS/";
	/*public $user = array(
						'id'=>0,
						'name'=>'guest',
						'group'=>array(),
						'db'=>array('localhost','guest','guestpassword')
						);
	*/
	public $user = array(
						'id'=>1,
						'name'=>'admin',
						'group'=>array(),
						'db'=>array('localhost','root','victinosh')
						);
	public $defaultRequest = "home";
	
	public function main()
		{
		$this->C->html;
		$this->C->fsync;
		
		$this->handleRequest();
		if($this->requestType == "D")
			{
			$this->fireEvent("ondata");	
			}else{
			$this->fireEvent("onpage");		
			}
		$this->fireEvent("onbegin");
		$this->fireEvent("onexit");
		}

	public function onpage()
		{
		$this->HTML = $this->C->html->ldHTML($this->dir."/BasicHTML.php");
		$this->HTML->base->push('http://'.$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL']);
		}
	
	public function onexit()
		{
		if(property_exists($this,"HTML"))echo $this->HTML->render();
		}
	}
	
$c = new c();
?>