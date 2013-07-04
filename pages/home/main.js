// JavaScript Document



function main()
{

}

function displayTable(name)
{
var whereObject = {},getColumns="*";
var tbl = new views.addTable('tablecontainer');
tbl.tableName = name;
if(arguments[1])whereObject = arguments[1];
if(arguments[2])getColumns = arguments[2];
if(window.currentTable)
	{
	currentTable.table.table.parentElement.removeChild(currentTable.table.table);
	currentTable.search.container.parentElement.removeChild(currentTable.search.container);
	currentTable.viewOptions.container.parentElement.removeChild(currentTable.viewOptions.container);
	}
tbl.getColumns = getColumns;
function getActiveColumns(rows){
	var activeColumns = [];
	for(x in rows[0])
		{
		if(x < 0 || x >= 0) continue;
		activeColumns.push(x);
		}
	tbl.activeColumns = activeColumns;
	return activeColumns;
	}

function ldTableRows(ret)
	{
	var columns = new DO.arrayQuery(ret.columns),$ = DO.dom;
	var colConfig = new DO.arrayQuery(ret.columnConfig);
	tbl.columnArray = columns;
	if(columns.EXTRA.auto_increment)
		var primary = columns.EXTRA.auto_increment.COLUMN_NAME;
		else
		var primary = false;
	tbl.data = ret;
	var activeColumns = getActiveColumns(ret.rows);
	tbl.configureColumns(activeColumns,primary,columns,tbl.data.define_lists,colConfig);
	tbl.columnPrimary = primary;
	for(var x = 0;x < ret.rows.length;x++)
		{
		tbl.appendRow(ret.rows[x][primary], function(table,column,num){
			var c = activeColumns[num];
			this.ondblclick = editRow;
			this.setValue(ret.rows[x][c]);
			this.row = ret.rows[x];
			});
		}
	
	setTableHeader();
	insertAddRow();
	tbl.search = views.addSearchBox('searchSettings');
	tbl.viewOptions = views.addViewBox('viewSettings');
	tbl.viewOptions.setColumns(tbl.table.config,getColumns,exec_reloadWithSelectedColumns);
	tbl.search.setColumns(tbl.data.columns,whereObject,doSearch);
	
	$.div({parentElement:tbl.viewOptions.settings},
		$.span($.b("Table Width:"),$.input({size:6,onchange:function()
			{
			tbl.table.table.style.width=this.value;	
			}}))
		);
	
	}
function doSearch(search)
	{
	window.test1 = search;
	window.currentTable = new displayTable(tbl.tableName,search,getColumns);
	}
function setTableHeader()
	{
	tbl.titles = [];
	for(var x = 0;x < tbl.activeColumns.length;x++)	
		{
		if(tbl.columnArray.COLUMN_NAME[tbl.activeColumns[x]].LABEL)
			tbl.titles.push(tbl.columnArray.COLUMN_NAME[tbl.activeColumns[x]].LABEL);
			else
			tbl.titles.push(tbl.activeColumns[x]);
		}
	tbl.setHeader(tbl.titles);
	}

function insertAddRow()
	{
	var rows = tbl.data.rows;
	tbl.appendRow("insertRow", function(table,column,num){
			
			this.onkeypress = function(eve)
				{
				console.log('Inserting New Data');
				if(eve.keyCode == '13')exec_insertData();
				};
			this.parentElement.className = 'noPrint';
			});
	tbl.markRowAsEditable(tbl.rows.insertRow[tbl.columnPrimary]);
	//tbl.rows.insertRow[tbl.columnPrimary].parentElement.onkeyup = exec_insertData;
	}

function exec_insertData()
	{
	var setObject = {};
	for(x in tbl.rows.insertRow)
		{
		setObject[x] = tbl.rows.insertRow[x].getValue(); 	
		}
	F('C/tables/insertIntoTable',insertData_cb,tbl.tableName,setObject);
	}
function insertData_cb(ret)
	{
	var whereObject = {};
	whereObject[tbl.columnPrimary] = ret;
	if(!ret)return alert(ret);
	F('C/tables/searchTable',function(ret)
		{
		tbl.data.rows.push(ret[0]);

		tbl.insertRow(tbl.rows.insertRow[tbl.columnPrimary].parentElement,
			function(table,column,num){
				this.ondblclick = editRow;
				this.setValue(ret[0][num]);
				this.row = ret[0];
				});
		
		},tbl.tableName,whereObject,tbl.activeColumns.join(','));
	}
function editRow()
	{
	var $ = DO.dom,tr = this.parentElement;
	if(this.parentElement.editMode == true)
		{
		tbl.unmarkRowAsEditable(this);
		this.onkeyup = function(){return true;};
		this.parentElement.editMode = false;
		this.onblur =function(){return true;};
		if(tr.saveButton)tr.removeChild(tr.saveButton.parentElement);tr.saveButton = false;
		}else{
		tbl.markRowAsEditable(this);
		var status = $.td({parentElement:tr},
			tr.saveButton = $.input({type:'submit',value:'Save',onclick:DO.Func(function(context){exec_saveData.call(context)},this)})
			);
		if(!this.parentElement.eventActive){
			this.parentElement.addEventListener('keydown',function(eve)
				{
				console.log(eve.keyCode);
				if(eve.keyCode == 13){
					console.log('Saving Changes!',this);
					exec_saveData.call(this);
					this.editMode = false;
					tbl.unmarkRowAsEditable(this.children[0]);
					
				}
				//this.onkeyup = function(){return false;};
			});
			this.parentElement.eventActive = true;
		}
		//this.onblur = exec_saveData;
		this.parentElement.editMode = true;
		}
	}
function exec_saveData()
	{
	if(!this.parentElement.editMode)return false;
	var whereObject = {},setObject = {},tr = this.parentElement,$=DO.dom;
	if(tr.saveButton.parentElement)
		tr.removeChild(tr.saveButton.parentElement);tr.saveButton = false;
	whereObject[tbl.columnPrimary] = this.row[tbl.columnPrimary];
	var tr = this.parentElement;
	for(var x = 0;x < tr.children.length;x++)
		{
		var column = tbl.activeColumns[x];
		setObject[column] = tr.children[x].getValue();
		}
	var status = $.td({parentElement:tr},$.b('Updating!!'));
	
	tbl.unmarkRowAsEditable(this);
	F('C/tables/updateTable',curry(function(where,primary,set,ret)
		{
		status.parentElement.removeChild(status);
		if(!ret)return  alert(ret);
		tbl.updateRow(where[primary],set);
		},whereObject,tbl.columnPrimary,setObject),tbl.tableName,setObject,whereObject);
	}

function exec_reloadWithSelectedColumns()
	{
	var columnObject = [];
	for(column in tbl.viewOptions.columnElements)
		{
		if(tbl.viewOptions.columnElements[column].checked)columnObject.push(column);
		}
	return loadDBTable(tbl.tableName,tbl.whereObject,columnObject);
	}

F('P/home/getTableData',ldTableRows,getColumns,name,tbl.row_start,tbl.row_end,whereObject);
return tbl;
}


function curry(){	
	return (function(args){
			var a = [],f=args[0];
			for(var x = 1;x < args.length;x++){a.push(args[x]);}
			return function(){
				if(arguments[0])a.push(arguments[0]);
				return f.apply(this,a);
			};
		})(arguments);
	}

/*
function loadTable(name)
{
if(name == "")return false;
if(arguments[1])
	var whereObject = arguments[1];
	else
	var whereObject = {};
document.getElementById('table_con').innerHTML = "";
var $ = DO.dom;
var tbl = {};
window.testing = tbl;
this.tbl = tbl;
tbl.name= name;
tbl.banner = document.getElementById('loading_banner');
tbl.banner.style.display='block';
tbl.banner.innerHTML = "Loading Table...";
tbl.table = false;
tbl.row_start= 0;
tbl.row_end = 10;
tbl.table_rows = [];
function ldTableRows(ret)
	{
	if(!ret)return banner.innerHTML = "Returned Error!:"+ret;
	
	//if(ret.length == 0)return banner.innerHTML = "This Table has no Data!";
	tbl.table = GUIDO.table({id:'tableRows',border:1,style:'border-color:#C9C9C9;',parentElement:'table_con'});
	tbl.table_rows = ret.rows;
	tbl.columns = [];
	tbl.table.thead.appendChild(tbl.thead_tr=DO.dom.tr());
	tbl.data = ret;
	tbl.columns = new DO.arrayQuery(ret.columns);
	tbl.activeColumns = [];
	if(ret.config[0])tbl.config = eval("("+ret.config[0].CONF_STRING+")");
	if(tbl.columns.EXTRA.auto_increment)
		tbl.primary = tbl.columns.EXTRA.auto_increment.COLUMN_NAME;
		else
		tbl.primary = false;
		
	
	var rows = ret.rows;
	for(x in rows[0])
		{
		if(x < 0 || x >= 0) continue;
		if(tbl.columns.COLUMN_NAME[x] && tbl.columns.COLUMN_NAME[x].LABEL != "")
			DO.dom.td({parentElement:tbl.thead_tr},DO.dom.div(tbl.columns.COLUMN_NAME[x].LABEL));
			else 
			DO.dom.td({parentElement:tbl.thead_tr},DO.dom.div(x));
		tbl.activeColumns.push(x);
		}

	tbl.table.appendMatrix(rows.length,tbl.activeColumns.length, lpRows);
	tbl.banner.style.display='none';
	//addInsertColumn();
	AddInsertRow();
	}

function addInsertColumn()
	{
	var td = false;
	tbl.insertRow = $.tr();
	tbl.insertInputs = {};
	for(var x = 0;x < tbl.activeColumns.length;x++)
		{
		td = $.td({style:'position:relative;',parentElement:tbl.insertRow});
		if(tbl.primary != tbl.activeColumns[x])
			tbl.insertInputs[tbl.activeColumns[x]] = $.input({parentElement:td,collumn:tbl.activeColumns[x]});
		}//auto_increment
	if(td)
		{
		$.div({parentElement:td,style:'position:absolute;right:-45px;top:0px;'},
			$.input({type:'submit',value:'Add',onclick:insertNewRow})
			);
		}
	tbl.table.thead.appendChild(tbl.insertRow);
	}
function lpRows(row,col)
	{
	var column = tbl.activeColumns[col];
	if(tbl.config && tbl.config[column] && tbl.config[column].link)
		{
		
		this.appendChild($.a(tbl.table_rows[row][col],
			{style:'color:blue',value:tbl.table_rows[row][col],table:tbl.config[column].link.table,column:tbl.config[column].link.column,onclick:followLink}
			));
		}else if(tbl.columns.COLUMN_NAME[column].DATA_TYPE == 'enum'){
		var list = tbl.columns.COLUMN_NAME[column].COLUMN_TYPE.slice(5,tbl.columns.COLUMN_NAME[column].COLUMN_TYPE.length-1);
		//var option_array = tbl.data.define_lists[list];
		var select = $.select();
		for(x = 0;x < option_array.length;x++)
			{
			var option = $.option({
				parentElement:select,
				value:option_array[x].name},
				option_array[x].name);	
			if(option_array[x].name == tbl.table_rows[row][col])option.selected = true;
			}
		this.appendChild(select);
		}else{	
		this.innerHTML = tbl.table_rows[row][col];	
		}
	
	}

function insertNewRow()
	{
	var setObject = {};
	for(var x = 0;x < tbl.insertRow.children.length;x++)
		{
		setObject[tbl.activeColumns[x]] = tbl.insertRow.children[x].innerHTML;
		}
	F('C/tables/insertIntoTable',insertNewRow_cb,tbl.name,setObject);
	}

function insertNewRow_cb(ret)
	{
	if(!ret)return alert(ret);
	var primary = tbl.primary;
	var getObject = {};
	getObject[primary] = ret;
	F('C/tables/searchTable',function(ret)
		{
		var tr = GUIDO_table_insertRowBefore(tbl.insertRow,tbl.activeColumns.length);
		for(x = 0;x < tr.children.length;x++)
			{
			tr.children[x].innerHTML = ret[0][x];
			tbl.insertRow.children[x].innerHTML = "";
			}
		},tbl.name,getObject,tbl.activeColumns.join(','));
	return true;
	if(tbl.primary)tbl.insertSetObject[tbl.primary] = ret;

	var tr = $.tr();
	for(x in tbl.activeColumns)
		{
		var column = tbl.activeColumns[x];
		$.td({parentElement:tr},tbl.insertSetObject[column]);	
		}
	if(tbl.table.tbody.firstChild)
		tbl.table.tbody.insertBefore(tr,tbl.table.tbody.firstChild);
		else
		tbl.table.tbody.appendChild(tr);
	}
function followLink()
	{
	var column = this.column;
	var value = this.value;
	var where = {};
	where[column] = value;
	window.currentTable = loadTable(this.table,where);
	}

function AddInsertRow()
	{
	var td = false;
	tbl.insertRow = $.tr();
	tbl.insertInputs = {};
	tbl.insertSetObject = {}
	for(var x = 0;x < tbl.activeColumns.length;x++)
		{
		td = $.td({parentElement:tbl.insertRow,style:'height:15px;'});
		//if(tbl.primary != tbl.activeColumns[x])
		//	tbl.insertInputs[tbl.activeColumns[x]] = $.input({parentElement:td,collumn:tbl.activeColumns[x]});
		}//auto_increment
	tbl.table.appendMatrix(1,tbl.activeColumns.length,function(row,col)
		{
		this.onkeypress = function()
			{
			tbl.insertSetObject[tbl.activeColumns[this.col]] = this.innerHTML;
			};
		this.row = row;
		this.col = col;
		this.onkeyup = function(eve)
			{
			tbl.insertRow = this.parentElement;
			if(eve.keyCode == 13)
				{
				insertNewRow();	
				}
			};
		
		},true);
	tbl.table.select(tbl.table.tbody.children.length-1,1,tbl.table.tbody.children.length-1,tbl.activeColumns.length).contentEditableOn();
	}
F('P/home/getTableData',ldTableRows,"*",name,tbl.row_start,tbl.row_end,whereObject);
return this;
}

function loadDBTable()
{
var argsArray = [];
for(x in arguments){argsArray.push(arguments[x]);}
window.currentTable = displayTable.apply(this,argsArray);	
}*/