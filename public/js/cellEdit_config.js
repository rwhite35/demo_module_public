/**
 * @constructor
 * Use this config script to define DataTables properties and attributes.
 * Gothcha: Checkbox was a major pian. The form element was difficult to 
 * * render and wasn't accissible through JQuery.  Requires JavaScript build ins.
 */
/* 
 * Assign EditorController properties to this container object. 
 */
const editorvars = {
		notificationsJson: "",		// (string) complete json file
		user_id: "0",				// (string) this users id
		colCount: 6, 				// (Int) number of DataTable columns in columns array
};
/*
 * usersRule instantiates and object with this users rules
 * return {object} c
 */
function usersRule( json, rid ) {
	const c = {"Notifications": [{}]};
	if ( undefined !== json.Notifications && json.Notifications.length ) {
		for (var j=0; j<json.Notifications.length; j++ ) {
			if ( json.Notifications[j].hasOwnProperty(rid) ) {
				c.Notifications[i] = json.Notifications[j];
				i++; // note increment i.
			}
		}
	}
	return c;
}


let i=0;				// NOTE: DT row indexes starts at 0, column count 1
let c=0;
var table;				// the table object instantiated

/*
 * create the datatable object and fill it with content
 */
 $(document).ready( function() {
	var ruleId = editorvars.user_id;		// each users JSON nodes
	var json = editorvars.notificationsJson;
	var cObj = usersRule( json, ruleId );
	table = $('#editRulesTable').DataTable({
		processing: true,
		fixedHeader: false,
		data: cObj.Notifications,
		
		// cellEdit only understand {index{row,column}}.  
		// Indexes/targets count from 0; but column count from 1 (ie 6 cols)
		columns: [
			{ "data": ruleId + ".client_id", "className":"list-input-class" },
			{ "data": ruleId + ".plus_days", "className":"list-input-class" },
			{ "data": ruleId + ".check_status", "className":"list-input-class" },
			{ "data": ruleId + ".timestamp", "className":"list-input-class" },
			{ "data": ruleId + ".active", "type": "text", "className":"list-input-class" },
			{ "data": null, "className": "select-checkbox" }	// checkbox
		],
		columnDefs: [
			{
				'targets': 0,
				'render': function(data, type, row) {
					return row[ruleId]['client_name'] + " (" + data + ")"
				}
			},
			{	// Check Status Select Menu
				'targets': 2,
				'orderable': false,
				'className': 'select-menu',
				'render' : function(data, type, row, meta) {
					var opts = [
						data,		// either 1,2,3 or 4
						'Hourly',
						'Daily',
						'Weekly',
						'Monthly'
					];
					var opts_slice = opts.slice(1);
					var act = "<select id='check_status', name='check_status'>";
					for( var i = 0; i<opts_slice.length; i++ ) {
						var interval = (i+1);
						var selected = ( data == interval ) ? "selected" : "";
						act = act + "<option value='" + interval + "' " + selected + ">" + 
								opts_slice[i] + "</option>";
					}
						act = act + "</select>";
					return act;
					}
			},
			{	// Timestamp convert to human readable date format
				'targets': 3,
				'render': function(data, type, row) {
					var date = new Date(data*1000);
					var formatted = date.getFullYear() + "-" + date.getMonth() + "-" + 
							date.getDay() + " " + date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds(); 
					return formatted;
				}
			},
			{	// Rule Action Select Menu
				'targets': 4,
				'orderable': false,
				'className': 'select-menu',
				'render' : function(data, type, row, meta) {
					var opts = [
						data,		// make stored the default value
						(data == "enabled" ) ? "disabled" : "enabled",
					];	
					var act = "<select id='active', name='active'>";
					for( var i = 0; i<opts.length; i++ ) {
						act = act + "<option value='" + opts[i] + "'>" + opts[i] + "</option>";
					}
					act = act + "</select>";
					return act;
				}
			},
			{	// Quick Check Checkbox Item
				'targets': 5,
				'orderable': false,
				// 'className': 'select-checkbox',
				'render' : function(data, type, full, meta) {
					return '<input type="checkbox" id="qck[' + c++ + ']">';
					}
			}
		],
		select: { 						  	// requires dataTables.select.min.js
			style: 'os',
			selector: 'tr td:last-child()'	// allows last child selectable for that row...
		}
	});
	/*
	 * make these columns the only editable one.
	 */
	table.MakeCellsEditable({			  // define what can be edited and how.
		"onUpdate" : callbackFunction,
		"columns": [1,2,5],
		"inputTypes": [
			{
				"column": 1,
				"type": "number",
				"options": null
			},
			{
				"column": 2,
				"type": "number",
				"options": null
			},
			{
				"column": 5,
				"type": "text",
				"options": null
			}
		]
	});
	
	/*
	 * For debugging only
	 */
	function callbackFunction(updatedCell, updatedRow, oldValue) {
	    console.log("The new value for the cell is: " + updatedCell.data());
	    console.log("The old value for that cell was: " + oldValue);
	    console.log("The values for each cell in that row are: " + updatedRow.data());
	};
	
	/*
	 * call on JSON success response
	 */
	function destroyTable() {
	    if ($.fn.DataTable.isDataTable('#myAdvancedTable')) {
	        table.destroy();
	        table.MakeCellsEditable("destroy");
	    }
	};
	
});
