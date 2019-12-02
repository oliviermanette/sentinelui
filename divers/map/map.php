<DOCTYPE html>
  <html>

  <head>
    <meta charset="utf-8">
    <title></title>
    <meta name="author" content="">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css"
    integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
    crossorigin=""/>
    <link rel="stylesheet" href="leaflet-pulse-icon/dist/L.Icon.Pulse.css" />

    <!-- BOOTSTRAP Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
          crossorigin="anonymous">

    <!-- BOOTSTRAP Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous">
    </script>
    <!-- FONT AWESOME CDN -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Make sure you put this AFTER Leaflet's CSS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js"
    integrity="sha512-GffPMF3RvMeYyc1LWMHtK8EbPv0iNZ8/oTtHPx9/cc2ILxQ+u905qIwdpULaqDkyBKgOaB57QTMg7ztg8Jm2Og=="
    crossorigin=""></script>
    <script src="leaflet-pulse-icon/dist/L.Icon.Pulse.js"></script>
    <script src="Leaflet.Control.Custom.js"></script>



    <style>
    #map {
      bottom: 0;
      left: 0;
      position: absolute;
      right: 0;
      top: 0;
    }

    @keyframes fade {
      from { opacity: 0.5; }
    }

    .blinking {
      animation: fade 1s infinite alternate;
    }
    </style>
  </head>

  <body>
    <?php
    require_once("DBHandler.php");
    $host = "92.243.19.37";
    $userName = "admin";
    $password = "eoL4p0w3r";
    $dbName = "sentinel_test";
    $db = new DB($userName, $password, $dbName,$host);

    $all_id_sensors = $db->query_select('SELECT id FROM `sensor` WHERE 1',"id");
    $all_site = $db->query_select('SELECT nom FROM `site` WHERE 1',"nom");
    $all_equipment = $db->query_select('SELECT nom FROM `structure` WHERE 1',"nom");
    $min_max_date_record = $db->query('SELECT (SELECT Max(date_time) FROM record) AS MaxDateTime,
    (SELECT Min(date_time) FROM record) AS MinDateTime');

    ?>

    <div id="mapid" style="width: 600px; height: 400px;"></div>
    <div id="txtHint"></div>


  </body>
  <script>

  $(document).ready(function(){
    fetDataMap(showMap);
  });

  function fetDataMap(functionToRun) {
    var xhttp;
    xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        //document.getElementById("txtHint").innerHTML = this.responseText;
        var jsonData =  this.responseText;

        return functionToRun(jsonData);

      }
    };
    xhttp.open("GET", "getDataMap.php", true);
    xhttp.send();
  }

  function showMap(data){
    data = JSON.parse(data);
    var lat_france = 46.2276;
    var long_france = 2.2137;

    var map = L.map('mapid', { attributionControl:false }).setView([lat_france, long_france], 5.3);

    L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibGlyb25lIiwiYSI6ImNrMmdrdmo1czAwOXozb29oc3NybXJqNTcifQ.sbQPrGi1n7lsCtlCvojTBA', {
      maxZoom: 18,
      attribution:'<a href="https://flod.ai">Flod Sentiel</a>',
      id: 'mapbox.streets'
    }).addTo(map);

    L.control.custom({
                position: 'topright',
                content : '<button type="button" class="btn btn-default">'+
                          '    <i class="fa fa-crosshairs"></i>'+
                          '</button>'+
                          '<button type="button" class="btn btn-danger">'+
                          '    <i class="fa fa-times"></i>'+
                          '</button>'+
                          '<button type="button" class="btn btn-success">'+
                          '    <i class="fa fa-check"></i>'+
                          '</button>'+
                          '<button type="button" class="btn btn-warning">'+
                          '    <i class="fa fa-exclamation-triangle"></i>'+
                          '</button>',
                classes : 'btn-group-vertical btn-group-sm',
                style   :
                {
                    margin: '10px',
                    padding: '0px 0 0 0',
                    cursor: 'pointer'
                },
                datas   :
                {
                    'foo': 'bar',
                },
                events:
                {
                    click: function(data)
                    {
                        console.log('wrapper div element clicked');
                        console.log(data);
                    },
                    dblclick: function(data)
                    {
                        console.log('wrapper div element dblclicked');
                        console.log(data);
                    },
                    contextmenu: function(data)
                    {
                        console.log('wrapper div element contextmenu');
                        console.log(data);
                    },
                }
            })
            .addTo(map);





    for(var i = 0; i < data.length; i++) {
      var obj = data[i];
      longitude_sensor = obj.longitude_sensor;
      latitude_sensor = obj.latitude_sensor;
      sensor_id = obj.sensor_id;
      site = obj.site;
      equipement = obj.equipement;

      L.marker([latitude_sensor, longitude_sensor] ).addTo(map)
      .bindPopup("<b>" + site + "</b><br />" + equipement).openPopup();
    }


    //var pulsingIcon = L.icon.pulse({iconSize:[10,10],color:'red'});
    //var marker = L.marker([lat_finister1,long_finister1],{icon: pulsingIcon}).addTo(map);

  }


  /*L.marker([lat_finister1, long_finister1], {
  icon: L.icon({
  iconUrl: 'https://unpkg.com/leaflet@1.0.3/dist/images/marker-icon.png',
  className: 'blinking',
  iconSize: [24,36],
  iconAnchor: [12,36]
})
}).addTo(map).bindPopup("<b>Hello world!</b><br />I am a popup.").openPopup();*/




</script>

</html>
