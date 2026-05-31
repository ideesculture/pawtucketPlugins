<?php
ini_set("display_errors", 1);
error_reporting(E_ERROR);
require_once(__CA_MODELS_DIR__.'/ca_site_pages.php');
require_once(__CA_APP_DIR__.'/plugins/Articles/lib/articles_functions.php');

class FrontController extends ActionController
{
    # -------------------------------------------------------
    protected $opo_config;        // plugin configuration file
    protected $opa_list_of_lists; // list of lists
    protected $opa_listIdsFromIdno; // list of lists
    protected $opa_locale; // locale id
    private $opo_list;
    private $plugin_path;
    # -------------------------------------------------------
    # Constructor
    # -------------------------------------------------------

    public function __construct(&$po_request, &$po_response, $pa_view_paths = null)
    {
        parent::__construct($po_request, $po_response, $pa_view_paths);
        $this->plugin_path = __CA_APP_DIR__ . '/plugins/Articles';

        $this->opo_config = Configuration::load(__CA_APP_DIR__ . '/plugins/Articles/conf/articles.conf');

        // Extracting theme name to properly handle views in distinct theme dirs
        $vs_theme_dir = explode("/", $po_request->getThemeDirectoryPath());
        $vs_theme = end($vs_theme_dir);
        $this->opa_view_paths[] = $this->plugin_path."/themes/".$vs_theme."/views";
    }

    # -------------------------------------------------------
    # Functions to render views
    # -------------------------------------------------------
    public function Index2($type = "")
    {
        global $g_ui_locale;
        
        // Get  all the pages
        $pages = ca_site_pages::getPageList();
        // Reordering to have the newest at the beginning
        $pages = array_reverse($pages);

	    	$blocks = "";
        $i = 1;
        
        foreach($pages as $page) {
            // Limit to the 3 last ids
            if($i>3) break;

            $vt_page = new ca_site_pages($page["page_id"]);
            // Skip non published articles
            if(!$vt_page->get("access")) continue;
            if($vt_page->get("template_id") == 6) continue;

            
            $keywords = explode(",",$vt_page->get("keywords"));
            //print $g_ui_locale;
            $langue = substr($g_ui_locale, 0, 2);
            if(!in_array($langue,$keywords)) continue;

            $article = $vt_page->get("content");
            if($article["date_from"]) {
                $date_from = $article["date_from"];
                // Ignore if the article is to be published in the future
                if(time() < strtotime($date_from)) continue;
            }
            if($article["date_to"]) {
                    $date_to = $article["date_to"];
                    // Ignore if the article is to be published in the future
                    if(time() > strtotime($date_to)) continue;
            }
            $this->view->setVar("article", $article);
            $this->view->setVar("id", $page["page_id"]);
            $this->view->setVar("template_title", $page["template_title"]);
            $this->view->setVar("template_id", $vt_page->get("template_id"));
            $blocks .= $this->render("front/front_block_html.php", true);
            $i++;
        }
        //$page = new ca_site_pages(1);
        $this->view->setVar("blocks", $blocks);
        $this->render('front/front_page_html.php');
    }

    public function Index($type = "")
    {
        global $g_ui_locale;

        $vt_user = $this->request->getUser();
        $roles = $vt_user->getUserGroups();
        $is_redactor = false;
        foreach($roles as $role) {
            if($role["code"]=="redactor") {
                $is_redactor = true;}
        }
		
	    $all_articles = ca_site_pages::getPageList();
	    $all_articles = array_reverse($all_articles);
	    $articles = [];
	    foreach ($all_articles as $testarticle) {
	        if ($testarticle["template_title"]=="article") {
                $articles[] = $testarticle;
            }
        }
        $blocks = "";
        $i = 1;
        foreach($articles as $page) {
            // Limit to the 3 last ids
           // if($i>3) break;

            $vt_page = new ca_site_pages($page["page_id"]);
            // Skip non published articles
            if(!$vt_page->get("access") && !$is_redactor) continue;
            if($vt_page->get("template_id") == 6) continue;

            
            $keywords = explode(",",$vt_page->get("keywords"));
            //print $g_ui_locale;
            $langue = substr($g_ui_locale, 0, 2);
            if(!in_array($langue,$keywords)) continue;
            $article = $vt_page->get("content");
            if($article["date_from"] && !$is_redactor) {
                $date_from = $article["date_from"];
                // Ignore if the article is to be published in the future
                if(time() < strtotime($date_from)) continue;
            }
            if($article["date_to"]  && !$is_redactor) {
                    $date_to = $article["date_to"];
                    // Ignore if the article is to be published in the future
                    if(time() > strtotime($date_to)) continue;
            }
            $articleSorted[] = ["page_id"=>$page["page_id"], "date_from" =>$article["date_from"], "template_title" => $page["template_title"]];
        }
        usort($articleSorted, 'date_compare');
        
        foreach ($articleSorted as $art) {
            // Limit to the 6 last ids
            if($i>5) break;        
            $page = new ca_site_pages($art["page_id"]);
            $article = $page->get("content");
            if(!$page->get("access") && !$is_redactor) continue;
            $this->view->setVar("article", $article);
            $keywords = explode(",",$page->get("keywords"));
            //print $g_ui_locale;
            $langue = substr($g_ui_locale, 0, 2);
            if(!in_array($langue,$keywords)) continue;

            if($article["date_from"] && !$is_redactor) {
                $date_from = $article["date_from"];
                // Ignore if the article is to be published in the future
                if(time() < strtotime($date_from)) continue;
            }
            if($article["date_to"] && !$is_redactor) {
                $date_to = $article["date_to"];
                // Ignore if the article is to be published in the future
                if(time() > strtotime($date_to)) continue;
            }
            
            $this->view->setVar("access", $page->get("access"));
            $this->view->setVar("id", $art["page_id"]);
            $this->view->setVar("is_redactor", $is_redactor);
            $blocks .= $this->render("front/front_block_html.php", true);
            $i++;
        }
        
        //$page = new ca_site_pages(1);
        $this->view->setVar("blocks", $blocks);
		//var_dump($blocks);die();
        $this->render('front/front_page_html.php');
    }
}
?>
