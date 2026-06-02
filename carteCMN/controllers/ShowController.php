<?php
    /* ----------------------------------------------------------------------
     * simpleListEditor
     * ----------------------------------------------------------------------
     * List & list values editor plugin for Providence - CollectiveAccess
     * Open-source collections management software
     * ----------------------------------------------------------------------
     *
     * Plugin by idéesculture (www.ideesculture.com)
     * This plugin is published under GPL v.3. Please do not remove this header
     * and add your credits thereafter.
     *
     * File modified by :
     * ----------------------------------------------------------------------
     */

    require_once(__CA_MODELS_DIR__."/ca_objects.php");

 	class ShowController extends ActionController {
 		# -------------------------------------------------------
  		protected $opo_config;		// plugin configuration file
        protected $opa_list_of_lists; // list of lists
        protected $opa_listIdsFromIdno; // list of lists
        protected $opa_locale; // locale id
		private $opo_list;
 		# -------------------------------------------------------
 		# Constructor
 		# -------------------------------------------------------

 		public function __construct(&$po_request, &$po_response, $pa_view_paths=null) {
            parent::__construct($po_request, $po_response, $pa_view_paths);
 			// NO RIGHTS CHECKED FOR NOW
 			/*if (!$this->request->user->canDoAction('can_use_simplelisteditor_plugin')) {
 				$this->response->setRedirect($this->request->config->get('error_display_url').'/n/3000?r='.urlencode($this->request->getFullUrlPath()));
 				return;
 			}*/
/*
 			$this->opo_config = Configuration::load(__CA_APP_DIR__.'/plugins/conf/carteCMN.conf');
             $this->plugin_path = __CA_APP_DIR__ . '/plugins/carteCMN';
            
            // Extracting theme name to properly handle views in distinct theme dirs
            $vs_theme_dir = explode("/", $po_request->getThemeDirectoryPath());
            $vs_theme = end($vs_theme_dir);
            $this->opa_view_paths[] = $this->plugin_path."/themes/".$vs_theme."/views";
            var_dump($this->opa_view_paths[]);die();*/
 			
        }

		public function mhGeojson() {
			$geojson_path = __CA_APP_DIR__.'/plugins/carteCMN/mh/';
			$departement = $this->request->getParameter("departement", pString);
			
			//$geojson = file_get_contents('liste-des-immeubles-proteges-au-titre-des-monuments-historiques-'.$departement.'.geojson');
			// S'il existe déjà un fichier pour le département demandé, affiche le
			if (file_exists($geojson_path.'liste-des-immeubles-proteges-au-titre-des-monuments-historiques-'.$departement.'.geojson')) {
				echo file_get_contents($geojson_path.'liste-des-immeubles-proteges-au-titre-des-monuments-historiques-'.$departement.'.geojson');
				exit;
			}

			// Charge le fichier "liste-des-immeubles-proteges-au-titre-des-monuments-historiques.geojson" dans la variable $geojson
			$geojson = file_get_contents($geojson_path.'liste-des-immeubles-proteges-au-titre-des-monuments-historiques.geojson');
			// Parse le contenu de $geojson dans la variable $data
			$data = json_decode($geojson);
			// Pour voir un enregistrement exemple, décommenter ce qui suit
			//var_dump($data->features[0]);
			//die();
			$depts = explode(",", $departement);
			if(sizeof($depts) > 5) {
				$geojson = '{"type":"FeatureCollection","features":[]}';
				// Stocke le résultat dans un fichier avec suffixe le code du département puis geojson
				file_put_contents($geojson_path.'liste-des-immeubles-proteges-au-titre-des-monuments-historiques-'.$departement.'.geojson', $geojson);
				// Retourne le contenu de $geojson
				echo $geojson;
				exit;
			}
		
			// Crée un tableau vide dans la variable $immeubles
			$immeubles = [];
		
			foreach($depts as $dept) {
				// Pour chaque élément de $data
				foreach ($data->features as $feature) {
					// Si le département de l'élément est égal à la valeur de la variable $departement
					if ($feature->properties->departement_format_numerique === $dept) {
						// Ajoute l'élément à la variable $immeubles
						$immeubles[] = $feature;
					}
				}
			}
			// Encode le contenu de $immeubles dans la variable $geojson
			$geojson = json_encode($immeubles);
			$geojson = '{"type":"FeatureCollection","features":'.$geojson.'}';
			// Stocke le résultat dans un fichier avec suffixe le code du département puis geojson
			file_put_contents($geojson_path.'liste-des-immeubles-proteges-au-titre-des-monuments-historiques-'.$departement.'.geojson', $geojson);
			// Retourne le contenu de $geojson
			echo $geojson;
			exit;
		}

		public function communeSprGeojson() {
			$geojson_path = __CA_APP_DIR__.'/plugins/carteCMN/spr/';
			$departement = $this->request->getParameter("departement", pString);

			// parse le fichier a-com2022.json qui contient les coordonnées de chaque commune en fonction de son code INSEE
			$communes = json_decode(file_get_contents($geojson_path.'a-com2022.json'));
			ob_start();
			print "Création des fichiers de géométrie des communes...<br>";
			ob_flush();
			//var_dump($communes->features[0]);die();
			foreach($communes->features as $commune) {
				$code_insee = $commune->properties->codgeo;
				$geometry = $commune->geometry;
				if (!file_exists($geojson_path.'geometry_communes/'.$code_insee.'.json')) {
					file_put_contents($geojson_path.'geometry_communes/'.$code_insee.'.json', json_encode($geometry));	
				}
			}
			print "Fini !<br>";
			exit;
		}

		public function sprGeojson() {
			$geojson_path = __CA_APP_DIR__.'/plugins/carteCMN/spr/';
			$departement = $this->request->getParameter("departement", pString);

			// S'il existe déjà un fichier pour le département demandé, affiche le
			if (file_exists($geojson_path.'spr-'.$departement.'.geojson')) {
				echo file_get_contents($geojson_path.'spr-'.$departement.'.geojson');
				exit;
			}
			$depts = explode(",", $departement);
			if(sizeof($depts) > 5) {
				$geojson = '{"type":"FeatureCollection","features":[]}';
				// Stocke le résultat dans un fichier avec suffixe le code du département puis geojson
				file_put_contents($geojson_path.'spr-'.$departement.'.geojson', $geojson);
				// Retourne le contenu de $geojson
				echo $geojson;
				exit;
			}
			$sprs = json_decode(file_get_contents($geojson_path.'liste-des-sites-patrimoniaux-remarquables-spr.json'));
			$features = [];
			foreach($depts as $dept) {
				foreach($sprs as $spr) {
					$feature = new stdClass();
					if ($spr->code_departement === $dept) {
						foreach($spr as $key => $value) {
							$feature->properties->$key = $value;
						}
					} else {
						continue;
					}
					$code_insee = $spr->code_insee;
					$geometry = json_decode(file_get_contents($geojson_path.'geometry_communes/'.$code_insee.'.json'));
					$feature->geometry = $geometry;
					$feature->type = "Feature";
					$features[] = $feature;
				}
			}
			$geojson = json_encode($features, JSON_PRETTY_PRINT);
			$geojson = '{"type":"FeatureCollection","features":'.$geojson.'}';
			print $geojson;
			file_put_contents($geojson_path.'spr-'.$departement.'.geojson', $geojson);

			exit;
		}

		public function batimentsEtatGeojson() {
			$geojson_path = __CA_APP_DIR__.'/plugins/carteCMN/batiments-etat/';
			$departement = $this->request->getParameter("departement", pString);
			// S'il existe déjà un fichier pour le département demandé, affiche le
			if (file_exists($geojson_path.'parc_immobilier_etat_20211231-'.$departement.'.geojson')) {
				echo file_get_contents($geojson_path.'parc_immobilier_etat_20211231-'.$departement.'.geojson');
				exit;
			}

			$depts = explode(",", $departement);
			if(sizeof($depts) > 5) {
				$geojson = '{"type":"FeatureCollection","features":[]}';
				// Stocke le résultat dans un fichier avec suffixe le code du département puis geojson
				file_put_contents($geojson_path.'parc_immobilier_etat_20211231-'.$departement.'.geojson', $geojson);
				// Retourne le contenu de $geojson
				echo $geojson;
				exit;
			}

			// Charge le fichier "parc_immobilier_etat_20211231.geojson" dans la variable $geojson
			$geojson = file_get_contents($geojson_path.'parc_immobilier_etat_20211231.geojson');
			// Parse le contenu de $geojson dans la variable $data
			$data = json_decode($geojson);
			// Pour voir un enregistrement exemple, décommenter ce qui suit
			//var_dump($data->features[0]);die();

			// Crée un tableau vide dans la variable $immeubles
			$immeubles = [];
			foreach($depts as $dept) {
				// Pour chaque élément de $data
				foreach ($data->features as $feature) {
					// Si le département de l'élément est égal à la valeur de la variable $departement
					if ($feature->properties->dept === $dept) {
						// Ajoute l'élément à la variable $immeubles
						$immeubles[] = $feature;
					}
				}
			}
			// Encode le contenu de $immeubles dans la variable $geojson
			$geojson = json_encode($immeubles);
			$geojson = '{"type":"FeatureCollection","features":'.$geojson.'}';
			// Stocke le résultat dans un fichier avec suffixe le code du département puis geojson
			file_put_contents($geojson_path.'parc_immobilier_etat_20211231-'.$departement.'.geojson', $geojson);
			// Retourne le contenu de $geojson
			echo $geojson;
			die();
		}

 		# -------------------------------------------------------
 		# Functions to render views
 		# -------------------------------------------------------
 		public function Index() {
 			$id = $this->request->getParameter("id", pInteger);
            $t_object = new ca_objects($id);
            $georeference = $t_object->getWithTemplate("^ca_objects.georeference");

            $this->view->setVar("georeference", $georeference);
            $this->view->setVar("t_object", $t_object);
            $this->render('index_html.php');
 		}

 		
 	}
 ?>
