// JavaScript Document
views = {};

views.addTable = function(parentElement){
	var table = {},$ = DO.dom;
	table.table = GUIDO.table({id:'tableRows',border:1,style:'border-collapse:collapse;',parentElement:parentElement});
	table.colCount = {};
	table.rows = {};
	window.test1 = table;
	function __configTd(td,columnName)
		{
		if(table.colConfig.column_name && table.colConfig.column_name[columnName] && table.colConfig.column_name[columnName].type == 'enum')
			{
			return __configTd_ENUM(td,columnName);	
			}else{
			td.setValue = function(value){this.innerHTML = value;};
			td.getValue = function(){return this.innerHTML;};
			}
		}
	
	function __configTd_ENUM(td,columnName)
		{
		var option_array = table.define_lists[columnName];
		if(!table.define_lists[columnName])option_array=[];
		window.test2 = option_array;
		var select = $.select({parentElement:td,
				disabled:true,
				style:'display:none;'
				});
		options = {};
		options['NULL']=$.option({
				parentElement:select,
				value:''},'');
		for(x = 0;x < option_array.length;x++)
			{
			options[option_array[x].name] = $.option({
				parentElement:select,
				value:option_array[x].name},
				option_array[x].name);	
			}
		var label = $.span({parentElement:td});
		td.value = "";
		td.label = label;
		td.select = select;
		td.selected = false;
		td.options = options;
		td.setValue = function(value)
			{
			if(this.selected)this.selected.selected = false;
			if(this.options[value]){
				this.selected = this;
				this.label.innerHTML = value;
				if(value == ''){
					this.options.NULL.selected = true;
					}else{
					this.options[value].selected = true;
				}
				if(this.onchange)td.onchange(this.select.value);
				}
			};
		td.getValue = function()
			{
			return this.select.value;
			};
		td.select.onchange = function()
			{
			if(this.parentElement.onchange)this.parentElement.onchange(this.value);	
			this.parentElement.label.innerHTML = this.value;
			};
		td.oneditable = function()
			{
			this.label.style.display='none';
			this.select.style.display = 'block';
			this.select.disabled = false;
			this.contentEditable = false;
			this.whenFocused = function()
				{
				this.select.focus();	
				};
			this.onselectstart = function(){return false;};
			};
		td.oneditableoff = function()
			{
			this.label.style.display='block';
			this.select.style.display = 'none';
			this.select.disabled = true;
			this.contentEditable = false;
			this.whenFocused = function()
				{
				return false;	
				};
			this.onselectstart = function(){return true;}
			};
		return select;
		}
	
	return {
		updateRow:function(rowID,data){
			if(!table.rows[rowID])return false;
			for(x in data){
				if(!table.rows[rowID][x])continue;
				table.rows[rowID][x].setValue(data[x]);
			}
		},
		setHeader:function(columnTitles){
			window.test = columnTitles;
			var tr = $.tr({parentElement:table.table.thead});
			for(var x = 0;x < columnTitles.length;x++)
				{
				$.td({parentElement:tr},columnTitles[x]);
				}
			},
		configureColumns:function(columns,primary,columnConfig,define_lists,colConfig){
			table.columns = columns;
			table.primaryColumn = primary;
			table.config = columnConfig;
			table.colConfig = colConfig;
			table.define_lists = define_lists;
			},
		appendRow:function(name){
			var Func = false, rowName = name;
			table.rows[name] = {};
			if(arguments[1])Func = arguments[1];
			table.table.appendMatrix(1,table.columns.length,function(row,col)
				{
				table.rows[name][table.columns[col]] = this;
				__configTd(this,table.columns[col]);
				if(Func)Func.call(this,table,table.columns[col],col);
				return true;
				},true);
			},
		insertRow:function(before,name){
			var Func = false, rowName = name;
			table.rows[name] = {};
			if(arguments[1])Func = arguments[1];
			return table.table.insertBefore(before,table.columns.length,function(row,col)
				{
				table.rows[name][table.columns[col]] = this;
				__configTd(this,table.columns[col]);
				if(Func)Func.call(this,table,table.columns[col],col);
				return true;
				},true);
			},
		markRowAsEditable:function(row){
			var rowCol = GUIDO_table_task_getRowAndCol.call(row);
			table.table.select(rowCol[0],0,rowCol[0],table.columns.length).contentEditableOn();
			},
		unmarkRowAsEditable:function(row){
			var rowCol = GUIDO_table_task_getRowAndCol.call(row);
			table.table.select(rowCol[0],0,rowCol[0],table.columns.length).contentEditableOff();
			},
		data:table,
		table:table,
		rows:table.rows
		};
	};

views.addSearchBox = function(parent)
	{
	var search = {},$ = DO.dom;	
	search.container = $.div({cls:'hideMeCon',parentElement:parent},
		$.div({cls:'hideMeButton',onclick:function()
			{
			if(this.parentElement.inputElements.style.display == 'none')
				{
				this.parentElement.inputElements.style.display = "block";	
				}else{
				this.parentElement.inputElements.style.display = 'none';	
				}
			},nid:'toggleSearch'},'Search Options'),
		search.inputs = $.div({nid:'inputElements',style:'display:none;',cls:'hideMeContent'})
		);
	
	search.setColumns = function(columns,searchObject,searchFunc){
		var $ = DO.dom;
		search.columns = columns;
		search.searchElements = {};
		search.searchFunc = searchFunc;
		var thead,tbody;
		$.table({border:0,parentElement:search.inputs},thead = $.thead(),tbody = $.tbody());
		var header = $.tr({parentElement:thead});
		var row = $.tr({parentElement:tbody});
		for(var x =0;x< columns.length;x++)
			{
			var label = columns[x].LABEL;
			if(!label)label = columns[x].COLUMN_NAME;
			$.td({parentElement:header},columns[x].LABEL);	
			$.td({parentElement:row},search.searchElements[columns[x].COLUMN_NAME]=$.input({size:10}));
			if(searchObject[columns[x].COLUMN_NAME] == "")
				search.searchElements[columns[x].COLUMN_NAME].value = "null";
			if(searchObject[columns[x].COLUMN_NAME])
				search.searchElements[columns[x].COLUMN_NAME].value = searchObject[columns[x].COLUMN_NAME];
			}
		$.input({parentElement:search.inputs,type:'submit',value:'Search',onclick:exec_search});
		};
	function exec_search()
		{
		var searchObject = {};
		for(x in search.searchElements)
			{
			if(search.searchElements[x].value == "")continue;
			if(search.searchElements[x].value == 'null'){
				searchObject["~"+x] = '';
				}else{
				searchObject["~"+x] = search.searchElements[x].value;	
				}
			}
		search.searchFunc(searchObject);
		}
	
	return search;	
	};

views.addViewBox = function(parent)
	{
	var viewBox = {},$ = DO.dom;
	viewBox.container = $.div({cls:'hideMeCon',parentElement:parent},
		$.div({cls:'hideMeButton',onclick:function()
			{
			if(this.parentElement.inputElements.style.display == 'none')
				{
				this.parentElement.inputElements.style.display = "block";	
				}else{
				this.parentElement.inputElements.style.display = 'none';	
				}
			},nid:'toggleSearch'},'View Options'),
		viewBox.settings = $.div({nid:'inputElements',style:'display:none;',cls:'hideMeContent'})
		);
	viewBox.setColumns = function(columns,selectedColumns,columnFunc){
		var $ = DO.dom;
		viewBox.columns = columns;
		viewBox.columnElements = {};
		viewBox.columnFunc = columnFunc;
		var thead,tbody;
		$.table({border:0,parentElement:viewBox.settings},thead = $.thead(),tbody = $.tbody());
		var header = $.tr({parentElement:thead});
		var row = $.tr({parentElement:tbody});
		
		for(x in columns.ORDINAL_POSITION)
			{
			if(!columns.ORDINAL_POSITION[x])continue;
			var col = columns.ORDINAL_POSITION[x], label = columns.ORDINAL_POSITION[x].LABEL;
			if(!col.LABEL)label = col.COLUMN_NAME;
			$.td({parentElement:header},col.LABEL);	
			$.td({parentElement:row},viewBox.columnElements[col.COLUMN_NAME]=$.input({size:10,type:'checkbox'}));
			if(selectedColumns == "*")
				viewBox.columnElements[col.COLUMN_NAME].checked = true;
			}
		if(typeof selectedColumns == "object"){
			for(var x = 0;x < selectedColumns.length;x++)
			{
			viewBox.columnElements[selectedColumns[x]].checked = true;
			}}
		$.input({parentElement:viewBox.settings,type:'submit',value:'Refresh',onclick:columnFunc});
		};
	return viewBox;
	};