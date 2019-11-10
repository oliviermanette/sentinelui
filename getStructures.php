<?php
/*
getStructure.php
author : Olivier F.L. Manette

retrieve from the database the list of structures that are linked to a specific site
and display it as a HTML SELECT object.

REQUIREMENTS:
************
In order to work, this file should receive the POST variable site_id
which contain the id number of the choosen site in the database.
*/
ini_set('display_errors', 1);
require_once("DBHandler.php");
$host = "92.243.19.37";
$userName = "admin";
$password = "eoL4p0w3r";
$dbName = "sentinel_test";
$db = new DB($userName, $password, $dbName,$host);

$siteID = $_POST['site_id'];
$query_equipement = "SELECT DISTINCT equipement,equipement_id, nom, site_id FROM
(SELECT  gs.sensor_id, site.nom AS nom, site.id AS site_id, st.nom AS equipement, st.id AS equipement_id FROM structure AS st
            INNER JOIN record AS r ON (r.structure_id=st.id)
            INNER JOIN sensor AS s ON (s.id = r.sensor_id)
            INNER JOIN sensor_group AS gs ON (gs.sensor_id=s.id)
            INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
            LEFT JOIN site ON (site.id=st.site_id)
            WHERE gn.name = 'RTE' AND site_id = $siteID) AS RTE ";
//echo $query_equipement;
//$all_equipment = $db->query_select_light('SELECT id, nom FROM `structure` WHERE site_id='.$siteID);
$all_equipment = $db->query_select_light($query_equipement);
?>

<label>Choose equipment</label>
<select class="browser-default custom-select" name="equipment"  id="equipment">
	<option value="" selected>Select equipment</option>
	<?php while($equip = $all_equipment->fetch_object()){
		echo "<option value='$equip->equipement_id'>$equip->equipement </option>";
	}
	?>
</select>
