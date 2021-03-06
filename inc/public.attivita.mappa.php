<?php

/*
 * ©2013 Croce Rossa Italiana
 */

?>

<div class="row-fluid">
  <div class="span8">
    <h2>
      <i class="icon-globe muted"></i>
      Cosa fa Croce Rossa attorno a me?
    </h2>
  </div>
  <div class="span4 allinea-centro">
    <div class="alert alert-success" style="margin-bottom: 5px;">
      <i class="icon-time"></i> Attività svolte nell'ultimo mese.
    </div>
    <div class="alert alert-info">
      <i class="icon-calendar"></i> Per la vista calendario, <a href="?p=login"><strong>accedi a Gaia</strong>.</a>
    </div>
  </div>
  
</div>

<div class="row-fluid">
  
  
  <div id="laMappa" class="span12 mappaGoogle bordo" style="min-height: 700px;">
    
   

  </div>
  
</div>

<script type="text/javascript">
  function initialize() {
    var opzioni = {
      zoom: 6,
      center: new google.maps.LatLng(41.9,12.4833333),
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    var map = new google.maps.Map(document.getElementById("laMappa"), opzioni);
    
    var messaggio = [], marcatore = [];                         
    <?php $i = 0; $elenco = Attivita::elencoMappa(); foreach ( $elenco as $a ) { 
      ?>
      messaggio.push(new google.maps.InfoWindow({
        content: "<a href='https://gaia.cri.it/?p=attivita.scheda&id=<?php echo $a->id; ?>'><?php echo htmlentities($a->nome); ?></a><br /><?php echo htmlentities($a->luogo); ?>"
      }));
      marcatore.push(new google.maps.Marker({
        position: new google.maps.LatLng(<?php echo $a->latlng(); ?>),
        map: map, animation: google.maps.Animation.DROP
      }));
      google.maps.event.addListener(marcatore[<?php echo $i; ?>], 'click', function() {
        messaggio[<?php echo $i; ?>].open(map, marcatore[<?php echo $i; ?>]);
      });
      <?php $i++; ?>

      <?php } ?>
      
    }
    function loadScript() {
      var script = document.createElement("script");
      script.type = "text/javascript";
      script.src = "https://maps.google.com/maps/api/js?sensor=false&callback=initialize";
      document.body.appendChild(script);
    }
    window.onload = loadScript;
  </script>