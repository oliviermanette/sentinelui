
function drawTemperatureFromData(temperatureDataJson, canvaID = "canvas_temperature"){
  if (typeof temperatureDataJson != 'object'){
    temperatureDataJson = JSON.parse(temperatureDataJson);
  }
  var temperature = [];
  var date = [];
  for (var i in temperatureDataJson) {
    temperature.push(temperatureDataJson[i].temperature);
    date.push(temperatureDataJson[i].date_d);
  }

  //console.log(date);
  var chartdata = {
    labels: date,
    datasets : [
    {
      labels: date,
      borderColor: "#3e95cd",
      backgroundColor: "#f6f6f6",
      pointBackgroundColor: "#3e95cd",
      hoverBackgroundColor: 'rgba(200, 200, 200, 1)',
      hoverBorderColor: 'rgba(200, 200, 200, 1)',
      data: temperature
    }
    ]
  };
  var canva_id = "#"+canvaID
  var ctx = $(canva_id);

  var options = {
    responsive: true,
    maintainAspectRatio: true,
    scales: {
      xAxes: [{
        scaleLabel: {
          display: true,
          labelString: 'Date'
        }
      }],
      yAxes: [{
        ticks: {
          beginAtZero: false,
        },
        scaleLabel: {
          display: true,
          labelString: 'Temeprature (°C)'
        },
        //type: 'logarithmic',
      }]
    },
    legend: { display: false },
    title: {
      display: true,
      text: 'Temperature en fonction du temps'
    }
  }

  var chartInstance = new Chart(ctx, {
    type: 'line',
    data: chartdata,
    options: options
  });

  var barGraph = new Chart(ctx, {
    type: 'line',
    data: chartdata,
    options:options
  });
}

function drawInclinometerFromData(inclinometerDataJson, canvaID = "canvas_inclinometre"){
  if (typeof inclinometerDataJson != 'object'){
    inclinometerDataJson = JSON.parse(inclinometerDataJson);
  }
  var nx = [];
  var ny = [];
  var nz = [];
  var date = [];
  for (var i in inclinometerDataJson) {
    nx.push(inclinometerDataJson[i].nx);
    ny.push(inclinometerDataJson[i].ny);
    nz.push(inclinometerDataJson[i].nz);
    date.push(inclinometerDataJson[i].date_d);
  }

  //console.log(nz);
  var chartdata = {
    labels: date,
    datasets : [
    {
      label: 'X',
      fill: false,
      backgroundColor: 'blue',
      borderColor: 'blue',
      data: nx
    },
    {
      label: 'Y',
      fill: false,
      backgroundColor: 'orange',
      borderColor: 'orange',
      data: ny
    },
    {
      label: 'Z',
      fill: false,
      backgroundColor: 'green',
      borderColor: 'green',
      data: nz
    }
    ]
  };
  var canva_id = "#"+canvaID;
  var ctx = $(canva_id);

  var options = {
    responsive: true,
    maintainAspectRatio: true,
    scales: {
      scales: {
        xAxes: [{
          scaleLabel: {
            display: true,
            labelString: 'Date'
          }
        }],
        yAxes: [{
          ticks: {
            beginAtZero: false,
          },
          scaleLabel: {
            display: true,
            labelString: 'Height (m)'
          },
        }]
      },
      legend: {
        display: true
      },
      title: {
        display: true,
        text: 'Inclinometre en fonction du temps'
      }
    }
  };

  var chartInstance = new Chart(ctx, {
    type: 'line',
    data: chartdata,
    options: options,
  });
}

function drawSpectreFromData(spectreDataJson, canvaID = "canvas_spectre"){
  //console.log("spetre json: ",spectreDataJson);
  var subspectre = [];
  var minFreqData = [];
  var maxFreqData = [];
  var resolutionData = [];
  var date_data = [];
  for (var i in spectreDataJson) {
    subspectre.push(spectreDataJson[i].subspectre);
    minFreqData.push(parseInt(spectreDataJson[i].min_freq));
    maxFreqData.push(parseInt(spectreDataJson[i].max_freq));
    resolutionData.push(parseInt(spectreDataJson[i].resolution));
    date_data.push(spectreDataJson[i].date);
  }
  //console.log(date_data);
  min_freq = parseInt(minFreqData[0]);
  var min_freq_initial = min_freq;
  var max_freq = Math.max.apply(Math, maxFreqData);
  //console.log("maxFreq : ", max_freq);
  var index_start = 2;
  var index_stop = 4;
  var array_data = [];

  for (var i = 0; i < subspectre.length; i++){
    for (var j = 0; j < subspectre[i].length/2; j++){
      var y_data_amplitude = hex2dec(subspectre[i].substring(index_start, index_stop))
      if (j > 0){
        min_freq = min_freq + resolutionData[i];
      }
      if (j < (subspectre[i].length/2)-1){
        var obj = {x:min_freq,y:y_data_amplitude};
        array_data.push(obj);
      }
      index_start+=2;
      index_stop+=2;
    }
    index_start = 2;
    index_stop = 4;
  }

  var canva_id = "#" + canvaID;
  var ctx = document.getElementById(canvaID).getContext('2d');

  /*** Gradient ***/
  var gradient = ctx.createLinearGradient(0, 0, 0, 400);
  gradient.addColorStop(0, 'rgba(250,174,50,1)');
  gradient.addColorStop(1, 'rgba(250,174,50,0)');
  /***************/
  var title = "Spectre pour la semaine du " + date_data[0] + " au " + date_data[date_data.length-1];

  var chartdata = {
    datasets : [
    {
      showLine: true,
      borderColor: "#3e95cd",
      backgroundColor: gradient,
      pointBackgroundColor: "#3e95cd",
      data:array_data,
    }
    ]
  };
  var options = {
    responsive: true,
    maintainAspectRatio: true,
    scales: {
      display : true,
      xAxes: [{
        gridLines: {
          display:true
        },
        ticks: {
          beginAtZero: false,
          min: min_freq_initial,
          max : max_freq,
        },
        scaleLabel: {
          display: true,
          labelString: 'Frequence (Hz)'
        }
      }],
      yAxes: [{
        gridLines: {
          display:true,
        },
        ticks: {
          beginAtZero: false,
        },
        scaleLabel: {
          display: true,
          labelString: 'Amplitude (mg)'
        },
      }]
    },
    legend: {
      display: false
    },
    title: {
      display: true,
      text: title
    }
  };
  var chartInstance = new Chart(ctx, {
    type: 'scatter',
    data: chartdata,
    options: options
  });

}

function drawChocFromData(chocDataJson, canvaID = "canvas_choc" ){
  if (typeof chocDataJson != 'object'){
    chocDataJson = JSON.parse(chocDataJson);
  }
  var amplitude = [];
  var amplitude_1 = [];
  var amplitude_2 = [];
  var time_data = [];
  var time_1 = [];
  var time_2 = [];
  var date_data = []

  amplitude.push(parseFloat(chocDataJson[0].amplitude_1));
  amplitude.push(parseFloat(chocDataJson[0].amplitude_2));
  time_data.push(parseFloat(chocDataJson[0].time_1));
  time_data.push(parseFloat(chocDataJson[0].time_2));
  date_data.push(chocDataJson[0].date_d);
  var array_data = []; // create an empty array

  var obj = {x:0,y:0};
  array_data.push(obj);
  for(var i=0;i<2;i++)
  {
    var obj = {x:time_data[i],y:amplitude[i]};
    array_data.push(obj);
  }
  var obj = {x:time_data[0]+time_data[1],y:0};
  array_data.push(obj);

  var chartdata = {
    datasets : [
    {
      data:array_data,
      showLine: true,
    }
    ]
  };
  var title = "Last choc which occur on " + chocDataJson[0].date_d;
  var canva_id = "#"+canvaID;
  var ctx = $(canva_id);
  var barGraph = new Chart(ctx, {
    type: 'scatter',
    data: chartdata,
    options: {
      scales: {
        display : true,
        xAxes: [{
          ticks: {
            beginAtZero: true,
            min:0,
            max:time_data[0]+time_data[1],
            step:0.01,
          },
          type: 'linear',
          scaleLabel: {
            display: true,

            labelString: 'Time (s)'
          }
        }],
        yAxes: [{
          //reverse: true,
          //  type:       "time",
          ticks: {
            beginAtZero: true,
          },
          scaleLabel: {
            display: true,
            labelString: 'Amplitude (g)'
          }
        }]
      },
      legend: { display: false },
      title: {
        display: true,
        text: title
      }
    }
  });
}

//Draw a specific subspectre from the sensor
function drawSubSpectreFromData(subspectreDataJson,canvaID = "canvas_subspectre"){
  //console.log(data);
  if (typeof subspectreDataJson != 'object'){
    subspectreDataJson = JSON.parse(subspectreDataJson);
  }
  subspectre_hex = subspectreDataJson[0].payload;
  min_freq = parseInt(subspectreDataJson[0].min_freq);
  max_freq = parseInt(subspectreDataJson[0].max_freq);
  resolution = parseInt(subspectreDataJson[0].resolution)
  subspectre_number = subspectreDataJson[0].subspectre_number;
  var index_start = 2;
  var index_stop = 4;
  var new_sub ='';
  var array_data = [];

  var min_freq_initial = min_freq;
  for (var i = 0; i < subspectre_hex.length/2; i++){
    var y_data_amplitude = hex2dec(subspectre_hex.substring(index_start, index_stop))
    if (i > 0){
      min_freq = min_freq + resolution;
    }
    if (i < (subspectre_hex.length/2)-1){
      var obj = {x:min_freq,y:y_data_amplitude};
      array_data.push(obj);
    }
    index_start+=2;
    index_stop+=2;
  }
  var title = "Spectre | Resolution = " + String(resolution) + "Hz | Sous spectre = " + String(subspectre_number);
  var chartdata = {
    datasets : [
    {
      data:array_data,
      showLine: true,
    }
    ]
  };
  var canva_id = "#"+canvaID;
  var ctx = $(canva_id);
  var barGraph = new Chart(ctx, {
    type: 'scatter',
    data: chartdata,
    options: {
      scales: {
        display : true,
        xAxes: [{
          ticks: {
            beginAtZero: false,
            min: min_freq_initial,
            max : max_freq,
          },
          scaleLabel: {
            display: true,
            labelString: 'Frequence (Hz)'
          }
        }],
        yAxes: [{
          ticks: {
            beginAtZero: false,
          },
          scaleLabel: {
            display: true,
            labelString: 'Amplitude (mg)'
          },
        }]
      },
      legend: { display: false },
      title: {
        display: true,
        text: title
      }
    }
  });
}


function showMap(data){
  data = JSON.parse(data);
  //console.log(data);
  var lat_france = 46.2276;
  var long_france = 2.2137;

  var map = L.map('map', { attributionControl:false }).setView([lat_france, long_france], 5.3);

  L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibGlyb25lIiwiYSI6ImNrMmdrdmo1czAwOXozb29oc3NybXJqNTcifQ.sbQPrGi1n7lsCtlCvojTBA', {
    maxZoom: 18,
    attribution:'<a href="https://flod.ai">Flod Sentiel</a>',
    id: 'mapbox.streets'
  }).addTo(map);

  L.control.custom({
    position: 'topright',
    content : '<button type="button" class="btn btn-default">'+
      '    <i class="fa fa-crosshairs"></i>'+
      '</button>'+
      '<button type="button" class="btn btn-danger">'+
        '    <i class="fa fa-times"></i>'+
        '</button>'+
        '<button type="button" class="btn btn-success">'+
          '    <i class="fa fa-check"></i>'+
          '</button>'+
          '<button type="button" class="btn btn-warning">'+
            '    <i class="fa fa-exclamation-triangle"></i>'+
            '</button>',
            classes : 'btn-group-vertical btn-group-sm',
            style   :
            {
              margin: '30px',
              padding: '0px 0 0 0',
              cursor: 'pointer',
              height: '100px',
            },
            datas   :
            {
              'foo': 'bar',
            },
            events:
            {
              click: function(data)
              {
                console.log('wrapper div element clicked');
                console.log(data);
              },
              dblclick: function(data)
              {
                console.log('wrapper div element dblclicked');
                console.log(data);
              },
              contextmenu: function(data)
              {
                console.log('wrapper div element contextmenu');
                console.log(data);
              },
            }
          })
          .addTo(map);

          for(var i = 0; i < data.length; i++) {
            var obj = data[i];
            longitude_sensor = obj.longitude_sensor;
            latitude_sensor = obj.latitude_sensor;
            sensor_id = obj.sensor_id;
            site = obj.site;
            equipement = obj.equipement;

            L.marker([latitude_sensor, longitude_sensor] ).addTo(map)
            .bindPopup("<b>" + site + "</b><br />" + equipement).openPopup();
          }
        }
