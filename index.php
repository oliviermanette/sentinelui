<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
  header("location: login.php");
  exit;
}
?>

<DOCTYPE html>
  <html>
  <head>

  </head>
  <!-- Script Bootstrap and jquery -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js"></script>
  <script src="semantic/dist/semantic.min.js"></script>
  <script src="bootstrap/js/bootstrap.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>


  <!-- Bootstrap core CSS -->
  <link rel="stylesheet" type="text/css" href="semantic/dist/semantic.min.css">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">

  <body>
    <div class="ui container">
      <?php
      //echo $_SESSION['id'];
      ?>
      <h1>Data from sensors </h1>
    </div>
    <?php
    ini_set('display_errors', 1);
    require_once("DBHandler.php");

    $host = "92.243.19.37";
    $userName = "admin";
    $password = "eoL4p0w3r";
    $dbName = "sentinel_test";
    $db = new DB($userName, $password, $dbName,$host);

    $all_id_sensors = $db->query_select('SELECT id FROM `sensor` WHERE 1',"id");
    $all_site = $db->query_select_light('SELECT id, nom FROM site WHERE owner_id='.$_SESSION['id']);
    $all_equipment = $db->query_select('SELECT nom FROM `structure` WHERE 1',"nom");
    $min_max_date_record = $db->query('SELECT (SELECT Max(date_time) FROM record) AS MaxDateTime,
    (SELECT Min(date_time) FROM record) AS MinDateTime');
    $min_date_time = $min_max_date_record[0]->MinDateTime;
    $max_date_time = $min_max_date_record[0]->MaxDateTime;
    $min_date_time = strtotime( $min_date_time );
    $max_date_time = strtotime( $max_date_time );
    $min_date = date( 'm-d-Y', $min_date_time );
    $max_date = date( 'm-d-Y', $max_date_time );

    ?>

    <div class="container">
    </br>
    <div class="col text-center">
      <a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a>
    </div>
  </br>
  <form name="contact-form" class="ui tiny form" action="" method="post" id="sendData">

    <div class="ui form">
      <div class="four fields">
        <div class="field">
          <label>Choose site</label>
          <select class="browser-default custom-select" name="siteDB" id="siteDB">
            <option value="" selected>Select site</option>
            <?php while($site = $all_site->fetch_object()){
              echo "<option value='$site->id'>$site->nom </option>";
            }
            ?>
          </select>
        </div>

        <div class="field" id="equipmentField">
          <label>Choose equipment</label>
          <select class="browser-default custom-select" name="equipment"  id="equipment">
            <option value="" selected>Select Equipement</option>
            <?php foreach($all_equipment as $equipment){
              echo "<option value='$equipment'>$equipment</option>";
            }
            ?>
          </select>
        </div>

        <div class="field">
          <label>Start date</label>
          <div class="ui calendar" id="startDate">
            <div class="ui input left icon">
              <i class="calendar icon"></i>
              <input type="text" placeholder="Date">
            </div>
          </div>
        </div>

        <div class="field">
          <label>End date</label>
          <div class="ui calendar" id="endDate">
            <div class="ui input left icon">
              <i class="calendar icon"></i>
              <input type="text" placeholder="Date">
            </div>
          </div>
        </div>

        <div class="field">
          <label>Choose type</label>
          <select class="browser-default custom-select" id="typemsg">
            <option value="" selected>Select type</option>
            <option value=''>All</option>
            <option value='choc'>Choc</option>
            <option value='inclinometre'>Inclinometre</option>
            <option value='global'>Global</option>
          </select>
        </div>

        <button type="submit" id="submit" class="btn btn-primary">Submit</button>
      </form>
    </div>
    <div class="form-check">
      <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input" name ="groupby" id="groupby">
        <label class="custom-control-label" for="groupby">Group Result</label>
      </div>
    </div>
    <div class="form-check">
      <!-- Default checked -->
      <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input" name ="allmsg" id="allmsg" checked>
        <label class="custom-control-label" for="allmsg">All messages</label>
      </div>
    </div>
  </div>


  <!--
  <div style="width: 50%">
  <canvas id="canvas2" height="450" width="600"></canvas>
</div>
-->
<div id="chart-display-container-specific" class="container">
  <canvas id="canvas" height="450" width="1200"></canvas>
</div>
<div id="chart-display-container-all" class="container">
  <div class="row">
    <div class="col">
      <div id="chart-container">
        <canvas id="canvas_spectre" height="450" width="1200"></canvas>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <div id="chart-container">
        <canvas id="canvas_inclinometre"></canvas>
      </div>
    </div>
    <div class="col">
      <div id="chart-container">
        <canvas id="canvas_temperature"></canvas>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col">
    </div>
    <div class="col">
      <div id="chart-container">
        <canvas id="canvas_choc"></canvas>
      </div>
    </div>
    <div class="col">
    </div>
  </div>
</div>
<!-- Container for displaying the table data -->
<div class="container">
  <div id="resultcontainer"></div>
</div>
</body>

<script>



$(document).on("click", "a.download", function(e) {
  e.preventDefault();
  var drawAll = false;
  var id_sensor_request = $(this).data('idsensor');
  var type_msg_request = $(this).data('typemsg');
  var time_data_request = $(this).data('date');
  var site_request = $(this).data('site');
  var equipement_request = $(this).data('equipement');

  var formData = {
    'id_sensor_request'              : id_sensor_request,
    'type_msg_request'              : type_msg_request,
    'time_data_request'             : time_data_request,
    'site_request'             : site_request,
    'equipement_request' : equipement_request,
    'drawAll' : drawAll
  };
  console.log(formData);

  $.ajax({
    url:"getDataChart.php",
    method:"POST",
    data: formData,
    success:function(data)
    {
      var general_canva = document.getElementById("chart-display-container-all");
      general_canva.style.display = "none";
      var specific_canva = document.getElementById("chart-display-container-specific");
      specific_canva.style.display = "block";


      $('#canvas').html(data);
      if (type_msg_request == "global"){
        drawTemperatureFromData(data, "canvas");
      }else if (type_msg_request == "inclinometre"){
        drawInclinometerFromData(data,"canvas");
      }else if (type_msg_request == "choc"){
        drawChocFromData(data,"canvas");
      }else if (type_msg_request == "spectre"){
        drawSubSpectreFromData(data,"canvas");
      }
    }
  });
});

function drawTemperature(data){
  console.log(data);
  data = JSON.parse(data);

  var temperature = [];
  var date = [];
  for (var i in data) {
    temperature.push(data[i].temperature);
    date.push(data[i].date_d);
  }

  console.log(date);
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
  var ctx = $("#canvas");
  var timeFormat = 'YYYY-MM-DD';
  var barGraph = new Chart(ctx, {
    type: 'line',
    data: chartdata,

    options: {
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
  });
}


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
  var barGraph = new Chart(ctx, {
    type: 'line',
    data: chartdata,

    options: {
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
  var barGraph = new Chart(ctx, {
    type: 'line',
    data: chartdata,
    options: {

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
  });
}

function drawSpectreFromData(spectreDataJson, canvaID = "canvas_spectre"){
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
    date_data.push(spectreDataJson[i].date_time);
  }

  /*console.log("Subspectre : ", subspectre);
  console.log("maxFreqData : ", maxFreqData);
  console.log("resolutionData : ", resolutionData);*/

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
  console.log("Array DATA : ",array_data);
  var title = "All spectre between " + date_data[0] + " and " + date_data[4];
  var chartdata = {
    datasets : [
      {
        data:array_data,
        showLine: true,
        borderColor: "#3e95cd",
        backgroundColor: "#f6f6f6",
        pointBackgroundColor: "#3e95cd",
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
          gridLines: {
            display:false
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
            display:false
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
    }
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

  amplitude.push(parseInt(chocDataJson[0].amplitude_1));
  amplitude.push(parseInt(chocDataJson[0].amplitude_2));
  time_data.push(parseInt(chocDataJson[0].time_1));
  time_data.push(parseInt(chocDataJson[0].time_2));
  date_data.push(parseInt(chocDataJson[0].date_d));
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
          },
          scaleLabel: {
            display: true,
            labelString: 'Time (μs)'
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
            labelString: 'Amplitude (mg)'
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

function drawInclinometer(data){
  //console.log(data);
  data = JSON.parse(data);
  var nx = [];
  var ny = [];
  var nz = [];
  var date = [];
  for (var i in data) {
    nx.push(data[i].nx);
    ny.push(data[i].ny);
    nz.push(data[i].nz);
    date.push(data[i].date_d);
  }

  //console.log(date);
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
  var ctx = $("#canvas");
  var timeFormat = 'YYYY-MM-DD';
  var barGraph = new Chart(ctx, {
    type: 'line',
    data: chartdata,

    options: {
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
          //type: 'logarithmic',
        }]
      },
      legend: { display: true },
      title: {
        display: true,
        text: 'Inclinometre en fonction du temps'
      }
    }
  });
}

//Draw choc data
function drawChoc(data){
  data = JSON.parse(data);
  var amplitude = [];
  var amplitude_1 = [];
  var amplitude_2 = [];
  var time_data = [];
  var time_1 = [];
  var time_2 = [];
  var date = []

  amplitude.push(parseInt(data[1].amplitude_1));
  amplitude.push(parseInt(data[1].amplitude_2));
  time_data.push(parseInt(data[1].time_1));
  time_data.push(parseInt(data[1].time_2));
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
    labels: date,
    datasets : [
      {
        data:array_data,
        showLine: true,
      }
    ]
  };
  var ctx = $("#canvas");
  var barGraph = new Chart(ctx, {
    type: 'scatter',
    data: chartdata,
    options: {
      scales: {
        display : true,
        xAxes: [{
          ticks: {
            beginAtZero: true,
          },
          scaleLabel: {
            display: true,
            labelString: 'Time (μs)'
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
            labelString: 'Amplitude (mg)'
          }
        }]
      },
      legend: { display: false },
      title: {
        display: true,
        text: 'Choc '
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

//Draw the completre spectrum (5 subspectre assembled)
function drawCompleteSpectre(data){
  //console.log(data);
  data = JSON.parse(data);
  var subspectre = [];
  var minFreqData = [];
  var maxFreqData = [];
  var resolutionData = [];
  var date_data = [];
  for (var i in data) {
    subspectre.push(data[i].subspectre);
    minFreqData.push(parseInt(data[i].min_freq));
    maxFreqData.push(parseInt(data[i].max_freq));
    resolutionData.push(parseInt(data[i].resolution));
    date_data.push(data[i].date_time);
  }

  min_freq = parseInt(minFreqData[0]);
  var min_freq_initial = min_freq;
  var max_freq = parseInt(maxFreqData[4]);
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
  //console.log(array_data);
  var title = "All spectre between " + date_data[0] + " and " + date_data[4];
  var chartdata = {
    datasets : [
      {
        data:array_data,
        showLine: true,
        borderColor: "#3e95cd",
        backgroundColor: "#f6f6f6",
        pointBackgroundColor: "#3e95cd",
      }
    ]
  };
  var ctx = $("#canvas");
  var barGraph = new Chart(ctx, {
    type: 'scatter',
    data: chartdata,
    options: {
      scales: {
        display : true,
        xAxes: [{
          gridLines: {
            display:false
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
            display:false
          },
          ticks: {
            beginAtZero: false,
          },
          scaleLabel: {
            display: true,
            labelString: 'Amplitude (mg)'
          },
          //type: 'logarithmic',
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
function hex2dec(hex){
  return parseInt(hex,16);
}
$(document).ready(function(){


  var general_canva = document.getElementById("chart-display-container-all");
  general_canva.style.display = "none";
  var specific_canva = document.getElementById("chart-display-container-specific");
  specific_canva.style.display = "none";

  /*
  var id_sensor="6";
  $.ajax({
  type: 'POST',
  url: 'getDataChart.php',
  data        : {sensor_id:id_sensor}, // our data object
  success: function (data) {
  //$('#chart-container').html(data);
  //console.log(data);
  //drawTemperature(data);
  //drawInclinometer(data);
  //drawChoc(data);
  //drawSubSpectre(data);
  //drawCompleteSpectre(data);
}
});*/

$("#sendData").submit(function(event) {
  event.preventDefault();
  var drawAll = true;
  // get the form data
  var formData = {
    'site'              : $('#siteDB option:selected').val(),
    'equipment'              : $('#equipment option:selected').val(),
    'typemsg'             : $('#typemsg option:selected').val(),
    'dateMin' : dateMin,
    'dateMax' : dateMax,
    'drawAll' : drawAll
  };
  console.log(formData);
  // process the form
  $.ajax({
    type        : 'GET', // define the type of HTTP verb we want to use (POST for our form)
    url         : 'fetch.php', // the url where we want to POST
    data        : formData, // our data object
    async: true,
    success:function(data)
    {
      $('#resultcontainer').html(data);
    }
  }),
  $.ajax({
    type        : 'POST', // define the type of HTTP verb we want to use (POST for our form)
    url         : 'getDataChart.php', // the url where we want to POST
    data        : formData, // our data object
    async: true,
    success:function(data)
    {
      //$('#resultcontainer').html(data);
      console.log("RESULT DATA : ",data);
      var specific_canva = document.getElementById("chart-display-container-specific");
      specific_canva.style.display = "none";
      var general_canva = document.getElementById("chart-display-container-all");
      if (general_canva.style.display === "none") {
        general_canva.style.display = "block";
      }

      data = JSON.parse(data);

      var temperature_data_json = data['temperature_data'];
      var choc_data_json = data['choc_data'];
      var inclinometre_data_json = data['inclinometre_data'];
      var spectre_data_json = data['spectre_data'];

      //console.log(spectre_data_json);

      drawTemperatureFromData(temperature_data_json,"canvas_temperature");
      drawInclinometerFromData(inclinometre_data_json,"canvas_inclinometre");
      drawSpectreFromData(spectre_data_json,"canvas_spectre");
      drawChocFromData(choc_data_json, "canvas_choc")
      //drawTemperature(data);
    }
  })

});


$( "#siteDB" ).change(function() {
  var postData = {
    'site_id'	:	$( this ).children("option:selected").val()
  };
  $.ajax({
    type        : 'POST', // define the type of HTTP verb we want to use (POST for our form)
    url         : 'getStructures.php', // the url where we want to POST
    data        : postData, // our data object
    success:function(data)
    {
      $('#equipmentField').html(data);
    }
  });
});


var dateMin = "";
var dateMax = "";
$('.ui.calendar#startDate').calendar({
  type: 'date',
  onChange: function (date, text) {
    dateMin = text;
    dateMin = new Date(dateMin);
    dateMin= dateMin.toISOString().slice(0,10).replace(/-/g,"-");
  },
});

$('.ui.calendar#endDate').calendar({
  type: 'date',
  onChange: function (date, text) {
    dateMax = text;
    dateMax = new Date(dateMax);
    dateMax= dateMax.toISOString().slice(0,10).replace(/-/g,"-");
  },
});

var typeMSG = "";
//Init
if ($("#allmsg").is(':checked')){
  load_data("", 'init','all');
}

$('input[name="groupby"]').change(function() {
  if($(this).prop("checked") == false){
    load_data('','init',typeMSG);
  }else{
    load_data("", 'initGroupBy','all');
  }
});


function load_data(query_data, actionToDo, type_msg)
{
  console.log("ACTION : ",actionToDo);
  $.ajax({
    url:"fetch.php",
    method:"GET",

    data:{query:query_data, action:actionToDo, type_msg:type_msg},
    success:function(data)
    {
      $('#resultcontainer').html(data);
    }
  });
}

$('#rangestart').calendar({
  type: 'date',
  endCalendar: $('#rangeend')
});
$('#rangeend').calendar({
  type: 'date',
  startCalendar: $('#rangestart')
});

});

</script>
</html>
