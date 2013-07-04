<?php
class SWAF_M extends SWAF
    {
    public $strict = false;
    public $queryType = false;
    public $dbs = false;
    public $error = "";    
    public $orderBy = "";

    public function query()
        {
        
        if(!$this->dbs && !$this->__initDatabase())
            {
            trigger_error("SWAF_Model: Failed due to database connection error!\n");
            return false;
            }
        
        $limit = "";
        $args = func_get_args();
        if(isset($args[0]) && is_int($args[0]) &&
            isset($args[1]) && is_int($args[1]))
            {
            $limit="LIMIT ".$args[0].",".$args[1]."";
            }
        
        if(isset($this->table) && $this->table)
        	$table = $this->table;
        	else
        	$table = get_class($this);
        
        $sql = $this->queryType." ";
        if($this->queryType == "SELECT")
            {
            if(isset($this->where) && $this->where)
            	$where = "WHERE ".$this->where;
            	else
            	$where = "";
            
            $sql.= $this->selectCollums." FROM $table $where";
            if($this->orderBy != "")$sql.= " ".$this->orderBy;
            $sql.=" $limit";
 
            $res = $this->__dbFetchAll($sql,"\nSWAF_Model:".get_class($this)."SQL String =>$sql\n");
            //if(property_exists($this,checkSelectResults))$res = $this->chkSelect($res);
            }elseif($this->queryType == "INSERT"){
            $sql.= " INTO $table SET ".$this->set;
            $res = $this->__dbQuery($sql,"\n\nSWAF_Model:".get_class($this)."SQL String =>$sql\n");
            if($res) return mysql_insert_id();
            }elseif($this->queryType == "UPDATE"){
            $sql.= "$table SET ".$this->set. " WHERE ".$this->where;
            $res = $this->__dbQuery($sql,"\nSWAF_Model:".get_class($this)."SQL String =>$sql\n");
            }elseif($this->queryType == "DELETE"){
            $sql.= " FROM $table WHERE ".$this->where;
            $res = $this->__dbQuery($sql,"\nSWAF_Model:".get_class($this)."SQL String =>$sql\n");
	     }else{
	     return false;
	     }
        trigger_error($sql);
        $this->where = "";
 		
        $this->sql = $sql;
        $this->orderBy = "";
		return $res;
        }
    
    public function select($collums)
        {
        $this->where = "";
        $this->queryType = "SELECT";
        $this->selectCollums = $collums;
        if(is_array($collums))
        	{
        	$this->selectCollums = implode(',',$collums);
        	}
        return $this;
        }
    
    public function insert()
        {
        $this->queryType = "INSERT";
        return $this;
        }

    public function update()
        {
        $this->queryType = "UPDATE";
        return $this;
        }        
    public function delete()
        {
        $this->queryType = "DELETE";
        return $this;
        }     
    public function set($obj)
        {
        if(!is_object($obj) && !is_array($obj))
            {
            trigger_error("SAWF_Model:".get_class($this)."=>set. Expected arguemnt shuld be object");
            return $false;
            }
        $this->set = $this->createSetFromObject($obj);
        return $this;
        }
    
    public function where($obj)
        {
        if(is_string($obj))
        	{
        	$this->where = $obj;
        	return $this;
        	}
        if(!is_array($obj) && !is_object($obj))
            {
            trigger_error("SAWF_Model:".get_class($this)."=>set. Expected arguemnt shuld be object");
            return false;
            }
        $this->where = $this->createWhereFromObject($obj);
        
        return $this;
        }
    
    public function order_by($column,$oper)
    	{
    	$this->orderBy = "ORDER BY $column $oper";	
    	return $this;
    	}
    
    public function createSetFromObject($obj)
        {
        $sqlSet = "";
        foreach($obj as $var=>$val)
            {
            if($var && ($var[0] == "-" ||
            			$var[0] == "~" || 
            			$var[0] == "!" || 
            			$var[0] == "<" || 
            			$var[0] == ">" ||
            			$var[0] == "="
            			)){
            	$tvar = substr($var,((strlen($var)-1)*-1));				
            }else{
            	$tvar = $var;	
            }
            if(!method_exists($this,$tvar) && (property_exists($this,"route") && $this->route == false) )
                continue;
            
            if(property_exists($this,"route"))
            	{
            	$rt = $this->route;
            	}else{
            	$rt = $var;	
            	}
            if(is_int($rt)){continue;}
            if($this->$rt($val))
                {
                if($sqlSet != "")$sqlSet.=", ";
                if(strpos($val,'"')>0)
                	{
                	$val = str_replace('"',"'",$val);	
                	}
                $sqlSet.="$var=\"$val\"";
                }else{
                trigger_error("SWAF_Model: Invalid $var given!\n".$this->error);
                $this->error = "";
                }
            }
  
        return $sqlSet;    
        }
    
    public function createWhereFromObject($obj)
        {
        if(is_string($obj))return $obj;
        $where = "";
        $args = func_get_args();
        if(isset($args[1]))
        	{
        	$parentVar = $args[1];
            $sep = "OR";
            }else{
            $parentVar = false;
            $sep = "AND";
            }
            
        foreach($obj as $var=>$val)
            {
            if((is_array($val) || is_object($val)) && sizeof($val)==0)continue;
			if(is_string($val))
				{
				if($parentVar)
					$tvar= $parentVar;
					else
					$tvar = $var;
            	if(!method_exists($this,$tvar) && (property_exists($this,"route") && $this->route == false) )continue;
            
            	if(property_exists($this,"route"))
            		{
            		$rt = $this->route;
            		}else{
            		$rt = $tvar;	
            		}
				if(!method_exists($this,$rt) || $this->$rt($val) == false)
					{
					continue;
					}  

				}

            if(is_array($val) || is_object($val))
                {
					
                if($var[0] == "-" && is_array($val))
                	{
                	$var = substr($var,((strlen($var)-1)*-1));
                	
                	if(!method_exists($this,$var) || !$this->$var($val[0]) || !$this->$var($val[1]))
                		continue;
                	if($where != "")$where.=" $sep ";
                	$val = "'{$val[0]}' AND '{$val[1]}'";
                	$where.="$var BETWEEN $val";
                	}else if(is_string($var))
                	{
                	if($where != "")$where.=" $sep ";
                	$where.="(".$this->createWhereFromObject($val,$var).")";
                	}else{
                	if($where != "")$where.=" $sep ";
                	$where.="(".$this->createWhereFromObject($val).")";
                	}
                }else{
                if($var[0] == "~")
                	{
                		$comp = "LIKE";
                		$var = substr($var,((strlen($var)-1)*-1));
                	}elseif($var[0] == "="){
                		$comp = "=";
                		$var = substr($var,((strlen($var)-1)*-1));
                	}elseif($var[0] == "!"){
                		$comp = "<>";
                		$var = substr($var,((strlen($var)-1)*-1));
                	}elseif($var[0] == "<"){
                		$comp = "<";
                		$var = substr($var,((strlen($var)-1)*-1));
                	}elseif($var[0] == ">"){
                		$comp = ">";
                		$var = substr($var,((strlen($var)-1)*-1));
                	}else{		
                		$comp = "=";
                	}
                if($parentVar)$var = $parentVar;
                if($where != "")$where.=" $sep ";
                $where.="$var $comp '$val'";
                }
            }
        return $where;    
        }
    public function __dbConnect()
        {
        if(isset($this->site->user['db']))
        	{
        	$host = $this->site->user['db'][0];
        	$user = $this->site->user['db'][1];
        	$pass = $this->site->user['db'][2];
        	}else{
        	trigger_error("Invalid db access due to non-existent credentials!");
        	return false;
        	}
        $this->dbs = mysql_connect($host, $user, $pass);
        if(!$this->dbs)
            trigger_error(mysql_error());
            
        return $this->dbs;
        }
    
    public function __initDatabase()
        {
        if($this->__dbConnect())
            return $this->__dbSelectDB($this->dbName);
            else
            return false;
        return true;    
        }
    
    public function __dbSelectDB($database)
        {
        if(!mysql_select_db($database,$this->dbs))
	        {
	        trigger_error("Error: ".mysql_error()."\n");
	        return false;
	        }
        return true;
        }
    
    public function __dbQuery($sql,$error)
        {
        
        if(!$this->dbs && !$this->__initDatabase())
            {
            trigger_error("SWAF_Model: Failed due to database connection error!\n");
            return false;
            }
        $Request = mysql_query($sql);
	    if(!$Request)
		    {
		    trigger_error(mysql_error()."::$error\n");
		    return false;
		    }
        return $Request;
        }
    
    public function __dbFetch($sql,$error)
        {
        if(!$this->dbs && !$this->__initDatabase())
            {
            trigger_error("SWAF_Model: Failed due to database connection error!\n");
            return false;
            }
        $Request = mysql_query($sql);
	    if(!$Request)
    		{
    		trigger_error(mysql_error()."::$error\n");
		    return false;
		    }
        return mysql_fetch_array($Request);      
        }
    public function __dbFetchAll($sql,$error)
        {
        if(!$this->dbs && !$this->__initDatabase())
            {
            trigger_error("SWAF_Model: Failed due to database connection error!\n");
            return false;
            }
        $Request = mysql_query($sql);
	    if(!$Request)
		    {
            trigger_error(mysql_error()."::$error\n");
		    return false;
		    }
        $ret = "";
        while($item =  mysql_fetch_array($Request))
             {
              $ret[] = $item;
             }
        return $ret;      
        }
    
    public function mysql_insert_id()
    	{
    	return mysql_insert_id($this->dbs);
    	}
    
    public function is_date($dateStr,$str)
        {
       // $dateStr = "yyyy-mm-dd";
        $year = "";
        $month = "";
        $day = "";
        $result = true;
        for($x = 0;$x < strlen($dateStr)-1;$x++)
            {
            $cChar = $dateStr[$x];
            $dChar = $str[$x];
            if($cChar == "y")
                {
                if(!is_int($dChar))
                    {
                    $result = false;
                    break;
                    }
                $year.=$dChar;
                }elseif($cChar == "m")
                {
                if(!is_int($dChar))
                    {
                    $result = false;
                    break;
                    }
                $month.=$dChar;
                }elseif($cChar == "d")
                {
                if(!is_int($dChar))
                    {
                    $result = false;
                    break;
                    }
                $day.=$dChar;
                }elseif($cChar == ":" || $cChar == "."||$cChar == "-" ||
                    $cChar == "/" || $cChar == "\\")
                {
                if(!$dChar != $cChar)
                    {
                    $result = false;
                    break;
                    }
                }else{
                $result = false;
                break;
                }
            }
        if($month>12)
            $result = false;
        if($day > 31)
            $result = false;    
        
        if($result == true)
            {
            return true;
            }else{
            $this->error = "Date did not match $dateStr;";
            return false;
            }
        
        }
        
    public function objectToWhere($object)
    	{
    		
    		
    	}
    }
?>