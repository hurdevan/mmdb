<?php
$HTMLFile = "";
$HTMLCurrentDefine = false;

class html extends SWAF_C
    {
    public $HTML = "";
    public function onpage()
        {
        //$this->HTML = $this->ldHTML("htmlDefineHTML.php");
        //$this->site->HTML->head->push($this->HTML);
        //$this->site->HTML->head->insertJSFile("{$this->localPath}htmlDefine.js"); 
        }
   
    
    public function ldHTML($path)
        {
        global $htmldefs;
        global $htmlCD;
   
            
        if(!is_file($path)) 
            {
            trigger_error("htmlDefine: Invalid HTML File Given! => $path.$file",E_USER_NOTICE);
            return false;     
            }   
        
        $htmldefs = new htmldef();// Clear object and start a new
        $htmldefs->__parent = $htmldefs;
        $htmldefs->site = $this->site;
        //$htmldefs->creator = $this->caller;
        $htmlCD = $htmldefs;
        
        $ret = $htmldefs;
        
        ob_start();
        include($path);
        enddef();
        
        $html = ob_get_contents();// Grabs any HTML;

        if($html && $html != "")
            {
            $ret->push($html);
            ob_end_clean();//Clear HTML;
            }
        
        //if($this->caller)$this->caller->HTML = $ret;
        return $ret;
        }
    
    /*                                                                          // Version 1
    public function loadHTMLDef($file)
        {
        global $HTMLFile;
        if($this->caller)
            {
            $path = $this->caller->localPath;
            }else{
            $this->caller = $this;
            $path = $this->localPath."/";
            }
            
            
        if(!is_file($path.$file)) return false;
        $HTMLFile = "";
        ob_start();
        include($path.$file);
        endDefine();
        if($this->caller)$this->caller->HTML = (object) $HTMLFile;
        
        return (object) $HTMLFile;
        }
    
    public function renderHTML($html)
        {
        //if(!isset($this->caller->HTML))return false;
        return $this->__renderLoop($html);
        }
        
    public function __renderLoop($htmlArray)
        {
        $html = "";
        foreach($htmlArray as $chunk)
            {
            if(is_string($chunk))$html.=$chunk;
                elseif(is_object($chunk))$html.=$this->__renderLoop($chunk);
                elseif(is_array($chunk))$html.=$this->__renderLoop($chunk);
            }
        return $html;
        } */   
    }
    
  /*  


function defineHTML($value)
{
global $HTMLCurrentDefine;
global $HTMLFile;
$ct = ob_get_contents();
if($ct)
    { $HTMLFile[] = $ct;
    ob_end_clean();
    }
ob_start();
$HTMLCurrentDefine = $value;
}

function endDefine()
{
global $HTMLCurrentDefine;
global $HTMLFile;

if($HTMLCurrentDefine)
    {
    $HTMLFile[$HTMLCurrentDefine] = "";
    $HTMLFile[$HTMLCurrentDefine] = ob_get_contents();
    }else{
    $ct = ob_get_contents();
        if($ct)
            { 
            $HTMLFile[] = $ct;
            ob_end_clean();
             }
    }
$HTMLCurrentDefine = false;
ob_end_clean();
ob_start();
}*/
                                                                                // Version 2
$htmldefs = new htmldef();
$htmldefs->__parent = $htmldefs;
$htmlCD = $htmldefs;

function startdef()
{
global $htmldefs;
global $htmlCD;
$args = func_get_args();

//if(!isset($htmlCD->__childCount))$htmlCD->__childCount = -1;

$html = ob_get_contents();// Grabs any HTML;

if($html && $html != "")
    {
    $htmlCD->push($html);
    ob_end_clean();//Clear HTML;
    }

$child = new htmldef();
$child->__parent = $htmlCD;
$child->site = $htmlCD->site;
$child->creator = $htmlCD->creator;
if(!isset($args))   // Determine current the new HTML/Child segment id
    {
    $htmlCD->push($child);
    $child->name=sizeof($htmlCD->defs);
    }else{
    $id = $args[0];
    $htmlCD->$id = $child;
    $child->name=$id;
    }

ob_start();
$htmlCD = $htmlCD->$id;// Set The new Definition as the current one.
}

function enddef()
{
global $htmldefs;
global $htmlCD;

$html = ob_get_contents();// Grabs any HTML;

if($html && $html != "")
    {
    $htmlCD->push($html);
    ob_end_clean();//Clear HTML;
    ob_start();
    }
$htmlCD = $htmlCD->__parent;    
}


class htmldef
    {
    public $defs = "";
    public $__parent = false;
    public $serial = false;
    public $site = false;
    public $creator = false;
    public $name = "";
    
    public function copy()
        {
        if(!$this->serial)$this->serial = serialize($this);
        $obj = unserialize($this->serial);
        $obj->serial = false;
        return $obj;
        }
    
    public function __get($var)
        {
        if(isset($this->defs[$var]))
            return $this->defs[$var];
            else
            return false;
        }
    public function __set($var,$val)
        {
        return $this->defs[$var] = $val;  
        }
    public function push($val)
        {
        return $this->defs[] = $val;
        }
    
    public function insertCSSFile($file)
        {
        $link = "<link rel=\"stylesheet\" type=\"text/css\" href=\"$file\"></link>";
        return $this->push($link);    
        }
    public function insertJSFile($file)
        {
        $script = "<script language=\"javascript\" type=\"text/javascript\" src=\"$file\"></script>";
        return $this->push($script);     
        }  
        
    public function render()
        {
        $args = func_get_args();
        
        $html = "";
        $defs = $this->defs;
        if(!isset($this->defs) && isset($args[0]))
            {
            $defs = $args[0];
            }
        if($defs == "")return "";
        
        
        foreach($defs as $segment)
            {
            if(is_object($segment) && method_exists($segment,'render'))
                $html.=$segment->render();
                else
                $html.=$segment;
            }
        return $html;
        }
    public function passHTML($name)
        {
        $html = $this->render();
 
        if(isset($args[0]))
        	$id = $args[0];
        	else
        	$id = $this->name;
        $this->site->HTML->script->push("HTMLDef['".$name."_".$id."']=".json_encode($html).";");
        }    
    }


?>
