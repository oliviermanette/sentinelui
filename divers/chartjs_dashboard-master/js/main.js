$(function() {
	  // redrawing func
	var t;
	function size (animate) {
		// body...
		clearTimeout(t);

		t = setTimeout(function (i,el) {
			// body...
			$('canvas').each(function (i,el) {
				// body...
				$(el).attr({
				"width": $(el).parent().width(),
				"height": $(el).parent().outerHeight()
				});
			});

			redraw(animate);

			var m = 0;

			$('.widget').height("");
			$('.widget').each(function (i,el) {	m = Math.max(m, $(el).height()); });
			$('.widget').height(m);
			
		}, 300); // setTimeout func ends here
		//	The Timeout should run after 100 milliseconds
		
	} // size func ends here

	$(window).on('resize', size);
	function redraw (animate) {
		var options = {};
		if (!animate) {
			options.animate = false;
		} else{
			options.animate = true;
		}
		// the rest of our chart drawing will happen here

		// doughnut chart drawing
		var data = [
		    {
		        value: 20,
		        color:"#637b85"
		    },
		    {
		        value : 30,
		        color : "#2c9c69"
		    },
		    {
		        value : 40,
		        color : "#dbba34"
		    },
		    {
		        value : 10,
		        color : "#c62f29"
		    }
		];
		var canvas = $('#hours').get(0);
		var ctx = canvas.getContext('2d');
		new Chart(ctx).Doughnut(data);


		// The Line Graph drawing
		line_data = {
		    labels : ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"],
		    datasets : [
		        {
		            fillColor : "rgba(99,123,133,0.4)",
		            strokeColor : "rgba(220,220,220,1)",
		            pointColor : "rgba(220,220,220,1)",
		            pointStrokeColor : "#fff",
		            data : [65,54,30,81,56,55,40]
		        },
		        {
		            fillColor : "rgba(219,186,52,0.4)",
		            strokeColor : "rgba(220,220,220,1)",
		            pointColor : "rgba(220,220,220,1)",
		            pointStrokeColor : "#fff",
		            data : [20,60,42,58,31,21,50]
		        },
		    ]
		}
		var line_canvas = $('#shipments').get(0);
		var line_ctx = line_canvas.getContext("2d");
		new Chart(line_ctx).Line(line_data, options);

		// Radar Graph drawing

		var radar_data = {
    		labels : ["Helpful","Friendly","Kind","Rude","Slow","Frustrating"],
    		datasets : [
        		{
		            fillColor : "rgba(220,220,220,0.5)",
		            strokeColor : "#637b85",
		            pointColor : "#dbba34",
		            pointStrokeColor : "#637b85",
		            data : [65,59,90,81,30,56]
		        }
   			]
		}
		var radar_canvas = $('#departments').get(0);
		var radar_ctx = radar_canvas.getContext("2d");
		new Chart(radar_ctx).Radar(radar_data, options);

	} // redraw fun ends here 
	
	size(); // this kicks off the first drawing; note the the first clal to size will animate the charts in.
	
}); // ready fun ends here 
