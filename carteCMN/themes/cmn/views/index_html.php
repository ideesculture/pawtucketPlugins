<?php 
    $georeference = $this->getVar("georeference");
?>
<div class="col-md-12">
    <div id="map"></div>
</div>

<style>
    #map {width: 90%; height:100vh; margin: auto; }
</style>

<script src="/leaflet_export.js"></script>
<script type='text/javascript'>
	jQuery(document).ready(function() {
		$('.trimText').readmore({
		  speed: 75,
		  maxHeight: 120,
		  moreLink: '<a href="#">En savoir plus</a>',
		  lessLink: '<a href="#">Moins</a>'
		});

		var map = L.map('map', {
                 center: <?= $georeference ?>,
                 zoom: 17 });
   
		// Ajout fonds de carte (tile et WMS)
		var baselayers = {
			OSM: L.tileLayer('http://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png'),
		};
		baselayers.OSM.addTo(map);

		// Ajout du bati en wms comme couche 
		var Parcelbati = L.tileLayer.wms('http://mapsref.brgm.fr/wxs/refcom-brgm/refign', 
								{layers: 'PARVEC_BATIMENT',format: 'image/png',transparent:true}).addTo(map); 
		
		// Ajout du cadatre en wms comme couche 
		var Cadastre = L.tileLayer('http://tms.cadastre.openstreetmap.fr/*/transp/{z}/{x}/{y}.png')
		.addTo(map); 
		
		// Ajout des amanegements cyclables en wms comme couche 
		var Routes = L.tileLayer.wms('https://public.sig.rennesmetropole.fr/geoserver/ows?', 
								{layers: 'ref_rva:vgs_troncon_domanialite',format: 'image/png',transparent:true}); 
		var marker = L.marker(<?= $georeference?>).addTo(map);

		
		// Gestion des couches
		var data = {"Parcelbati": Parcelbati, "Cadastre": Cadastre, "Routes": Routes};
		
		// Selecteur fonds de carte
		L.control.layers(baselayers, data, {collapsed : false}).addTo(map);	 
		
		// Echelle cartographique
		L.control.scale().addTo(map);


	});
    </script>