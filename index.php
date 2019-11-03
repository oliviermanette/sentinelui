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

<!-- Container for displaying the table data -->
<div class="container">
  <div id="resultcontainer"></div>
</div>
</body>

<script>

$(document).on("click", "a[name=download]", function(e) {
  e.preventDefault();
  var query_id = $(this).data('id');
  var type_msg = $(this).data('type');
  var time_data = $(this).data('time');
  $.ajax({
    url:"fetch.php",
    method:"GET",
    data:{id_download:query_id, type_msg:type_msg,time_data:time_data},
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

  $("#sendData").submit(function(event) {
    event.preventDefault();
    // get the form data
    var formData = {
      'site'              : $('#siteDB option:selected').val(),
      'equipment'              : $('#equipment option:selected').val(),
      'sensorIDDB'             : $('#sensorIDDB option:selected').val(),
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
