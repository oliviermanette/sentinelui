<?php
//setting header to json
//header('Content-Type:application/json');
//database
require_once "config.php";

if (isset($_POST["id_sensor_request"],$_POST["type_msg_request"],$_POST["time_data_request"],$_POST["site_request"],$_POST["equipement_request"])){
  $sensor_id= $_POST["id_sensor_request"];
  $type_msg =  $_POST['type_msg_request'];
  $time_data =  $_POST['time_data_request'];
  $site =  $_POST['site_request'];
  $equipement =  $_POST['equipement_request'];
  if (!$_POST["drawAll"] == true){
    //All Spectre
    /*$query_set = "SET @min_date = (SELECT MIN(date_d) FROM
    (SELECT s.nom AS site, st.nom AS equipement, r.sensor_id, r.date_time as date_d, `subspectre`,`subspectre_number`,`min_freq`,`max_freq`,`resolution` FROM `spectre` AS sp
    JOIN record AS r ON (r.id=sp.record_id)
    JOIN structure as st ON (st.id=r.structure_id)
    JOIN site as s ON (s.id=st.site_id)
    WHERE sp.subspectre_number LIKE '001' AND r.sensor_id LIKE '6'
    ORDER BY r.date_time ASC
    ) AS first_subspectre_msg)";
    $result =  mysqli_query($connect, $query_set);

    $query = "
    SELECT s.nom, st.nom, r.sensor_id, r.date_time, `subspectre`,`subspectre_number`,`min_freq`,`max_freq`,`resolution` FROM `spectre` AS sp
    JOIN record AS r ON (r.id=sp.record_id)
    JOIN structure as st ON (st.id=r.structure_id)
    JOIN site as s ON (s.id=st.site_id)
    WHERE r.sensor_id LIKE '6' AND (DATE(r.date_time) BETWEEN DATE(@min_date) AND DATE_ADD(@min_date, INTERVAL 4 DAY))
    ORDER BY r.date_time ASC
    ";*/

  }else {


    $query ="";
    if ($type_msg == "global"){
      //Temperature
      $query = "SELECT `temperature`, DATE(`date_time`) AS date_d FROM `record`
      WHERE `msg_type` LIKE 'inclinometre' AND `sensor_id` LIKE '$sensor_id' ORDER BY date_d ASC ";

    }else if ($type_msg == "inclinometre"){
      //Inclinometre
      $query = "SELECT `sensor_id`, DATE(`date_time`) AS date_d, `nx`,`ny`,`nz`, `temperature`  FROM `record`
      WHERE `msg_type` LIKE 'inclinometre' AND `sensor_id` LIKE '$sensor_id' ORDER BY date_d ASC ";

    }else if ($type_msg == "choc"){
      //Choc
      $query = "SELECT `sensor_id`, DATE(`date_time`) AS date_d, `amplitude_1`, `amplitude_2`,`time_1`,`time_2`  FROM `record`
      WHERE `msg_type` LIKE 'choc' AND `sensor_id` LIKE '$sensor_id' ORDER BY date_d ASC ";

    }else if ($type_msg == "spectre"){
      //Choc
      //Sub Spectre
      $query = "SELECT s.nom, st.nom, r.sensor_id, r.payload, r.date_time AS date_d,
      `subspectre`,`subspectre_number`,`min_freq`,`max_freq`,`resolution`
      FROM `spectre` AS sp
      JOIN record AS r ON (r.id=sp.record_id)
      JOIN structure as st ON (st.id=r.structure_id)
      JOIN site as s ON (s.id=st.site_id)
      WHERE CAST(r.date_time as DATE)  LIKE '$time_data' AND r.sensor_id='$sensor_id' ";

    }

    //query to get data from the table

    //execute query
    $result =  mysqli_query($connect, $query);
    if ($result)
    {
      $row = mysqli_num_rows($result);


      $data = array();
      foreach ($result as $row) {
        $data[] = $row;
        //echo $row["temperature"] ."</br>";
      }
      print json_encode($data);
    }

  }

}


if (isset($_POST["site"],$_POST["equipment"])){
  //$type_msg =  $_POST['typemsg'];
  /*$dateMin =  $_POST['dateMin'];
  $dateMax =  $_POST['dateMax'];*/
  $site =  $_POST['site'];
  $equipment =  $_POST['equipment'];
  //All
  $data = array();
  //Find ID sensor from site ID and equipement ID
  $query_id =  "SET @sensor_id = (SELECT DISTINCT(`sensor_id`) FROM `record` AS r
  JOIN structure as st ON (st.id=r.structure_id)
  JOIN site as s ON (s.id=st.site_id)
  WHERE s.id = '$site' AND st.id = '$equipment')";
  $result_id =  mysqli_query($connect, $query_id);

  //Temperature
  $query_temperature = "SELECT `temperature`, DATE(`date_time`) AS date_d FROM `record`
  WHERE `msg_type` LIKE 'inclinometre' AND `sensor_id` LIKE @sensor_id ORDER BY date_d ASC ";
  $result_temperature =  mysqli_query($connect, $query_temperature);
  $row_temp = mysqli_num_rows($result_temperature);

  foreach ($result_temperature as $row_temp) {
    $data["temperature_data"][] = $row_temp;
    //echo $row["temperature"] ."</br>";
  }
  $query_inclinometre = "SELECT `sensor_id`, DATE(`date_time`) AS date_d, `nx`,`ny`,`nz`, `temperature`  FROM `record`
  WHERE `msg_type` LIKE 'inclinometre' AND `sensor_id` LIKE @sensor_id ORDER BY date_d ASC ";
  $result_inclinometre =  mysqli_query($connect, $query_inclinometre);
  $row_inclinometre = mysqli_num_rows($result_inclinometre);

  foreach ($result_inclinometre as $row_inclinometre) {
    $data["inclinometre_data"][] = $row_inclinometre;
    //echo $row["temperature"] ."</br>";
  }

  $query_choc = "SELECT `sensor_id`, DATE(`date_time`) AS date_d, `amplitude_1`, `amplitude_2`,`time_1`,`time_2`  FROM `record`
  WHERE `msg_type` LIKE 'choc' AND `sensor_id` LIKE @sensor_id ORDER BY date_d ASC ";
  $result_choc =  mysqli_query($connect, $query_choc);
  $row_choc = mysqli_num_rows($result_choc);

  foreach ($result_choc as $row_choc) {
    $data["choc_data"][] = $row_choc;
    //echo $row["temperature"] ."</br>";
  }

  print json_encode($data);
}
