<?php
ini_set("display_errors","1");
ERROR_REPORTING(E_ALL);
include "SWAF_Model.php";
class SWAF
	{
	public $dir = __DIR__;
	public $file = __FILE__;
	public $query = false;
	public $queryType = false;
	public $site = false;
	public $user = array('id'=>0,'name'=>'guest','group'=>array());
	public $userid = false;
	public $session = false;
	public $request = false;
	public $C = false;
	public $M = false;
	public $P = false;
	public $rawRequest = false;
	protected $ACD_Object = false;
	
	public function __construct()
		{
		$this->dir = getcwd();
		if(get_parent_class($this) == "SWAF" && get_class($this) != "SWAF_M")
			{
			//set_error_handler(array($this,"reportError"));
			
			$this->C = new SWAF_Plugin_Container($this,"C");
			$this->P = new SWAF_Plugin_Container($this,"P");
			$this->M = new SWAF_Plugin_Container($this,"M");
			$this->site = $this;
			if(isset($_SERVER['REDIRECT_URL']))
				{
				$this->rawRequest = str_replace($this->rootPath,'',$_SERVER['REDIRECT_URL']);
				}else{
				$this->rawRequest = "";	
				}
			$this->request = explode('/',$this->rawRequest);
			if(isset($this->request[0]) && strlen($this->request[0]) == 0 && isset($this->request[1]))
				{
				array_shift($this->request);
				}
			
			if(isset($this->request[0]) && $this->request[0] == "D")
				{
				$this->requestType = "D";
				$this->request = array_slice($this->request,1);
				}else{
				$this->requestType = "P";	
				}
			$this->__regEvent();
			$this->isSession();
			if(method_exists($this,"main"))$this->main();
			}else{
			}
		}
	
	public function isSession()
		{
		if(isset($_COOKIE['swaf_session']) && $_COOKIE['swaf_session'] != '')
			{
			$sql =	"SELECT * FROM users WHERE uid = (SELECT uid FROM session WHERE session_name='".$_COOKIE['swaf_session']."')";
			$db = new SWAF_M();
			
			$db->site = $this->site;
			$db->dbName = "SWAF";
			if(property_exists($this,"dbName"))$db->dbName = $this->dbName;
			$res = $db->__dbFetch($sql,'Failed to get user by session!');
			if(!isset($res['uid']) || !$res['uid']){
				$this->site->session = 0;
				return false;
			}
			$user = array(
						'id'=>$res['uid'],
						'name'=>$res['username'],
						'group'=>explode(",",$res['group']),
						'db'=>array($res['db_host'],$res['db_username'],$res['db_password'])
						);
			$this->site->user = $user;
			$this->site->session = $_COOKIE['swaf_session'];
			return $user;
			}else{
			$this->site->session = 0;		
			}
		return false;
		}
	
	public function loginAsUser($user,$pass)
		{
		if($this->user['id'] != 0 && !(isset($this->user['type']) && $this->user['type']=='tmp'))return false;
		$session_name = md5(time().$_SERVER['REMOTE_ADDR']);
		$sql = "INSERT INTO session (uid,session_name)VALUES((SELECT uid FROM users WHERE username='$user' AND password='$pass'),'$session_name')";
		$db = new SWAF_M();
		$db->site = $this->site;
		$db->dbName = "SWAF";
		if($this->dbName)$db->dbName = $this->dbName;
		$res = $db->__dbQuery($sql,'Failed to login user! sql=>'.$sql);
		if(!$res)return false;
		$_COOKIE['swaf_session'] = $session_name;
		setcookie('swaf_session',$session_name,time()+60*60*24,'/');
		$this->site->session = $session_name;
		return $this->isSession();
		}
	
	public function handleRequest()
		{
		if(property_exists($this,"doRedirectLocal") && $this->doRedirectLocal == true && $this->site->requestType == "P")
			{//$_SERVER['REQUEST_URI']
			if(strstr($_SERVER['REDIRECT_URL'],'.'))
				{
				$type = "file";	
				}else{
				$type = "dir";
				}
			if(!isset($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == "/" && $_SERVER['REQUEST_URI'] == "")
				{
				//For possible requests:
				// 	1.
				//  2. /
				// Redirects to - page/
				header('Location: '.get_class($this)."/");
				die();
				}else if($_SERVER['REQUEST_URI'] == "/".get_class($this))
				{
				//For possible requests:
				//	1. page
				// redirect to - page/
				header('Location: '.get_class($this)."/");
				die();
				}else{
				$tmp = explode(get_class($this),$_SERVER['REDIRECT_URL'],2);
				$file = $this->dir.$tmp[1];
				if(strstr($file,"."))
					{
					$tmp = explode(".",$file);
					if($tmp[1] != "php")
						{
						$path = str_replace($this->site->serverPath,"",$file);
						//echo $path;
						header("Location: /$path");
						die();
						}
					}
				//echo $file;
				if($type == "dir")
					{
					chdir($file."/");
					if($file[strlen($file)-1] != "/")$file.="/";
					//echo $file.$this->indexFile;
					includeFile($file.$this->indexFile);
					die();
					return true;
					}else{
					$tmp = explode("/",$file,-1);
					$dir = implode("/",$tmp);
					chdir($dir);
					includeFile($file);
					die();
					return true;						
					}
				}
			
			}
		$request = $this->getRequestType();
		$type = $request[0];
		if(!isset($request[1]) || $request[1] == "")
			{
			if(property_exists($this,'defaultRequest'))
				{
				$req = $this->defaultRequest;
				if(method_exists($this,$req))
					{
					//SWAF_Call(array($this,$req),$_REQUEST);
					array_push($this->site->onbeginEvents,array($this,$req));
					}else{
					//trigger_error("Loading: $req");
					return $this->$type->$req->handleRequest();	
					}
				}
			}else{
			$type = $request[0];
			$cName = $request[1];
			if($type == "METHOD")
				{
				//trigger_error("Calling: $cName");
				return SWAF_Call(array($this,$cName));
				}else{		
				//trigger_error("Loading: $type->$cName");
				$res = $this->$type->$cName;
				if($res)
					{
					$res->request = array_slice($this->request,2);
					return $res->handleRequest();
					}
				return $res;
				}
			}
		}
	
	public function getRequestType()
		{
		if(sizeof($this->request)==0 || $this->request[0] == "P")
			{
			if(isset($this->request[1]))
				{
				return array('P',$this->request[1]);
				}else{
				return array('P',false);
				}	
			}else if($this->request[0] == "C"){
			if(isset($this->request[1]))
				{
				return array('C',$this->request[1]);
				}else{
				return array('C',false);
				}
			}else if($this->request[0] == "M"){
			if(isset($this->request[1]))
				{
				return array('M',$this->request[1]);
				}else{
				return array('M',false);
				}
			}else if($this->request[0] != "" && $this->site->requestType=="D"){
			return array('METHOD',$this->request[0]);
			}elseif($this->request[0] != "" && $this->site->requestType=="P"){
			return array('P',$this->request[0]);
			}else{
			return array('P',false);	
			}
		}
	
	public $onbeginEvents = array();
	public $onexitEvents = array();
	public $onpageEvents = array();
	public $ondataEvents = array();
	
	public function fireEvent($event)
		{
		$eventName = "$event"."Events";
		if(isset($this->site->$eventName))
			{
			foreach($this->site->$eventName as $class)
				{
				
				if(is_array($class))
					{
					$cls = $class[0];
					$e = $class[1];
					$cls->$e();
					}else{
					$class->$event();	
					}
				}
			}else{
			trigger_error("Invalid event name given:$event");	
			}
		}
	
	public function __regEvent()
		{
		if(method_exists($this,"onbegin"))array_push($this->site->onbeginEvents,$this);
		if(method_exists($this,"onexit"))array_push($this->site->onexitEvents,$this);
		if(method_exists($this,"onpage"))array_push($this->site->onpageEvents,$this);	
		if(method_exists($this,"ondata"))array_push($this->site->ondataEvents,$this);
		}
	
	public function __call($funcName,$args)
		{
		//trigger_error("Attempting to access, $funcName");
		if($this->ACD_Object)
			{
			if($this->ACD_Object->hasAccess($funcName,$this->site->user,ACD_Access) == true)
				{
				$perms = $this->ACD_Object->$funcName;
				if(isset($perms['runas']))
					{
					$originalUser = $this->site->user;
					$this->site->user = $perms['runas'];
					$this->site->user['type'] = 'tmp';
					trigger_error("Elevating Permissions per runas!");
					return call_user_func_array(array($this,$funcName),$args);
					trigger_error("returing to original permission!");
					if(isset($this->site->user['type']) && $this->site->user['type'] == 'tmp')
						{$this->site->user = $originalUser;}
					}else{
					return call_user_func_array(array($this,$funcName),$args);	
					}	
				}else{
				trigger_error("Invalid Access attempt => ".get_class($this)."->$funcName()\n");
				return false;
				}
			}else{
			trigger_error("No ACD Object Found! Was Attempting to call:$funcName!");	
				
			}
			
		}
	
	/*public function __get($var)
		{
		trigger_error("Attempting to access, $var");
		if($this->ACD_Object)
			{
			if($this->ACD_Object->hasAccess($var,$this->site->user,ACD_Access) == true)
				{
				$perms = $this->ACD_Object->$var;
				
				if(isset($perms['getter']))
					{
					
					$getterClass = $perms['getter'][0];
					$getterMethod = $perms['getter'][1];
					echo "$getterClass->$getterMethod";
					$C = $this->site->C->$getterClass;
					if(method_exists($C,$getterMethod))
						{
						return $C->$getterMethod(get_class($this),$var);	
						}
					}
				return $this->ACD_Object->$var;		
				}else{
				trigger_error("Invalid Access attempt => ".get_class($this)."->$funcName()\n");
				return false;
				}
			}else{
			trigger_error("No ACD Object Found!");	
				
			}	
		}
	public function __set($var,$val)
		{
		trigger_error("Attempting to access, $var");
		if($this->ACD_Object && property_exists($this->ACD_Object,$var))
			{
			if($this->ACD_Object->hasAccess($var,$this->site->user,ACD_Write) == true)
				{
				$perms = $this->ACD_Object->$var;
				
				if(isset($perms['setter']))
					{
					
					$getterClass = $perms['setter'][0];
					$getterMethod = $perms['setter'][1];
					echo "$getterClass->$getterMethod";
					$C = $this->site->C->$getterClass;
					if(method_exists($C,$getterMethod))
						{
						return $C->$getterMethod(get_class($this),$var,$val);	
						}
					}
				return $this->ACD_Object->$var;		
				}else{
				trigger_error("Invalid Access attempt => ".get_class($this)."->$funcName()\n");
				return false;
				}
			}else{
			$this->	
				
			}	
		}*/

	public function initClassACD()
		{
		if(property_exists($this,"ACD_Class") && class_exists($this->ACD_Class))
			{
			$this->ACD_Object = new $this->ACD_Class();
			//trigger_error("Loading ACD!:");
			}else{
			$this->ACD_Object = new SWAF_ACD;	
			}
		}
	}
define("ACD_None",0);
define("ACD_Access",1);
define("ACD_Write",2);



class SWAF_ACD
	{
	
	public function hasAccess($target,$user,$request)
		{
		//trigger_error("here");
		if($this->checkOwner($target,$user,$request))return true;
		//trigger_error("Is not Owner!");
		if(!$this->checkRestricted($target,$user,$request))return false;
		//trigger_error("Is not Restricted!");
		if($this->checkOthers($target,$user,$request))return true;
		//trigger_error("Others do not have access!");
		if($this->checkGroup($target,$user,$request))return true;
		//trigger_error("Does not have group access!");
		return false;
		}
	public function checkOthers($target,$user,$request)
		{
		$perms = $this->$target;
		if($perms['others'] == $request || $perms['others'] == ACD_Write)return true;
		return false;
		}
	public function checkGroup($target,$user,$request)
		{
		$perms = $this->$target;
		//if($perms['group'][0] == $user['id'])return true;
		//if($perms['group'][1] == $request || $perms['group'][1] == ACD_Write)return true;
		foreach($perms['group'][0] as $y=>$id)
			{
			if($id == $user['id'] && ($request == $perms['group'][1] || $perms['group'][1] == ACD_Write))return true;	
			}		
		
		return false;
		}
	public function checkRestricted($target,$user,$request)
		{
		$perms = $this->$target;
		foreach($perms['restricted'] as $y=>$id)
			{
			if($id == $user['id'])return false;	
			}
		return true;
		}
	public function checkOwner($target,$user,$request)
		{
		$perms = $this->$target;
		if($perms['owner'] == $user['id'])
			{
			return true;
			}else{
			return false;	
			}
		}
	}

class SWAF_Plugin_Container
	{
	public $parent = false;
	public $type = false;
	public $plugins = "";
	
	public function __get($plugin)
		{
		if(isset($this->plugins[$plugin]))
			{
			return $this->plugins[$plugin];
			}else{
			if($this->type == "C")
				{
				return $this->ldController($plugin);	
				}elseif($this->type == "P"){
				return $this->ldPage($plugin);
				}elseif($this->type = "M"){
				return $this->ldModel($plugin);	
				}
			}
		}
	
	function __construct($parent,$type)
		{
		$this->parent = $parent;
		$this->type = $type;
		}
		
	function ldController($cName)
		{
		$path = $this->parent->dir."/controllers/$cName/$cName.php";
		$dir = $this->parent->dir."/controllers/$cName";
		if(file_exists($path))
			{
			include $path;
			if(class_exists($cName))
				{
				$c = new $cName();
				$this->plugins[$cName] = $c;
				$c->site = $this->parent->site;
				$c->initClassACD($c);
				$c->C = new SWAF_Plugin_Container($c,'C');
				$c->M = new SWAF_Plugin_Container($c,'M');
				//$c->P = $this->site->P;
				$c->dir = $dir;
				$c->__regEvent();
				if(method_exists($c,'init'))$c->init();
				return $c;
				}else{
				trigger_error("Failed to find class when loading Controller:$cName");
				return false;
				}
			}else{
			trigger_error("Invalid Controller requested! $cName");
			return false;
			}
		}
	function ldPage($cName)
		{
		$path = $this->parent->dir."/pages/$cName/$cName.php";
		$dir = $this->parent->dir."/pages/$cName";
		$local = "/pages/$cName";
		if(file_exists($path))
			{
			include $path;
			if(class_exists($cName))
				{
				$c = new $cName();
				$this->plugins[$cName] = $c;
				$c->C = new SWAF_Plugin_Container($c,'C');
				$c->M = new SWAF_Plugin_Container($c,'M');
				$c->P = new SWAF_Plugin_Container($c,'P');
				$c->site = $this->parent->site;
				$c->initClassACD($c);
				$c->dir = $dir;
				$c->cdir = "/".$this->parent->site->rootPath."/pages/$cName";
				$c->localDir = $local;
				$c->__regEvent();
				if(method_exists($c,'init'))$c->init();
				return $c;
				}else{
				trigger_error("Failed to find class when loading Page:$cName");
				return false;
				}
			}else{
			trigger_error("Invalid Page requested! $path");
			return false;
			}
		}
	function ldModel($cName)
		{
		$path = $this->parent->dir."/models/$cName/$cName.php";
		$dir = $this->parent->dir."/models/$cName";
		if(file_exists($path))
			{
			include $path;
			if(class_exists($cName))
				{
				$c = new $cName();
				$this->plugins[$cName] = $c;
				$c->M = new SWAF_Plugin_Container($c,'M');
				$c->site = $this->parent->site;
				$c->initClassACD($c);
				$c->dir = $dir;
				$c->__regEvent();
				if(method_exists($c,'init'))$c->init();
				return $c;
				}else{
				trigger_error("Failed to find class when loading Model:$cName");
				return false;
				}
			}else{
			trigger_error("Invalid Model requested! $cName =>$path");
			return false;
			}
		}	
	}

class SWAF_C extends SWAF
	{

	}

function SWAF_Call($call,$args = array())
{
return call_user_func_array($call,$args);	
}
function includeFile($file)
{
include($file);	
}
?>