var top_product_response 	= null;
var last_days_response 		= null;
var ic_pie_placement 		= 'insideGrid';
var ic_pie_show_legend 		= true;
var ic_pie_location 		= 'e';
var ic_pie_show_legend 		= true;
var chart;					 

jQuery(document).ready(function($){
	
	jQuery('._proc_date').datepicker({
        dateFormat : 'yy-mm-dd',
		changeMonth: true,
		changeYear: true
    });	
							//	alert("5");
	var data = {"action":"wcismis_action_comman","graph_by_type":"top_product"}
	$.ajax({
		type: "POST",	   
     	data: data,
	  	async: false,
      	url: ajax_object.ajaxurl,
      	dataType:"json",
      	success: function(response) {
			//alert(JSON.stringify(response))
			if(response.length > 0){
				top_product_response = response;
				wcismis_pie_chart_top_product(top_product_response);
			}
      	},
	  	error: function(jqXHR, textStatus, errorThrown) {
  			//alert(jqXHR.responseText);
			//alert(textStatus);
			//alert(errorThrown);
		 }
    });
	
	function wcismis_pie_chart_top_product(response){
		try{
			 chart = AmCharts.makeChart("top_product_pie_chart", {
				  "type": "pie",
				  "theme": "light",
				  "dataProvider": response,
				  "valueField": "Qty",
				  "titleField": "ItemName",
				   "balloon":{
				   "fixedPosition":false
				  }
				  
				} );
		}
		catch(e){
			alert(e.message);
		}
	}
	
	
	var data = {"action":"wcismis_action_comman","graph_by_type":"Last_7_days_sales_order_amount"}
	$.ajax({
		type: "POST",	   
     	data: data,
	  	async: false,
      	url: ajax_object.ajaxurl,
      	dataType:"json",
      	success: function(response) {
			//alert(JSON.stringify(response));			
			if(response.length > 0){
				last_days_response = response;
				wcismis_Last_7_days_sales_order_amount(last_days_response);
			}
      	},
	  	error: function(jqXHR, textStatus, errorThrown) {
  			//alert(jqXHR.responseText);
			//alert(textStatus);
			//alert(errorThrown);
		 }
    });
	
	function wcismis_Last_7_days_sales_order_amount(response){
		try{
			var chart = AmCharts.makeChart("last_7_days_sales_order_amount", {
				"type": "serial",
				"theme": "light",
				"marginRight": 40,
				"marginLeft": 40,
				"autoMarginOffset": 20,
				"mouseWheelZoomEnabled":true,
				"dataDateFormat": "YYYY-MM-DD",
				"valueAxes": [{
					"id": "v1",
					"axisAlpha": 0,
					"position": "left",
					"ignoreAxisWidth":true
				}],
				"balloon": {
					"borderThickness": 1,
					"shadowAlpha": 0
				},
				"graphs": [{
					"id": "g1",
					"balloon":{
					  "drop":false,
					  "adjustBorderColor":false,
					  "color":"#ffffff"
					},
					"bullet": "round",
					"bulletBorderAlpha": 1,
					"bulletColor": "#FFFFFF",
					"bulletSize": 5,
					"hideBulletsCount": 50,
					"lineThickness": 2,
					"title": "red line",
					"useLineColorForBulletBorder": true,
					"valueField": "TotalAmount",
					"balloonText": "<span style='font-size:16px;'>[[value]]</span>"
				}],
				"chartCursor": {
					"pan": true,
					"valueLineEnabled": true,
					"valueLineBalloonEnabled": true,
					"cursorAlpha":1,
					"cursorColor":"#258cbb",
					"limitToGraph":"g1",
					"valueLineAlpha":0.2,
					"valueZoomable":true
				},
				"valueScrollbar":{
				  "oppositeAxis":false,
				  "offset":50,
				  "scrollbarHeight":10
				},
				"categoryField": "Date",
				"categoryAxis": {
					"parseDates": true,
					"dashLength": 1,
					"minorGridEnabled": true
				},
				"dataProvider": response
			});
		}
		catch(e){
			alert(e.message);
		}
	}	
});
