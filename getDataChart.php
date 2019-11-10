<?php
//setting header to json
//header('Content-Type:application/json');
//database
require_once "config.php";

if ($_POST["drawAll"] == "false"){
  if (isset($_POST["id_sensor_request"],$_POST["time_data_request"],
  $_POST["type_msg_request"], $_POST["site_request"],$_POST["equipement_request"])){

    $sensor_id= $_POST["id_sensor_request"];
    $time_data =  $_POST['time_data_request'];
    $site =  $_POST['site_request'];
    $equipement =  $_POST['equipement_request'];
    $type_msg =  $_POST['type_msg_request'];

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
else {
  if (isset($_POST["site_request"],$_POST["equipement_request"])){

    $dateMin='';
    $dateMax='';
    if (isset($_POST["dateMin"]) && isset($_POST["dateMax"]) ){
      $dateMin = $_POST["dateMin"];
      $dateMax = $_POST["dateMax"];
    }

    $site =  $_POST['site_request'];
    $equipment =  $_POST['equipement_request'];

    //All
    $data = array();
    //Find ID sensor from site ID and equipement ID
    $query_id =  "SET @sensor_id = (SELECT DISTINCT(`sensor_id`) FROM `record` AS r
    JOIN structure as st ON (st.id=r.structure_id)
    JOIN site as s ON (s.id=st.site_id)
    WHERE s.id = '$site' AND st.id = '$equipment')";
    $result_id =  mysqli_query($connect, $query_id);
    //echo $query_id;
    //echo "</br>";
    //Temperature
    $query_temperature = "SELECT `temperature`, DATE(`date_time`) AS date_d FROM `record`
    WHERE `msg_type` LIKE 'inclinometre' AND `sensor_id` LIKE @sensor_id ORDER BY date_d ASC ";
    $result_temperature =  mysqli_query($connect, $query_temperature);
    $row_temp = mysqli_num_rows($result_temperature);

    foreach ($result_temperature as $row_temp) {
      $data["temperature_data"][] = $row_temp;
      //echo $row["temperature"] ."</br>";
    }
    $query_inclinometre = "SELECT `sensor_id`, DATE(`date_time`) AS date_d, `nx`,`ny`,`nz`  FROM `record`
    WHERE `msg_type` LIKE 'inclinometre' AND `sensor_id` LIKE @sensor_id ORDER BY date_d ASC ";
    $result_inclinometre =  mysqli_query($connect, $query_inclinometre);
    $row_inclinometre = mysqli_num_rows($result_inclinometre);

    foreach ($result_inclinometre as $row_inclinometre) {
      $data["inclinometre_data"][] = $row_inclinometre;
    }
    //Simplifier TODO
    $query_date_max = "SET @max_date_choc = (SELECT MAX(date_d) FROM (SELECT `sensor_id`, DATE(`date_time`) AS date_d, `amplitude_1`, `amplitude_2`,`time_1`,`time_2`  FROM `record`
    WHERE `msg_type` LIKE 'choc' AND `sensor_id` LIKE '6' ) AS TMP)";
    $result_date_max =  mysqli_query($connect, $query_date_max);

    $query_choc = "SELECT `sensor_id`, DATE(`date_time`) AS date_d,
    `amplitude_1`, `amplitude_2`,`time_1`,`time_2`
    FROM `record` AS re WHERE `msg_type` LIKE 'choc' AND `sensor_id` LIKE @sensor_id AND DATE(re.date_time) = @max_date_choc";

    $result_choc =  mysqli_query($connect, $query_choc);
    $row_choc = mysqli_num_rows($result_choc);

    foreach ($result_choc as $row_choc) {
      $data["choc_data"][] = $row_choc;
      //echo $row["temperature"] ."</br>";
    }
    //All Spectre
    $query_date_max = "SET @max_date = (SELECT MAX(date_d) FROM
    (SELECT s.nom AS site, st.nom AS equipement, r.sensor_id, r.date_time as date_d, `subspectre`,`subspectre_number`,`min_freq`,`max_freq`,`resolution` FROM `spectre` AS sp
    JOIN record AS r ON (r.id=sp.record_id)
    JOIN structure as st ON (st.id=r.structure_id)
    JOIN site as s ON (s.id=st.site_id)
    WHERE sp.subspectre_number LIKE '001' AND r.sensor_id LIKE @sensor_id
    ORDER BY r.date_time ASC
    ) AS first_subspectre_msg)";
    $result_date_max =  mysqli_query($connect, $query_date_max);

    $query_all_dates = "SELECT Date(r.date_time) as date_d FROM
    `spectre` AS sp
    JOIN record AS r ON (r.id=sp.record_id)
    JOIN structure as st ON (st.id=r.structure_id)
    JOIN site as s ON (s.id=st.site_id)
    WHERE sp.subspectre_number LIKE '001' AND r.sensor_id LIKE @sensor_id ";
    if (!empty($dateMin) && !empty($dateMax)){
      $query_all_dates .="AND (date(r.date_time) BETWEEN date('$dateMin%') and date('$dateMax%')) ";
    }
    $query_all_dates .="ORDER BY r.date_time ASC";

    //echo $query_all_dates;
    //echo "</br>";
    $result_all_dates =  mysqli_query($connect, $query_all_dates);

    $spectre_number = 0;
    foreach ($result_all_dates as $row_date) {
      $spectre_name= 'spectre_'.$spectre_number;
      $current_date = $row_date['date_d'];
      //Reconstruct the all spectre for the current date
      $query_all_spectre_i = "SELECT s.nom, st.nom, r.sensor_id, Date(r.date_time) AS date, `subspectre`,`subspectre_number`,`min_freq`,`max_freq`,`resolution` FROM `spectre` AS sp
      JOIN record AS r ON (r.id=sp.record_id)
      JOIN structure as st ON (st.id=r.structure_id)
      JOIN site as s ON (s.id=st.site_id)
      WHERE r.sensor_id LIKE @sensor_id AND (DATE(r.date_time) BETWEEN DATE('$current_date') AND DATE_ADD('$current_date', INTERVAL 4 DAY))
      ORDER BY r.date_time ASC";

      $result_spectre_i =  mysqli_query($connect, $query_all_spectre_i);
      //echo "</br>";
      //echo $query_all_spectre_i ."</br>";

      $row_spectre = mysqli_num_rows($result_spectre_i);
      //echo $query_all_spectre;


      foreach ($result_spectre_i as $row_spectre) {
        $data["spectre_data"][$spectre_name][] = $row_spectre;

      }
      $spectre_number++;
    }

    print json_encode($data);
  }
}




/*
if (isset($_POST["site_request"],$_POST["equipement_request"])){

$dateMin='';
$dateMax='';
if (isset($_POST["dateMin"]) && isset($_POST["dateMax"]) ){
$dateMin = $_POST["dateMin"];
$dateMax = $_POST["dateMax"];
}

$site =  $_POST['site_request'];
$equipment =  $_POST['equipement_request'];

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
$query_inclinometre = "SELECT `sensor_id`, DATE(`date_time`) AS date_d, `nx`,`ny`,`nz`  FROM `record`
WHERE `msg_type` LIKE 'inclinometre' AND `sensor_id` LIKE @sensor_id ORDER BY date_d ASC ";
$result_inclinometre =  mysqli_query($connect, $query_inclinometre);
$row_inclinometre = mysqli_num_rows($result_inclinometre);

foreach ($result_inclinometre as $row_inclinometre) {
$data["inclinometre_data"][] = $row_inclinometre;
}
//Simplifier TODO
$query_date_max = "SET @max_date_choc = (SELECT MAX(date_d) FROM (SELECT `sensor_id`, DATE(`date_time`) AS date_d, `amplitude_1`, `amplitude_2`,`time_1`,`time_2`  FROM `record`
WHERE `msg_type` LIKE 'choc' AND `sensor_id` LIKE '6' ) AS TMP)";
$result_date_max =  mysqli_query($connect, $query_date_max);

$query_choc = "SELECT `sensor_id`, DATE(`date_time`) AS date_d,
`amplitude_1`, `amplitude_2`,`time_1`,`time_2`
FROM `record` AS re WHERE `msg_type` LIKE 'choc' AND `sensor_id` LIKE @sensor_id AND DATE(re.date_time) = @max_date_choc";

$result_choc =  mysqli_query($connect, $query_choc);
$row_choc = mysqli_num_rows($result_choc);

foreach ($result_choc as $row_choc) {
$data["choc_data"][] = $row_choc;
//echo $row["temperature"] ."</br>";
}
//All Spectre
$query_date_max = "SET @max_date = (SELECT MAX(date_d) FROM
(SELECT s.nom AS site, st.nom AS equipement, r.sensor_id, r.date_time as date_d, `subspectre`,`subspectre_number`,`min_freq`,`max_freq`,`resolution` FROM `spectre` AS sp
JOIN record AS r ON (r.id=sp.record_id)
JOIN structure as st ON (st.id=r.structure_id)
JOIN site as s ON (s.id=st.site_id)
WHERE sp.subspectre_number LIKE '001' AND r.sensor_id LIKE @sensor_id
ORDER BY r.date_time ASC
) AS first_subspectre_msg)";
$result_date_max =  mysqli_query($connect, $query_date_max);

$query_all_dates = "SELECT Date(r.date_time) as date_d FROM
`spectre` AS sp
JOIN record AS r ON (r.id=sp.record_id)
JOIN structure as st ON (st.id=r.structure_id)
JOIN site as s ON (s.id=st.site_id)
WHERE sp.subspectre_number LIKE '001' AND r.sensor_id LIKE @sensor_id ";
if (!empty($dateMin) && !empty($dateMax)){
$query_all_dates .="AND (date(r.date_time) BETWEEN date('$dateMin%') and date('$dateMax%')) ";
}
$query_all_dates .="ORDER BY r.date_time ASC";

//echo $query_all_dates;
$result_all_dates =  mysqli_query($connect, $query_all_dates);

$spectre_number = 0;
foreach ($result_all_dates as $row_date) {
$spectre_name= 'spectre_'.$spectre_number;
$current_date = $row_date['date_d'];
//Reconstruct the all spectre for the current date
$query_all_spectre_i = "SELECT s.nom, st.nom, r.sensor_id, Date(r.date_time) AS date, `subspectre`,`subspectre_number`,`min_freq`,`max_freq`,`resolution` FROM `spectre` AS sp
JOIN record AS r ON (r.id=sp.record_id)
JOIN structure as st ON (st.id=r.structure_id)
JOIN site as s ON (s.id=st.site_id)
WHERE r.sensor_id LIKE '6' AND (DATE(r.date_time) BETWEEN DATE('$current_date') AND DATE_ADD('$current_date', INTERVAL 4 DAY))
ORDER BY r.date_time ASC";

$result_spectre_i =  mysqli_query($connect, $query_all_spectre_i);
//echo $query_all_spectre_i ."</br>";

$row_spectre = mysqli_num_rows($result_spectre_i);
//echo $query_all_spectre;


foreach ($result_spectre_i as $row_spectre) {
$data["spectre_data"][$spectre_name][] = $row_spectre;

}
$spectre_number++;
}

print json_encode($data);
}
*/
