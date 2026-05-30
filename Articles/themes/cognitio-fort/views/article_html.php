<?php


$is_redactor = $this->getVar("is_redactor");
$page = $this->getVar("page");
$access = $this->getVar("access");
$article = $this->getVar("article");
$id = $this->getVar("id");
$article["image"] = str_replace("https://phoi.ideesculture.fr/", "/", $article["image"]);


// sanitize page name for browse tab
$browser_tab_label = $article["title"];
?>
<script>
    window.parent.history.pushState('', "<?= $browser_tab_label ?>", '/index.php/Articles/Display/Details/id/'.$id);
    window.parent.document.title = "<?= $browser_tab_label ?>";
</script>
<?php

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

$template_id = $page->get("template_id");
switch ($template_id) {
    default:
        $template = "article";
        break;
}
$old_path = ucfirst($template) . "s";

?>
<!-- article_html.php -->
<div class="<?= $template ?>-phoi">
    <?php if ($is_redactor) : ?>
        <section class="section" id="buttons" style="padding-top: 0;padding-bottom: 0;">
            <div class="container">
                <a href="/index.php/Articles/Editor/New/template_id/1">
                    <button class="button action-btn add-new is-uppercase has-text-centered">
                        <span class="icon"><i class="mdi mdi-plus"></i></span>&nbsp; <?php _p("Nouveau"); ?>
                    </button>
                </a>
                <a href="/index.php/Articles/Editor/Article/id/<?= $id ?>">
                    <button class="button action-btn add-new is-uppercase has-text-centered">
                        <span class="icon"><i class="mdi mdi-lead-pencil"></i></span>&nbsp; <?php _p("Modifier"); ?>
                    </button>
                </a>

                <button class="button action-btn add-new is-uppercase has-text-centered is-dark" onClick="$('#delete').show();">
                    <span class="icon"><i class="mdi mdi-delete"></i></span>&nbsp; <?php _p("Supprimer"); ?>
                </button>

                <div class="modal" id="delete">
                    <div class="modal-background"></div>
                    <div class="modal-card">
                        <header class="modal-card-head">
                            <p class="modal-card-title">Suppression</p>
                            <button class="delete" aria-label="close"></button>
                        </header>
                        <section class="modal-card-body">
                            <p>Êtes vous sur de vouloir supprimer ce contenu ?</p>
                        </section>
                        <footer class="modal-card-foot">
                            <a href="/index.php/<?= $old_path ?>/Show/Delete/id/<?= $id ?>"><button class="button is-danger">Supprimer</button>
                                <button class="button" onClick="$('#delete').hide();">Annuler</button>
                        </footer>
                    </div>
                </div>

                <?php if (!$access) : ?>
                    <a href="/index.php/<?= $old_path ?>/Show/Publish/id/<?= $id ?>">
                        <button class="button action-btn add-new is-uppercase has-text-centered">
                            <span class="icon"><i class="mdi mdi-publish"></i></span>&nbsp; <?php _p("Publier"); ?>
                        </button>
                    </a>
                    <span class="tag is-warning" style="margin-top:10px;margin-left:12px;">BROUILLON</span>
                <?php else : ?>
                    <a href="/index.php/<?= $old_path ?>/Show/Unpublish/id/<?= $id ?>">
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
                <p>- Cet article n'est pas encore publié et ne peut être affiché que par les rédacteurs du site.</p>
            </div>
        </section>
    <?php endif; ?>
    <?php if ($is_redactor || $access) : ?>
        <section class="section" id="article" style="max-width: 800px;">
            <div class="container">

                <div class="article-header level" style="margin-top:50px;">
                    <div class="level-left">
                        <h1 class="title"><?php _p($article["title"]); ?></h1>
                        <h2 class="subtitle"><?php _p($article["subtitle"]); ?></h2>
                    </div>
                    <div class="level-right">
                        <p class="published-by"><?php _p("publié par"); ?></p>
                        <p class="publisher"><?php _p($article["author"]); ?></p>
                        <p class="date"><?php _p($article["date"]); ?></p>
                    </div>
                </div>
                <div>
                    <img src="<?php _p($article["image"]); ?>" alt="image 1" style="width:100%;height:auto;">
                </div>
                <?php
                $blocs = json_decode($article["blocs"], true);

                foreach ($blocs as $bloc) :
                    $bloc["content"] = str_replace("\\n", "", $bloc["content"]);
                    // convert markdown links to html links
                    $bloc["content"] = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="\2">\1</a>', $bloc["content"]);

                    $bloc["image"] = str_replace("https://phoi.ideesculture.fr/", "/", $bloc["image"]);
                    $bloc["image1"] = str_replace("https://phoi.ideesculture.fr/", "/", $bloc["image1"]);
                    $bloc["image2"] = str_replace("https://phoi.ideesculture.fr/", "/", $bloc["image2"]);

                    switch ($bloc["type"]):
                        case "lead-dropcap":
                ?>

                            <article class="article-content">
                                <div class="lead-dropcap"><?php _p($bloc["content"]); ?></div>
                            </article>

                        <?php break;
                        case "paragraph": ?>

                            <article class="article-content">
                                <?php _p($bloc["content"]); ?>
                            </article>

                        <?php break;
                        case "quote": ?>

                            <article class="article-content">
                                <div class="quote"><?php _p($bloc["content"]); ?></div>
                            </article>


                        <?php break;
                        case "large-image":
                        ?>

                            <figure class="large-image">
                                <img src="<?php print $bloc["image"]; ?>" alt="Image 2 fullwidth">
                                <figcaption><?php print $bloc["figcaption"]; ?></figcaption>
                            </figure>

                        <?php break;
                        case "image-is-fullsize":
                        ?>

                            <figure class="image-full">
                                <img src="<?php print $bloc["image"]; ?>" alt="Image 2 fullwidth">
                                <figcaption><?php print $bloc["figcaption"]; ?></figcaption>
                            </figure>

                        <?php break;
                        case "two-images":
                        ?>

                            <div class="columns image-row two-images">
                                <figure class="column">
                                    <img src="<?php print $bloc["image1"]; ?>" alt="Image 3">
                                    <figcaption><?php print $bloc["figcaption1"]; ?></figcaption>
                                </figure>
                                <figure class="column">
                                    <img src="<?php print $bloc["image2"]; ?>" alt="Image 4">
                                    <figcaption><?php print $bloc["figcaption2"]; ?></figcaption>
                                </figure>
                            </div>

                        <?php break;
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
						case "raw":
							var_dump($bloc["content"]);
							die();
							break;
						case "ImageGallery":
							$images = $bloc["content"]["images"];
							if (count($images) > 0) {
								print '<div class="article-content image-gallery">';
								foreach ($images as $image) {
									$image_url = str_replace("https://phoi.ideesculture.fr/", "/", $image["url"]);
									$caption = $image["caption"];
									print '<figure><img src="' . $image_url . '" alt="Gallery Image"><figcaption>' . $caption . '</figcaption></figure>';
								}
								print '</div>';
							}
							break;
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
                        default:
						var_dump($bloc);
						die();
                            print "<div style='border:1px solid black; padding:50px;margin:20px 0;>Type JSON inconnu : {$bloc["type"]}</div>";

                            break;
                    endswitch;
                endforeach; ?>

            </div>
        </section>

    <?php endif; ?>
</div>
</div>
<style>
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

    .article-header.level {
        display: block !important;
    }

    .article-arcanes .article-content {
        margin-bottom: 25px !important;
        margin-top: 15px !important;
        line-height: 1.65em !important;
    }

    @media only screen and (max-width: 600px) {

        .article-arcanes .article-content,
        .exposition-phoi .article-content,
        .podcast-phoi .article-content,
        .playlist-phoi .article-content {
            padding: 0 5% 0 5%;
            font-family: "Lora", serif;
        }
    }

.container figure img {
	width:100%;
}
</style>
<div>