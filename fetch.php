<?php
//fetch.php
require_once "config.php";

if (isset($_GET['action'])){
  if ($_GET['action'] == "initGroupBy"){
    initGroupBy();
  }else if($_GET['action'] == "init"){
    init();
  }
}
//After sending the form, we process the data
if (isset($_GET['site'], $_GET['sensorIDDB'], $_GET['equipment'], $_GET['dateMin'],$_GET['dateMax'], $_GET['typemsg'])){
  $site = $_GET['site'];
  $equipment = $_GET['equipment'];
  $sensorID = $_GET['sensorIDDB'];
  $typeMSG =  $_GET['typemsg'];
  $dateMin = $_GET['dateMin'];
  $dateMax = $_GET['dateMax'];
  initSubmit($site, $equipment, $sensorID, $typeMSG, $dateMin, $dateMax );
}

function initSubmit($site, $equipment, $sensorID, $typeMSG, $dateMin, $dateMax ){
  global $connect;
  $query = "
  SELECT r.sensor_id AS `#sensor`, r.date_time AS `Date-Time`,
  r.msg_type AS `Type Message`, s.nom AS `Site`, st.nom AS `Equipement`
  FROM record as r
  LEFT JOIN structure AS st
  on st.id=r.structure_id
  LEFT JOIN site AS s
  ON s.id = st.site_id
  WHERE
  ( date( r.date_time) between date('$dateMin') and date('$dateMax'))
  AND s.nom LIKE '$site'
  AND r.msg_type LIKE '$typeMSG'
  AND st.nom LIKE '$equipment'
  order by  r.date_time desc
  ";


  $result = mysqli_query($connect, $query);
  #echo 'Result : '. $result . "\n";
  if ($result)
  {
    $output = '

    <table id="dataTable" class="display" style="width:100%">
    <thead>
    <tr>
    <th>#Sensor</th>
    <th>Date-Time</th>
    <th>Type Message</th>
    <th>site</th>
    <th>Equipement</th>
    <th>Action</th>
    </tr></thead>
    <tbody>
    ';
    $row = mysqli_num_rows($result);
    //printf("Number of row in the table : " . $row);

    while($row = mysqli_fetch_array($result))
    {
      $output .= '
      <tr>
      <td>'.$row["#sensor"].'</td>
      <td>'.$row["Date-Time"].'</td>
      <td>'.$row["Type Message"].'</td>
      <td>'.$row["Site"].'</td>
      <td>'.$row["Equipement"].'</td>
      <td><a href="index.php?id_download='.$row["#sensor"].'" data-id='.$row["#sensor"].' id="linkdownload" name="download">Show Data</a>
      </tr>
      ';
    }
    $output .= '</tbody></table>';
    echo $output;

  }
  else
  {
    echo 'Data Not Found';
  }
  echo "<script type='text/javascript'>
  $(document).ready(function(){ $('#dataTable').DataTable( {
    'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, 'All']],
    'iDisplayLength': 50
  } );});</script>";

}

function init() {
  global $connect;
  $query = "
  SELECT r.sensor_id AS `#sensor`, r.date_time AS `Date-Time`,
  r.msg_type AS `Type Message`, s.nom AS `Site`, st.nom AS `Equipement`
  FROM record as r
  LEFT JOIN structure AS st
  on st.id=r.structure_id
  LEFT JOIN site AS s
  ON s.id = st.site_id
  ";

  $result = mysqli_query($connect, $query);
  #echo 'Result : '. $result . "\n";
  if ($result)
  {
    $output = '

    <table id="dataTable" class="display" style="width:100%">
    <thead>
    <tr>
    <th>#Sensor</th>
    <th>Date-Time</th>
    <th>Type Message</th>
    <th>site</th>
    <th>Equipement</th>
    <th>Action</th>
    </tr></thead>
    <tbody>
    ';
    $row = mysqli_num_rows($result);
    //printf("Number of row in the table : " . $row);

    while($row = mysqli_fetch_array($result))
    {
      $output .= '
      <tr>
      <td>'.$row["#sensor"].'</td>
      <td>'.$row["Date-Time"].'</td>
      <td>'.$row["Type Message"].'</td>
      <td>'.$row["Site"].'</td>
      <td>'.$row["Equipement"].'</td>
      <td><a href="index.php?id_download='.$row["#sensor"].'" data-id='.$row["#sensor"].' data-type="all"  data-time='.$row["Date-Time"].'  name="download">Show Data</a>
      </tr>
      ';
    }
    $output .= '</tbody></table>';
    echo $output;

  }
  else
  {
    echo 'Data Not Found';
  }
  echo "<script type='text/javascript'>
  $(document).ready(function(){ $('#dataTable').DataTable( {
    'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, 'All']],
    'iDisplayLength': 50
  } );});</script>";

}

if (isset($_GET['id_download'], $_GET['type_msg'], $_GET['time_data'] )) {
  $ID_sensor =  $_GET['id_download'];
  $type_msg =  $_GET['type_msg'];
  $time_data =  $_GET['time_data'];
  $query = "
  SELECT `sensor_id` AS 'Sensor ID', `date_time` AS 'Date-Time', `msg_type` AS 'Type Message',`amplitude_1`, `amplitude_2`, `nx` AS 'X',`ny` AS 'Y',`nz` AS 'Z',`temperature` AS 'Temperature',`battery_state` AS 'Level Battery'
  FROM `record`
  WHERE sensor_id LIKE '$ID_sensor'
  AND CAST(date_time as DATE) = '$time_data'
  ";

  $result = mysqli_query($connect, $query);
  if ($result)
  {
    $output .= '

    <table id="dataTable" class="display" style="width:100%">
    <thead>
    <tr>
    <th>Sensor ID</th>
    <th>Date-Time</th>
    <th>Type Message</th>
    <th>Amplitude 1</th>
    <th>Amplitude 2</th>
    <th>X</th>
    <th>Y</th>
    <th>Z</th>
    <th>Temperature</th>
    <th>Level Battery</th>
    </tr></thead>
    <tbody>
    ';
    $row = mysqli_num_rows($result);
    //printf("Number of row in the table : " . $row);

    while($row = mysqli_fetch_array($result))
    {
      $output .= '
      <tr>
      <td>'.$row["Sensor ID"].'</td>
      <td>'.$row["Date-Time"].'</td>
      <td>'.$row["Type Message"].'</td>
      <td>'.$row["amplitude_1"].'</td>
      <td>'.$row["amplitude_2"].'</td>
      <td>'.$row["X"].'</td>
      <td>'.$row["Y"].'</td>
      <td>'.$row["Z"].'</td>
      <td>'.$row["Temperature"].'</td>
      <td>'.$row["Level Battery"].'</td>
      </tr>
      ';
    }
    $output .= '</tbody></table>';
    echo $output;

  }
}

function initGroupBy() {
  global $connect;
  $query = "
  SELECT sensor_id AS 'Sensor ID', s.nom AS `Site`, st.nom AS `Equipement`,
  count(*) AS '#messages',
  sum(case when msg_type = 'global' then 1 else 0 end) AS '#global',
  sum(case when msg_type = 'inclinometre' then 1 else 0 end) AS '#inclinometre',
  sum(case when msg_type = 'choc' then 1 else 0 end) AS '#choc'
  FROM record AS r
  LEFT JOIN structure AS st
  ON st.id=r.structure_id
  LEFT JOIN site AS s
  ON s.id = st.site_id
  GROUP BY sensor_id, st.nom, s.nom
  ";
  $result = mysqli_query($connect, $query);
  #echo 'Result : '. $result . "\n";
  if ($result)
  {
    $output = '

    <table id="dataTable" class="display" style="width:100%">
    <thead>
    <tr>
    <th>Sensor ID</th>
    <th>Site</th>
    <th>Equipement</th>
    <th>#messages</th>
    <th>#global</th>
    <th>#inclinometre</th>
    <th>#choc</th>
    <th>Action</th>
    </tr></thead>
    <tbody>
    ';
    $row = mysqli_num_rows($result);
    //printf("Number of row in the table : " . $row);

    while($row = mysqli_fetch_array($result))
    {
      $output .= '
      <tr>
      <td>'.$row["Sensor ID"].'</td>
      <td>'.$row["Site"].'</td>
      <td>'.$row["Equipement"].'</td>
      <td>'.$row["#messages"].'</td>
      <td>'.$row["#global"].'</td>
      <td>'.$row["#inclinometre"].'</td>
      <td>'.$row["#choc"].'</td>
      <td><a  href="index.php?id_download='.$row["Sensor ID"].'" data-id='.$row["Sensor ID"].' data-typemsg="all" id="linkdownload" name="download">Show Data</a>
      </tr>
      ';
    }
    $output .= '</tbody></table>';
    echo $output;

  }
  else
  {
    echo 'Data Not Found';
  }
  echo "<script type='text/javascript'>
  $(document).ready(function(){ $('#dataTable').DataTable( {
    'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, 'All']]
  } );});</script>";
}
