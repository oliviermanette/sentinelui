Chart.defaults.global.plugins.datalabels.display = false;

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
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.font = "35px normal 'Helvetica Nueue'";
        ctx.fillStyle = "gray";
        ctx.fillText(
          "Pas encore de ce type de données disponible pour ce capteur",
          width / 2,
          height / 2
        );
        ctx.restore();
      }
    },
  });

  var canva_id = "#" + canvaID;
  var ctx = $(canva_id);

  var myChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: [],
      datasets: [],
    },
  });
}

drawArrow = function (context, fromx, fromy, tox, toy) {
  var headlen = 10; // length of head in pixels
  var dx = tox - fromx;
  var dy = toy - fromy;
  var angle = Math.atan2(dy, dx);
  context.moveTo(fromx, fromy);
  context.lineTo(tox, toy);
  context.lineTo(
    tox - headlen * Math.cos(angle - Math.PI / 6),
    toy - headlen * Math.sin(angle - Math.PI / 6)
  );
  context.moveTo(tox, toy);
  context.lineTo(
    tox - headlen * Math.cos(angle + Math.PI / 6),
    toy - headlen * Math.sin(angle + Math.PI / 6)
  );
};

function drawVerticalAxis(canvaID) {
  Chart.plugins.register({
    beforeDraw: function (chart, options) {
      if (chart.config.data.drawXYAxes) {
        var ctx = chart.chart.ctx;
        var yaxis = chart.scales["scale"];
        var paddingX = 100;
        var paddingY = 40;

        ctx.save();
        ctx.beginPath();
        ctx.strokeStyle = "#0000ff";
        ctx.lineWidth = 0.75;

        drawArrow(
          ctx,
          yaxis.xCenter,
          yaxis.yCenter,
          yaxis.xCenter - yaxis.drawingArea - paddingX,
          yaxis.yCenter
        );
        drawArrow(
          ctx,
          yaxis.xCenter,
          yaxis.yCenter,
          yaxis.xCenter + yaxis.drawingArea + paddingX,
          yaxis.yCenter
        );
        drawArrow(
          ctx,
          yaxis.xCenter,
          yaxis.yCenter,
          yaxis.xCenter,
          yaxis.yCenter - yaxis.drawingArea - paddingY
        );
        drawArrow(
          ctx,
          yaxis.xCenter,
          yaxis.yCenter,
          yaxis.xCenter,
          yaxis.yCenter + yaxis.drawingArea + paddingY
        );

        ctx.stroke();
        ctx.restore();
      }
    },
  });
}

/**
 * @desc Draw chart for displaying number of choc per day
 * @param json data - data which contain nb of choc and date
 * @return chart instance
 */
function drawChartNbChocPerDate(data, canvaID = "canvas_choc_nb") {
  if (typeof data != "object") {
    data = JSON.parse(data);
  }
  //console.log("drawChartNbChocPerDate -> data", data);
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
      datasets: [
        {
          labels: date,
          data: nb_choc,
        },
      ],
    };

    var canva_id = "#" + canvaID;
    var ctx = $(canva_id);

    //Options for the chart
    var options = {
      maintainAspectRatio: false,
      responsive: true,
      plugins: [ChartDataLabels],
      scales: {
        xAxes: [
          {
            scaleLabel: {
              display: true,
              labelString: "Date",
            },
            gridLines: {
              display: false,
            },
          },
        ],
        yAxes: [
          {
            ticks: {
              beginAtZero: true,
              stepSize: 1,
            },
            gridLines: {
              display: false,
            },
            scaleLabel: {
              display: true,
              labelString: "Nombre Choc",
            },
            //type: 'logarithmic',
          },
        ],
      },
      legend: {
        display: false,
      },
      title: {
        display: true,
        text: "Nombre de choc enregistré",
        fontSize: 15,
      },
      pan: {
        enabled: false,
        mode: "xy",
      },
      zoom: {
        enabled: false,
        mode: "xy",
      },
    };
    //Create the instance
    var chartInstance = new Chart(ctx, {
      type: "bar",
      data: chartdata,
      options: options,
    });
  }
}

function drawChartNbChocPerHour(data, canvaID = "canvas_choc_nb_hour") {
  if (typeof data != "object") {
    data = JSON.parse(data);
  }
  //console.log("drawChartNbChocPerDate -> data", data);
  if (isEmpty(data)) {
    drawNoDataAvailable(canvaID);
  } else {
    var mapPowerDateTime = new Map();
    var barColorArr = [];
    var nb_chocArr = [];
    var dateArr = [];
    for (var i in data) {
      var date_hour = i;
      //console.log("drawChartNbChocPerHour -> data", data[i]);

      nb_chocArr.push(data[i].length);
      dateArr.push(date_hour);
      mapPowerDateTime.set(date_hour, data[i]);
    }

    //Create the dataset
    var chartdata = {
      labels: dateArr,
      datasets: [
        {
          backgroundColor: barColorArr,
          labels: dateArr,
          data: nb_chocArr,
        },
      ],
    };

    var canva_id = "#" + canvaID;
    var ctx = $(canva_id);

    //Options for the chart
    var options = {
      maintainAspectRatio: false,
      responsive: true,
      plugins: [ChartDataLabels],
      tooltips: {
        callbacks: {
          title: function (tooltipItem, data) {
            let date = data.labels[tooltipItem[0].index];
            //console.log("drawChartNbChocPerHour -> date", date);

            let dataChoc = mapPowerDateTime.get(date);
            //console.log("drawChartNbChocPerHour -> hour", hour);
            //console.log(tooltipItem[0]['value']);
            //return "Le " + date + " à " + hour;*/
          },
          label: function (tooltipItem, data) {
            let date = tooltipItem.label;
            //console.log("drawChartNbChocPerHour -> date", date);
            let dataChoc = mapPowerDateTime.get(date);
            //console.log("drawChartNbChocPerHour -> dataChoc", dataChoc);
            var stringArr = [];
            for (var j = 0; j < dataChoc.length; j++) {
              var device_number = dataChoc[j]["device_number"];
              var power = dataChoc[j]["power"];
              var time = dataChoc[j]["date_time"].split(" ")[1];
              var string =
                "\nCapteur " +
                device_number +
                "\nP:" +
                power +
                " mW\nà " +
                time +
                "\n";
              stringArr.push(string);
            }
            //console.log(stringArr);

            return stringArr;
          },
          afterLabel: function (tooltipItem, data) {
            //let hour = mapPowerDateTime.get(tooltipItem['value']).split(" ")[1];
            //return "Heure : " + hour;
          },
        },
        backgroundColor: "#FFF",
        titleFontSize: 15,
        titleFontColor: "#233754",
        bodyFontColor: "#000",
        bodyFontSize: 14,
        displayColors: false,
      },

      scales: {
        xAxes: [
          {
            scaleLabel: {
              display: true,
              labelString: "Date",
            },
            gridLines: {
              display: false,
            },
          },
        ],
        yAxes: [
          {
            ticks: {
              beginAtZero: true,
              stepSize: 1,
            },
            gridLines: {
              display: false,
            },
            scaleLabel: {
              display: true,
              labelString: "Nombre Choc",
            },
            //type: 'logarithmic',
          },
        ],
      },
      legend: {
        display: false,
      },
      title: {
        display: true,
        text: "Nombre de choc par heure si au moins deux capteurs différents",
        fontSize: 15,
      },
      pan: {
        enabled: false,
        mode: "xy",
      },
      zoom: {
        enabled: false,
        mode: "xy",
      },
      plugins: {
        datalabels: {
          display: true,
          color: "#ffffff",
          font: {
            weight: "bold",
            size: 13,
          },
        },
      },
    };
    //Create the instance
    var chartInstance = new Chart(ctx, {
      type: "bar",
      data: chartdata,
      options: options,
    });

    var dataset = chartInstance.data.datasets[0];

    var chartColors = {
      red: "#ee5253",
      orange: "#ff9f43",
      blue: "#54a0ff",
    };

    for (var i = 0; i < dataset.data.length; i++) {
      var nbChoc = dataset.data[i];
      if (nbChoc > 6) {
        barColorArr.push(chartColors.red);
        //dataset.backgroundColor[i] = chartColors.red;
      } else if (nbChoc > 3 && nbChoc < 7) {
        barColorArr.push(chartColors.orange);
      } else {
        barColorArr.push(chartColors.blue);
      }
    }
    chartInstance.update();
  }
}

/**
 * @desc Draw chart for displaying the power of each choc per day
 * @param json data - data which contain power of each choc and date
 * @return chart instance
 */
function drawChartPowerChocPerDate(data, canvaID = "canvas_choc_nb") {
  if (typeof data != "object") {
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
        y: data[i].power,
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

    var timeFormat = "DD/MM/YYYY";

    var chartdata = {
      datasets: [
        {
          label: "Puissance du choc (W)",
          data: dataChartArr,
          fill: false,
          showLine: false,
          backgroundColor: "#F5DEB3",
          borderColor: "red",
        },
      ],
    };
    var options = {
      maintainAspectRatio: true,
      responsive: true,
      title: {
        display: true,
        text: "Chart.js Time Scale",
      },
      scales: {
        xAxes: [
          {
            stacked: true,
            type: "time",
            ticks: {
              source: "date",
            },
            time: {
              format: timeFormat,
              unit: "day",
              tooltipFormat: "DD/MM/YYYY",
              scaleLabel: {
                display: true,
                labelString: "Date",
              },
            },
          },
        ],
        yAxes: [
          {
            stacked: false,
            scaleLabel: {
              display: true,
              labelString: "Puissance",
            },
          },
        ],
      },
      pan: {
        enabled: false,
        mode: "xy",
      },
      zoom: {
        enabled: false,
        mode: "xy",
      },
    };

    var chartInstance = new Chart(ctx, {
      type: "scatter",
      data: chartdata,
      options: options,
    });
  }
}

/**
 * @desc Draw chart for displaying the power of each choc per day
 * @param json data - data which contain power of each choc and date
 * @return chart instance
 */
function drawChartPowerChocPerDateBar(data, canvaID = "canvas_choc_nb") {
  if (typeof data != "object") {
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
      datasets: [
        {
          data: [],
        },
      ],
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
            return "Puissance : " + power + " mW";
          },
          afterLabel: function (tooltipItem, data) {
            //let hour = mapPowerDateTime.get(tooltipItem['value']).split(" ")[1];
            //return "Heure : " + hour;
          },
        },
        backgroundColor: "#FFF",
        titleFontSize: 15,
        titleFontColor: "#233754",
        bodyFontColor: "#000",
        bodyFontSize: 14,
        displayColors: false,
      },
      scales: {
        xAxes: [
          {
            stacked: true,
            scaleLabel: {
              display: true,
              labelString: "Date",
            },
            gridLines: {
              display: false,
            },
          },
        ],
        yAxes: [
          {
            stacked: true,
            ticks: {
              beginAtZero: true,
              stepSize: 0.1,
            },
            gridLines: {
              display: false,
            },
            scaleLabel: {
              display: true,
              labelString: "Puissance du choc (mW)",
            },
            //type: 'logarithmic',
          },
        ],
      },
      legend: {
        display: false,
      },
      title: {
        display: true,
        text: "Puissance des chocs enregistrés",
        fontSize: 15,
      },
      pan: {
        enabled: false,
        mode: "xy",
      },
      zoom: {
        enabled: false,
        mode: "xy",
      },
    };

    //Create chart Instance
    var chartInstance = new Chart(ctx, {
      type: "bar",
      data: chartdata,
      options: options,
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
        powerValues: powerChocArr[d],
      });
    }

    var colorArr = Array("#919191", "#4b809c", "#106d9c", "#a37524", "#a34f3e");

    //We received max 12 choc per day, so we create 6 datasets for hava maximum 6 stacked bar per day
    var max_choc_per_day = 12;
    for (let i = 0; i < max_choc_per_day; i++) {
      var newDataset = {
        data: [],
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
            chartInstance.data.datasets[index].backgroundColor =
              colorArr[index];
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
function drawChartTemperatureFromData(temperatureData, canvaID) {
  if (typeof temperatureData != "object") {
    temperatureData = JSON.parse(temperatureData);
  }
  if (isEmpty(temperatureData)) {
    drawNoDataAvailable(canvaID);
  } else {
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
      datasets: [
        {
          labels: date,
          lineTension: 0.1,
          backgroundColor: "rgba(167,105,0,0.4)",
          borderColor: "rgb(167, 105, 0)",
          borderCapStyle: "butt",
          borderDash: [],
          borderDashOffset: 0.0,
          borderJoinStyle: "miter",
          pointBorderColor: "white",
          //pointBackgroundColor: "black",
          pointBorderWidth: 1,
          pointHoverRadius: 8,
          //pointHoverBackgroundColor: "brown",
          pointHoverBorderColor: "yellow",
          pointHoverBorderWidth: 2,
          pointRadius: 4,
          pointHitRadius: 10,
          spanGaps: true,
          data: temperature,
        },
      ],
    };
    var canva_id = "#" + canvaID;
    var ctx = $(canva_id);

    var options = {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        xAxes: [
          {
            scaleLabel: {
              display: true,
              labelString: "Date",
            },
            ticks: {
              autoskip: true,
              maxTicksLimit: 15,
            },
          },
        ],
        yAxes: [
          {
            ticks: {
              beginAtZero: false,
            },
            scaleLabel: {
              display: true,
              labelString: "Temeprature (°C)",
            },
            //type: 'logarithmic',
          },
        ],
      },
      legend: {
        display: false,
      },
      title: {
        display: true,
        text: "Temperature relevé par le capteur en fonction du temps",
        fontSize: 18,
      },
      pan: {
        enabled: false,
        mode: "xy",
      },
      zoom: {
        enabled: false,
        mode: "xy",
      },
    };

    var chartInstance = new Chart(ctx, {
      type: "line",
      data: chartdata,
      options: options,
    });
  }
}

function drawChartHistoricalTemperature(temperatureData, canvaID) {
  if (typeof temperatureData != "object") {
    temperatureData = JSON.parse(temperatureData);
  }
  if (isEmpty(temperatureData)) {
    drawNoDataAvailable(canvaID);
  } else {
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
      datasets: [
        {
          labels: date,
          backgroundColor: "rgba(79,117,180,0.4)",
          borderColor: "rgba(49,85,144,1)",
          borderWidth: 1,
          lineTension: 0,
          data: temperature,
        },
      ],
    };
    var canva_id = "#" + canvaID;
    var ctx = $(canva_id);

    var options = {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        xAxes: [
          {
            scaleLabel: {
              display: true,
              labelString: "Date",
            },
            ticks: {
              autoskip: true,
              maxTicksLimit: 20,
            },
          },
        ],
        yAxes: [
          {
            ticks: {
              beginAtZero: false,
            },
            scaleLabel: {
              display: true,
              labelString: "Temeprature (°C)",
            },
            //type: 'logarithmic',
          },
        ],
      },
      legend: {
        display: false,
      },
      title: {
        display: true,
        text: "Température de référence (méteo locale) en fonction du temps",
        fontSize: 18,
      },
      pan: {
        enabled: false,
        mode: "xy",
      },
      zoom: {
        enabled: false,
        mode: "xy",
      },
    };

    var chartInstance = new Chart(ctx, {
      type: "line",
      data: chartdata,
      options: options,
    });
  }
}

function drawBothTemperature(temperatureSensors, temperatureWeather, canvaID) {
  if (typeof temperatureSensors != "object") {
    temperatureSensors = JSON.parse(temperatureSensors);
  }
  if (typeof temperatureWeather != "object") {
    temperatureWeather = JSON.parse(temperatureWeather);
  }
  if (isEmpty(temperatureSensors)) {
    drawNoDataAvailable(canvaID);
  } else {
    let temperature = [];
    let officialTemperature = [];
    let date = [];
    let officialDateTemperature = [];

    for (let i in temperatureSensors) {
      if (temperatureSensors[i].temperature < 100) {
        temperature.push(temperatureSensors[i].temperature);
        date.push(temperatureSensors[i].date_d);
      }
    }
    for (let i in temperatureWeather) {
      if (temperatureWeather[i].temperature < 100) {
        officialTemperature.push(temperatureWeather[i].temperature);
        officialDateTemperature.push(temperatureWeather[i].date_d);
        officialTemperature.push(null);
        officialDateTemperature.push(temperatureWeather[i].date_d);
      }
    }
    console.log("date :", date);
    console.log("Sensor temperature :", temperatureSensors);
    console.log("weather temperature :", temperatureWeather);
    console.log("Official temperature :", officialTemperature);
    console.log("Sensor temperature :", temperature);
    var chartdata = {
      labels: date,
      datasets: [
        {
          fill: true,
          label: "Température de la station météo",
          lineTension: 0.1,
          backgroundColor: "rgba(167,105,0,0.4)",
          borderColor: "rgb(167, 105, 0)",
          borderCapStyle: "butt",
          borderDash: [],
          borderDashOffset: 0.0,
          borderJoinStyle: "miter",
          pointBorderColor: "white",
          pointBackgroundColor: "black",
          pointBorderWidth: 1,
          pointHoverRadius: 8,
          pointHoverBackgroundColor: "brown",
          pointHoverBorderColor: "yellow",
          pointHoverBorderWidth: 2,
          pointRadius: 4,
          pointHitRadius: 10,
          spanGaps: true,
          xAxisID: "xAxis1",
          data: officialTemperature,
        },
        {
          fill: false,
          label: "Température du capteur",
          borderColor: "red",
          borderWidth: 1,
          lineTension: 0,
          borderCapStyle: "square",
          borderDash: [], // try [5, 15] for instance
          borderDashOffset: 0.0,
          borderJoinStyle: "miter",
          pointBorderColor: "black",
          pointBackgroundColor: "white",
          pointBorderWidth: 1,
          pointHoverRadius: 8,
          pointHoverBackgroundColor: "yellow",
          pointHoverBorderColor: "brown",
          pointHoverBorderWidth: 2,
          pointRadius: 4,
          pointHitRadius: 10,
          spanGaps: false,
          xAxisID: "xAxis2",
          data: temperature,
        },
      ],
    };
    var canva_id = "#" + canvaID;
    var ctx = $(canva_id);

    var options = {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        xAxes: [
          {
            id: "xAxis1",
            scaleLabel: {
              display: true,
              labelString: "Date",
            },
            ticks: {
              autoskip: true,
              maxTicksLimit: 20,
            },
          },
          {
            id: "xAxis2",
            autoskip: true,
            maxTicksLimit: 20,
          },
        ],
        yAxes: [
          {
            ticks: {
              beginAtZero: false,
            },
            scaleLabel: {
              display: true,
              labelString: "Temperature (°C)",
            },
            //type: 'logarithmic',
          },
        ],
      },
      legend: {
        display: true,
      },
      title: {
        display: true,
        text: "Temperature de référence",
        fontSize: 18,
      },
      pan: {
        enabled: false,
        mode: "xy",
      },
      zoom: {
        enabled: false,
        mode: "xy",
      },
    };

    var chartInstance = new Chart(ctx, {
      type: "line",
      data: chartdata,
      options: options,
    });
  }
}

/**
 * @desc Draw chart for displaying raw inclinometer data in function to date
 * @param json inclinometereData - data which contain inclinometer data and date
 * @return chart instance
 */
function drawChartInclinometerFromData(
  inclinometerData,
  canvaID = "canvas_inclinometre"
) {
  if (typeof inclinometerData != "object") {
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
    datasets: [
      {
        label: "X °",
        fill: false,
        backgroundColor: "blue",
        borderColor: "blue",
        data: nx,
      },
      {
        label: "Y °",
        fill: false,
        backgroundColor: "orange",
        borderColor: "orange",
        data: ny,
      },
      {
        label: "Z °",
        fill: false,
        backgroundColor: "green",
        borderColor: "green",
        data: nz,
      },
    ],
  };
  var canva_id = "#" + canvaID;
  var ctx = $(canva_id);

  var options = {
    responsive: true,
    maintainAspectRatio: true,
    scales: {
      scales: {
        xAxes: [
          {
            scaleLabel: {
              display: true,
              labelString: "Date",
            },
          },
        ],
        yAxes: [
          {
            ticks: {
              beginAtZero: false,
            },
            scaleLabel: {
              display: true,
              labelString: "Height (m)",
            },
          },
        ],
      },
      legend: {
        display: true,
      },
      title: {
        display: true,
        text: "Inclinometre en fonction du temps",
      },
      pan: {
        enabled: false,
        mode: "xy",
      },
      zoom: {
        enabled: false,
        mode: "xy",
      },
    },
  };

  var chartInstance = new Chart(ctx, {
    type: "line",
    data: chartdata,
    options: options,
  });
}

/**
 * @desc Draw chart for displaying angle XYZ data in function to date
 * @param json inclinometereData - data which contain inclinometer data and date
 * @return chart instance
 */
function drawChartAngleXYZFromData(
  inclinometerData,
  canvaID,
  excludeAngle = null
) {
  if (typeof inclinometerData != "object") {
    inclinometerData = JSON.parse(inclinometerData);
  }
  var includeX,
    includeY,
    includeZ = true;

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

    switch (excludeAngle) {
      case "x":
        var hiddenX = true;
        break;
      case "y":
        var hiddenY = true;
      case "z":
        var hiddenZ = true;
        break;
      default:
    }
    var ratio = 10;
    const avgX = computeAverage(angle_x);
    const avgY = computeAverage(angle_y);
    const avgZ = computeAverage(angle_z);
    var rangeHighAxisX = Math.max(parseInt(avgX), parseInt(avgY)) + ratio;
    var rangeLowAxisX = Math.min(parseInt(avgX), parseInt(avgY)) - ratio;
    var rangeHighAxisZ = parseInt(avgZ + ratio);
    var rangeLowAxisZ = parseInt(avgZ - ratio);

    var dragOptions = {
      animationDuration: 1000,
      borderColor: "rgba(225,225,225,0.3)",
      borderWidth: 5,
      backgroundColor: "rgb(225,225,225)",
    };

    var chartdata = {
      labels: date,
      datasets: [
        {
          label: "X °",
          fill: false,
          backgroundColor: "#20324B",
          borderColor: "#20324B",
          data: angle_x,
          yAxisID: "y-axis-1",
          hidden: hiddenX,
        },
        {
          label: "Y °",
          fill: false,
          backgroundColor: "orange",
          borderColor: "orange",
          data: angle_y,
          yAxisID: "y-axis-1",
          hidden: hiddenY,
        },
        {
          label: "Z °",
          fill: false,
          backgroundColor: "royalblue",
          borderColor: "royalblue",
          data: angle_z,
          yAxisID: "y-axis-2",
          hidden: hiddenZ,
        },
      ],
    };
    var canva_id = "#" + canvaID;
    var ctx = $(canva_id);

    var options = {
      responsive: true,
      hoverMode: "index",
      maintainAspectRatio: true,
      stacked: false,

      title: {
        display: true,
        text: "Valeurs d'inclinaison en fonction du temps",
        fontSize: 18,
      },
      scales: {
        yAxes: [
          {
            type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
            display: true,
            position: "left",
            id: "y-axis-1",
            scaleLabel: {
              display: true,
              labelString: "X° and Y°",
            },
            ticks: {
              min: rangeLowAxisX,
              max: rangeHighAxisX,
              beginAtZero: false,
              stepSize: 0.5,
              autoskip: true,
              maxTicksLimit: 10,
            },
          },
          {
            type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
            display: false,
            position: "right",
            id: "y-axis-2",
            scaleLabel: {
              display: true,
              labelString: "Z°",
            },
            ticks: {
              //TODO : change ratio automatic according to values
              min: rangeLowAxisZ,
              max: rangeHighAxisZ,
              beginAtZero: false,
              stepSize: 10,
              autoskip: true,
              maxTicksLimit: 10,
            },

            // grid line settings
            gridLines: {
              drawOnChartArea: false, // only want the grid lines for one axis to show up
            },
          },
        ],
        xAxes: [
          {
            ticks: {
              autoskip: true,
              maxTicksLimit: 15,
            },
          },
        ],
      },
      legend: {
        display: false, //This will do the task
      },
      // Container for pan options
      pan: {
        // Boolean to enable panning
        enabled: true,
        drag: dragOptions,
        // Panning directions. Remove the appropriate direction to disable
        // Eg. 'y' would only allow panning in the y direction
        mode: "y",
      },

      // Container for zoom options
      zoom: {
        // Boolean to enable zooming
        enabled: true,
        // Zooming directions. Remove the appropriate direction to disable
        // Eg. 'y' would only allow zooming in the y direction
        mode: "y",
        // Speed of zoom via mouse wheel
        // (percentage of zoom on a wheel event)
        speed: 0.1,
      },
    };

    var chartInstance = new Chart(ctx, {
      type: "line",
      data: chartdata,
      options: options,
    });

    return chartInstance;
  }
}

var isFirstDateSuperiorToSecondDate = function (date1, date2) {
  date1 = moment(date1, "DD/MM/YYYY HH:mm:ss").format("ll");
  console.log("isFirstDateSuperiorToSecondDate -> date1", date1);
  date2 = moment(date2, "DD/MM/YYYY HH:mm:ss").format("ll");
  console.log("isFirstDateSuperiorToSecondDate -> date2", date2);

  var greater = moment(date1).isSameOrAfter(date2, "day");
  return greater;
};

function drawVariationChartAngleXYZFromData(
  inclinometerData,
  canvaID,
  percentage = true,
  excludeAngle = null
) {
  if (typeof inclinometerData != "object") {
    inclinometerData = JSON.parse(inclinometerData);
  }

  switch (excludeAngle) {
    case "x":
      var hiddenX = true;
      break;
    case "y":
      var hiddenY = true;
    case "z":
      var hiddenZ = true;
      break;
    default:
  }

  let title = "";
  let label = "";
  if (percentage) {
    title =
      "Pourcentage de variation de l'inclinaison au fil du temps depuis le jour d'installation";
    label = "Variation %";
  } else {
    title =
      "Variation absolue de l'inclinaison au fil du temps depuis le jour d'installation";
    label = "Variation absolue X et Y °";
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

  //check if the date is in DESC order
  /*var superior = isFirstDateSuperiorToSecondDate(
    inclinometerData[0].date,
    inclinometerData[inclinometerData.length - 1].date
  );
  console.log("Before inclinometerData", inclinometerData);
  if (superior) {
    inclinometerData = inclinometerData.reverse();
    var test = inclinometerData.info.reverse();
    console.log("After ReverseinclinometerData", test);
  }*/

  const avgX = computeAverage(variation_angle_x);
  const avgY = computeAverage(variation_angle_y);

  var rangeHighAxisX = Math.max.apply(Math, variation_angle_x) * 2;
  var rangeLowAxisX = Math.min.apply(Math, variation_angle_x) * 2;

  var chartdata = {
    labels: date,
    datasets: [
      {
        label: "X °",
        fill: false,
        backgroundColor: "#20324B",
        borderColor: "#20324B",
        data: variation_angle_x,
        yAxisID: "y-axis-0",
        hidden: hiddenX,
      },
      {
        label: "Y °",
        fill: false,
        backgroundColor: "orange",
        borderColor: "orange",
        data: variation_angle_y,
        yAxisID: "y-axis-0",
        hidden: hiddenY,
      },
      {
        label: "Z °",
        fill: false,
        backgroundColor: "royalblue",
        borderColor: "royalblue",
        data: variation_angle_z,
        yAxisID: "y-axis-0",
        hidden: hiddenZ,
      },
    ],
  };
  var canva_id = "#" + canvaID;
  var ctx = $(canva_id);

  var options = {
    responsive: true,
    hoverMode: "index",
    maintainAspectRatio: true,

    title: {
      display: true,
      text: title,
      fontSize: 18,
    },
    scales: {
      yAxes: [
        {
          id: "y-axis-0",
          gridLines: {
            display: true,
          },
          ticks: {
            beginAtZero: false,
            min: rangeLowAxisX,
            max: rangeHighAxisX,
          },
          scaleLabel: {
            display: true,
            labelString: label,
          },
        },
      ],
      xAxes: [
        {
          ticks: {
            autoskip: true,
            maxTicksLimit: 15,
          },
        },
      ],
    },
    legend: {
      display: false, //This will do the task
    },
    pan: {
      enabled: true,
      mode: "y",
    },
    zoom: {
      enabled: true,
      mode: "y",
    },
  };

  var chartInstance = new Chart(ctx, {
    type: "line",
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
  //console.log("drawChartSpectreFromData -> spectreData", spectreData);
  var subspectreArr = [];
  var minFreqData = [];
  var maxFreqData = [];
  var resolutionData = [];
  var dateDataArr = [];

  dateTime = spectreData["date_time"];

  var min_freq_initial = spectreData["min_freq"];
  var max_freq = spectreData["max_freq"];

  var index_start = 2;
  var index_stop = 4;
  //Array for storing the data for chart
  var dataChartArr = [];
  //Loop over all subspectres (5 in total that compose the whole spectre)
  var sizeSpectres = Object.keys(spectreData).length;

  count_resolution_1 = 0;
  for (var i = 1; i <= 5; i++) {
    nameSub = "subspectre_" + String(i);

    if (spectreData.hasOwnProperty(nameSub)) {
      subspectreArr.push(spectreData[nameSub]);

      var resolution = parseInt(spectreData[nameSub]["resolution"]);
      var min_freq = parseInt(spectreData[nameSub]["min_freq"]);
      var max_freq = parseInt(spectreData[nameSub]["max_freq"]);
      if (resolution == 1) {
        count_resolution_1 = true;
      }
      //console.log("nouveau sub");
      //Loop over data of each subspectre
      for (var j = 0; j < spectreData[nameSub]["data"].length / 2; j++) {
        var y_data_amplitudeArr = accumulatedTable32(
          hex2dec(
            spectreData[nameSub]["data"].substring(index_start, index_stop)
          )
        );

        //because we need the first value of min_freq
        if (j > 0) {
          min_freq = min_freq + resolution;
        }
        if (j < spectreData[nameSub]["data"].length / 2 - 1) {
          var obj = {
            x: min_freq,
            y: y_data_amplitudeArr,
          };

          dataChartArr.push(obj);
        }

        index_start += 2;
        index_stop += 2;
      }

      index_start = 2;
      index_stop = 4;
    }
  }
  //console.log(dataChartArr);

  var canva_id = "#" + canvaID;
  var ctx = document.getElementById(canvaID).getContext("2d");

  /*** Gradient ***/
  var gradient = ctx.createLinearGradient(0, 0, 0, 400);
  gradient.addColorStop(0, "rgba(250,174,50,1)");
  gradient.addColorStop(1, "rgba(250,174,50,0)");
  /***************/
  var title = "Spectre du " + dateTime;

  var chartdata = {
    datasets: [
      {
        showLine: true,
        borderColor: "#3e95cd",
        backgroundColor: gradient,
        pointBackgroundColor: "#3e95cd",
        data: dataChartArr,
      },
    ],
  };
  var options = {
    responsive: true,
    maintainAspectRatio: true,
    scales: {
      display: true,
      xAxes: [
        {
          gridLines: {
            display: true,
          },
          ticks: {
            beginAtZero: false,
            min: min_freq_initial,
            max: max_freq,
          },
          scaleLabel: {
            display: true,
            labelString: "Frequence (Hz)",
          },
        },
      ],
      yAxes: [
        {
          gridLines: {
            display: true,
          },
          ticks: {
            beginAtZero: false,
          },
          scaleLabel: {
            display: true,
            labelString: "AmplitudeArr (mg)",
          },
        },
      ],
    },
    legend: {
      display: false,
    },
    title: {
      display: true,
      text: title,
    },
    pan: {
      enabled: true,
      mode: "y",
    },
    zoom: {
      enabled: true,
      mode: "y",
    },
  };
  var chartInstance = new Chart(ctx, {
    type: "scatter",
    data: chartdata,
    options: options,
  });
}

function drawChartSpeedVariationFromData(
  data,
  canvaID = "chartVitesseInclinometer"
) {
  //console.log(data);
  if (typeof chocData != "object") {
    data = JSON.parse(data);
  }

  if (isEmpty(data)) {
    drawNoDataAvailable(canvaID);
  } else {
    var variation_speed_xy = [];
    var date = [];

    for (var i in data) {
      //console.log(inclinometerData[i]);
      variation_speed_xy.push(data[i].delta_xy_cm);
      date.push(data[i].date);
    }

    var max_déplacement = Math.max.apply(null, variation_speed_xy);
    var min_déplacement = Math.min.apply(null, variation_speed_xy);
    var high_range_max = max_déplacement + 1;
    var low_range_max = min_déplacement + 1;

    var chartdata = {
      labels: date,
      datasets: [
        {
          label: "Deplacement",
          backgroundColor: "rgba(255,99,132,0.2)",
          borderColor: "rgba(255,99,132,1)",
          borderWidth: 2,
          //showLine: true,
          hoverBackgroundColor: "rgba(255,99,132,0.4)",
          hoverBorderColor: "rgba(255,99,132,1)",
          data: variation_speed_xy,
        },
      ],
    };
    var canva_id = "#" + canvaID;
    var ctx = $(canva_id);

    var options = {
      responsive: true,
      maintainAspectRatio: true,
      title: {
        display: false,
        text: "Vitesse de déplacement",
      },
      scales: {
        xAxes: [
          {
            scaleLabel: {
              display: true,
              labelString: "Date",
            },
            ticks: {
              autoskip: true,
              source: date,
              maxTicksLimit: 20,
            },
          },
        ],
        yAxes: [
          {
            ticks: {
              min: -1,
              max: high_range_max,
              beginAtZero: false,
              stepSize: 0.5,
              autoskip: true,
              maxTicksLimit: 20,
            },
            scaleLabel: {
              display: true,
              labelString: "Deplacement (cm)",
            },
          },
        ],
      },
      legend: {
        display: false,
      },
      pan: {
        enabled: true,
        mode: "xy",
      },
      zoom: {
        enabled: true,
        mode: "xy",
      },
    };

    var chartInstance = new Chart(ctx, {
      type: "line",
      data: chartdata,
      options: options,
    });
  }
}

function drawChartDirectionFromData(
  directionData,
  canvaID = "chartDirectionInclinometer",
  settings = []
) {
  if (typeof directionData != "object") {
    directionData = JSON.parse(directionData);
  }

  //console.log("directionData", directionData);
  //console.log("Setting :", settings);
  firstLevelXThresh = searchJsonInArray(
    settings,
    "name_setting",
    "first_inclinationX_thresh"
  );
  firstLevelYThresh = searchJsonInArray(
    settings,
    "name_setting",
    "first_inclinationY_thresh"
  );
  secondLevelYThresh = searchJsonInArray(
    settings,
    "name_setting",
    "second_inclinationY_thresh"
  );
  thirdLevelYThresh = searchJsonInArray(
    settings,
    "name_setting",
    "third_inclinationY_thresh"
  );

  if (firstLevelXThresh != null) {
    if (firstLevelXThresh.activated == 1) {
      var firstLevelXThresh = firstLevelXThresh.value;
    }
  }
  if (firstLevelYThresh != null) {
    if (firstLevelYThresh.activated == 1) {
      var firstLevelYThresh = firstLevelYThresh.value;
    }
  }
  if (secondLevelYThresh != null) {
    if (secondLevelYThresh.activated == 1) {
      var secondLevelYThresh = secondLevelYThresh.value;
    }
  }
  if (thirdLevelYThresh != null) {
    if (thirdLevelYThresh.activated == 1) {
      var thirdLevelYThresh = thirdLevelYThresh.value;
    }
  }

  if (isEmpty(directionData)) {
    drawNoDataAvailable(canvaID);
  } else {
    var dataChartArr = [];
    var dataRegressionArr = [];
    var val1Reg = 0;
    var val2Reg = 0;
    var deltaXArr = [];
    var deltaYArr = [];
    var mapDirectionDateTime = new Map();
    for (var i in directionData) {
      let date_time = directionData[i].date;
      deltaXArr.push(directionData[i].delta_x);
      deltaYArr.push(directionData[i].delta_y);
      var obj = {
        x: directionData[i].delta_x,
        y: directionData[i].delta_y,
      };
      var key = directionData[i].delta_x * directionData[i].delta_y;
      dataChartArr.push(obj);
      mapDirectionDateTime.set(key, date_time);
    }

    ratioAxisX = computeRatioAxis(deltaXArr);
    ratioAxisY = computeRatioAxis(deltaYArr);

    var ctx = document.getElementById(canvaID).getContext("2d");
    // Define the data
    var pointRadius = [];
    var pointBackgroundColors = [];
    var borderColor = [];
    var data = {
      datasets: [
        {
          type: "scatter",
          label: "Direction des déplacements",
          pointBackgroundColor: pointBackgroundColors,
          borderColor: borderColor,
          borderWidth: 2,
          //showLine: true,
          hoverBackgroundColor: "rgba(255,99,132,0.4)",
          hoverBorderColor: "rgba(255,99,132,1)",
          pointRadius: pointRadius,
          data: dataChartArr,
        },
      ],
    }; // Add data values to array
    // End Defining data
    var options = {
      maintainAspectRatio: false,
      responsive: false,
      tooltips: {
        callbacks: {
          title: function (tooltipItem, data) {
            //let date = data.labels[tooltipItem[0].index];
            //console.log("tooltipItem.value : ", tooltipItem);
            let date = mapDirectionDateTime.get(
              tooltipItem[0].value * tooltipItem[0].label
            );
            //console.log("Date : ", date)
            return "Le " + date;
          },
          label: function (tooltipItem, data) {
            return "X = " + tooltipItem.xLabel + " Y =" + tooltipItem.yLabel;
          },
          afterLabel: function (tooltipItem, data) {
            //let hour = mapPowerDateTime.get(tooltipItem['value']).split(" ")[1];
            //return "Heure : " + hour;
          },
        },
        backgroundColor: "#FFF",
        titleFontSize: 15,
        titleFontColor: "#233754",
        bodyFontColor: "#000",
        bodyFontSize: 14,
        displayColors: false,
      },
      title: {
        display: false,
        text: "Direction et inclinaison",
      },
      legend: {
        display: false,
      },
      scales: {
        yAxes: [
          {
            id: "y-axis-0",
            gridLines: {
              display: true,
              color: "rgba(255,99,132,0.2)",
            },
            scaleLabel: {
              display: true,
              labelString: "Delta Y (cm)",
              fontSize: 20,
            },
            ticks: {
              min: -10,
              max: +10,
              beginAtZero: false,
              stepSize: 0.5,
              autoskip: true,
            },
          },
        ],
        xAxes: [
          {
            id: "x-axis-0",
            gridLines: {
              display: true,
              color: "rgba(255,99,132,0.2)",
            },
            scaleLabel: {
              display: true,
              labelString: "Delta X (cm)",
              fontSize: 20,
            },
            ticks: {
              min: -10,
              max: +10,
              beginAtZero: false,
              stepSize: 0.5,
              autoskip: true,
            },
          },
        ],
      },
      pan: {
        enabled: true,
        mode: "xy",
      },
      zoom: {
        enabled: true,
        mode: "xy",
      },
      annotation: {
        events: ["click"],
        drawTime: "afterDatasetsDraw",
        annotations: [
          /*Trendline
      {
        type: 'line',
        mode: 'horizontal',
        scaleID: 'y-axis-0',
        value: val1Reg,
        endValue: val2Reg,
        borderColor: 'rgb(75, 192, 192)',
        borderWidth: 4,
        label: {
          enabled: true,
          content: 'Trendline',

        },
      },*/
          //Vertical axis
          {
            id: "hline1",
            type: "line",
            mode: "vertical",
            scaleID: "x-axis-0",
            value: 0,
            borderColor: "rgba(103, 128, 159, 0.7)",
            label: {
              enabled: true,
              content: "Y",
              position: "top",
              backgroundColor: "rgba(103, 128, 159, 0.7)",
            },
          },
          //horizontal axis
          {
            id: "hline2",
            type: "line",
            mode: "horizontal",
            scaleID: "y-axis-0",
            value: 0,
            borderColor: "rgba(103, 128, 159, 0.7)",
            label: {
              backgroundColor: "rgba(103, 128, 159, 0.7)",
              content: "X",
              position: "right",
              enabled: true,
            },
          },
          //first level Y thresh
          {
            id: "hline3",
            type: "line",
            mode: "horizontal",
            scaleID: "y-axis-0",
            value: firstLevelYThresh,
            borderColor: "rgba(240, 52, 52, 0.8)",
            label: {
              backgroundColor: "rgba(240, 52, 52, 0.8)",
              content: "First level Y",
              position: "top",
              enabled: true,
            },
          },
          //second level Y thresh
          {
            id: "hline4",
            type: "line",
            mode: "horizontal",
            scaleID: "y-axis-0",
            value: secondLevelYThresh,
            borderColor: "rgba(240, 52, 52, 0.8)",
            label: {
              backgroundColor: "rgba(240, 52, 52, 0.8)",
              content: "Second level Y",
              position: "top",
              enabled: true,
            },
          },
          //third level Y thresh
          {
            id: "hline5",
            type: "line",
            mode: "horizontal",
            scaleID: "y-axis-0",
            value: thirdLevelYThresh,
            borderColor: "rgba(240, 52, 52, 0.8)",
            label: {
              backgroundColor: "rgba(240, 52, 52, 0.8)",
              content: "Third level Y",
              position: "top",
              enabled: true,
            },
          },
          //first level X thresh
          {
            id: "hline6",
            type: "line",
            mode: "vertical",
            scaleID: "x-axis-0",
            value: firstLevelXThresh,
            borderColor: "rgba(240, 52, 52, 0.8)",
            label: {
              backgroundColor: "rgba(240, 52, 52, 0.8)",
              content: "First level X",
              position: "top",
              enabled: true,
            },
          },
        ],
      },
    };

    // End Defining data
    var myChart = new Chart(ctx, {
      type: "scatter",
      data: data,
      options: options,
    });
    //console.log(myChart);
    //Change color and size of last point
    for (i = 0; i < myChart.data.datasets[0].data.length - 4; i++) {
      pointBackgroundColors.push("rgba(103, 128, 159, 0.7)");
      borderColor.push("rgba(103, 128, 159, 1)");
      pointRadius.push(i * 0.01);
    }
    for (
      i = myChart.data.datasets[0].data.length - 4;
      i < myChart.data.datasets[0].data.length;
      i++
    ) {
      if (i < myChart.data.datasets[0].data.length - 1) {
        pointBackgroundColors.push("rgba(103, 128, 159, 0.7)");
        borderColor.push("rgba(103, 128, 159, 1)");
        pointRadius.push(i * 0.05);
      } else {
        pointBackgroundColors.push("rgba(255,99,132,1)");
        pointRadius.push(7);
      }
    }

    for (var i = 0; i < myChart.data.datasets[0].data.length; i++) {
      //pointRadius.push(i);
    }
    myChart.update();

    /*
    function addData(chart, label, data) {
      chart.data.labels.push(label);
      chart.data.datasets.forEach((dataset) => {
        dataset.data.push(data);
      });
      chart.update();
    }
    function removeData(chart) {
      chart.data.labels.pop();
      chart.data.datasets.forEach((dataset) => {
        dataset.data.pop();
      });
      chart.update();
    }
    const sleep = milliseconds => {
      return new Promise(resolve => setTimeout(resolve, milliseconds));
    };

    /*Function to update the bar chart
    function updateBarGraph(chart, data) {
      chart.data.datasets.pop();

      chart.data.datasets.push({
        data: []
      });
      for (i = 0; i < data.length; i++) {
        var obj = {
          x: data[i].delta_x,
          y: data[i].delta_y
        };
        sleep(2000).then(() => {
          chart.data.datasets[0].data[i] = obj;
          chart.update();
        });

      }

    }

    /*Updating the bar chart with updated data in every second.
    setInterval(function () {
      updateBarGraph(myChart, directionData);
    }, 1000);
*/
  }
}

function drawChartDirectionFromDataWithRegression(
  directionData,
  regressionArr = null,
  canvaID = "chartDirectionInclinometer",
  settings = []
) {
  if (typeof directionData != "object") {
    directionData = JSON.parse(directionData);
  }

  //console.log("directionData", directionData);
  //console.log("Setting :", settings);
  firstLevelXThresh = searchJsonInArray(
    settings,
    "name_setting",
    "first_inclinationX_thresh"
  );
  firstLevelYThresh = searchJsonInArray(
    settings,
    "name_setting",
    "first_inclinationY_thresh"
  );
  secondLevelYThresh = searchJsonInArray(
    settings,
    "name_setting",
    "second_inclinationY_thresh"
  );
  thirdLevelYThresh = searchJsonInArray(
    settings,
    "name_setting",
    "third_inclinationY_thresh"
  );

  if (firstLevelXThresh != null) {
    if (firstLevelXThresh.activated == 1) {
      var firstLevelXThresh = firstLevelXThresh.value;
    }
  }
  if (firstLevelYThresh != null) {
    if (firstLevelYThresh.activated == 1) {
      var firstLevelYThresh = firstLevelYThresh.value;
    }
  }
  if (secondLevelYThresh != null) {
    if (secondLevelYThresh.activated == 1) {
      var secondLevelYThresh = secondLevelYThresh.value;
    }
  }
  if (thirdLevelYThresh != null) {
    if (thirdLevelYThresh.activated == 1) {
      var thirdLevelYThresh = thirdLevelYThresh.value;
    }
  }

  if (isEmpty(directionData)) {
    drawNoDataAvailable(canvaID);
  } else {
    var dataChartArr = [];
    var dataRegressionArr = [];
    var val1Reg = 0;
    var val2Reg = 0;
    var deltaXArr = [];
    var deltaYArr = [];
    var mapDirectionDateTime = new Map();
    for (var i in directionData) {
      let date_time = directionData[i].date;
      deltaXArr.push(directionData[i].delta_x);
      deltaYArr.push(directionData[i].delta_y);
      var obj = {
        x: directionData[i].delta_x,
        y: directionData[i].delta_y,
      };
      var key = directionData[i].delta_x * directionData[i].delta_y;
      dataChartArr.push(obj);
      mapDirectionDateTime.set(key, date_time);
    }

    if (regressionArr) {
      if (typeof regressionArr != "object") {
        regressionArr = JSON.parse(regressionArr);
      }
      const clean_data = dataChartArr.map(({ x, y }) => {
        return [x, y]; // we need a list of [[x1, y1], [x2, y2], ...]
      });

      console.log("clean_data", clean_data);
      console.log("regression", regressionArr);
      console.log("Type", typeof regressionArr);
      const result = regression.linear([clean_data]);
      console.log("result", result);
      const gradient = result.equation[0];
      const yIntercept = result.equation[1];
      for (var i in directionData) {
        var obj = {
          x: directionData[i].delta_x,

          y:
            directionData[i].delta_x * regressionArr["slope"] +
            regressionArr["intercept"],
        };

        dataRegressionArr.push(obj);
      }
      val1RegX = -3;
      val2RegX = 5;
      val1RegY = val1RegX * regressionArr["slope"] + regressionArr["intercept"];
      console.log("val1Reg", val1Reg);
      val2RegY = val2RegX * regressionArr["slope"] + regressionArr["intercept"];
      console.log("val2Reg", val2Reg);
      console.log("Youhou dataRegressionArr", dataRegressionArr);
      console.log("dataRegressionArr value init", dataRegressionArr[0]["x"]);
    }

    ratioAxisX = computeRatioAxis(deltaXArr);
    ratioAxisY = computeRatioAxis(deltaYArr);

    var ctx = document.getElementById(canvaID).getContext("2d");
    // Define the data
    var pointRadius = [];
    var pointBackgroundColors = [];
    var borderColor = [];
    var data = {
      datasets: [
        {
          type: "scatter",
          label: "Direction des déplacements",
          pointBackgroundColor: pointBackgroundColors,
          borderColor: borderColor,
          borderWidth: 2,
          //showLine: true,
          hoverBackgroundColor: "rgba(255,99,132,0.4)",
          hoverBorderColor: "rgba(255,99,132,1)",
          pointRadius: pointRadius,
          data: dataChartArr,
        },
        {
          type: "line",
          label: "Trendline",
          borderWidth: 2,
          //showLine: true,
          data: [
            {
              x: val1RegX,
              y: val1RegY,
            },
            {
              x: val2RegX,
              y: val2RegY,
            },
          ],
        },
      ],
    }; // Add data values to array
    // End Defining data
    var options = {
      maintainAspectRatio: false,
      responsive: false,
      tooltips: {
        callbacks: {
          title: function (tooltipItem, data) {
            //let date = data.labels[tooltipItem[0].index];
            //console.log("tooltipItem.value : ", tooltipItem);
            let date = mapDirectionDateTime.get(
              tooltipItem[0].value * tooltipItem[0].label
            );
            //console.log("Date : ", date)
            return "Le " + date;
          },
          label: function (tooltipItem, data) {
            return "X = " + tooltipItem.xLabel + " Y =" + tooltipItem.yLabel;
          },
          afterLabel: function (tooltipItem, data) {
            //let hour = mapPowerDateTime.get(tooltipItem['value']).split(" ")[1];
            //return "Heure : " + hour;
          },
        },
        backgroundColor: "#FFF",
        titleFontSize: 15,
        titleFontColor: "#233754",
        bodyFontColor: "#000",
        bodyFontSize: 14,
        displayColors: false,
      },
      title: {
        display: false,
        text: "Direction et inclinaison",
      },
      legend: {
        display: false,
      },
      scales: {
        yAxes: [
          {
            id: "y-axis-0",
            gridLines: {
              display: true,
              color: "rgba(255,99,132,0.2)",
            },
            scaleLabel: {
              display: true,
              labelString: "Delta Y (cm)",
              fontSize: 20,
            },
            ticks: {
              min: -10,
              max: +10,
              beginAtZero: false,
              stepSize: 0.5,
              autoskip: true,
            },
          },
        ],
        xAxes: [
          {
            id: "x-axis-0",
            gridLines: {
              display: true,
              color: "rgba(255,99,132,0.2)",
            },
            scaleLabel: {
              display: true,
              labelString: "Delta X (cm)",
              fontSize: 20,
            },
            ticks: {
              min: -10,
              max: +10,
              beginAtZero: false,
              stepSize: 0.5,
              autoskip: true,
            },
          },
        ],
      },
      pan: {
        enabled: true,
        mode: "xy",
      },
      zoom: {
        enabled: true,
        mode: "xy",
      },
      annotation: {
        events: ["click"],
        drawTime: "afterDatasetsDraw",
        annotations: [
          {
            id: "hline1",
            type: "line",
            mode: "vertical",
            scaleID: "x-axis-0",
            value: 0,
            borderColor: "rgba(103, 128, 159, 0.7)",
            label: {
              enabled: true,
              content: "Y",
              position: "top",
              backgroundColor: "rgba(103, 128, 159, 0.7)",
            },
          },
          //horizontal axis
          {
            id: "hline2",
            type: "line",
            mode: "horizontal",
            scaleID: "y-axis-0",
            value: 0,
            borderColor: "rgba(103, 128, 159, 0.7)",
            label: {
              backgroundColor: "rgba(103, 128, 159, 0.7)",
              content: "X",
              position: "right",
              enabled: true,
            },
          },
          //first level Y thresh
          {
            id: "hline3",
            type: "line",
            mode: "horizontal",
            scaleID: "y-axis-0",
            value: firstLevelYThresh,
            borderColor: "rgba(240, 52, 52, 0.8)",
            label: {
              backgroundColor: "rgba(240, 52, 52, 0.8)",
              content: "First level Y",
              position: "top",
              enabled: true,
            },
          },
          //second level Y thresh
          {
            id: "hline4",
            type: "line",
            mode: "horizontal",
            scaleID: "y-axis-0",
            value: secondLevelYThresh,
            borderColor: "rgba(240, 52, 52, 0.8)",
            label: {
              backgroundColor: "rgba(240, 52, 52, 0.8)",
              content: "Second level Y",
              position: "top",
              enabled: true,
            },
          },
          //third level Y thresh
          {
            id: "hline5",
            type: "line",
            mode: "horizontal",
            scaleID: "y-axis-0",
            value: thirdLevelYThresh,
            borderColor: "rgba(240, 52, 52, 0.8)",
            label: {
              backgroundColor: "rgba(240, 52, 52, 0.8)",
              content: "Third level Y",
              position: "top",
              enabled: true,
            },
          },
          //first level X thresh
          {
            id: "hline6",
            type: "line",
            mode: "vertical",
            scaleID: "x-axis-0",
            value: firstLevelXThresh,
            borderColor: "rgba(240, 52, 52, 0.8)",
            label: {
              backgroundColor: "rgba(240, 52, 52, 0.8)",
              content: "First level X",
              position: "top",
              enabled: true,
            },
          },
        ],
      },
    };

    // End Defining data
    var myChart = new Chart(ctx, {
      type: "scatter",
      data: data,
      options: options,
    });
    //console.log(myChart);
    //Change color and size of last point
    for (i = 0; i < myChart.data.datasets[0].data.length - 4; i++) {
      pointBackgroundColors.push("rgba(103, 128, 159, 0.7)");
      borderColor.push("rgba(103, 128, 159, 1)");
      pointRadius.push(i * 0.01);
    }
    for (
      i = myChart.data.datasets[0].data.length - 4;
      i < myChart.data.datasets[0].data.length;
      i++
    ) {
      if (i < myChart.data.datasets[0].data.length - 1) {
        pointBackgroundColors.push("rgba(103, 128, 159, 0.7)");
        borderColor.push("rgba(103, 128, 159, 1)");
        pointRadius.push(i * 0.05);
      } else {
        pointBackgroundColors.push("rgba(255,99,132,1)");
        pointRadius.push(7);
      }
    }

    for (var i = 0; i < myChart.data.datasets[0].data.length; i++) {
      //pointRadius.push(i);
    }
    myChart.update();
  }
}

/**
 * @desc Draw chart for displaying choc data (amplitudeArr in function of time)
 * @param json chocData - data which contain choc data (amplitudeArr 1, time 1, amplitudeArr 2, time 2)
 * @return chart instance
 */
function drawChartChocFromData(chocData, canvaID = "canvas_choc") {
  if (typeof chocData != "object") {
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
    y: 0,
  };
  dataChartArr.push(obj);
  for (var i = 0; i < 2; i++) {
    var obj_1 = {
      x: timeDataArr[i],
      y: amplitudeArr[i],
    };
    dataChartArr.push(obj_1);
  }
  var obj_2 = {
    x: timeDataArr[0] + timeDataArr[1],
    y: 0,
  };
  dataChartArr.push(obj_2);

  var chartdata = {
    datasets: [
      {
        data: dataChartArr,
        showLine: true,
      },
    ],
  };

  var title = "Last choc which occur on " + chocData[0].date_d;
  var canva_id = "#" + canvaID;
  var ctx = $(canva_id);

  var barGraph = new Chart(ctx, {
    type: "scatter",
    data: chartdata,
    options: {
      scales: {
        display: true,
        xAxes: [
          {
            ticks: {
              beginAtZero: true,
              min: 0,
              max: timeDataArr[0] + timeDataArr[1],
              step: 0.01,
            },
            type: "linear",
            scaleLabel: {
              display: true,

              labelString: "Time (s)",
            },
          },
        ],
        yAxes: [
          {
            ticks: {
              beginAtZero: true,
            },
            scaleLabel: {
              display: true,
              labelString: "AmplitudeArr (g)",
            },
          },
        ],
      },
      legend: {
        display: false,
      },
      title: {
        display: true,
        text: title,
      },
    },
  });
}

/**
 * @desc Draw chart for displaying subspectre
 * @param json subspectreData - data which contain subspectre data
 * @return chart instance
 */
function drawChartSubSpectreFromData(
  subspectreData,
  canvaID = "canvas_subspectre"
) {
  if (typeof subspectreData != "object") {
    subspectreData = JSON.parse(subspectreData);
  }

  subspectre_hex = subspectreData[0].payload;
  min_freq = parseInt(subspectreData[0].min_freq);
  max_freq = parseInt(subspectreData[0].max_freq);
  resolution = parseInt(subspectreData[0].resolution);
  subspectre_number = subspectreData[0].subspectre_number;

  var index_start = 2;
  var index_stop = 4;
  var new_sub = "";
  var dataChartArr = [];

  var min_freq_initial = min_freq;

  for (var i = 0; i < subspectre_hex.length / 2; i++) {
    var y_data_amplitudeArr = hex2dec(
      subspectre_hex.substring(index_start, index_stop)
    );

    if (i > 0) {
      min_freq = min_freq + resolution;
    }

    if (i < subspectre_hex.length / 2 - 1) {
      var obj = {
        x: min_freq,
        y: y_data_amplitudeArr,
      };
      dataChartArr.push(obj);
    }
    index_start += 2;
    index_stop += 2;
  }
  var title =
    "Spectre | Resolution = " +
    String(resolution) +
    "Hz | Sous spectre = " +
    String(subspectre_number);

  var chartdata = {
    datasets: [
      {
        data: dataChartArr,
        showLine: true,
      },
    ],
  };

  var canva_id = "#" + canvaID;
  var ctx = $(canva_id);

  var barGraph = new Chart(ctx, {
    type: "scatter",
    data: chartdata,
    options: {
      scales: {
        display: true,
        xAxes: [
          {
            ticks: {
              beginAtZero: false,
              min: min_freq_initial,
              max: max_freq,
            },
            scaleLabel: {
              display: true,
              labelString: "Frequence (Hz)",
            },
          },
        ],
        yAxes: [
          {
            ticks: {
              beginAtZero: false,
            },
            scaleLabel: {
              display: true,
              labelString: "AmplitudeArr (mg)",
            },
          },
        ],
      },
      legend: {
        display: false,
      },
      title: {
        display: true,
        text: title,
      },
    },
  });
}

/**
 * @desc show map with sensors data on it
 * @param array data - data which contain sensors info to display on the map
 * @return map instance
 */
function showMap(data) {
  console.log("Data :", data);
  data = JSON.parse(data);
  //console.log("Data show map : ", data);
  //For centering the map around France at initialization
  var lat_france = 46.2276;
  var long_france = 2.2137;

  var map = L.map("map", {
    attributionControl: false,
  }).setView([lat_france, long_france], 5.3);

  L.tileLayer(
    "https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibGlyb25lIiwiYSI6ImNrMmdrdmo1czAwOXozb29oc3NybXJqNTcifQ.sbQPrGi1n7lsCtlCvojTBA",
    {
      maxZoom: 18,
      attribution: '<a href="https://flod.ai">Flod Sentiel</a>',
      id: "mapbox.streets",
    }
  ).addTo(map);

  L.control
    .custom({
      position: "topright",
      content:
        '<button type="button" class="btn btn-default">' +
        '    <i class="fa fa-crosshairs"></i>' +
        "</button>" +
        '<button type="button" class="btn btn-danger">' +
        '    <i class="fa fa-times"></i>' +
        "</button>" +
        '<button type="button" class="btn btn-success">' +
        '    <i class="fa fa-check"></i>' +
        "</button>" +
        '<button type="button" class="btn btn-warning">' +
        '    <i class="fa fa-exclamation-triangle"></i>' +
        "</button>",
      classes: "btn-group-vertical btn-group-sm",
      style: {
        margin: "30px",
        padding: "0px 0 0 0",
        cursor: "pointer",
        height: "100px",
      },
      datas: {
        foo: "bar",
      },
      events: {
        click: function (data) {
          console.log("wrapper div element clicked");
          console.log(data);
        },
        dblclick: function (data) {
          console.log("wrapper div element dblclicked");
          console.log(data);
        },
        contextmenu: function (data) {
          console.log("wrapper div element contextmenu");
          console.log(data);
        },
      },
    })
    .addTo(map);

  for (var i = 0; i < data.length; i++) {
    var obj = data[i];

    if (obj.longitude_sensor != null || obj.latitude_sensor != null) {
      longitude_sensor = obj.longitude_sensor;
      latitude_sensor = obj.latitude_sensor;

      device_number = obj.device_number;
      site = obj.site;
      equipement = obj.equipement;
      line_HT = obj.transmission_line_name;

      L.marker([latitude_sensor, longitude_sensor])
        .addTo(map)
        .bindPopup(
          "<b>Capteur : " +
            device_number +
            "</b><br />" +
            site +
            " | " +
            equipement +
            " | " +
            line_HT
        )
        .openPopup();
    }
  }
}

function addData(chart, label, color, data) {
  chart.data.datasets.push({
    label: label,
    backgroundColor: color,
    data: data,
  });
  chart.update();
}

function drawPrevision(site) {
  window.weatherWidgetConfig = window.weatherWidgetConfig || [];
  window.weatherWidgetConfig.push({
    selector: ".weatherForecastWidget",
    apiKey: "052NMBEM22M3WRCGVFD7209PV",
    location: site,
    unitGroup: "metric",
  });
  (function () {
    var d = document,
      s = d.createElement("script");
    s.src = "public/js/weather-forecast-widget.min.js";
    s.setAttribute("data-timestamp", +new Date());
    (d.head || d.body).appendChild(s);
  })();
}
