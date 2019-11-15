<?php

$query_all_dates = "SELECT r.date_time as date_d FROM
`spectre` AS sp
JOIN record AS r ON (r.id=sp.record_id)
JOIN structure as st ON (st.id=r.structure_id)
JOIN site as s ON (s.id=st.site_id)
WHERE sp.subspectre_number LIKE '001' AND r.sensor_id LIKE '6' AND (r.date_time BETWEEN '2019-10-01' AND '2019-10-30')
ORDER BY r.date_time ASC";

?>
