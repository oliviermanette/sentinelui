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
    $all_equipment = $db->query_select_light('SELECT id, nom FROM `structure` WHERE site_id='.$siteID);
?>

<label>Choose equipment</label>
<select class="browser-default custom-select" name="equipment"  id="equipment">
	<option selected>Select equipment</option>
    <?php while($equip = $all_equipment->fetch_object()){
               echo "<option value='$equip->id'>$equip->nom </option>";
           }
    ?>
</select>
