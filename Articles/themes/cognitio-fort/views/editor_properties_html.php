<?php

    $is_redactor = $this->getVar("is_redactor");
    $access = $this->getVar("access");
    $article = $this->getVar("article");
    $page = $this->getVar("page");
    $template_id = $page->get("template_id");
    //var_dump($page->get("template_id"));die();
    $lang = $this->getVar("lang");
    $langs = explode(",", $lang);
    $titre = $this->getVar("titre");
    $id = $this->getVar("id");
    $strip_hosts = [__CA_SITE_PROTOCOL__ . "://" . __CA_SITE_HOSTNAME__ . "/", "https://phoi.ideesculture.fr/", "https://www.phoi.io/"];
    $article["image"] = str_replace($strip_hosts, "/", $article["image"]);

    // Check if article is programmed in the past
    $is_past = false;
    if($article["date_to"]) {
        $date_to = substr($article["date_to"], 6, 4)."-".substr($article["date_to"], 3, 2)."-".substr($article["date_to"], 0, 2);
        // Ignore if the article is to be published in the future
        if(time() > strtotime($date_to)) $is_past = true;
    }
    // Check if article is programmed in the past
    $is_future = false;
    if($article["date_from"]) {
        $date_from = substr($article["date_from"], 6, 4)."-".substr($article["date_from"], 3, 2)."-".substr($article["date_from"], 0, 2);
        // Ignore if the article is to be published in the future
        if(time() < strtotime($date_from)) $is_future = true;
    }

    switch($template_id) {
        case "2":
            $template = "exposition";
            break;
        case "3":
            $template = "playlist";
            break;
        case "4":
            $template = "podcast";
            break;
        default :
            $template = "article";
            break;
    }
    $old_path = ucfirst($template)."s";
?>
<div class="<?= $template ?>-phoi" style="margin-bottom:120px;">
    
<?php if($is_redactor): ?>
    <section class="section" id="buttons" style="padding-top: 0;padding-bottom: 0;">
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

            <?php if(!$access): ?>
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

            <?php if(($article["date_from"]) && ($is_redactor)){ ?><span class="tag <?php
            if($is_future) {
                print "is-warning";
            }
            if(($is_past)) {
                print "is-danger";
            }
            if((!$is_past) && (!$is_future)) {
                print "is-success";
            }

            ?>" style="margin-top:4px;margin-left:0px;">PROGRAMMÉ <?= $article["date_from"]." - ".$article["date_to"]; ?></span><br/>
            <?php } ?>

        </div>
    </section>
<?php elseif(!$access): ?>
    <section class="section" id="article">
        <div class="container">
            <p>- Cet article n'est pas encore publié et ne peut être affiché que par les rédacteurs du site.</p>
        </div>
    </section>
<?php endif; ?>
<?php if($is_redactor): ?>
    <section class="section" id="article">

    <script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.27.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/header@2.27.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/paragraph@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/simple-image@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/embed@2.5.3/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/delimiter@2.27.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@2.5.0/dist/bundle.min.js"></script>

		<script src="
https://cdn.jsdelivr.net/npm/bulma-calendar@6.1.18/dist/js/bulma-calendar.min.js
"></script>
<link href="
https://cdn.jsdelivr.net/npm/bulma-calendar@6.1.18/dist/css/bulma-calendar.min.css
" rel="stylesheet">


    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style.css">

    <div class="container">
        <div class="article-arcanes">
            <div class="article-header level">
                <div class="level-left">
                    <h1 class="title"><?= $article["title"] ?></h1>
                    <h1 class="subtitle"><?= $article["subtitle"] ?></h1>
                </div>
                <div class="level-right">
                    <p class="date"><?= $article["date"] ?></p>
                </div>
            </div>
            <div>

            </div>
        </div>
    </div>
<form action="/index.php/Articles/Editor/SaveArticleProperties/id/<?= $id ?>" method="post">
    <div class="field is-horizontal" style="display:none;">
        <div class="field-label is-normal">
            <label class="label">Langues</label>
        </div>
        <div class="field-body">
            <div class="field">
                <div class="buttons">
                    <button class="button is-primary lang-button <?= (in_array("en",$langs) ? "" : "is-inverted") ?>" data-lang="en">EN</button>
                    <button class="button is-primary lang-button <?= (in_array("fr",$langs) ? "" : "is-inverted") ?>" data-lang="fr">FR</button>
                    <button class="button is-primary lang-button <?= (in_array("my",$langs) ? "" : "is-inverted") ?>" data-lang="my">MY</button>
                    <button class="button is-primary lang-button <?= (in_array("si",$langs) ? "" : "is-inverted") ?>" data-lang="si">SI</button>
                    <input id="lang" class="<?= implode(" ", $langs) ?>" type="hidden" name="keywords" placeholder="Langues" value="<?= $lang ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="field is-horizontal" style="display: none;">
        <div class="field-label is-normal">
            <label class="label">Type</label>
        </div>
        <div class="field-body">
            <div class="field">
                <div class="select">
                    <select name="template_id">
                        <option value="1" <?= ( $template_id == 1 ? 'selected' : '') ?>>Article</option>
                        <option value="2" <?= ( $template_id == 2 ? 'selected' : '') ?>>Exposition</option>
                        <option value="4" <?= ( $template_id == 4 ? 'selected' : '') ?>>Podcast</option>
                        <option value="3" <?= ( $template_id == 3 ? 'selected' : '') ?>>Playlist</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="field is-horizontal">
        <div class="field-label is-normal">
            <label class="label">Titre (onglet navigateur)</label>
        </div>
        <div class="field-body">
            <div class="field">
                <p class="control">
                    <input class="input" name="titre" type="text" placeholder="Titre (onglet navigateur)" value="<?= ($titre=="titre..." ? "" : $titre) ?>">
                </p>
            </div>
        </div>
    </div>

    <div class="field is-horizontal">
        <div class="field-label is-normal">
            <label class="label">Titre (affichage)</label>
        </div>
        <div class="field-body">
            <div class="field">
                <p class="control">
                    <input class="input" name="titredisplay" type="text" placeholder="Titre (affichage)" value="<?= ($article["title"]=="titre..." ? "" : $article["title"]) ?>">
                </p>
            </div>
        </div>
    </div>

    <div class="field is-horizontal">
        <div class="field-label is-normal">
            <label class="label">Sous-titre</label>
        </div>
        <div class="field-body">
            <div class="field">
                <p class="control">
                    <input class="input" type="text" name="soustitre" placeholder="Sous-titre" value="<?= $article["subtitle"] ?>">
                </p>
            </div>
        </div>
    </div>

    <div class="field is-horizontal">
        <div class="field-label is-normal">
            <label class="label">Auteur</label>
        </div>
        <div class="field-body">
            <div class="field">
                <p class="control">
                    <input class="input" type="text" name="auteur" placeholder="Auteur" value="<?= $article["author"] ?>">
                </p>
            </div>
        </div>
    </div>

    <div class="field is-horizontal">
        <div class="field-label is-normal">
            <label class="label">Date (affichage)</label>
        </div>
        <div class="field-body">
            <div class="field">
                <p class="control">
                    <input class="input" type="text" name="date" placeholder="Date - free type format" value="<?= $article["date"] ?>">
                </p>
            </div>
        </div>
    </div>

    <div class="field is-horizontal">
        <div class="field-label is-normal">
            <label class="label">Image principale (URL)</label>
        </div>
        <div class="field-body">
            <div class="field">
                <p class="control">
                    <input class="input " type="text" name="image" placeholder="URL image" value="<?= $article["image"] ?>">
                </p>
            </div>
        </div>
    </div>

    <div class="field is-horizontal">
        <div class="field-label is-normal">
            <label class="label">Date de publication</label>
        </div>
        <div class="field-body">
            <div class="field">
                <p class="control">
                    <input id="bulmaCalendar" class="input" type="text" name="date_from" placeholder="Date de publication au format 2023-01-01" value="<?= $article["date_from"] ?>">
                </p>
            </div>
        </div>
    </div>

    <div class="field is-horizontal">
        <div class="field-label is-normal">
            <label class="label">Date de fin de publication<small><?= $article["date_to"] ?></small></label>
        </div>
        <div class="field-body">
            <div class="field">
                <p class="control">
                    <input id="bulmaCalendar2" class="input" type="text" name="date_to" placeholder="Date de fin de publication au format 2023-01-01" value="<?= $article["date_to"] ?>">
                </p>
            </div>
        </div>
    </div>
    <iframe src="/index.php/Articles/Editor/Upload/manual.php?id=<?= $id ?>" style="width:100%;height:46px;"></iframe>


    <div class="field is-horizontal" style="padding-top:40px;">
        <div class="field-label is-normal">
        </div>
        <div class="field-body">
            <button class="button is-primary" type="submit">Enregistrer</button>
        </div>
    </div>


</form>
<?php endif; ?>
</div>
    </div>

</section>
</div>

<script>
    $(document).ready(function() {
       $(".lang-button").on("click", function(event) {
           //$(this).prop('disabled', true);
           event.preventDefault();
           console.log($(this).data("lang"));
           $("#lang").toggleClass($(this).data("lang"));
           $(this).toggleClass("is-inverted");
           console.log($("#lang").attr("class").split(" ").join(","));
           $("#lang").val($("#lang").attr("class").split(" ").join(","));

       });

    });
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
        h1{
            text-align: center;
        }
        .btn{
            text-align: center;
            align-items:center;
            justify-content: center;
            display: flex;
            background:rgb(119, 206, 119);
            padding:.4rem;
            width:20%;
            margin:auto;
            border-radius: 8px;
            color:white;
            cursor: pointer;
        }
        .btn:hover{
            background:rgb(17, 170, 17);
        }

        blockquote {
            font-family: "Lora", serif;
            font-size:24px;
            font-style: italic !important;
            font-weight: bold !important;
            font-size: 1.5rem;
            color: #232425;
            line-height: 150%;
            padding: 3rem 0 2rem 0; }
        blockquote div.cdx-quote__text:first-letter {
            color: #7dafca;
            font-size: 72px;
            line-height: 100%;
            float: left;
            padding-right: 0.1em; }
    div.cdx-input.cdx-quote__caption {
        display:none !important;
    }
    .codex-editor__redactor {
        padding-bottom: 100px !important;
    }

        #audio-player,
        #audio-player html,
        #audio-player body {
            overflow: hidden;
            scroll-behavior: unset;
        }
        .article-content{
            margin-bottom: 15px;
        }
        ul{
            list-style: circle;
        }
				.datetimepicker-header.is-date-only {
					display: none;
				}
    </style>
<?php //die();
