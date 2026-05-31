					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
require_once(__CA_APP_DIR__.'/plugins/Articles/lib/articles_functions.php');
$next = $this->getVar("next_id");
$prev = $this->getVar("prev_id");

$is_redactor = $this->getVar("is_redactor");

$access = $this->getVar("access");
$article = $this->getVar("article");

$page = $this->getVar("page");
$id = $this->getVar("id");

// sanitize page name for browse tab
$browser_tab_label = $article["title"];
$browser_tab_label = str_replace("\"", "", $browser_tab_label);
?>
<script>
    // Ajout dans l'historique
    window.parent.history.pushState('', "<?= $browser_tab_label ?>", '/index.php/Articles/Display/Details/id/<?= $id ?>');
    // Définition du titre de la page
    window.parent.document.title = "<?= $browser_tab_label ?>";
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.js" integrity="sha512-BxJRFdTKV85fhFUw+olPr0B+UEzk8FTLxRB7dAdhoQ7SXmwMECj1I4BlSmZfeoSfy0OVA8xFLTDyObu3Nv1FoQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.css" integrity="sha512-ZsHJliDVkFVbmwvOjSlsp9NhO+8Lu+qDAg0JVuXGQmh9RBgf8z1IT6tytgYVl8b6hAHUNkuhbqLFuXOkZ0VNvw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js"></script>

<?php

// Extract absolute path from URL by removing protocol and domain
if (!empty($article["image"]) && strpos($article["image"], "https://") === 0) {
    $parts = explode("/", $article["image"]);
    // Remove https:, empty string, and domain (first 3 elements)
    array_splice($parts, 0, 3);
    $article["image"] = "/" . implode("/", $parts);
}

//die();
// Check if article is programmed in the past
$is_past = false;
if ($article["date_to"]) {
    $date_to = substr($article["date_to"], 6, 4) . "-" . substr($article["date_to"], 3, 2) . "-" . substr($article["date_to"], 0, 2);
    // Ignore if the article is to be published in the future
    if (time() > strtotime($date_to)) $is_past = true;
}
// Check if article is programmed in the past
$is_future = false;
if ($article["date_from"]) {
    $date_from = substr($article["date_from"], 6, 4) . "-" . substr($article["date_from"], 3, 2) . "-" . substr($article["date_from"], 0, 2);
    // Ignore if the article is to be published in the future
    if (time() < strtotime($date_from)) $is_future = true;
}

$template_id = $page->get('template_id');
switch ($template_id) {
    case "2":
        $template = "exposition";
        break;
    case "3":
        $template = "playlist";
        break;
    case "4":
        $template = "podcast";
        break;
    default:
        $template = "article";
        break;
}
$old_path = ucfirst($template) . "s";

// Absolute site root, derived from the deployed CollectiveAccess host
$site_root = __CA_SITE_PROTOCOL__ . "://" . __CA_SITE_HOSTNAME__;
// Hosts (current + legacy) that should be stripped from stored content to make URLs relative
$strip_hosts = [$site_root . "/", "https://phoi.ideesculture.fr/", "https://www.phoi.io/"];

MetaTagManager::addMetaProperty("og:url", $site_root . "/index.php/Articles/Display/Details/id/" . $id);
MetaTagManager::addMetaProperty("og:type", "website");
MetaTagManager::addMeta("twitter:card", "summary");
$blocs = json_decode($article["blocs"], true);
$content = $blocs[1]["content"];
$content = strip_tags($content);
$content = mb_substr($content, 0, 119);
if (mb_strlen($content) == 119) {
    $content = $content . "...";
}

MetaTagManager::addMetaProperty("og:description", ($article["subtitle"] ? $article["subtitle"] : ($content ? $content  : "Phonothèque Historique de l'Océan Indien")));
MetaTagManager::addMeta("twitter:description", ($article["subtitle"] ? $article["subtitle"] : ($content ? $content  : "Phonothèque Historique de l'Océan Indien")));
if($article["title"]) {
	MetaTagManager::setWindowTitle($article["title"]);
}

MetaTagManager::addMetaProperty("og:title", $article["title"]);
MetaTagManager::addMeta("twitter:title", $article["title"]);
MetaTagManager::addMetaProperty("og:image:alt", $article["title"]);
MetaTagManager::addMetaProperty("og:image", $article["image"]);
MetaTagManager::addMeta("twitter:image", $article["image"]);

$playlisttracks = [];
?>


<div class="<?= $template ?>-phoi">
    <?php if ($is_redactor) : ?>
        <section class="section" id="buttons" style="padding-top: 0;padding-bottom: 24px;">
            <div class="container">
                <a href="/index.php/Articles/Editor/New/template_id/<?= $template_id ?>">
                    <button class="button action-btn add-new is-uppercase has-text-centered">
                        <span class="icon"><i class="mdi mdi-plus"></i></span>&nbsp; <?php _p("Nouveau"); ?>
                    </button>
                </a>
                <a href="/index.php/Articles/Editor/Properties/id/<?= $id ?>">
                    <button class="button action-btn add-new is-uppercase has-text-centered">
                        <span class="icon"><i class="mdi mdi-playlist-edit"></i></span>&nbsp; <?php _p("Propriétés"); ?>
                    </button>
                </a>
                <a href="/index.php/Articles/Editor/Article/id/<?= $id ?>" class="active">
                    <button class="button action-btn add-new is-uppercase has-text-centered">
                        <span class="icon"><i class="mdi mdi-lead-pencil"></i></span>&nbsp; <?php _p("Éditeur"); ?>
                    </button>
                </a>
                <a href="/index.php/Articles/Display/Details/id/<?= $id ?>" class="active">
                    <button class="button action-btn add-new is-uppercase has-text-centered">
                        <span class="icon"><i class="mdi mdi-eye"></i></span>&nbsp; <?php _p("Afficher"); ?>
                    </button>
                </a>
                <a href="/index.php/Articles/Editor/Versions/id/<?= $id ?>">
                    <button class="button action-btn add-new is-uppercase has-text-centered">
                        <span class="icon"><i class="mdi mdi-history"></i></span>&nbsp; <?php _p("Versions"); ?>
                    </button>
                </a>
                <button class="button action-btn add-new is-uppercase has-text-centered is-dark" onClick="$('#delete').show();">
                    <span class="icon"><i class="mdi mdi-delete"></i></span>&nbsp; <?php _p("Supprimer"); ?>
                </button>

                <div class="modal" id="delete">
                    <div class="modal-background"></div>
                    <div class="modal-card" style="margin-top:300px;">
                        <header class="modal-card-head">
                            <p class="modal-card-title">Suppression</p>
                            <button class="delete" aria-label="close"></button>
                        </header>
                        <section class="modal-card-body">
                            <p>Êtes vous sur de vouloir supprimer ce contenu ?</p>
                        </section>
                        <footer class="modal-card-foot">
                            <a href="/index.php/Articles/Show/Delete/id/<?= $id ?>"><button class="button is-danger">Supprimer</button></a>
                            <button class="button" onClick="$('#delete').hide();">Annuler</button>
                        </footer>
                    </div>
                </div>

                <?php if (!$access) : ?>
                    <a href="/index.php/Articles/Display/Publish/id/<?= $id ?>">
                        <button class="button action-btn add-new is-uppercase has-text-centered">
                            <span class="icon"><i class="mdi mdi-publish"></i></span>&nbsp; <?php _p("Publier"); ?>
                        </button>
                    </a>
                    <span class="tag is-warning" style="margin-top:10px;margin-left:12px;">BROUILLON</span>
                <?php else : ?>
                    <a href="/index.php/Articles/Display/Unpublish/id/<?= $id ?>">
                        <button class="button action-btn add-new is-uppercase has-text-centered">
                            <span class="icon"><i class="mdi mdi-lead-pencil"></i></span>&nbsp; <?php _p("Dépublier"); ?>
                        </button>
                    </a>
                <?php endif; ?>

                <?php if (($article["date_from"]) && ($is_redactor)) { ?><span class="tag <?php
						if ($is_future) {
							print "is-warning";
						}
						if (($is_past)) {
							print "is-danger";
						}
						if ((!$is_past) && (!$is_future)) {
							print "is-success";
						}

						?>" style="margin-top:4px;margin-left:0px;">PROGRAMMÉ <?= $article["date_from"] . " - " . $article["date_to"]; ?></span><br />
                <?php } ?>

            </div>
        </section>
    <?php elseif (!$access) : ?>
        <section class="section" id="article">
            <div class="container">
                <p>Cet article n'est pas encore publié et ne peut être affiché que par les rédacteurs du site.</p>
            </div>
        </section>
    <?php endif; ?>
    <?php if ($is_redactor || $access) : ?>
        <section class="section" id="prevnext" style="padding-bottom:0;padding-top:470px;background-size:cover !important;background:url('<?= $article["image"] ?>');">
        </section>
        <div class="container">
			<div class="article-header level">
				<div class="level-left">
					<h1 class="title"><?php _p($article["title"]); ?></h1>
					<h2 class="subtitle"><?php _p($article["subtitle"]); ?></h2>
				</div>
				<div class="level-right">
					<p class="date"><?php _p($article["date"]); ?></p>
				</div>
			</div>
        </div>
<div class="container">
	<div class="row">
		<div>		
        <section class="section" id="article">
            <div class="container">

                <?php
                $blocs = json_decode($article["blocs"], true);
                $blocs = $blocs["blocks"];
                foreach ($blocs as $bloc) :
                    $bloc["content"] = str_replace("\\n", "", $bloc["content"]);
                    // convert markdown links to html links
                    $bloc["content"] = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="\2">\1</a>', $bloc["content"]);

                    $bloc["image"] = str_replace($strip_hosts, "/", $bloc["image"]);
                    $bloc["image1"] = str_replace($strip_hosts, "/", $bloc["image1"]);
                    $bloc["image2"] = str_replace($strip_hosts, "/", $bloc["image2"]);

                    switch ($bloc["type"]):
                        case "simpleAlbum":
                            $vt_object = new ca_objects($bloc["data"]["id"]);
                            $template = "<ifdef code='notes'><p><b>Description</b> : ^ca_objects.notes</p></ifdef>
                            <p><b>Année :</b> <ifdef code='ca_objects.date'>^ca_objects.date</ifdef><ifnotdef code='ca_objects.date'>Non renseigné</ifnotdef></p>
                            <ifdef code='ca_objects.format_conteneur.format_conteneur_support'><p><b>Support :</b> ^ca_objects.format_conteneur.format_conteneur_support</p></ifdef>
                            <ifdef code='ca_objects.format_conteneur.format_conteneur_vitesses'><p><b>Tours par minute :</b> ^ca_objects.format_conteneur.format_conteneur_vitesses</p></ifdef>
                            <ifdef code='ca_objects.format_conteneur.format_conteneur_nbtitres'><p><b>Nombre de titres :</b> ^ca_objects.format_conteneur.format_conteneur_nbtitres</p></ifdef>
                            <ifdef code='ca_objects.format_conteneur.format_conteneur_dimension'><p><b>Diamètre :</b> ^ca_objects.format_conteneur.format_conteneur_dimension</p></ifdef>
                            <ifdef code='ca_objects.format_conteneur.format_conteneur_stereo'><p><b>Canaux :</b> ^ca_objects.format_conteneur.format_conteneur_stereo</p></ifdef>
                            <unit relativeTo='ca_objects.parent'> 
                            <ifdef code='ca_entities' restrictToRelationshipTypes='label'><p><b>Label :</b> <unit relativeTo='ca_entities' restrictToRelationshipTypes='label'><l>^ca_entities.preferred_labels</l></unit></p></ifdef>
                            </unit>
                            <ifdef code='pays_liste'><p><b>Pays :</b> ^ca_objects.pays_liste</p></ifdef>";
                ?>
                            <div class="simple-album">
                                <div class="card is-horizontal">
                                    <div class="card-image">
                                        <figure class="image"><img src="<?= $site_root . __CA_URL_ROOT__ ?>/app/plugins/Articles/assets/placeholder-white.png" alt="<?= htmlspecialchars($vt_object->getWithTemplate("^ca_objects.preferred_labels")) ?>"></figure>
                                    </div>
                                    <div class="card-content">
                                        <div class="media">
                                            <div class="media-content">
                                                <p class="title is-4"><?= $vt_object->getWithTemplate("<unit relativeTo='ca_objects.parent'>^ca_objects.preferred_labels</unit>"); ?></p>
                                                <p class="subtitle is-5"><b>Phonogramme :</b> <?= $vt_object->getWithTemplate("^ca_objects.preferred_labels"); ?></p>
                                            </div>
                                        </div>
                                        <div class="content">
                                            <?= $vt_object->getWithTemplate($template); ?>



                                            <p><a href="/index.php/Detail/objects/7385">Voir l'objet</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                            break;
                        case "quote":
                        ?>

                            <article class="article-content">
                                <div class="lead-dropcap">
                                    <p><strong><?php _p($bloc["data"]["text"]); ?></strong></p>
                                </div>
                            </article>

                        <?php break;
                        case "list":
                            if ($bloc["data"]["style"] == "unordered") {
                                print "<article class=\"article-content\"><ul>\n";
                            } else {
                                print "<article class=\"article-content\"><ol>\n";
                            }
                            foreach ($bloc["data"]["items"] as $item) {
                                print "<li>" . $item . "</li>";
                            }
                            if ($bloc["data"]["style"] == "unordered") {
                                print "</ul></article>\n";
                            } else {
                                print "</ol></article>\n";
                            }
                            break;
                        case "header":
                            //var_dump($bloc);die();
                            // NOTE : H2 H3 H4 is within $bloc["data"]["level"]
                        ?>
                            <article class="article-content">
                                <h<?= $bloc["data"]["level"] ?>><?php _p($bloc["data"]["text"]); ?></h<?= $bloc["data"]["level"] ?>>
                            </article>
                        <?php break;
                        case "paragraph":
                            $text = $bloc["data"]["text"];

                            // Recherche des liens vers le thésaurus (host-agnostique : courant + hérités)
                            preg_match_all("|<a href=\"(https?://[^/\"]+)/index.php/Thesaurus/View/Index/tag/([0-9]+)\">([^<]*)</a>|", $text, $matches);
                            foreach ($matches[3] as $key => $match) {
                                $host = $matches[1][$key];
                                $match_id = $matches[2][$key];
                                $substring = "<a href=\"" . $host . "/index.php/Thesaurus/View/Index/tag/" . $match_id . "\">" . $match . "</a>";
                                $replacement = "<a class=\"hasTooltip\" data-style=\"qtip-light\" data-tooltip=\"" . $match . "\" href=\"/index.php/Thesaurus/View/Index/tag/" . $match_id . "\">" . $match . "</a>";
                                $text = str_replace($substring, $replacement, $text);
                            }

                            // Recherche des liens vers les fiches objet (host-agnostique : courant + hérités)
                            preg_match_all("|<a href=\"(https?://[^/\"]+)/index.php/Detail/objects/([0-9]+)\">([^<]*)</a>|", $text, $matches);
                            foreach ($matches[3] as $key => $match) {
                                $host = $matches[1][$key];
                                $match_id = $matches[2][$key];
                                $substring = "<a href=\"" . $host . "/index.php/Detail/objects/" . $match_id . "\">" . $match . "</a>";
                                $replacement = "<a class=\"hasTooltip\" data-style=\"qtip-light\" data-tooltip=\"" . $match . "\" href=\"/index.php/Detail/objects/" . $match_id . "\">" . $match . "</a>";
                                $text = str_replace($substring, $replacement, $text);
                            }
                        ?>

                            <article class="article-content">
                                <p><?= $text  ?></p>
                            </article>

                        <?php break;
                        case "large-image":
                        ?>

                            <figure class="large-image">
                                <img src="<?php print $bloc["image"]; ?>" alt="Image 2 fullwidth">
                                <figcaption><?php print $bloc["figcaption"]; ?></figcaption>
                            </figure>

                        <?php break;
                        case "simpleimage":
                            // var_dump($bloc);
                            // die();
                            $styles = $bloc["data"];
                            unset($styles["url"]);
                            unset($styles["content"]);
                            $classes = "";
                            foreach ($styles as $style => $bool) {
                                if ($bool) $classes .= $style . " ";
                            }
                        ?>
                            <figure class="simple-image <?= $classes ?>">
                                <img src="<?php print $bloc["data"]["url"]; ?>" alt="<?php print $bloc["data"]["caption"]; ?>">
                                <figcaption><?php print $bloc["data"]["caption"]; ?></figcaption>
                            </figure>
                        <?php
                            break;
                        case "image-is-fullsize":
                        ?>

                            <figure class="image-full">
                                <img src="<?php print $bloc["image"]; ?>" alt="Image 2 fullwidth">
                                <figcaption><?php print $bloc["figcaption"]; ?></figcaption>
                            </figure>

                        <?php break;
                        case "delimiter":
                        ?>
                            <div class="delimiter"></div>
                        <?php
                            break;
						case "imageGallery":
							?>
							<article class="article-content imageGallery">
							<?php
							$images = $bloc["data"]["urls"];
							if (count($images) > 0) {
								print '<table style="width:100%"><tr>';
								foreach ($images as $image) {
									print '<td style="width:50%"><img src="' . $image . '" alt="Gallery Image"></td>';
								}
								print '</tr></table>';
							}
							?>
							</article>
							<?php
							break;

                        case "image-with-text":
                        ?>

                            <article class="article-content">
                                <div class="columns image-with-text">
                                    <div class="column">
                                        <img src="<?php print $bloc["image"]; ?>" alt="image 5">
                                    </div>
                                    <div class="column">
                                        <?php print str_replace("&quo;", '"', $bloc["content"]); ?>
                                    </div>
                                </div>
                            </article>

                            <?php break;
                        case "references":
                            print "<div class=\"article-content footnotes\">";
                            if ($bloc["footnote1"]) print "<h4>Références</h4><ol>";
                            if ($bloc["footnote1"]) print "<li id=\"footnote1\">{$bloc["footnote1"]}</li>";
                            if ($bloc["footnote2"]) print "<li id=\"footnote1\">{$bloc["footnote2"]}</li>";
                            if ($bloc["footnote3"]) print "<li id=\"footnote1\">{$bloc["footnote3"]}</li>";
                            if ($bloc["footnote4"]) print "<li id=\"footnote1\">{$bloc["footnote4"]}</li>";
                            if ($bloc["footnote5"]) print "<li id=\"footnote1\">{$bloc["footnote5"]}</li>";
                            if ($bloc["footnote6"]) print "<li id=\"footnote1\">{$bloc["footnote6"]}</li>";
                            if ($bloc["footnote1"]) print "</ol>";
                            print "<h4>Pour en savoir plus</h4>";
                            print $bloc["content"];
                            print "</div>";
                            break;
                        case "simplevideo":
                            if (strpos($bloc["data"]["url"], "vimeo") === false) {
                            ?>
                                <div style="max-width:700px;margin:0 auto;">
                                    <video controls style="width:100%;">
                                        <source src="<?= $bloc["data"]["url"] ?>" type="video/mp4">
                                    </video>
                                    <p>
                                    <figcaption><?= $bloc["data"]["caption"] ?></figcaption>
                                    </p>
                                </div>
                            <?php } else { ?>
                                <script src="https://player.vimeo.com/api/player.js"></script>
                                <div style="max-width:700px;margin:0 auto;">

                                    <iframe src="<?= $bloc["data"]["url"]; ?>" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen="" style="width:100%;height:450px;background:black;"></iframe>
                                </div>
                            <?php
                            }
                            break;
                        case "simpleaudio":
                            //var_dump($bloc);
                            //die();
                            $caption = $bloc['data']['caption'];
                            $caption = str_replace("'", "`", $caption);
                            $caption = str_replace("&nbsp;", " ", $caption);
                            $playlisttracks[] = "{\"name\":\"" . $caption . "\",\"url\":\"" . $bloc['data']['url'] . "\", \"image\":\"/img_article_phoi.png\", \"artist\":\"\", \"album\":\"" . $article['title'] . "\"}";
                            if (sizeof($playlisttracks) == 1) :
                            ?>
                                <article class="article-content" style="clear:both;">
                                    <button class="button btn button-default btn-default" onClick="playlistLoadAndPlay();">Charger la playlist</button>
                                </article>
                            <?php endif; ?>
                            <article class="article-content" style="clear:both;">
                                <div class="simpleaudio-content">
                                    <img src="/img_article_phoi.png" style="height:40px" align="absmiddle">
                                    <span class="player-icons">
                                        <span class="icon">
                                            <i class="mdi mdi-play is-large" onclick="playlistLoadTrack('<?= $caption ?>', '<?= $bloc['data']['url'] ?>', '/img_article_phoi.png', '', '<?= str_replace("'", "\'", $article['title']) ?>');"></i>
                                        </span>
                                        <span class="icon">
                                            <i class="mdi mdi-stop is-large" onclick="parent.stopTrack();"></i>
                                        </span>
                                    </span> <?= $bloc['data']['caption'] ?>
                                </div>

                            </article>
                <?php
                            break;
                        case "embed":
                            print ' <iframe style="width:100%;padding: 0 22% 0 22%;" height="320" frameborder="0" allowfullscreen="" src="' . $bloc["data"]["embed"] . '" class="embed-tool__content"></iframe>';
                            print "<figcaption>{$bloc["data"]["caption"]}</figcaption>";
                            break;

                        case "simpleCollectageVideo":
                            $t_object = new ca_objects($bloc["data"]["id"]);
                            $vimeo = $t_object->getWithTemplate("^ca_objects.vimeo");
                            if (!$vimeo){
                                $video_url = $t_object->getWithTemplate('^ca_object_representations.media.original.url');
                                if ($video_url){
                                ?>
                                    <div style="width:700px;margin:10px auto;height:400px"><video controls style="width:100%;max-width:700px">
            
                                        <source id="videosource" src="<?= $t_object->getWithTemplate('^ca_object_representations.media.original.url'); ?>">
            
                                        <track default kind="captions" label="français" srclang="fr" src="<?= $t_object->getWithTemplate('^ca_objects.vtt_fr.url'); ?>">
                                        <track default kind="captions" label="english" srclang="en" src="<?= $t_object->getWithTemplate('^ca_objects.vtt_en.url'); ?>">
                                        <track default kind="captions" label="malagasy" srclang="malagasy" src="<?= $t_object->getWithTemplate('^ca_objects.vtt_my.url'); ?>">
                                        <track default kind="captions" label="shibushi" srclang="shibushi" src="<?= $t_object->getWithTemplate('^ca_objects.vtt_si.url'); ?>">
            
                                        Sorry, your browser doesn't support embedded videos.
                                    </video></div>
                               
                                <?php
                                }
                                ?>
                            <?php
                            
                            }else{ ?>
                                <div style="width:700px;margin:10px auto;height:400px"><iframe src="https://player.vimeo.com/video/<?= $vimeo ?>" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen style="width:100%;height:100%;background:black;"></iframe></div><script src="https://player.vimeo.com/api/player.js"></script>
                            <?php 
                            
                                }; 
                            break;
						case "table":
							$rows = isset($bloc["data"]["content"]) ? $bloc["data"]["content"] : [];
							if (is_array($rows) && count($rows) > 0) {
								$with_headings = !empty($bloc["data"]["withHeadings"]);
								print '<article class="article-content"><table>';
								foreach ($rows as $r_index => $row) {
									if (!is_array($row)) continue;
									$cell_tag = ($with_headings && $r_index === 0) ? "th" : "td";
									print "<tr>";
									foreach ($row as $cell) {
										// Cells may contain inline HTML produced by the editor — print as-is
										print "<{$cell_tag}>" . $cell . "</{$cell_tag}>";
									}
									print "</tr>";
								}
								print '</table></article>';
							}
							break;
						case "columns":
							// @aaaalrashd/editorjs-columns : { columns, ratio, blocks:[ [..], .. ], style }
							$cols_blocks = isset($bloc["data"]["blocks"]) && is_array($bloc["data"]["blocks"]) ? $bloc["data"]["blocks"] : [];
							$ratio = isset($bloc["data"]["ratio"]) && is_array($bloc["data"]["ratio"]) ? $bloc["data"]["ratio"] : [];
							if (count($cols_blocks) > 0) {
								print '<article class="article-content"><div class="columns editorjs-columns">';
								foreach ($cols_blocks as $ci => $col_blocks) {
									$flex = isset($ratio[$ci]) ? ' style="flex:' . floatval($ratio[$ci]) . ' 1 0"' : '';
									print '<div class="column"' . $flex . '>';
									if (is_array($col_blocks)) {
										foreach ($col_blocks as $inner) {
											print articles_render_inner_block($inner, $strip_hosts);
										}
									}
									print '</div>';
								}
								print '</div></article>';
							}
							break;
						case "raw":
							print $bloc["data"]["html"];
							break;
						case "caObject":
						case "caOccurrence":
						case "caSet":
							$ca_id = isset($bloc["data"]["id"]) ? $bloc["data"]["id"] : "";
							$ca_type_map = ["caObject" => "objects", "caOccurrence" => "occurrences", "caSet" => "sets"];
							if ($ca_id !== "" && isset($ca_type_map[$bloc["type"]])) {
								$ca_html = articles_render_ca_entity($ca_type_map[$bloc["type"]], $ca_id);
								if ($ca_html !== "") {
									print '<article class="article-content ca-entity">' . $ca_html . '</article>';
								}
							}
							break;
						case "AnyButton":
							$btn_text = isset($bloc["data"]["text"]) ? trim($bloc["data"]["text"]) : "";
							$btn_link = isset($bloc["data"]["link"]) ? trim($bloc["data"]["link"]) : "";
							// Strip known hosts so internal links stay relative
							$btn_link = str_replace($strip_hosts, "/", $btn_link);
							if ($btn_text !== "" && $btn_link !== "") {
								$is_external = (bool)preg_match('|^https?://|', $btn_link);
							?>
							<article class="article-content has-text-centered">
								<a class="button is-primary" href="<?= htmlspecialchars($btn_link) ?>"<?= $is_external ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= htmlspecialchars($btn_text) ?></a>
							</article>
							<?php
							}
							break;
                        default:
                            //var_dump($bloc);die();

                            print "<div style='border:1px solid black; padding:50px;margin:20px 0;'>Type JSON inconnu : {$bloc["type"]}</div>";

                            break;
                    endswitch;
                endforeach; ?>

            </div>
        </section>

    <?php endif; ?>
</div>
</div>

<?php
// --- Other expositions in the same language ---
require_once(__CA_MODELS_DIR__.'/ca_site_pages.php');
global $g_ui_locale;
$va_all_pages = ca_site_pages::getPageList();
$va_other_expos = [];
$vs_langue = substr($g_ui_locale, 0, 2);
foreach ($va_all_pages as $va_page) {
	if ($va_page["template_title"] !== "article") continue;
	if ((int)$va_page["page_id"] === (int)$id) continue; // exclude current

	$vo_page = new ca_site_pages($va_page["page_id"]);
	if (!$vo_page->get("access")) continue;

	$va_keywords = explode(",", $vo_page->get("keywords"));
	if (!in_array($vs_langue, $va_keywords)) continue;

	$va_content = $vo_page->get("content");

	if ($va_content["date_from"] && time() < strtotime($va_content["date_from"])) continue;
	if ($va_content["date_to"] && time() > strtotime($va_content["date_to"])) continue;

	$vs_image = $va_content["image"];
	$va_parsed = parse_url($vs_image);
	if (isset($va_parsed["host"])) {
		$vs_image = $va_parsed["path"];
	}

	$va_other_expos[] = [
		"page_id" => $va_page["page_id"],
		"title" => $va_content["title"],
		"image" => $vs_image,
	];
}

if (count($va_other_expos) > 0):
?>
<div class="container" style="padding-bottom:60px;">
	<hr style="border:none; height:1px; background-color:#9e9485; margin-bottom:60px;">
	<h2 style="padding-bottom: 10px;">Nos expositions</h2>
	<div class="row expositions-tiles">
		<?php foreach ($va_other_expos as $va_expo): ?>
		<div class="col-sm-6">
			<a href="/index.php/Articles/Display/Details/id/<?= (int)$va_expo["page_id"]; ?>" class="expo-tile">
				<div class="expo-tile-image">
					<?php if ($va_expo["image"]): ?>
					<img src="<?= htmlspecialchars($va_expo["image"]); ?>" alt="<?= htmlspecialchars($va_expo["title"]); ?>">
					<?php else: ?>
					<div class="expo-tile-placeholder"></div>
					<?php endif; ?>
				</div>
				<div class="expo-tile-title"><?= htmlspecialchars($va_expo["title"]); ?></div>
			</a>
		</div>
		<?php endforeach; ?>
	</div>
</div>
<style>
.expositions-tiles {
	display: flex;
	flex-wrap: wrap;
}
.expo-tile {
	display: block;
	text-decoration: none;
	color: inherit;
	margin-bottom: 30px;
	transition: opacity 0.2s;
}
.expo-tile:hover {
	opacity: 0.85;
	text-decoration: none;
	color: inherit;
}
.expo-tile-image {
	width: 100%;
	height: 400px;
	overflow: hidden;
	border-radius: 6px;
	background: linear-gradient(135deg, #17578b 0%, #328aad 35%, #7cafc9 100%);
}
.expo-tile-image img {
	display: block;
	width: 100% !important;
	height: 100% !important;
	object-fit: cover !important;
}
.expo-tile-placeholder {
	width: 100%;
	height: 100%;
}
.expo-tile-title {
	margin-top: 0;
	font-size: 16px;
	font-weight: 600;
	text-align: center;
	padding-top: 10px;
	padding-bottom: 10px;
	color: #264684;
	background-color: transparent;
	font-family: "WantedSansBlack", "Helvetica Neue", Helvetica, Arial, sans-serif;
}
</style>
<?php endif; ?>

<script>
    function playlistLoadAndPlay() {
        console.log("playlistLoad");
        parent.loadPlaylist([
            <?php
            $playlisttracks = array_reverse($playlisttracks);
            print implode(",", $playlisttracks);
            ?>
        ]);
        parent.playTrack();
    }
    $(document).ready(function() {
        $('.hasTooltip').each(function() {
            $(this).qtip({
                content: {
                    html: $(this).attr("data-tooltip")
                },
                style: {
                    classes: 'qtip-light'
                }
            });
        });

        // Numérotation des H2 avec un rond sur fond noir et flèches de navigation
        $('.article-content h2').each(function(index) {
            var number = index + 1;
            var totalH2 = $('.article-content h2').length;

            var prevArrow = '<span class="h2-nav-arrow h2-prev-arrow" data-target="' + (index - 1) + '">&lt;</span>';
            var nextArrow = '<span class="h2-nav-arrow h2-next-arrow" data-target="' + (index + 1) + '">&gt;</span>';
            var numberBadge = '<span class="h2-number">' + number + '</span>';

            var navigation = '';
            if (index > 0) {
                navigation += prevArrow;
            } else {
                navigation += '<span class="h2-nav-arrow h2-nav-placeholder"></span>';
            }

            navigation += numberBadge;

            if (index < totalH2 - 1) {
                navigation += nextArrow;
            } else {
                navigation += '<span class="h2-nav-arrow h2-nav-placeholder"></span>';
            }

            $(this).prepend(navigation);
        });

        // Gestion des clics sur les flèches de navigation
        $(document).on('click', '.h2-nav-arrow', function() {
            var targetIndex = $(this).data('target');
            if (targetIndex >= 0 && targetIndex < $('.article-content h2').length) {
                var targetH2 = $('.article-content h2').eq(targetIndex);
                $('html, body').animate({
                    scrollTop: targetH2.offset().top - 100
                }, 500);
            }
        });
    })
</script>
<style>
    .article-header h1 {
        color: #1B1B1B;
    }

    #pageArea {
        max-width: 1344px;
        margin: 0 auto;
        padding: 24px 26px;
    }

    #buttons button,
    span.tag {
        border-radius: 0;
    }

    #buttons button span.icon {
        display: none;
    }

    #audio-player,
    #audio-player html,
    #audio-player body {
        overflow: hidden;
        scroll-behavior: unset;
    }

    .article-content {
        margin-bottom: 15px;
    }

    ul {
        list-style: circle;
    }
    .article-content li {
        padding-bottom: 12px;
    }

    .simple-image.floatRight,
    .simple-image.floatLeft {
        margin-top: 0 !important;
        padding: 20px 0;
    }

    .simple-image {
        padding: 20px 0;
        text-align: center;
    }

    .floatLeft {
        float: left;
        margin-right: 20px;
        margin-left: 40px;
        z-index: 1;
        padding-top: 0;
		text-align: center;
		width:50%;
    }

    .floatRight {
        float: right;
        margin-left: 20px;
        z-index: 1;
        padding-top: 0;
    }

    .floatLeft img,
    .floatRight img {
        width: 300px !important;
        height: auto !important;
        max-width: none !important;
		
    }

    @media screen and (min-width: 1408px) {

        .floatLeft img,
        .floatRight img {
            width: 320px !important;
        }

        .floatLeft,
		.floatRight {
			width:50%;
			
        }

        figure.simple-image.caption.floatLeft,
        figure.simple-image.caption.floatRight {
            padding: 0 !important;
        }


    }

    @media screen and (max-width: 1215px) and (min-width: 898px) {

        .floatLeft img,
        .floatRight img {
            width: 230px !important;
        }

        .floatLeft {
            max-width: 325px;
            margin-left: 22%;
            margin-right: 0;
            max-width: none !important;
        }

        .floatRight {
            max-width: 325px;
            margin-right: 22%;
            margin-left: 0;
            max-width: none !important;
        }

        figure.simple-image.caption.floatLeft,
        figure.simple-image.caption.floatRight,
        figure.simple-image.floatLeft,
        figure.simple-image.floatRight {
            padding: 0 !important;
        }

    }

    @media screen and (max-width: 897px) {

        .floatLeft img,
        .floatRight img {
            width: 100% !important;
            height: auto;
        }

        .floatLeft,
        .floatRight {
            float: none;
            max-width: none;
            padding: 0 22% 0 22% !important;
            margin: 0;
        }
    }

    .delimiter {
        clear: both;
    }

    .article-arcanes figcaption,
    .exposition-phoi figcaption,
    .podcast-phoi figcaption,
    .playlist-phoi figcaption {
        padding: 0 22% 0 22%;
    }

    .article-arcanes .article-content {
        margin-bottom: 25px !important;
        margin-top: 15px !important;
        line-height: 1.65em !important;
    }

    .article-arcanes .article-content h2 {
        line-height: 1.15em !important;
    }

    @media only screen and (max-width: 780px) {

        .article-arcanes .article-content,
        .exposition-phoi .article-content,
        .podcast-phoi .article-content,
        .playlist-phoi .article-content {
            padding: 0 5% 0 5%;
            font-family: "Lora", serif;
        }
    }

    .simple-album .content p:not(:last-child),
    .simple-album .content dl:not(:last-child),
    .simple-album .content ol:not(:last-child),
    .simple-album .content ul:not(:last-child),
    .simple-album .content blockquote:not(:last-child),
    .simple-album .content pre:not(:last-child),
    .simple-album .content table:not(:last-child) {
        margin-bottom: 4px;
    }

    .simple-album .card.is-horizontal .card-image,
    .is-horizontal.bResultItemCol .card-image {
        max-width: 500px;
    }

	.button {
		box-shadow: none !important;
	}
	.article-content a {
		text-decoration: underline;
	}

.container figure img {
	width:100%;
}

/* Option "50%" de l'outil image : 50% max de la largeur du conteneur, centrée */
.simple-image.halfWidth img {
	width:50% !important;
	max-width:50% !important;
	height:auto;
	display:block;
	margin-left:auto;
	margin-right:auto;
}

.simple-image.withBackground {
	background:#264684;
	padding: 50px 80px;
	margin-top:30px;
	margin-bottom:30px;
	color:lightgray;
}
.container figure.withBackground img {
	width:40%;
}


#pageArea {
	display: none;
}
.navbar {
	margin-bottom: 0;
}

.article-content h2 {
	margin-top: 140px;
}

.simple-image.stretched {
	/* "Sort" du conteneur (max 1200px) : largeur = min(100vw, 1420px), recentrée
	   sur l'écran via margin-left:50% + translateX(-50%), indépendamment de la
	   largeur du parent. */
	width: 100vw;
	max-width: 1420px;
	margin-left: 50%;
	transform: translateX(-50%);
	margin-top: 60px;
	margin-bottom: 60px;
}
.simple-image.stretched img {
	width: 100%;
}
/* Sécurise le débord horizontal lié à 100vw (largeur de la scrollbar) : on clippe
   au niveau du wrapper plein-largeur, sans rogner l'image ni casser sticky. */
.article-phoi,
.exposition-phoi,
.playlist-phoi,
.podcast-phoi {
	overflow-x: clip;
}

.h2-number {
	display: inline-block;
	background-color: #33457c;
	color: #fff;
	border-radius: 50%;
	width: 40px;
	height: 40px;
	line-height: 40px;
	text-align: center;
	margin-right: 2px;
	margin-left: 2px;
	font-size: 18px;
	font-weight: bold;
	margin-top:-12px;
    position: relative;
    top: -5px;
}

.h2-nav-arrow {
	display: inline-block;
	color: #33457c;
	font-size: 30px;
	font-family: "WantedSans";
	cursor: pointer;
	transition: all 0.3s ease;
	vertical-align: middle;
	user-select: none;
    position: relative;
    top: -5px;
}

.h2-nav-arrow:hover {
	color: #1a2a4a;
	transform: scale(1.2);
}

.h2-nav-placeholder {
	opacity: 0;
	cursor: default;
}

.h2-prev-arrow {
	margin-right: 2px;
}

.h2-next-arrow {
	margin-left: 2px;
	margin-right:14px;
}

.container {
	max-width: 1200px;
}
#article .container {
	padding-bottom: 40px;
}

.article-content table,
.article-content table tr {
	width:100%;
}
.article-content table td {
	padding:10px;
}
.article-content table td img {
	width:100%;
}

/* Bloc CA Object — carte enrichie */
.ca-entity .ca-object-card {
	display: flex;
	gap: 16px;
	align-items: flex-start;
	border-radius: 4px;
	padding: 14px;
}
.ca-entity .ca-object-card__image img {
	display: block;
	width: 170px !important;
	height: auto;
	border-radius: 3px;
}
.ca-entity .ca-object-card__body {
	flex: 1 1 auto;
	min-width: 0;
}
.ca-entity .ca-object-card__title {
	font-weight: bold;
	margin-bottom: 6px;
}
.ca-entity .ca-object-card__body p {
	margin: 2px 0;
}
.ca-entity .ca-object-card__cta {
	margin-top: 12px;
}

/* Bloc colonnes (editorjs-columns) : Bulma .columns ne passe pas en flex par
   défaut sur cette base -> on force la mise en page ici. Les largeurs (flex)
   sont posées en inline par type selon le ratio. */
.editorjs-columns {
	display: flex;
	gap: 20px;
	align-items: flex-start;
}
.editorjs-columns > .column {
	flex: 1 1 0;
	min-width: 0;
}
@media screen and (max-width: 768px) {
	.editorjs-columns {
		flex-wrap: wrap;
	}
	.editorjs-columns > .column {
		flex-basis: 100% !important;
	}
}
</style>
