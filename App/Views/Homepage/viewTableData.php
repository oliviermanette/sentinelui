

<table id="tableData" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
<thead>
<tr>
<th>Sensor ID</th>
<th>Site</th>
<th>Equipement</th>
<th>#messages</th>
<th>#global</th>
<th>#inclinometre</th>
<th>#choc</th>
<th>#spectre</th>
</tr></thead>
<tbody>
  <?php $row = mysqli_num_rows($brief_data_record);

  while($row = mysqli_fetch_array($brief_data_record))
  {
    ?>
    <tr>
    <td> <?= $row["Sensor ID"] ?></td>
    <td><?= $row["Site"] ?></td>
    <td><?= $row["Equipement"] ?></td>
    <td><?= $row["#messages"] ?></td>
    <td><?= $row["#global"] ?></td>
    <td><?= $row["#inclinometre"] ?></td>
    <td><?= $row["#choc"] ?></td>
    <td><?= $row["#spectre"] ?></td>
    </tr>
  <?php
  }
  ?>
  </tbody></table>
