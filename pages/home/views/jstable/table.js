// JavaScript Document

function jstable()
{
var $ = DO.dom;
var thead = false;
var tbody = false;
var tfoot = false;
var table = $.table(
		thead = $.thead(),
		tbody = $.tbody(),
		tfoot = $.tfoot()
		);
/**
 * addMatrix (int rows,int cols);
 * creates new rows and columns and appends them in the table
 * returns true if successfull - else, false.
 */
function addMatrix(rows,cols)
	{
	for(var x = 0;x < rows;x++)
		{
		var tr = $.tr();
		for(var y = 0;y < cols;y++)
			{
			$.td({parentElement:tr});
			}
		tbody.appendChild(tr);
		}
	return true;
	}


/**
 * addRow(int start,[element row1,element row2,... ]);
 * inserts a new row or given rows into the table.
 * if start = 0 then the it will insert at the beginning of the table.
 * if start = -1 then it will append at the end of table.
 * if start > 0 then it will insert after said number of rows.
 * if start = element tr then it will insert after given element.
 * return true upon success - else, false.
 */
function addRow(start)
	{
	var afterTR = false;
	if(typeof start == "number")
		{
		start++;
		if(tbody.children[start])
			{
			afterTR = tbody.children[start];	
			}else if(start > tbody.children.length){
			afterTR = false;	
			}
		}else if(typeof start == 'object' && start.appendChild){
		afterTR = start;	
		}else{
		return false;
		}
	
	if(arguments.length > 0)
		{
		for(var x = 1;x < arguments.length;x++)
			{
			if(!(typeof arguments[x] == 'object' && arguments[x].appendChild)) continue;
			var td = arguments[x];
			if(afterTR)
				afterTR.parentElement.insertBefore(td,afterTR);
				else
				tbody.insertBefore(td,tbody.children[start]);
			}
		return true;
		}else{
		var td = $.div();
		if(afterTR)
			afterTR.parentElement.insertBefore(td,afterTR);
			else
			tbody.insertBefore(td,tbody.children[start]);			
		return td;
		}
	}
	

/**
 * addCol({int rowint start,[element row1,element row2,... ]);
 * inserts a new column or given columns into the table.
 * if start = 0 then the it will insert at the beginning of the table.
 * if start = -1 then it will append at the end of table.
 * if start > 0 then it will insert after said number of rows.
 * if start = element td then it will insert after given element.
 * return true upon success - else, false.
 */
function addCol(row,start)
	{
	var afterTR = false;
	if(typeof start == "number")
		{
		start++;
		if(tbody.children[start])
			{
			afterTR = tbody.children[start];	
			}else if(start > tbody.children.length){
			afterTR = false;	
			}
		}else if(typeof start == 'object' && start.appendChild){
		afterTR = start;	
		}else{
		return false;
		}
	if(typeof row == 'number' && tbody.children[row])
		row = tbody.children[row];
		else if(!(typeof row == "object" && row.appendChild))
		return false;
	
	var td = $.td();
	if(afterTR)
		row.insertBefore(td);
		else
		row.insertBefore(td,row.children[start]);
	return td;
	}

/**
 * remRow({int row,element row});
 * remove the provided row from the element
 * if row = int then it will remove the row at that location
 * if row = element tr then it will remove that row
 * returns the removed row if successful - else, false.
 */
function remRow(row)
	{}
	
/**
 * remCol({int col,element col});
 * remove the provided rolumn from the element
 * if col = int then it will remove the column at that location
 * if col = element td then it will remove that column
 * returns the removed column if successful - else, false.
 */
function remCol(col)
	{}

/**
 * execMatrix(int startRow,int startCol, int endRow, int endCol, function func);
 * executes the given function in the context of each of the columns in the provided range.
 * returns true upon success - else, false.
 */
function execMatrix(startRow,startCol,endRow,endCol,func)
	{
	for(var x = startRow;x <= endRow;x++)
		{
		var tr = tbody.children[x];
		if(!tr)return this;
		for(var y=startCol; y <= endCol;y++)
			{
			if(tr.children[y])func.call(tr.children[y]);
			}
		}
	return true;		
	}
	
/**
 * sort(array collumn);
 * sorts the table by each of the collumns number proved in the array and in the order given.
 * array = each column number desired to be sorted
 * returns true upon success - else, false.
 */
function sort(columns)
	{}
table.addMatrix= addMatrix;
table.addRow= addRow;
table.addCol = addCol;
table.execMatrix = execMatrix;
table.table = table;
table.tbody = tbody;
table.thead = thead;
table.tfoot = tfoot;

return table;
}