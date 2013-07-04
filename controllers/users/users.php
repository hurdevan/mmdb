<?php
class users extends SWAF_C
	{
	public $ACD_Class = "users_ACD";
	protected function login($user,$pass)
		{
		//print_r($this->site->user);
		$ret = $this->site->loginAsUser($user,$pass);
		if($ret)
			{
			return true;
			}else{
			return false;	
			}
		}

	public function getUser()
		{
		return $this->site->user['name'];	
		}
	}

class users_ACD extends SWAF_ACD
	{
	public $login = array(
						"owner"=>1,
						"group"=>array(array(1),ACD_Access),
						"others"=>ACD_Write,
						"restricted"=>array(),
						"runas"=>array('id'=>1,'name'=>'admin','group'=>array(),'db'=>array('localhost','root','victinosh'))
						);
	
	}
?>