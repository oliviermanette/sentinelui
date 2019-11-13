<?php

require_once "config.php";

$sql = "SELECT DISTINCT sensor.device_number AS sensor_id, st.transmision_line_name AS Ligne_HT, st.longitude AS longitude_equipement,
 st.latitude AS latitude_equipement, st.nom AS equipement,s.nom AS site,
 s.latitude AS latitude_site, s.longitude AS longitude_site
FROM structure AS st
LEFT JOIN site AS s ON (s.id = st.site_id)
LEFT JOIN record AS r ON (r.structure_id= st.id)
INNER JOIN sensor ON (r.sensor_id=sensor.id)
INNER JOIN sensor_group AS gs ON (gs.sensor_id=sensor.id)
INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
WHERE gn.name = 'RTE' ";
$result = mysqli_query($connect, $sql);
$output = '';

if ($result->num_rows > 0) {

  $arr = [];
  $inc = 0;
  while ($row = $result->fetch_assoc()) {
    # code...
    $jsonArrayObject = (array('sensor_id' => $row["sensor_id"], 'ligne_HT' => $row["Ligne_HT"],
    'latitude_site' => $row["latitude_site"],
    'longitude_site' => $row["longitude_site"], 'latitude_equipement' => $row["latitude_equipement"], 'longitude_equipement' => $row["longitude_equipement"],
    'site' => $row["site"], 'equipement' => $row["equipement"]));
    $arr[$inc] = $jsonArrayObject;
    $inc++;
  }
  $json_array = json_encode($arr);
  echo $json_array;
}
else{
  echo "0 results";
}


?>
