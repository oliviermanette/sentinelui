Chart.defaults.global.plugins.datalabels.display = false;

/**
 * @desc Draw chart for displaying number of choc per day 
 * @param json data - data which contain nb of choc and date
 * @return chart instance
 */
function drawChartNbChocPerDate(data, canvaID = "canvas_choc_nb") {
  if (typeof data != 'object') {
    data = JSON.parse(data);
  }
  if (isEmpty(data)) {

    drawNoDataAvailable(canvaID);

  } else {
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

    var canva_id = "#" + canvaID;
    var ctx = $(canva_id);

    //Options for the chart
    var options = {
      maintainAspectRatio: false,
      responsive: true,
      plugins: [ChartDataLabels],
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
        text: 'Nombre de choc enregistré',
        fontSize: 15
      },
      pan: {
          enabled: false,
          mode: 'xy'
        },
        zoom: {
          enabled: false,
          mode: 'xy',
        }
    };
    //Create the instance
    var chartInstance = new Chart(ctx, {
      type: 'bar',
      data: chartdata,
      options: options
    });

  }

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
  if (isEmpty(data)) {

    drawNoDataAvailable(canvaID);

  } else {
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


    var canva_id = "#" + canvaID;
    var ctx = $(canva_id);


    var timeFormat = 'DD/MM/YYYY';

    var chartdata = {
      datasets: [{
        label: "Puissance du choc (g)",
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
          stacked: true,
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
          stacked: false,
          scaleLabel: {
            display: true,
            labelString: 'Puissance'
          }
        }]
      },
      pan: {
          enabled: false,
          mode: 'xy'
        },
        zoom: {
          enabled: false,
          mode: 'xy',
        }
    };

    var chartInstance = new Chart(ctx, {
      type: 'scatter',
      data: chartdata,
      options: options
    });

  }

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

  if (isEmpty(data)) {

    drawNoDataAvailable(canvaID);

  } else {

    let dataBak = data;
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

    var canva_id = "#" + canvaID;
    var ctx = $(canva_id);

    //Create options for chart dataset
    var options = {
      maintainAspectRatio: false,
      responsive: true,
      tooltips: {
        callbacks: {
          title: function (tooltipItem, data) {
            let date = data.labels[tooltipItem[0].index];

            let hour = mapPowerDateTime.get(tooltipItem[0].value).split(" ")[1];
            //console.log(tooltipItem[0]['value']);
            return "Le " + date + " à " + hour;
          },
          label: function (tooltipItem, data) {
            let power = tooltipItem.value;
            return "Puissance : " + power + " g";
          },
          afterLabel: function (tooltipItem, data) {
            //let hour = mapPowerDateTime.get(tooltipItem['value']).split(" ")[1];
            //return "Heure : " + hour;
          }
        },
        backgroundColor: '#FFF',
        titleFontSize: 15,
        titleFontColor: '#233754',
        bodyFontColor: '#000',
        bodyFontSize: 14,
        displayColors: false
      },
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
            labelString: 'Puissance du choc (g)'
          },
          //type: 'logarithmic',
        }]
      },
      legend: {
        display: false
      },
      title: {
        display: true,
        text: 'Puissance des chocs enregistrés',
        fontSize: 15
      },
      pan: {
          enabled: false,
          mode: 'xy'
        },
        zoom: {
          enabled: false,
          mode: 'xy',
        }
    };

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
    //we set a map (key => power value => date time) so that we could then retrieve
    //the hours thank to the power for tooltip use in the chart
    var mapPowerDateTime = new Map();
    for (var i in data) {
      //count = i;

      let date_time = data[i].date_d;
      let date = date_time.split(" ")[0];
      let hour = date_time.split(" ")[1];
      let power = data[i].power;


      mapPowerDateTime.set(power, date_time);
      if (!datesArr.includes(date)) {
        chocPerDayCount = 0;
        if (powerChocPerDayArr.length > 0) {
          powerChocArr.push(powerChocPerDayArr);
        }
        powerChocPerDayArr = Array();
        datesArr.push(date);


      }
      if (datesArr.includes(date)) {
        powerChocPerDayArr.push(power);

        chocPerDayCount += 1;
      }

    }
    powerChocArr.push(powerChocPerDayArr);

    var dict = []; // create an empty dict which will contain date associated with power values
    for (var d in datesArr) {
      dict.push({
        date: datesArr[d],
        powerValues: powerChocArr[d]
      });
    }

    var colorArr = Array("#919191", "#4b809c", "#106d9c", "#a37524", "#a34f3e");

    //We received max 12 choc per day, so we create 6 datasets for hava maximum 6 stacked bar per day
    var max_choc_per_day = 12;
    for (let i = 0; i < max_choc_per_day; i++) {
      var newDataset = {
        data: []
      };
      chartInstance.data.datasets.push(newDataset);

    }

    //Loop over each date to draw value of each choc power
    for (const [key, value] of Object.entries(dict)) {
      //Axis date
      chartInstance.data.labels[key] = value.date;
      chartInstance.update();

      var count = 0;
      //Convert to float 
      var powerValueArray = value.powerValues.map(function (v) {
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
    if (temperatureData[i].temperature < 100) {
      temperature.push(temperatureData[i].temperature);
      date.push(temperatureData[i].date_d);
    }
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
  var canva_id = "#" + canvaID;
  var ctx = $(canva_id);

  var options = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      xAxes: [{
        scaleLabel: {
          display: true,
          labelString: 'Date'
        },
        ticks:{
          autoskip: true,
          maxTicksLimit: 20
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
    },
    pan: {
        enabled: false,
        mode: 'xy'
      },
      zoom: {
        enabled: false,
        mode: 'xy',
      }
  };

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
      },
      pan: {
          enabled: false,
          mode: 'xy'
        },
        zoom: {
          enabled: false,
          mode: 'xy',
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

  if (isEmpty(inclinometerData)) {

    drawNoDataAvailable(canvaID);

  } else {
    // Object is NOT empty
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
    const avgX = computeAverage(angle_x);
    const avgY = computeAverage(angle_y);
    const avgZ = computeAverage(angle_z);
    var rangeHighAxisX = parseInt((avgX+avgY)/2 + 10);
    var rangeLowAxisX = parseInt((avgX + avgY) / 2 - 10);
    var rangeHighAxisZ = parseInt(avgZ + 10);
    var rangeLowAxisZ = parseInt(avgZ - 10);

    var dragOptions = {
      animationDuration: 1000,
      borderColor: 'rgba(225,225,225,0.3)',
      borderWidth: 5,
      backgroundColor: 'rgb(225,225,225)',
    };
    

    var chartdata = {
      labels: date,
      datasets: [{
          label: 'X °',
          fill: false,
          backgroundColor: '#20324B',
          borderColor: '#20324B',
          data: angle_x,
          yAxisID: "y-axis-1",
        },
        {
          label: 'Y °',
          fill: false,
          backgroundColor: 'orange',
          borderColor: 'orange',
          data: angle_y,
          yAxisID: "y-axis-1",
        },
        {
          label: 'Z °',
          fill: false,
          backgroundColor: 'royalblue',
          borderColor: 'royalblue',
          data: angle_z,
          yAxisID: "y-axis-2",
        }
      ]
    };
    var canva_id = "#" + canvaID;
    var ctx = $(canva_id);

    var options = {
      responsive: true,
      hoverMode: 'index',
      maintainAspectRatio: true,
      stacked: false,

      title: {
        display: true,
        text: "Valeurs d'inclinaison en fonction du temps",
        fontSize:18,
      },
      scales: {
        yAxes: [{
          type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
          display: true,
          position: "left",
          id: "y-axis-1",
          scaleLabel: {
            display: true,
            labelString: 'X° and Y°'
          },
          ticks: {
            min: rangeLowAxisX,
            max: rangeHighAxisX,
            beginAtZero: false,
            stepSize: 0.5,
            autoskip: true,
            maxTicksLimit: 10
          },
        }, {
          type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
          display: true,
          position: "right",
          id: "y-axis-2",
          scaleLabel: {
            display: true,
            labelString: 'Z°'
          },
          ticks: {
            //TODO : change ratio automatic according to values
            min: rangeLowAxisZ,
            max: rangeHighAxisZ,
            beginAtZero: false,
            stepSize: 10,
            autoskip: true,
            maxTicksLimit: 10
          },

          // grid line settings
          gridLines: {
            drawOnChartArea: false, // only want the grid lines for one axis to show up
          },
        }],
        xAxes: [{
          ticks: {
            autoskip: true,
            maxTicksLimit: 15
          },
        }]
      },
      // Container for pan options
      pan: {
          // Boolean to enable panning
          enabled: true,
          drag: dragOptions,
          // Panning directions. Remove the appropriate direction to disable 
          // Eg. 'y' would only allow panning in the y direction
          mode: 'y'
        },

        // Container for zoom options
        zoom: {
          // Boolean to enable zooming
          enabled: true,
          // Zooming directions. Remove the appropriate direction to disable 
          // Eg. 'y' would only allow zooming in the y direction
          mode: 'y',
          // Speed of zoom via mouse wheel
          // (percentage of zoom on a wheel event)
          speed: 0.1,
        }
    };


    var chartInstance = new Chart(ctx, {
      type: 'line',
      data: chartdata,
      options: options,
    });

    return chartInstance;
  }
}

function resetZoom(chartInstance) {
  chartInstance.resetZoom();
}

function drawNoDataAvailable(canvaID) {

  Chart.plugins.register({
    afterDraw: function (chart) {
      if (chart.data.datasets.length === 0) {
        // No data is present
        var ctx = chart.chart.ctx;
        var width = chart.chart.width;
        var height = chart.chart.height;
        chart.clear();

        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.font = "22px normal 'Helvetica Nueue'";
        ctx.fillStyle = "gray";
        ctx.fillText('Pas de données disponible', width / 2, height / 2);
        ctx.restore();
      }
    }
  });


  var canva_id = "#" + canvaID;
  var ctx = $(canva_id);

  var myChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: [],
      datasets: []
    }
  });

}

function drawVariationChartAngleXYZFromData(inclinometerData, percentage = true, canvaID = "canvas_inclinometre", threshAnnotation = 2) {
  if (typeof inclinometerData != 'object') {
    inclinometerData = JSON.parse(inclinometerData);
  }

  let title = "";
  let label = "";
  if (percentage){
    title = "Pourcentage de variation de l\'inclinaison au fil du temps";
    label = "Variation %";
  }else {
    title = "Variation absolue de l\'inclinaison au fil du temps";
    label = "Variation absolue °";
  }
  //console.log(inclinometerData);
  var variation_angle_x = [];
  var variation_angle_y = [];
  var variation_angle_z = [];
  var date = [];


  for (var i in inclinometerData) {
    //console.log(inclinometerData[i]);
    variation_angle_x.push(inclinometerData[i].variationAngleX);
    variation_angle_y.push(inclinometerData[i].variationAngleY);
    variation_angle_z.push(inclinometerData[i].variationAngleZ);
    date.push(inclinometerData[i].date);
  }

  var chartdata = {
    labels: date,
    datasets: [{
        label: 'X °',
        fill: false,
        backgroundColor: '#20324B',
        borderColor: '#20324B',
        data: variation_angle_x,
        yAxisID: "y-axis-0",
      },
      {
        label: 'Y °',
        fill: false,
        backgroundColor: 'orange',
        borderColor: 'orange',
        data: variation_angle_y,
        yAxisID: "y-axis-0",
      },
      {
        label: 'Z °',
        fill: false,
        backgroundColor: 'royalblue',
        borderColor: 'royalblue',
        data: variation_angle_z,
        yAxisID: "y-axis-0",
      }
    ]
  };
  var canva_id = "#" + canvaID;
  var ctx = $(canva_id);

  var options = {
    responsive: true,
    hoverMode: 'index',
    maintainAspectRatio: true,

    title: {
      display: true,
      text: title,
      fontSize: 18,
    },
    scales: {
      yAxes: [{
        id: "y-axis-0",
        gridLines: {
          display: true,
        },
        ticks: {
          beginAtZero: false,
          min: -threshAnnotation - 1,
          max: threshAnnotation + 1,
        },
        scaleLabel: {
          display: true,
          labelString: label
        },
      }],
      xAxes: [{
        ticks: {
          autoskip: true,
          maxTicksLimit: 15

        },
      }],
      
    },
    pan: {
        enabled: true,
        mode: 'y'
      },
      zoom: {
        enabled: true,
        mode: 'y',
      },
      annotation: {
        events: ['click'],
        drawTime: 'afterDatasetsDraw',
        annotations: [{
          id: 'hline1',
          type: 'line',
          mode: 'horizontal',
          scaleID: 'y-axis-0',
          value: threshAnnotation,
          borderColor: 'red',
          borderDash: [2, 2],
          label: {
            enabled: true,
            content: 'Seuil d\'alerte haut',
            backgroundColor: "red",
          },
          onClick: function (e) {
            var link = "/settings";
            window.open(link);
          },
        }, {
          id: 'hline2',
          type: 'line',
          mode: 'horizontal',
          scaleID: 'y-axis-0',
          value: -threshAnnotation,
          borderColor: 'red',
          borderDash: [2, 2],
          label: {
            backgroundColor: "red",
            content: "Seuil d\'alerte bas",
            enabled: true
          },
          onClick: function (e) {
            var link = "/settings";
            window.open(link);
          },
        }
      ],

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

  for (var s = 0; s < subspectre.length; s++) {
    for (var j = 0; j < subspectre[s].length / 2; j++) {
      var y_data_amplitudeArr = hex2dec(subspectre[s].substring(index_start, index_stop));
      if (j > 0) {
        min_freq = min_freq + resolutionData[s];
      }
      if (j < (subspectre[s].length / 2) - 1) {
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
    },
    pan: {
        enabled: true,
        mode: 'y'
      },
      zoom: {
        enabled: true,
        mode: 'y',
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
  var dateDataArr = [];

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
    var obj_1 = {
      x: timeDataArr[i],
      y: amplitudeArr[i]
    };
    dataChartArr.push(obj_1);
  }
  var obj_2 = {
    x: timeDataArr[0] + timeDataArr[1],
    y: 0
  };
  dataChartArr.push(obj_2);

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
  resolution = parseInt(subspectreData[0].resolution);
  subspectre_number = subspectreData[0].subspectre_number;

  var index_start = 2;
  var index_stop = 4;
  var new_sub = '';
  var dataChartArr = [];

  var min_freq_initial = min_freq;

  for (var i = 0; i < subspectre_hex.length / 2; i++) {

    var y_data_amplitudeArr = hex2dec(subspectre_hex.substring(index_start, index_stop));

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