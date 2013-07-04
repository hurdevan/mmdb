// JavaScript Document
function F(path,callback)
	{
	var url = "D/C/fsync/call?c="+path;
	var args = [];
	var data = "";
	for(var x = 2;x < arguments.length;x++)
		{
		//url+="&";
		//var jarg = JSON.stringify([arguments[x]]);
		//url+="p"+x+"="+encodeURI(jarg);
		args.push(arguments[x]);
		}
	data+="a="+encodeURIComponent(JSON.stringify(args))+"&";
	fsyncURLS.push(url);	
	return AjaxPostQuery(url,data,callback);
	}
	
var fsyncVeryRaw = [];
var fsyncRawResp = [];
var fsyncURLS = [];
var fsyncReturns = [];
function AjaxPostQuery(url,data,Func)
{

if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  Query=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  Query=new ActiveXObject("Microsoft.XMLHTTP");
  }
//var Query = new XMLHttpRequest();
fsyncURLS.push(url);
Query.open("POST",url,true);
if(arguments[2] && arguments[2].toString() == "[object FormData]")
    {
    Query.send(arguments[2]);
    }else{
    Query.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    //Query.setRequestHeader("Content-type","text/xml;charset=utf-8");
    Query.send(data);

    }
    
Query.onreadystatechange=(function(Query,Func)
  {
  return function()
	{
	if(Query.readyState==4 && Query.status==200)
		{
		fsyncVeryRaw.push(Query.responseText);
		var json = Query.responseText.split("Fsync_Data");
		if(json[1])json = json[1];
		fsyncRawResp.push(json);
		var ret = eval("("+json+")");
		fsyncReturns.push(ret);
		Func(ret[0]);
		}
	};
  })(Query,Func); 
return this;
}
fsyncRawResp = [];