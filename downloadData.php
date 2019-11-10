<?php
require_once "config.php";
ini_set('display_errors', 1);

$query = "SELECT sensor.id, sensor.deveui, s.nom AS Site, st.nom AS Equipement, `date_time`,
`payload`, `msg_type` AS 'Type message', `amplitude_1` AS 'Amplitude 1',
`amplitude_2` AS 'Ampltiude 2',`time_1` AS 'Time 1', `time_2` AS 'Time 2',
r.nx AS X, r.ny AS Y, r.nz, `temperature` AS Temperature, `battery_level` AS Battery
FROM record AS r
INNER JOIN structure AS st
ON st.id=r.structure_id
INNER JOIN site AS s
ON s.id = st.site_id
INNER JOIN sensor ON (sensor.id=r.sensor_id)
INNER JOIN sensor_group AS gs ON (gs.sensor_id=sensor.id)
INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
WHERE gn.name = 'RTE' AND Date(r.date_time) >= Date(sensor.installation_date)";

if ($_GET['exportData'] == "excel"){
  $timestamp = time();
  $filename = 'Export_data_sensors_' . $timestamp . '.xls';

  header("Content-Type: application/vnd.ms-excel");
  header("Content-Disposition: attachment; filename=\"$filename\"");

  $isPrintHeader = false;

  $result = mysqli_query($connect, $query);
  while($row = mysqli_fetch_assoc($result))
  {
    if (! $isPrintHeader) {
      echo implode("\t", array_keys($row)) . "\n";
      $isPrintHeader = true;
    }
    echo implode("\t", array_values($row)) . "\n";
  }
  exit();
}else if ($_GET['exportData'] == "csv"){

  $timestamp = time();
  $filename = 'Export_data_sensors_' . $timestamp . '.csv';

  header('Content-Type: text/csv; charset=utf-8');
  header("Content-Disposition: attachment; filename=\"$filename\"");

  $output = fopen("php://output", "w");

  fputcsv($output, array('Deveui', 'Site', 'Equipement', 'Date Time',
  'payload', 'Type message', 'payload', 'Amplitude 1', 'Amplitude 2',
  'Time 1', 'Time 2', 'X', 'Y', 'Z', 'Temperature', 'Batterie'));

  $result = mysqli_query($connect, $query);
  while($row = mysqli_fetch_assoc($result))
  {
    fputcsv($output, $row);
  }
  fclose($output);


  exit();
}
/*
*/

/**/




?>
