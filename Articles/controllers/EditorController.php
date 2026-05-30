<?php
ini_set("display_errors", 1);
error_reporting(E_ERROR);
require_once(__CA_MODELS_DIR__.'/ca_site_pages.php');

class EditorController extends ActionController
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
    # Internal utilities
    # -------------------------------------------------------
    private function getRandomWord($len = 10) {
        $word = array_merge(range('a', 'z'), range('A', 'Z'));
        shuffle($word);
        return substr(implode($word), 0, $len);
    }

    # -------------------------------------------------------
    # Functions to render views
    # -------------------------------------------------------
    public function Index($type = "")
    {
        // Detecting through Session if we are in "partie froide" or "partie chaude"
        session_start();
        if(filter_var($_GET["partie"], FILTER_SANITIZE_STRING) == "froide") {
            $_SESSION["partie"] = "froide";
        }
        if($_SESSION["partie"] == "froide") {
            //$this->response->setRedirect(caNavUrl($this->request, "", "Phonotheque", "Partenaires"));
        }
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

            $article = $vt_page->get("content");
            $this->view->setVar("article", $article);
            $this->view->setVar("id", $page["page_id"]);
            $this->view->setVar("template_title", $page["template_title"]);
            $blocks .= $this->render("front/front_block_html.php", true);
            $i++;
        }
        //$page = new ca_site_pages(1);
        $this->view->setVar("blocks", $blocks);
        $this->render('front/front_page_html.php');
    }


    public function Article() {
        $is_redactor = false;
        foreach($this->getRequest()->getUser()->getUserGroups() as $group) {
            if($group["code"] == "redactor") $is_redactor=true;
        }
        $id= $this->request->getParameter("id", pInteger);
        $force = $this->request->getParameter("force", pInteger);
        // TODO Redirect if no ID
        $page = new ca_site_pages($id);
        $this->view->setVar("page", $page);
        //$page = new ca_site_pages(1);
        $article = $page->get("content");
        //var_dump($article);die();

        $page = new ca_site_pages($id);
        $this->view->setVar("access", $page->get("access"));

        if($force) {
            $article["blocs"] = json_encode("");
        }
        $this->view->setVar("article", $article);

        $this->view->setVar("is_redactor", $is_redactor);
        $this->view->setVar("id", $id);
        $this->render('editor_article_html.php');
    }

	public function Upload() {
		$upload_dir = __CA_BASE_DIR__ . "/upload/";	
?>
<head>
	<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.0/clipboard.min.js"></script>
</head>
<body>
	<div class="container-fluid">

<?php 
	if(!isset($_FILES['file'])) :
?>
<form method="post" enctype="multipart/form-data" action="manual.php">
	<div class="row">
<div class="col-10">Uploader une image <input type="file" id="file" class="btn" name="file">
 </div>
<div class="col-2" style="text-align: right;">   <button class="btn btn-info">Envoyer</button>
 </div>
 </div> 
</form>
<?php
	endif;
	
if(isset($_FILES['file'])) :
?>
	<div class="row">
<div class="col-6 col-sm-6">			<small><span id="foo"><?php	
	$folder       = 'files/';
	$file         = basename($_FILES['file']['name']);
	$max_filesize = 1024 * 1024 * 512; // 512 MB
	$size         = filesize($_FILES['file']['tmp_name']);
	$extensions   = array('.png', '.gif', '.jpg', '.jpeg','.pdf','.JPG','.JPEG','.PNG','.mp3','.MP3' );
	$extension    = strrchr($_FILES['file']['name'], '.');
	
	$actual_link = "https://$_SERVER[HTTP_HOST]" . "/upload/" . $folder;

	//Starting security checks
	// Check PHP upload error codes
	switch ($_FILES['file']['error']) {
		case UPLOAD_ERR_OK:
			// No error, continue with other checks
			break;
		case UPLOAD_ERR_INI_SIZE:
			$error = 'Le fichier dépasse la limite upload_max_filesize définie dans php.ini';
			break;
		case UPLOAD_ERR_FORM_SIZE:
			$error = 'Le fichier dépasse la limite MAX_FILE_SIZE du formulaire';
			break;
		case UPLOAD_ERR_PARTIAL:
			$error = 'Le fichier n\'a été que partiellement uploadé';
			break;
		case UPLOAD_ERR_NO_FILE:
			$error = 'Aucun fichier n\'a été uploadé';
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			$error = 'Dossier temporaire manquant sur le serveur';
			break;
		case UPLOAD_ERR_CANT_WRITE:
			$error = 'Échec d\'écriture du fichier sur le disque';
			break;
		case UPLOAD_ERR_EXTENSION:
			$error = 'Une extension PHP a arrêté l\'upload du fichier';
			break;
		default:
			$error = 'Erreur inconnue lors de l\'upload';
			break;
	}

	if (!isset($error) && !in_array($extension, $extensions)) {
	    //If the extension is not in the array
	    $error = 'Upload is restricted to png, gif, jpg, jpeg, pdf, mp3...';
	}
	if (!isset($error) && $size > $max_filesize) {
	    $error = 'Filesize is over the max filesize defined (512 MB)...';
	}
	
	if (!isset($error)) {
	    //No error, uploading
	    //Name reformating

	    if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir.$folder . $file)) {
	        //if true, upload has started
	        print "$actual_link$file";
	    } else {
	        print 'Upload has failed. '.$folder . $file;
	    }
		if(!$_FILES['file']['tmp_name']) {
			print "Upload has failed. No file selected.";
		}
	} else {
	    print json_encode($error);
	}
?></span></small>
 </div>
<div class="col col-sm" style="text-align: right;"><!-- Trigger -->
<button class="btn btn-info" data-clipboard-target="#foo">
    <img src="https://clipboardjs.com/assets/images/clippy.svg" style="filter: invert(100%);height:24px;width:auto" alt="Copier">
</button>
<button class="btn btn-info" onClick="window.location.href='/index.php/Articles/Editor/Upload/manual.php';">
    ↻
</button>
</div>
 </div> 
<?php endif; ?>
	</div>
</body>
<style>
	html, body {
    height: 100% !important;
    width:  100% !important;
	margin:0;
	padding:0;
	}
	</style>
    <script>
    var btns = document.querySelectorAll('button');
    var clipboard = new ClipboardJS(btns);
    clipboard.on('success', function(e) {
        console.log(e);
    });
    clipboard.on('error', function(e) {
        console.log(e);
    });
    </script>
<?php		

		die();
	}
    public function SaveArticleJson() {
        $id= $this->request->getParameter("id", pInteger);
        // TODO Redirect if no ID
        $page = new ca_site_pages($id);
        $page->setMode(ACCESS_WRITE);
        $article = $page->get("content");
        $article["blocs"]=json_encode($_POST);
        $article["blocs"]=str_replace('"false"',"false",$article["blocs"]);
        $article["blocs"]=str_replace('"true"',"true",$article["blocs"]);
        $page->set("content", $article);
        $page->update();
        if($page->numErrors()) {
            print json_encode(["result"=>"ko", "errors"=>json_encode($page->getErrors())]);
        } else {
            print json_encode(["result"=>"ok", "id"=>$id]);
        }
    }

    public function Properties() {
        $is_redactor = false;
        foreach($this->getRequest()->getUser()->getUserGroups() as $group) {
            if($group["code"] == "redactor") $is_redactor=true;
        }
        $this->view->setVar("is_redactor", $is_redactor);

        $id= $this->request->getParameter("id", pInteger);
        // TODO Redirect if no ID
        $page = new ca_site_pages($id);
        //$page = new ca_site_pages(1);
        $this->view->setVar("page", $page);
        $article = $page->get("content");
        $lang = $page->get("keywords");
        $titre = $page->get("title");

        $this->view->setVar("titre", $titre);
        $this->view->setVar("lang", $lang);
        $this->view->setVar("access", $page->get("access"));
        $this->view->setVar("article", $article);

        $this->view->setVar("id", $id);
        $this->render('editor_properties_html.php');
    }

    public function SaveArticleProperties() {
        $id= $this->request->getParameter("id", pInteger);
        // var_dump($_POST);
        // var_dump($id);
        // die();

        // TODO Redirect if no ID or if no site page corresponding the ID
        $vt_page = new ca_site_pages($id);
        $vt_page->setMode(ACCESS_WRITE);
        //$vt_page->set(["template_id"=>1, "title"=>"article...", "description"=>"", "path"=>"/path".$this->getRandomWord(), "access"=>0 ]);
        $vt_page->set("keywords", $this->request->getParameter("keywords", pString));
        $vt_page->set("title", $this->request->getParameter("titre", pString));
        $content = $vt_page->get("content");
        //var_dump($old_content);die();
        $content["title"] = $this->request->getParameter("titredisplay", pString);
        $content["subtitle"] = $this->request->getParameter("soustitre", pString);
        $content["author"]= $this->request->getParameter("auteur", pString);
        $content["date"]= $this->request->getParameter("date", pString);
        $content["date_from"]=$this->request->getParameter("date_from", pString);
        $content["date_to"]=$this->request->getParameter("date_to", pString);
        $content["image"]=$this->request->getParameter("image", pString);
        
        //"content"=>$old_content["blocks"]
        $vt_page->set("ca_site_pages.content", $content);
        $vt_page->update();

		$id = $vt_page->getPrimaryKey();

        $this->redirect("/index.php/Articles/Editor/Properties/id/".$id);
    }

    public function Publish() {
        $is_redactor = false;
        foreach($this->getRequest()->getUser()->getUserGroups() as $group) {
            if($group["code"] == "redactor") $is_redactor=true;
        }
        $id= $this->request->getParameter("id", pInteger);
        // TODO Redirect if no ID
        $page = new ca_site_pages($id);
        $page->setMode(ACCESS_WRITE);
        $page->set("access", 1);
        $page->update();

        $this->redirect("/index.php/Articles/Show/Details/id/".$id);
    }

    public function Unpublish() {
        $is_redactor = false;
        foreach($this->getRequest()->getUser()->getUserGroups() as $group) {
            if($group["code"] == "redactor") $is_redactor=true;
        }
        $id= $this->request->getParameter("id", pInteger);
        // TODO Redirect if no ID
        $page = new ca_site_pages($id);
        $page->setMode(ACCESS_WRITE);
        $page->set("access", 0);
        $page->update();

        $this->redirect("/index.php/Articles/Show/Details/id/".$id);
    }

    public function New() {
        $is_redactor = false;
        foreach($this->getRequest()->getUser()->getUserGroups() as $group) {
            if($group["code"] == "redactor") $is_redactor=true;
        }

        $template_id= $this->request->getParameter("template_id", pInteger);
        $page = new ca_site_pages();
        $page->setMode(ACCESS_WRITE);
        $page->set(["template_id"=>$template_id, "title"=>"titre...", "description"=>"", "path"=>"/path".$this->getRandomWord(), "access"=>0 ]);

        $page->insert();
        $id=$page->getPrimaryKey();
        
        if($page->getErrors()) {
            var_dump($page->getErrors());
            die();
        }
        
        $page->set("keywords", "en,fr,my,si");
        $page->set("ca_site_pages.content", ["image"=>"/banniere-bihm.jpg"]);
        $page->update();

        $this->redirect("/index.php/Articles/Editor/Properties/id/".$id);
    }

}
?>
