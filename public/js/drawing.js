/**
 * @desc Draw chart for displaying number of choc per day 
 * @param json data - data which contain nb of choc and date
 * @return chart instance
 */
function drawChartNbChocPerDate(data, canvaID = "canvas_choc_nb") {
  if (typeof data != 'object') {
    data = JSON.parse(data);
  }

  var nb_choc = [];
  var date = [];

  for (var i in data) {
    nb_choc.push(data[i].nb_choc);
    date.push(data[i].date_d);
  }

  //Create the dataset 
  var chartdata = {
    labels: date,
    datasets: [{
      labels: date,
      data: nb_choc
    }]
  };

  var canva_id = "#" + canvaID
  var ctx = $(canva_id);

  //Options for the chart
  var options = {
    maintainAspectRatio: true,
    responsive: true,
    scales: {
      xAxes: [{
        scaleLabel: {
          display: true,
          labelString: 'Date'
        },
        gridLines: {
          display: false
        }
      }],
      yAxes: [{
        ticks: {
          beginAtZero: true,
          stepSize: 1
        },
        gridLines: {
          display: false
        },
        scaleLabel: {
          display: true,
          labelString: 'Nombre Choc'
        },
        //type: 'logarithmic',
      }]
    },
    legend: {
      display: false
    },
    title: {
      display: true,
      text: 'Nombre de choc'
    }
  }
  //Create the instance
  var chartInstance = new Chart(ctx, {
    type: 'bar',
    data: chartdata,
    options: options
  });

}

/**
 * @desc Draw chart for displaying the power of each choc per day
 * @param json data - data which contain power of each choc and date
 * @return chart instance
 */
function drawChartPowerChocPerDate(data, canvaID = "canvas_choc_nb") {
  if (typeof data != 'object') {
    data = JSON.parse(data);
  }

  var power_choc = [];
  var date = [];

  var power_choc_per_day = Array();
  var count_choc_per_day = 0;

  var dataChartArr = [];
  var count_date = -1;

  for (var i in data) {
    var date_d = new Date(data[i].date_d);
    date_d = getFormattedDate(date_d);
    var obj = {
      x: date_d,
      y: data[i].power
    };
    dataChartArr.push(obj);
    count = i;
    if (!date.includes(data[i].date_d)) {
      count_date += 1;
      count_choc_per_day = 0;
      if (power_choc_per_day.length > 0) {
        power_choc.push(power_choc_per_day);
      }
      power_choc_per_day = Array();
      date.push(data[i].date_d);

    }
    if (date.includes(data[i].date_d)) {
      //console.log(data[i].date_d, " number of choc : ", count_choc_per_day);
      power_choc_per_day.push(data[i].power);
      count_choc_per_day += 1;

    }

  }
  power_choc.push(power_choc_per_day);

  var dict = []; // create an empty array


  var canva_id = "#" + canvaID
  var ctx = $(canva_id);


  var timeFormat = 'DD/MM/YYYY';

  console.log(dataChartArr);
  var chartdata = {
    datasets: [{
      label: "Puissance du choc",
      data: dataChartArr,
      fill: false,
      showLine: false,
      backgroundColor: "#F5DEB3",
      borderColor: 'red'
    }]
  };
  var options = {
    maintainAspectRatio: true,
    responsive: true,
    title: {
      display: true,
      text: "Chart.js Time Scale"
    },
    scales: {
      xAxes: [{
        type: "time",
        ticks: {
          source: 'date'
        },
        time: {
          format: timeFormat,
          unit: 'day',
          tooltipFormat: 'DD/MM/YYYY',
          scaleLabel: {
            display: true,
            labelString: 'Date'
          }
        }
      }],
      yAxes: [{
        scaleLabel: {
          display: true,
          labelString: 'Puissance'
        }
      }]
    }
  }

  var chartInstance = new Chart(ctx, {
    type: 'scatter',
    data: chartdata,
    options: options
  });



}

/**
 * @desc Draw chart for displaying the power of each choc per day
 * @param json data - data which contain power of each choc and date
 * @return chart instance
 */
function drawChartPowerChocPerDateBar(data, canvaID = "canvas_choc_nb") {

  if (typeof data != 'object') {
    data = JSON.parse(data);
  }
  var powerChocArr = [];
  var datesArr = [];

  //Create chart data
  // We will fill later the datasets 
  var chartdata = {
    labels: [],
    datasets: [{
      data: []
    }]
  };

  var canva_id = "#" + canvaID
  var ctx = $(canva_id);

  //Create options for chart dataset
  var options = {
    maintainAspectRatio: true,
    responsive: true,
    scales: {
      xAxes: [{
        stacked: true,
        scaleLabel: {
          display: true,
          labelString: 'Date'
        },
        gridLines: {
          display: false
        }
      }],
      yAxes: [{
        stacked: true,
        ticks: {
          beginAtZero: true,
          stepSize: 0.1
        },
        gridLines: {
          display: false
        },
        scaleLabel: {
          display: true,
          labelString: 'Puissance du choc'
        },
        //type: 'logarithmic',
      }]
    },
    legend: {
      display: false
    },
    title: {
      display: true,
      text: 'Puissance des chocs'
    }
  }

  //Create chart Instance
  var chartInstance = new Chart(ctx, {
    type: 'bar',
    data: chartdata,
    options: options
  });

  //From the array of date and choc power, and check if for a specific date, we have multiple choc
  //If it's the case, we gather for this date all the choc data

  var powerChocPerDayArr = Array(); //Each array will contain the choc data for a specific day
  var chocPerDayCount = 0;

  for (var i in data) {
    count = i;

    if (!datesArr.includes(data[i].date_d)) {
      chocPerDayCount = 0;
      if (powerChocPerDayArr.length > 0) {
        powerChocArr.push(powerChocPerDayArr);
      }
      powerChocPerDayArr = Array();
      datesArr.push(data[i].date_d);

    }
    if (datesArr.includes(data[i].date_d)) {
      powerChocPerDayArr.push(data[i].power);
      chocPerDayCount += 1;
    }

  }
  powerChocArr.push(powerChocPerDayArr);

  var dict = []; // create an empty dict which will contain date associated with power values
  for (var i in datesArr) {
    dict.push({
      date: datesArr[i],
      powerValues: powerChocArr[i]
    });
  }

  var colorArr = Array("#919191", "#4b809c", "#106d9c", "#a37524", "#a34f3e");

  //We received max 6 choc per day, so we create 6 datasets for hava maximum 6 stacked bar per day
  var max_choc_per_day = 6;
  for (let i = 0; i < max_choc_per_day; i++) {
    var newDataset = {
      data: []
    };
    chartInstance.data.datasets.push(newDataset);
  }

  //Loop over each date to draw value of each choc power
  for (const [key, value] of Object.entries(dict)) {

    //Axis date
    chartInstance.data.labels[key] = value["date"];
    chartInstance.update();

    var count = 0;
    //Convert to float 
    var powerValueArray = value["powerValues"].map(function (v) {
      return parseFloat(v);
    });


    var max_choc_value_per_day = Math.max.apply(null, powerValueArray);
    var min_choc_value_per_day = Math.min.apply(null, powerValueArray);

    //For each day, loop all over the choc data
    for (let index = 0; index < max_choc_per_day; index++) {

      if (index >= 0 && index < powerValueArray.length) {
        //it exists
        if (powerValueArray[index] == max_choc_value_per_day) {
          chartInstance.data.datasets[index].backgroundColor = "#d93c1c";
        } else {
          chartInstance.data.datasets[index].backgroundColor = colorArr[index];
        }
        chartInstance.data.datasets[index].data[key] = powerValueArray[index];
      }
      //Put value at 0 for stacked bar
      else {
        chartInstance.data.datasets[index].data[key] = 0;
      }
    }
    // Finally, make sure you update your chart, to get the result on your screen
    chartInstance.update();
  }
}

/**
 * @desc Draw chart for displaying temperature in function to date
 * @param json temperatureData - data which contain temperature and date
 * @return chart instance
 */
function drawChartTemperatureFromData(temperatureData, canvaID = "canvas_temperature") {
  if (typeof temperatureData != 'object') {
    temperatureData = JSON.parse(temperatureData);
  }
  var temperature = [];
  var date = [];

  for (var i in temperatureData) {
    temperature.push(temperatureData[i].temperature);
    date.push(temperatureData[i].date_d);
  }

  var chartdata = {
    labels: date,
    datasets: [{
      labels: date,
      borderColor: "#3e95cd",
      backgroundColor: "#f6f6f6",
      pointBackgroundColor: "#3e95cd",
      hoverBackgroundColor: 'rgba(200, 200, 200, 1)',
      hoverBorderColor: 'rgba(200, 200, 200, 1)',
      data: temperature
    }]
  };
  var canva_id = "#" + canvaID
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
    legend: {
      display: false
    },
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

}

/**
 * @desc Draw chart for displaying raw inclinometer data in function to date
 * @param json inclinometereData - data which contain inclinometer data and date
 * @return chart instance
 */
function drawChartInclinometerFromData(inclinometerData, canvaID = "canvas_inclinometre") {
  if (typeof inclinometerData != 'object') {
    inclinometerData = JSON.parse(inclinometerData);
  }
  var nx = [];
  var ny = [];
  var nz = [];
  var date = [];

  for (var i in inclinometerData) {
    nx.push(inclinometerData[i].nx);
    ny.push(inclinometerData[i].ny);
    nz.push(inclinometerData[i].nz);
    date.push(inclinometerData[i].date_d);
  }


  var chartdata = {
    labels: date,
    datasets: [{
        label: 'X °',
        fill: false,
        backgroundColor: 'blue',
        borderColor: 'blue',
        data: nx
      },
      {
        label: 'Y °',
        fill: false,
        backgroundColor: 'orange',
        borderColor: 'orange',
        data: ny
      },
      {
        label: 'Z °',
        fill: false,
        backgroundColor: 'green',
        borderColor: 'green',
        data: nz
      }
    ]
  };
  var canva_id = "#" + canvaID;
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

/**
 * @desc Draw chart for displaying angle XYZ data in function to date
 * @param json inclinometereData - data which contain inclinometer data and date
 * @return chart instance
 */
function drawChartAngleXYZFromData(inclinometerData, canvaID = "canvas_inclinometre") {
  if (typeof inclinometerData != 'object') {
    inclinometerData = JSON.parse(inclinometerData);
  }
  console.log(inclinometerData);
  var angle_x = [];
  var angle_y = [];
  var angle_z = [];
  var date = [];

  for (var i in inclinometerData) {
    angle_x.push(inclinometerData[i].angle_x);
    angle_y.push(inclinometerData[i].angle_y);
    angle_z.push(inclinometerData[i].angle_z);
    date.push(inclinometerData[i].date_d);
  }


  var chartdata = {
    labels: date,
    datasets: [{
        label: 'X °',
        fill: false,
        backgroundColor: 'blue',
        borderColor: 'blue',
        data: angle_x
      },
      {
        label: 'Y °',
        fill: false,
        backgroundColor: 'orange',
        borderColor: 'orange',
        data: angle_y
      },
      {
        label: 'Z °',
        fill: false,
        backgroundColor: 'green',
        borderColor: 'green',
        data: angle_z
      }
    ]
  };
  var canva_id = "#" + canvaID;
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
            precison:2,
          },
          scaleLabel: {
            display: false,
            labelString: 'Angle (°)'
        
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


/**
 * @desc Draw chart for displaying spectre data in function to resolution
 * @param json spectreData - data which contain spectre data
 * @return chart instance
 */
function drawChartSpectreFromData(spectreData, canvaID = "canvas_spectre") {

  var subspectre = [];
  var minFreqData = [];
  var maxFreqData = [];
  var resolutionData = [];
  var dateDataArr = [];

  for (var i in spectreData) {
    subspectre.push(spectreData[i].subspectre);
    minFreqData.push(parseInt(spectreData[i].min_freq));
    maxFreqData.push(parseInt(spectreData[i].max_freq));
    resolutionData.push(parseInt(spectreData[i].resolution));
    dateDataArr.push(spectreData[i].date);
  }

  min_freq = parseInt(minFreqData[0]);
  var min_freq_initial = min_freq;
  var max_freq = Math.max.apply(Math, maxFreqData);

  var index_start = 2;
  var index_stop = 4;
  var dataChartArr = [];

  for (var i = 0; i < subspectre.length; i++) {
    for (var j = 0; j < subspectre[i].length / 2; j++) {
      var y_data_amplitudeArr = hex2dec(subspectre[i].substring(index_start, index_stop))
      if (j > 0) {
        min_freq = min_freq + resolutionData[i];
      }
      if (j < (subspectre[i].length / 2) - 1) {
        var obj = {
          x: min_freq,
          y: y_data_amplitudeArr
        };
        dataChartArr.push(obj);
      }
      index_start += 2;
      index_stop += 2;
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
  var title = "Spectre du " + dateDataArr[0];

  var chartdata = {
    datasets: [{
      showLine: true,
      borderColor: "#3e95cd",
      backgroundColor: gradient,
      pointBackgroundColor: "#3e95cd",
      data: dataChartArr,
    }]
  };
  var options = {
    responsive: true,
    maintainAspectRatio: true,
    scales: {
      display: true,
      xAxes: [{
        gridLines: {
          display: true
        },
        ticks: {
          beginAtZero: false,
          min: min_freq_initial,
          max: max_freq,
        },
        scaleLabel: {
          display: true,
          labelString: 'Frequence (Hz)'
        }
      }],
      yAxes: [{
        gridLines: {
          display: true,
        },
        ticks: {
          beginAtZero: false,
        },
        scaleLabel: {
          display: true,
          labelString: 'AmplitudeArr (mg)'
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

/**
 * @desc Draw chart for displaying choc data (amplitudeArr in function of time)
 * @param json chocData - data which contain choc data (amplitudeArr 1, time 1, amplitudeArr 2, time 2)
 * @return chart instance
 */
function drawChartChocFromData(chocData, canvaID = "canvas_choc") {
  if (typeof chocData != 'object') {
    chocData = JSON.parse(chocData);
  }
  var amplitudeArr = [];
  var amplitudeArr_1 = [];
  var amplitudeArr_2 = [];
  var timeDataArr = [];
  var timeArr_1 = [];
  var timeArr_2 = [];
  var dateDataArr = []

  amplitudeArr.push(parseFloat(chocData[0].amplitudeArr_1));
  amplitudeArr.push(parseFloat(chocData[0].amplitudeArr_2));
  timeDataArr.push(parseFloat(chocData[0].timeArr_1));
  timeDataArr.push(parseFloat(chocData[0].timeArr_2));
  dateDataArr.push(chocData[0].date_d);

  var dataChartArr = []; // create an empty array which will contain the data for chartJS in proper format

  var obj = {
    x: 0,
    y: 0
  };
  dataChartArr.push(obj);
  for (var i = 0; i < 2; i++) {
    var obj = {
      x: timeDataArr[i],
      y: amplitudeArr[i]
    };
    dataChartArr.push(obj);
  }
  var obj = {
    x: timeDataArr[0] + timeDataArr[1],
    y: 0
  };
  dataChartArr.push(obj);

  var chartdata = {
    datasets: [{
      data: dataChartArr,
      showLine: true,
    }]
  };

  var title = "Last choc which occur on " + chocData[0].date_d;
  var canva_id = "#" + canvaID;
  var ctx = $(canva_id);

  var barGraph = new Chart(ctx, {
    type: 'scatter',
    data: chartdata,
    options: {
      scales: {
        display: true,
        xAxes: [{
          ticks: {
            beginAtZero: true,
            min: 0,
            max: timeDataArr[0] + timeDataArr[1],
            step: 0.01,
          },
          type: 'linear',
          scaleLabel: {
            display: true,

            labelString: 'Time (s)'
          }
        }],
        yAxes: [{
          ticks: {
            beginAtZero: true,
          },
          scaleLabel: {
            display: true,
            labelString: 'AmplitudeArr (g)'
          }
        }]
      },
      legend: {
        display: false
      },
      title: {
        display: true,
        text: title
      }
    }
  });
}

/**
 * @desc Draw chart for displaying subspectre
 * @param json subspectreData - data which contain subspectre data
 * @return chart instance
 */
function drawChartSubSpectreFromData(subspectreData, canvaID = "canvas_subspectre") {

  if (typeof subspectreData != 'object') {
    subspectreData = JSON.parse(subspectreData);
  }

  subspectre_hex = subspectreData[0].payload;
  min_freq = parseInt(subspectreData[0].min_freq);
  max_freq = parseInt(subspectreData[0].max_freq);
  resolution = parseInt(subspectreData[0].resolution)
  subspectre_number = subspectreData[0].subspectre_number;

  var index_start = 2;
  var index_stop = 4;
  var new_sub = '';
  var dataChartArr = [];

  var min_freq_initial = min_freq;

  for (var i = 0; i < subspectre_hex.length / 2; i++) {

    var y_data_amplitudeArr = hex2dec(subspectre_hex.substring(index_start, index_stop))

    if (i > 0) {
      min_freq = min_freq + resolution;
    }

    if (i < (subspectre_hex.length / 2) - 1) {
      var obj = {
        x: min_freq,
        y: y_data_amplitudeArr
      };
      dataChartArr.push(obj);
    }
    index_start += 2;
    index_stop += 2;
  }
  var title = "Spectre | Resolution = " + String(resolution) + "Hz | Sous spectre = " + String(subspectre_number);

  var chartdata = {
    datasets: [{
      data: dataChartArr,
      showLine: true,
    }]
  };

  var canva_id = "#" + canvaID;
  var ctx = $(canva_id);

  var barGraph = new Chart(ctx, {
    type: 'scatter',
    data: chartdata,
    options: {
      scales: {
        display: true,
        xAxes: [{
          ticks: {
            beginAtZero: false,
            min: min_freq_initial,
            max: max_freq,
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
            labelString: 'AmplitudeArr (mg)'
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
    }
  });
}

/**
 * @desc show map with sensors data on it
 * @param array data - data which contain sensors info to display on the map
 * @return map instance
 */
function showMap(data) {
  data = JSON.parse(data);
  //console.log("Data show map : ", data);
  //For centering the map around France at initialization
  var lat_france = 46.2276;
  var long_france = 2.2137;

  var map = L.map('map', {
    attributionControl: false
  }).setView([lat_france, long_france], 5.3);

  L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibGlyb25lIiwiYSI6ImNrMmdrdmo1czAwOXozb29oc3NybXJqNTcifQ.sbQPrGi1n7lsCtlCvojTBA', {
    maxZoom: 18,
    attribution: '<a href="https://flod.ai">Flod Sentiel</a>',
    id: 'mapbox.streets'
  }).addTo(map);

  L.control.custom({
      position: 'topright',
      content: '<button type="button" class="btn btn-default">' +
        '    <i class="fa fa-crosshairs"></i>' +
        '</button>' +
        '<button type="button" class="btn btn-danger">' +
        '    <i class="fa fa-times"></i>' +
        '</button>' +
        '<button type="button" class="btn btn-success">' +
        '    <i class="fa fa-check"></i>' +
        '</button>' +
        '<button type="button" class="btn btn-warning">' +
        '    <i class="fa fa-exclamation-triangle"></i>' +
        '</button>',
      classes: 'btn-group-vertical btn-group-sm',
      style: {
        margin: '30px',
        padding: '0px 0 0 0',
        cursor: 'pointer',
        height: '100px',
      },
      datas: {
        'foo': 'bar',
      },
      events: {
        click: function (data) {
          console.log('wrapper div element clicked');
          console.log(data);
        },
        dblclick: function (data) {
          console.log('wrapper div element dblclicked');
          console.log(data);
        },
        contextmenu: function (data) {
          console.log('wrapper div element contextmenu');
          console.log(data);
        },
      }
    })
    .addTo(map);

  for (var i = 0; i < data.length; i++) {
    var obj = data[i];

    if (obj.longitude_sensor != null || obj.latitude_sensor != null) {
      longitude_sensor = obj.longitude_sensor;
      latitude_sensor = obj.latitude_sensor;


      sensor_id = obj.sensor_id;
      site = obj.site;
      equipement = obj.equipement;

      L.marker([latitude_sensor, longitude_sensor]).addTo(map)
        .bindPopup("<b>" + site + "</b><br />" + equipement).openPopup();
    }
  }
}

function addData(chart, label, color, data) {
  chart.data.datasets.push({
    label: label,
    backgroundColor: color,
    data: data
  });
  chart.update();
}