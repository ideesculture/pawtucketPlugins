<script src="/Leaflet.BigImage.js"></script>
<link rel="stylesheet" href="/Leaflet.BigImage.css">
<script src="//html2canvas.hertzen.com/dist/html2canvas.js"></script>
<script src="/leaflet_export.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.js" integrity="sha512-wUa0ktp10dgVVhWdRVfcUO4vHS0ryT42WOEcXjVVF2+2rcYBKTY7Yx7JCEzjWgPV+rj2EDUr8TwsoWF6IoIOPg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="/app/plugins/carteCMN/lib/leaflet-omnivore.min.js"></script>
<?php 
    $georeference = $this->getVar("georeference");
	$t_object = $this->getVar("t_object");

	$parcelles = $t_object->getWithTemplate("<unit relativeTo='ca_objects.parcelle' delimiter='§'>^ca_objects.parcelle.code_insee|^ca_objects.parcelle.ref_parcelle</unit>");
	$cadastre_commentaire = $t_object->getWithTemplate("^ca_objects.parcelle.cadastre_commentaire");
	$codes_insee = [];
	foreach(explode("§", $parcelles) as $commune) {
		$commune_data = explode("|", $commune);
		$parcelles = $commune_data[1];
		$parcelles = str_replace(" ", "", $parcelles);
	
		$code_insee = $commune_data[0];
		$code_dept = substr($code_insee,0,2);
		$url_prop_commune = "https://geo.api.gouv.fr/communes/".$code_insee."?fields=nom&format=json&geometry=centre";
		$infos_commune = json_decode(file_get_contents($url_prop_commune), 1);
		$nom_commune[$code_insee] = $infos_commune["nom"];
		$url_cadastre_parcelles = "https://cadastre.data.gouv.fr/data/etalab-cadastre/latest/geojson/communes/".$code_dept."/".$code_insee."/cadastre-".$code_insee."-parcelles.json.gz";
		$file_cadastre_parcelles = "cadastre-".$code_insee."-parcelles.json.gz";
		$file_cadastre_parcelles_unzipped = "cadastre-".$code_insee."-parcelles.json";
		if(!is_file(__CA_APP_DIR__."/plugins/carteCMN/cadastre/".$file_cadastre_parcelles)) {
			file_put_contents(__CA_APP_DIR__."/plugins/carteCMN/cadastre/".$file_cadastre_parcelles, file_get_contents($url_cadastre_parcelles));
			exec("cd ".__CA_APP_DIR__."/plugins/carteCMN/cadastre/ && gunzip ".$file_cadastre_parcelles);
		}
		$codes_insee[] = $code_insee;
		$parcels_to_display[] = $parcelles;
	}
	
?>
<script>
<?php
if($codes_insee[0]):
	foreach($codes_insee as $i=>$code_insee):
?>
		var parcelles_<?= $code_insee ?> = <?=  file_get_contents(__CA_APP_DIR__."/plugins/carteCMN/cadastre/cadastre-".$code_insee."-parcelles.json"); ?>;
		var parcels_to_display_<?= $code_insee ?> = ["<?php print str_replace("\n", "", implode("\", \"", explode(";", $parcels_to_display[$i]))); ?>"];

<?php
	endforeach;
else: 
?>
		var parcelles_ = [];
		var parcels_to_display_ = [];
<?php

endif; ?>


</script>
<div id="back" style='padding:10px;'>
	<button class='btn-default' onclick="window.history.back();">Retour</button>
	<button class='btn-default' onclick="toggleFullscreen()">Plein écran</button>
	<div class="pull-right">Départements visibles <span id="dptsVisibles"></span> <button class='btn-default' onClick="$('#legende').toggle()">Aide</button></div>
</div>
<div id="map"></div>

<div id="legende" style="display:none;position:absolute;top:130px;left:40px;width:750px;z-index:10000;background-color:white;padding:20px;">
<div class="container help">
	<div class="col-md-12">
		<h3>Aide</h3>
		<h4>Parcelles CMN</h3>
		<p>Les parcelles remises en dotation au CMN sont affichées en orange. Cliquez sur une parcelle pour obtenir son numéro éventuellement accéder au site du cadastre dans une nouvelle fenêtre.</p>
		<h4>Cadastre</h4>
		<p>Les percelles cadastrales s'affichent individuellement, ecadrées de noir et désignées par leur numéro. Elles sont regroupées par section, à bordure brune</p>
		<p>Les couches issues du cadastre sont chargées à la volée et ne permettent pas d'être incluses dans l'export PNG (en bas à droite de l'écran). Pour obtenir un export image avec la couche du Cadastre, vous devez faire une capture d'écran.</p>
		<h4>Natura 2000 (ZPS)</h4>
		<p>Les Zones de Protection Spéciale sont affichées en bleu. Attention, il n'existe pas de couche géographique pour la totalité du territoire. Les régions Pays-de-la-Loire et Ile-de-France disposent de couches globales à l'échelle de la région. Seules ces deux régions sont ici disponibles.</p>
		<h4>Monuments Historiques</h4>
		<p>Les monuments historiques sont affichés sous forme de points d'intérêts, avec une icône reprenant le logo MH. Cliquez sur un monument pour obtenir des informations sur celui-ci.</p>
		<p>Pour des raisons de performances dans les navigateurs, les monuments historiques ne sont pas affichés sur la carte par défaut. Vous pouvez les afficher en cliquant sur la couche "Monuments Historiques" dans le menu des couches.</p>
		<p>Pour des raisons de performances, seul <b>un affichage contenant maximum 5 départements</b> permettra d'afficher les MH.</p>
		<h4>Parc immobilier de l'État</h4>
		<p>Les bâtiments du parc immobilier de l'Etat sont affichés sous forme de points d'intérêts, avec une icône reprenant le logo de l'état français. Cliquez sur un bâtiment pour obtenir des informations sur celui-ci.</p>
		<h4>Sites patrimoniaux remarquables (SPR)</h4>
		<p>Les communes comportant des sites patrimoniaux remarquables sont affichées sous forme de calques de couleur (à préciser). Cliquez sur une commune concernée pour obtenir des informations sur le site du patrimoine remarquable en question.</p>
	</div>
</div>
<style>
    #map {
		width: 100%; 
		height:calc(100vh - 158px); 
		margin: auto; 
	}
	.container > .row > .col-xs-12 {
		padding:0;
	}
	body > nav.navbar {
		margin-bottom: 0 !important;
	}
	.leaflet-touch .leaflet-bar a#print-btn {
		width:auto;
		padding:0 8px;
		font-weight: bold;
	}
	#print-btn-disabled {
		width: auto;
		padding: 0 8px;
		background: white;
		font-size:0.8em;
	}
	.container.help p {
		font-size:16px;
	}
</style>

<script type='text/javascript'>
	function toggleFullscreen () {
		const element = document.getElementById("map");
		if (element.requestFullscreen) {
			element.requestFullscreen();
		} else if (element.mozRequestFullScreen) { // Firefox
			element.mozRequestFullScreen();
		} else if (element.webkitRequestFullscreen) { // Chrome, Safari and Opera
			element.webkitRequestFullscreen();
		} else if (element.msRequestFullscreen) { // IE/Edge
			element.msRequestFullscreen();
		}
	}

	var map; 
	// Départements
	dpts_group = new L.FeatureGroup();
	// Unesco WH
	unesco_group = new L.FeatureGroup();
	// Immeubles protégés au titre des Monuments Historiques
	mh_group = new L.FeatureGroup();
	// Parc immobilier de l'Etat
	pie_group = new L.FeatureGroup();
	// Sites du patrimoine remarquables
	spr_group = new L.FeatureGroup();
	var Cadastre;
	var dpts;
	var visibleDpts=[];

	var mhIcon = L.icon({
		iconUrl: '/app/plugins/carteCMN/mh/marker-mh-icon.png',
		shadowUrl: '/app/plugins/carteCMN/mh/marker-mh-shadow.png',
		iconSize:[25,41], shadowSize:[41,41], iconAnchor:[12,41], popupAnchor:[0,-28]
	});

	var pieIcon = L.icon({
		iconUrl: '/app/plugins/carteCMN/batiments-etat/marker-pie-icon.png',
		shadowUrl: '/app/plugins/carteCMN/batiments-etat/marker-pie-shadow.png',
		iconSize:[25,41], shadowSize:[41,41], iconAnchor:[12,41], popupAnchor:[0,-28]
	});

	var markerIcon = L.icon({
		iconUrl: '/app/plugins/carteCMN/marker-icon.png',
		shadowUrl: '/app/plugins/carteCMN/batiments-etat/marker-pie-shadow.png',
		iconSize:[25,41], shadowSize:[41,41], iconAnchor:[12,41], popupAnchor:[0,-28]
	});

	var whIcon = L.icon({
		iconUrl: '/app/plugins/carteCMN/marker-wh.png',
		iconSize:[40,40], iconAnchor:[20,20], popupAnchor:[0,-28]
	});

	function getFeaturesInView() {
		var features = [];
		map.eachLayer( function(layer) {
			if(layer instanceof L.Marker) {
				if(map.getBounds().contains(layer.getLatLng())) {
					features.push(layer.feature);
				}
			}
		});
		return features;
	}

	function onDragEnd() {
		var width = map.getBounds().getEast() - map.getBounds().getWest();
		var height = map.getBounds().getNorth() - map.getBounds().getSouth();
		visibleDpts=[];
		if (typeof dpts === 'undefined' || !dpts) { return; }
		dpts.eachLayer(function(layer) {
			if(layer.getBounds().intersects(map.getBounds())) {
				visibleDpts.push(layer.feature.properties.code);
			} else {
				//console.log("this layer is not visible");
			}
		});
		if(visibleDpts.length > 5) {
			mh_group.clearLayers();
			pie_group.clearLayers();
			spr_group.clearLayers();
			$('#dptsVisibles').html("> 5");
		} else {
			$('#dptsVisibles').html(visibleDpts.join(", "));
		}

		// Immeubles protégés au titre des Monuments Historiques
		var promise = $.getJSON("https://cmn.ideesculture.fr/index.php/carteCMN/Show/mhGeojson/departement/"+visibleDpts.join(","));
	    promise.then(function(data) {
			var mh = L.geoJson(data, {
				onEachFeature: function (feature, layer) {
					layer.bindPopup('<b>'+feature.properties.denomination_de_l_edifice+'</b><br/>'+'<a target=_blank href="'+feature.properties.lien_vers_la_base_archiv_mh+'">Monument historique</a><br/>'+'</p>');
				}
			});
			mh.eachLayer(function(layer) {
				layer.setIcon(mhIcon);
			});
			mh_group.clearLayers();
			mh.addTo(mh_group);
		});
		// Parc immobilier de l'Etat
		var promise2 = $.getJSON("https://cmn.ideesculture.fr/index.php/carteCMN/Show/batimentsEtatGeojson/departement/"+visibleDpts.join(","));
	    promise2.then(function(data) {
			var batiments = L.geoJson(data, {
				onEachFeature: function (feature, layer) {
					layer.bindPopup('<b>'+feature.properties.designation_site+'</b><br/>'+feature.properties.type+'<br/>'+feature.properties.ministere);
				}
			});
			batiments.eachLayer(function(layer) {
				layer.setIcon(pieIcon);
			});
			pie_group.clearLayers();
			batiments.addTo(pie_group);
		});
		// Sites patrimoniaux remarquables
		var sprStyle = {
			"color": "#ff9b09",
			"weight": 5,
			"opacity": 0.65
		};
		var promise3 = $.getJSON("https://cmn.ideesculture.fr/index.php/carteCMN/Show/sprGeojson/departement/"+visibleDpts.join(","));
	    promise3.then(function(data) {
			var spr = L.geoJson(data, {
				style: sprStyle,
				onEachFeature: function (feature, layer) {
					if(feature.properties.spr_initial_regime_de_creation === null) {
						feature.properties.spr_initial_regime_de_creation = "";
					}
					if(feature.properties.numero_du_spr === null) {
						feature.properties.numero_du_spr = "";
					}
					layer.bindPopup('<b>SPR '+feature.properties.commune+'</b><br/>'+feature.properties.spr_initial_regime_de_creation+'<br/>'+feature.properties.numero_du_spr+'<br/>'+feature.properties.code_insee);
				}
			});
			spr_group.clearLayers();
			spr.addTo(spr_group);
		});
	}

	jQuery(document).ready(function() {

		map = L.map('map', {
                 center: <?= $georeference ?>,
                 zoom: 17,
				minZoom:2,
			maxZoom:21 });
   
		// Ajout fonds de carte (tile et WMS)
		var baselayers = {
			OSM: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
		};
		var Stadia_Outdoors = L.tileLayer('https://tiles.stadiamaps.com/tiles/outdoors/{z}/{x}/{y}{r}.png', {
			maxZoom: 20,
			attribution: '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>, &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'
		});
		var OpenStreetMap_France = L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
			maxZoom: 20,
			attribution: '&copy; OpenStreetMap France | &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		});
		var Thunderforest_landscape = L.tileLayer('https://tile.thunderforest.com/landscape/{z}/{x}/{y}.png?apikey=04222a8063184a029feabd8008ede7d1', {
			attribution: '&copy; <a href="http://www.thunderforest.com/">Thunderforest</a>, &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
			apikey: '04222a8063184a029feabd8008ede7d1',
			maxZoom: 22
		});
		var Thunderforest_atlas = L.tileLayer('https://tile.thunderforest.com/atlas/{z}/{x}/{y}.png?apikey=04222a8063184a029feabd8008ede7d1', {
			attribution: '&copy; <a href="http://www.thunderforest.com/">Thunderforest</a>, &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
			apikey: '04222a8063184a029feabd8008ede7d1',
			maxZoom: 22
		});
		var baselayers = {
			Atlas: Thunderforest_atlas,
			Landscape : Thunderforest_landscape,
			OpenStreetmap: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png')
		};
		Thunderforest_atlas.addTo(map);
		//Thunderforest_landscape.addTo(map);
		//Stadia_Outdoors.addTo(map);


		// Ajout du bati en wms comme couche 
		var Parcelbati = L.tileLayer.wms('https://mapsref.brgm.fr/wxs/refcom-brgm/refign', 
								{layers: 'PARVEC_BATIMENT',format: 'image/png',transparent:true}); 
		
		// IGN Géoplateforme — cadastre overlay (PCI vectorized, transparent PNG)
		Cadastre = L.tileLayer(
			'https://data.geopf.fr/wmts?' +
			'SERVICE=WMTS&REQUEST=GetTile&VERSION=1.0.0' +
			'&LAYER=CADASTRALPARCELS.PARCELLAIRE_EXPRESS' +
			'&STYLE=normal&FORMAT=image/png' +
			'&TILEMATRIXSET=PM&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}',
			{
				attribution: 'Cadastre © <a href="https://www.ign.fr/">IGN</a>/Géoplateforme',
				maxNativeZoom: 19,
				maxZoom: 22,
				opacity: 0.7,
				tileSize: 256
			}
		);
		
		// Ajout des amanegements cyclables en wms comme couche 
		var marker = L.marker(<?= $georeference?>, {icon: markerIcon}).addTo(map);

		parcelles_group = new L.FeatureGroup();

		// Traitement de chacune des communes
<?php foreach($codes_insee as $code_insee) : ?>
		var parcelles_<?= $code_insee ?>_layer = L.geoJSON(parcelles_<?= $code_insee ?>, {
			
			style: {
				"color": "#3173a9",
				"weight": 5,
				"opacity": 1
			},
			filter: function(feature, layer) {
				//console.log(feature.properties);
				//console.log(feature.properties.section+feature.properties.numero);
				//console.log(parcels_to_display_<?= $code_insee ?>.includes(feature.properties.section+feature.properties.numero));
				return parcels_to_display_<?= $code_insee ?>.includes(feature.properties.section+feature.properties.numero);
    		},
			pointToLayer: function (feature, latlng) {
				return L.circleMarker(latlng, {
					radius: 8,
					fillColor: "#3173a9",
					color: "#000",
					weight: 1,
					opacity: 1,
					fillOpacity: 1
				});
			},
			onEachFeature: function (feature, layer) {
				//console.log(map.getCenter());
				let center = map.getCenter();
				//console.log();
				var bounds = layer.getBounds();
				var latLng = bounds.getCenter();
					
				layer.bindPopup("<?= $nom_commune[$code_insee] ?><br/> <a target='_blank' class='parcelle-click' data-latlng='"+latLng.lat+"/"+latLng.lng+"' data-href='https://cadastre.data.gouv.fr/map?style=ortho&parcelleId="+feature.properties.id+"'>Parcelle "+feature.properties.section+feature.properties.numero+"</a><br/><?php print $cadastre_commentaire ?>");				
				// https://cadastre.data.gouv.fr/map?style=ortho&parcelleId=71137000AN0397#18.55/46.4347472/4.659527
			} 
		});
		
		parcelles_group.addLayer(parcelles_<?= $code_insee ?>_layer);
<?php endforeach; ?>		
		//map.addLayer(parcelles_group);

		$("#map").on('click', ".parcelle-click", function() {
			let url = $(this).data("href")+"#"+(map.getZoom()-1)+"/"+$(this).data("latlng");
			window.open(url, '_blank').focus();
		});

		
		// Echelle cartographique
		L.control.scale().addTo(map);

		async function load_shapefile(url) {
			//let url = '';
			const response = await fetch(url)
			const shape_obj = await response.json();
			return shape_obj;
	    }

		// Natura 2000 — TopoJSON locaux (France métropolitaine), rendu via L.geoJSON natif
		// zsp.json contient les ZSC/SIC (habitats), zps.json les ZPS (oiseaux)
		// Naming des fichiers conservé tel que dans la source mapgeodata.fr.
		zps_group = L.featureGroup();
		var natura_styles = {
			zsc: { fillColor: 'orange', fillOpacity: 0.4, color: 'orange', weight: 1 },
			zps: { fillColor: 'green',  fillOpacity: 0.4, color: 'green',  weight: 1 }
		};
		function bindNaturaPopup(label) {
			return function(feature, layer) {
				var p = feature.properties || {};
				var eea = 'https://natura2000.eea.europa.eu/Natura2000/SDF.aspx?site=' + encodeURIComponent(p.SITECODE || '');
				var html = '<b>Natura 2000 (' + label + ')</b><br/>'
					+ '<a href="' + eea + '" target="_blank" rel="noopener">' + (p.SITENAME || 'site sans nom') + '</a><br/>'
					+ '<small>' + (p.SITECODE || '') + '</small>';
				layer.bindPopup(html, {maxWidth: 320});
			};
		}
		function loadNaturaLayer(url, styleKey, label) {
			var data = omnivore.topojson(url);
			data.on('ready', function() {
				var geo = data.toGeoJSON();
				var gl = L.geoJSON(geo, {
					style: natura_styles[styleKey],
					onEachFeature: bindNaturaPopup(label)
				});
				gl.addTo(zps_group);
			});
			data.on('error', function(err) { console.error('Natura 2000 load error for ' + url, err); });
		}
		loadNaturaLayer('/app/plugins/carteCMN/natura2000/zsp.json', 'zsc', 'ZSC');
		loadNaturaLayer('/app/plugins/carteCMN/natura2000/zps.json', 'zps', 'ZPS');

		var dptsStyle = {
			"color": "#555555",
			"weight": 2,
			"opacity": 0.50,
			"fill": false
		};
		var promise = $.getJSON("https://cmn.ideesculture.fr/app/plugins/carteCMN/departements/departements.geojson");
		promise.then(function(data) {
			console.log("dpts");
			console.log(data);
			dpts = L.geoJson(data, {
				style: dptsStyle,
				onEachFeature: function (feature, layer) {
					//console.log(feature.properties);
					//layer.bindPopup('<b>dpts '+feature.properties.commune+'</b><br/>'+feature.properties.spr_initial_regime_de_creation+'<br/>'+feature.properties.numero_du_spr+'<br/>'+feature.properties.code_insee);
				}
			});
			dpts.addTo(dpts_group);
		});
		map.addLayer(dpts_group);

		var whStyle = {
			"color": "#555555",
			"weight": 2,
			"opacity": 0.50,
			"fill": false
		};
		var promise = $.getJSON("https://cmn.ideesculture.fr/unesco-wh.geojson");
		promise.then(function(data) {
			console.log("unesco-wh");
			console.log(data);
			wh = L.geoJson(data, {
				style: whStyle,
				onEachFeature: function (feature, layer) {
					//console.log(feature.properties);
					//layer.bindPopup('<b>dpts '+feature.properties.commune+'</b><br/>'+feature.properties.spr_initial_regime_de_creation+'<br/>'+feature.properties.numero_du_spr+'<br/>'+feature.properties.code_insee);
					layer.bindPopup('UNESCO WH<br/><b>'+feature.properties.element_name_en+'</b><br/>'+feature.properties.property_name_en);
				}
			});
			wh.eachLayer(function(layer) {
				layer.setIcon(whIcon);
			});
			wh.addTo(unesco_group);
		});
		//map.addLayer(unesco_group);
		
		// Selecteur fonds de carte
		L.control.layers(
			baselayers,  // Couche de base
			{"Bâtiments": Parcelbati, "Cadastre": Cadastre, "Périmètre CMN": parcelles_group, "Natura 2000": zps_group, "Monuments Historiques": mh_group, "Parc immobilier de l'Etat": pie_group, "Sites patrimoniaux remarquables": spr_group, "Unesco WH":unesco_group},  // Couches de données
			{collapsed : false} // Options d'affichage
		).addTo(map);	 
		//$("#map").hide();

		L.control.bigImage({position: 'bottomright', title: 'Télécharger', printControlLabel: '💾 Télécharger', printControlTitle: 'Télécharger', inputTitle:'Niveau de zoom', downloadTitle:"Télécharger l'image", maxScale:"1", minScale:"1", opacity:'1'}).addTo(map);

		map.on('dragend', onDragEnd);
		map.on('zoomend', onDragEnd);

		setTimeout(function() {
			onDragEnd();
		}, 1000);

		$(".leaflet-control-layers-overlays input").click(function() {
			$("#print-btn-disabled").remove();
			$("a#print-btn").show();
		});
	});
</script>
<style>
	#print-params div.close,
	#print-params h6,
	#print-params input {
		display: none;
	}
	.download-button {
		display: inherit;
		box-shadow: none;
	}
</style>