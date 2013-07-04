<style>

</style>
<dvi>
	<div id="controls" class="noPrint">	
		<div id="table_list_con">
			<b>Table:</b>
			<select style="" id="table_list" onchange="window.currentTable = new displayTable(this.value);">
				<option value="">__None__</option>
				<?php startdef("table_list");enddef();?>
			</select>
		</div>
		<div id="searchSettings">
		
		</div>
		<div id="viewSettings">
		
		</div>
	</div>
	<div id="tablecontainer">
	</div>
</div>