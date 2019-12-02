<?php
$host = "92.243.19.37";
$userName = "admin";
$password = "eoL4p0w3r";
$dbName = "sentinel_test";
$mysqli = new mysqli($host, $userName, $password, $dbName);
if($mysqli->connect_error) {
  exit('Could not connect');
}


$sql = "SELECT `sensor_id`, s.latitude AS latitude_site, s.longitude AS longitude_site, AVG(r.latitude) AS latitude_sensor, AVG(r.longitude) AS longitude_sensor, s.nom AS site, st.nom AS equipement
FROM record AS r
JOIN structure AS st ON r.structure_id = st.id
JOIN site AS s ON s.id = st.site_id
GROUP BY `sensor_id`, s.nom, st.nom  ,  s.latitude, s.longitude
ORDER BY `r`.`sensor_id` ASC";
$result = mysqli_query($mysqli, $sql);
$output = '';

if ($result->num_rows > 0) {
  # code...
  $arr = [];
  $inc = 0;
  while ($row = $result->fetch_assoc()) {
    # code...
    $jsonArrayObject = (array('sensor_id' => $row["sensor_id"],'latitude_site' => $row["latitude_site"],
    'longitude_site' => $row["longitude_site"], 'latitude_sensor' => $row["latitude_sensor"], 'longitude_sensor' => $row["longitude_sensor"],
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
