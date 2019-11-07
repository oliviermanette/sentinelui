<?php
//fetch.php
require_once "config.php";
ini_set('display_errors', 1);


if (isset($_GET['action'])){
  if ($_GET['action'] == "initGroupBy"){
    initGroupBy();
  }else if($_GET['action'] == "init"){
    init();
  }else if($_GET['action'] == "downloadAllData"){
    downloadAllData();
  }
}

if (isset($_GET['site'], $_GET['equipment'], $_GET['dateMin'],$_GET['dateMax'], $_GET['typemsg'])){
  $site_id= $_GET['site'];
  $equipment_id = $_GET['equipment'];
  $typeMSG =  $_GET['typemsg'];
  $dateMin = $_GET['dateMin'];
  $dateMax = $_GET['dateMax'];
  initSubmit($site_id, $equipment_id, $typeMSG, $dateMin, $dateMax );
}

function initSubmit($site_id, $equipment_id, $typeMSG, $dateMin, $dateMax ){
  global $connect;
  $query_submit = '
  SELECT r.sensor_id AS `#sensor`, r.date_time AS `Date-Time`,
  r.msg_type AS `Type Message`, s.nom AS `Site`, st.nom AS `Equipement`
  FROM record as r
  LEFT JOIN structure AS st
  on st.id=r.structure_id
  LEFT JOIN site AS s
  ON s.id = st.site_id
  WHERE ';
  if (!empty($dateMin) && !empty($dateMax)){
      $query_submit .="(date(r.date_time) BETWEEN date('$dateMin%') and date('$dateMax%')) AND ";
  }
  $query_submit .="s.id LIKE '%$site_id' AND r.msg_type LIKE '%$typeMSG' AND st.id LIKE '%$equipment_id' order by r.date_time desc";

  #echo $query_submit;
  $result = mysqli_query($connect, $query_submit);
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
      <td><a class=download href="index.php?id_download='.$row["#sensor"].'" data-idsensor='.$row["#sensor"].'
       data-typemsg='.$row["Type Message"].' data-site='.$row["Site"].'
        data-equipement='.urlencode($row["Equipement"]).' data-date='.$row["Date-Time"].' id="linkdownload" name="download">Show Data</a></td>
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
      <td><a class=download href="index.php?id_download='.$row["#sensor"].'" data-idsensor='.$row["#sensor"].'
       data-typemsg='.$row["Type Message"].' data-site='.$row["Site"].'
        data-equipement='.urlencode($row["Equipement"]).' data-date='.$row["Date-Time"].' id="linkdownload" name="download">Show Data</a></td>
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
    'iDisplayLength': 20
  } );});</script>";

}

function downloadAllData(){
  global $connect;
  $query = "";
}

if (isset($_POST['id_sensor_request'], $_POST['type_msg_request'], $_POST['time_data_request'], $_POST['site_request'],$_POST['equipement_request']  )) {
  $ID_sensor =  $_POST['id_sensor_request'];
  $type_msg =  $_POST['type_msg_request'];
  $time_data =  $_POST['time_data_request'];
  $site =  $_POST['site_request'];
  $equipement =  $_POST['equipement_request'];
  $query = "
  SELECT `sensor_id` AS 'Sensor ID', CAST(date_time as DATE) AS 'Date-Time', `msg_type` AS 'Type Message',`amplitude_1`,
   `amplitude_2`, `time_1`, `time_2`,`nx` AS 'X',`ny` AS 'Y',`nz` AS 'Z',`temperature` AS 'Temperature',`battery_level` AS 'Level Battery'
  FROM `record`
  WHERE sensor_id LIKE '$ID_sensor'
  AND CAST(date_time as DATE) LIKE '$time_data'
  AND msg_type LIKE '$type_msg'
  ";

  $result = mysqli_query($connect, $query);
  if ($result)
  {
    $output = '

    <table id="dataTable" class="display" style="width:100%">
    <thead>
    <tr>
    <th>Sensor ID</th>
    <th>Date-Time</th>
    <th>Type Message</th>
    <th>Amplitude 1</th>
    <th>Amplitude 2</th>
    <th>Time 1</th>
    <th>Time 2</th>
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
      <td>'.$row["time_1"].'</td>
      <td>'.$row["time_2"].'</td>
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
      <td><a class=download href="index.php?id_download='.$row["Sensor ID"].'" data-idsensor='.$row["Sensor ID"].'  data-site='.$row["Site"].'
        data-equipement='.$row["Equipement"].' id="linkdownload" name="download">Show Data</a></td>
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
