<?php
//setting header to json
//header('Content-Type:application/json');
//database
require_once "config.php";

if (isset($_POST["sensor_id"])){
  $sensor_id= $_POST["sensor_id"];

  //query to get data from the table
  //Inclinometre
  /*$query = "SELECT `sensor_id`, DATE(`date_time`) AS date_d, `nx`,`ny`,`nz`, `temperature`  FROM `record`
  WHERE `msg_type` LIKE 'inclinometre' AND `sensor_id` LIKE '$sensor_id' ORDER BY date_d ASC ";*/

  //Choc
  /*$query = "SELECT `sensor_id`, DATE(`date_time`) AS date_d, `amplitude_1`, `amplitude_2`,`time_1`,`time_2`  FROM `record`
  WHERE `msg_type` LIKE 'choc' AND `sensor_id` LIKE '6' ORDER BY date_d ASC ";*/
  //Spectre
  /*$query = "SELECT s.nom, st.nom, r.sensor_id, r.payload, DATE(r.date_time) as date_d,
  `subspectre`,`subspectre_number`,`min_freq`,`max_freq`,`resolution` FROM `spectre`
  AS sp JOIN record AS r ON (r.id=sp.record_id) JOIN structure as st ON (st.id=r.structure_id)
  JOIN site as s ON (s.id=st.site_id) WHERE DATE(r.date_time) LIKE '2019-11-02' AND r.sensor_id='4'";
  */

  //All Spectre
  $query_set = "SET @min_date = (SELECT MIN(date_d) FROM
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
  ";

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
