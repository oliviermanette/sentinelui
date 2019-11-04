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
            <option selected>Select site</option>
            <?php while($site = $all_site->fetch_object()){
              echo "<option value='$site->id'>$site->nom </option>";
            }
            ?>
          </select>
        </div>

        <div class="field" id="equipmentField">
          <label>Choose equipment</label>
          <select class="browser-default custom-select" name="equipment"  id="equipment">
            <option selected>Select equipment</option>
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
<div class="container">
  <div class="row">
    <div class="col">
      <h2> Spectre </h2>
      <div id="chart-container">
        <canvas id="canvas" height="450" width="1200"></canvas>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <h2> Inclinometre </h2>
    </div>
    <div class="col">
      <h2> Temperature </h2>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <h1> Single column </h1>
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
    'equipement_request' : equipement_request
  };
  console.log(formData);

  $.ajax({
    url:"fetch.php",
    method:"POST",
    data: formData,
      success:function(data)
      {
        $('#resultcontainer').html(data);
      }
    });
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

  $(document).ready(function(){

    function hex2dec(hex){
      return parseInt(hex,16);
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

    //Draw a specific subspectre from the sensor
    function drawSubSpectre(data){
      //console.log(data);
      data = JSON.parse(data);
      subspectre_hex = data[0].payload;
      min_freq = parseInt(data[0].min_freq);
      max_freq = parseInt(data[0].max_freq);
      resolution = parseInt(data[0].resolution)
      subspectre_number = data[0].subspectre_number;
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
      var ctx = $("#canvas");
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

    function drawInclinometer(data){
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

      console.log(date);
      var chartdata = {
        labels: date,
        datasets : [
          {
            labels: date,
            fill: false,
            backgroundColor: 'rgba(200, 200, 200, 0.75)',
            borderColor: 'rgba(200, 200, 200, 0.75)',
            hoverBackgroundColor: 'rgba(200, 200, 200, 1)',
            hoverBorderColor: 'rgba(200, 200, 200, 1)',
            data: nx
          },
          {
            labels: date,
            fill: false,
            backgroundColor: 'rgba(200, 200, 200, 0.75)',
            borderColor: 'rgba(200, 200, 200, 0.75)',
            hoverBackgroundColor: 'rgba(200, 200, 200, 1)',
            hoverBorderColor: 'rgba(200, 200, 200, 1)',
            data: ny
          },
          {
            labels: date,
            fill: false,
            backgroundColor: 'rgba(200, 200, 200, 0.75)',
            borderColor: 'rgba(200, 200, 200, 0.75)',
            hoverBackgroundColor: 'rgba(200, 200, 200, 1)',
            hoverBorderColor: 'rgba(200, 200, 200, 1)',
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
              //reverse: true,
              //  type:       "time",
            }]
          },
          legend: { display: false },
          title: {
            display: true,
            text: 'Inclinometre en fonction du temps'
          }
        }
      });
    }

    function drawTemperature(data){
      data = JSON.parse(data);
      var temperature = [];
      var nx = [];
      var ny = [];
      var nz = [];
      var date = [];
      for (var i in data) {
        temperature.push(data[i].temperature);
        nx.push(data[i].nx);
        ny.push(data[i].ny);
        nz.push(data[i].nz);
        date.push(data[i].date_d);
        //marks.push(data[i].marks);
      }

      console.log(date);
      var chartdata = {
        labels: date,
        datasets : [
          {
            labels: date,
            backgroundColor: 'rgba(200, 200, 200, 0.75)',
            borderColor: 'rgba(200, 200, 200, 0.75)',
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
              //reverse: true,
              //  type:       "time",
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
        drawCompleteSpectre(data);
      }
    });

    $("#sendData").submit(function(event) {
      event.preventDefault();
      // get the form data
      var formData = {
        'site'              : $('#siteDB option:selected').val(),
        'equipment'              : $('#equipment option:selected').val(),
        'typemsg'             : $('#typemsg option:selected').val(),
        'dateMin' : dateMin,
        'dateMax' : dateMax
      };
      console.log(formData);
      // process the form
      $.ajax({
        type        : 'GET', // define the type of HTTP verb we want to use (POST for our form)
        url         : 'fetch.php', // the url where we want to POST
        data        : formData, // our data object
        success:function(data)
        {
          $('#resultcontainer').html(data);
        }
      })

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
