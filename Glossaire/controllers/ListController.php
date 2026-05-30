<?php
ini_set("display_errors", 1);
error_reporting(E_ERROR);
require_once(__CA_MODELS_DIR__.'/ca_site_pages.php');

class ListController extends ActionController
{
    # -------------------------------------------------------
    protected $opo_config;        // plugin configuration file
    private $plugin_path;
	private $opa_list;
    # -------------------------------------------------------
    # Constructor
    # -------------------------------------------------------

    public function __construct(&$po_request, &$po_response, $pa_view_paths = null)
    {
        parent::__construct($po_request, $po_response, $pa_view_paths);
        $this->plugin_path = __CA_APP_DIR__ . '/plugins/Glossaire';

        $this->opo_config = Configuration::load(__CA_APP_DIR__ . '/plugins/Glossaire/conf/glossaire.conf');

        // Extracting theme name to properly handle views in distinct theme dirs
        $vs_theme_dir = explode("/", $po_request->getThemeDirectoryPath());
        $vs_theme = end($vs_theme_dir);
        $this->opa_view_paths[] = $this->plugin_path."/themes/".$vs_theme."/views";
    }

    # -------------------------------------------------------
    # Functions to render views
    # -------------------------------------------------------
    public function Index() {
		// get the root of the ca_lists list_code = glossaire
		$vt_list = new ca_lists();
		$this->opa_list = $vt_list->load(["list_code"=>"glossaire", "deleted"=>0]);
		$vn_root_id = $vt_list->getRootItemIDForList();

		// for all the children of the root, get their title and description
		$vt_root = new ca_list_items($vn_root_id);
		$va_children = $vt_root->getHierarchyChildren($vn_root_id);

		// $va_children is an array of items, sort those by idno property (trimmed and without accents)
		usort($va_children, function($a, $b) {
			$idno_a = trim(caRemoveAccents($a["idno"]));
			$idno_b = trim(caRemoveAccents($b["idno"]));
			return strcmp(strtolower($idno_a), strtolower($idno_b));
		});

		$this->view->setVar("items", $va_children);
		$this->render("index_html.php");
	}
}