<?php
$is_redactor = $this->getVar("is_redactor");
$access = $this->getVar("access");
$article = $this->getVar("article");
$page = $this->getVar("page");
$id = $this->getVar("id");
$article["image"] = str_replace("https://phoi.ideesculture.fr/", "/", $article["image"]);

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

// Test if $article["blocs"] is in older format or ok
$blocs = json_decode($article["blocs"], 1);
//var_dump($blocs);die();
$is_older_format = false;
if(($blocs !== null) && ($blocs !== [])) {
    $is_older_format = ($blocs["time"] === null);
} else {
    $article["blocs"]="{}";
}

$template_id=$page->get('template_id');
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
<?php else: ?>
    <section class="section" id="article">
        <div class="container">
            <h2>Editeur d'article</h2>
            <p>Vous devez être connecté en tant que rédacteur pour pouvoir modifier cet article.</p>
        </div>
    </section>
<?php endif; ?>
<?php if($is_redactor): ?>
    <section class="section" id="article">

    <script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.27.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/header@2.7.0/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/paragraph@2.6.1/dist/bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/list@1.8.0/dist/bundle.min.js"></script>
    <!-- <script src="<?= __CA_URL_ROOT__ ?>/app/plugins/Articles/lib/editorjs-simpleimage-left-right/simpleimage-left-right.js"></script> -->
    <script src="<?= __CA_URL_ROOT__ ?>/app/plugins/Articles/lib/ideesculture-editorjs-image/simple-image.js"></script>
    <script src="<?= __CA_URL_ROOT__ ?>/app/plugins/Articles/lib/editorjs-audio/simple-audio.js"></script>
    <script src="<?= __CA_URL_ROOT__ ?>/app/plugins/Articles/lib/editorjs-video/simple-video.js"></script>
    <script src="<?= __CA_URL_ROOT__ ?>/app/plugins/Articles/lib/ideesculture-editorjs-album/simple-album.js?date=<?= time() ?>"></script>
    <script src="<?= __CA_URL_ROOT__ ?>/app/plugins/Articles/lib/ideesculture-editorjs-collectage-video/simple-collectage-video.js?date=<?= time() ?>"></script>
    <script src="<?= __CA_URL_ROOT__ ?>/app/plugins/Articles/lib/ideesculture-editorjs-morceau/simple-morceau.js?date=<?= time() ?>"></script>

    <script src="https://cdn.jsdelivr.net/npm/@editorjs/embed@2.5.3/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/delimiter@1.3.0/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@2.5.0/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/raw@latest"></script>

    <link rel="stylesheet" href="https://dev.phoi.io/themes/phoi/assets/pawtucket/css/theme.css">
    <!-- <link rel="stylesheet" href="<?= __CA_URL_ROOT__ ?>/app/plugins/Articles/lib/editorjs-simpleimage-left-right/simpleimage-left-right.css"> -->
    <link rel="stylesheet" href="<?= __CA_URL_ROOT__ ?>/app/plugins/Articles/lib/ideesculture-editorjs-image/simple-image.css">
    <link rel="stylesheet" href="<?= __CA_URL_ROOT__ ?>/app/plugins/Articles/lib/editorjs-audio/simple-audio.css">
    <link rel="stylesheet" href="<?= __CA_URL_ROOT__ ?>/app/plugins/Articles/lib/editorjs-video/simple-video.css">
    <link rel="stylesheet" href="<?= __CA_URL_ROOT__ ?>/app/plugins/Articles/lib/ideesculture-editorjs-album/simple-album.css">
    <!--<link rel="stylesheet" href="<?= __CA_URL_ROOT__ ?>/app/plugins/Articles/lib/ideesculture-editorjs-collectage-video/simple-collectage-video.css">-->

	<script src="https://cdn.jsdelivr.net/npm/@calumk/editorjs-columns@latest"></script>
	<!-- Load 3rd Party Tools -->
    <script src="https://cdn.jsdelivr.net/npm/editorjs-alert@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@calumk/editorjs-codeflask@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@calumk/editorjs-nested-checklist@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@rodrigoodhin/editorjs-image-gallery@latest"></script>

  
    <!-- <link rel="stylesheet" href="style.css"> -->

    <div class="container">
        <div class="article-chuv">
            
            <div class="article-header level">
                <div class="level-left">
                    <h1 class="title"><?= $article["title"] ?></h1>
                    <h1 class="subtitle"><?= $article["subtitle"] ?></h1>
                </div>
                <div class="level-right">
                    <p class="date"><?= $article["date"] ?></p>
                </div>
            </div>


            <?php if($is_older_format): ?>
                <div style="margin:70px;font-weight: bold;font-size:1.6em;">Ces blocs sont dans un précédent format. Vous ne pouvez pas éditer cet article.<br/>Merci de vous rapprocher de l'administrateur de la base.</div>
            <?php else: ?>
							<iframe id="upload-iframe" src="/index.php/Articles/Editor/Upload/manual.php?id=<?= $id ?>" style="width:100%;height:46px;margin:20px 0;"></iframe>
<?php if ($article["blocs"]=="{}") : ?>
								<p><button class="button is-big is-primary" onClick="editor.focus();editor.caret.focus();$(this).hide();">Cliquer ici pour commencer à rédiger l'article...</button></p>
<?php endif; ?>								
                <div id="editorjs"></div>
            <?php endif; ?>
        </div>
    </div>
    <?php if(!$is_older_format): ?>
        <div class="container">
                <button class="button is-primary" id="saveButton" disabled="disabled" onclick="articleSave()">Enregistrer</button>
                <button class="button" onclick="display()">Afficher</button>

        </div>
    <?php endif; ?>
<?php endif; ?>
</div>
    </div>

</section>
</div>

    <script>

        const editor = new EditorJS({
                holder: 'editorjs',
			    //autofocus: true,
                /**
                 * Available Tools list.
                 * Pass Tool's class or Settings object for each Tool you want to use
                 */
                tools: {
                    header: Header,
                    delimiter: Delimiter,
                    paragraph: {
                        class: Paragraph,
                        inlineToolbar: true
                    },
                    list:{
                      class: List,
                      inlineToolbar: true
                    },
                    embed: Embed,
                    simpleimage: {
                        class:IdeescultureEditorjsImage,
                        inlineToolbar: true
                    },
					imageGallery: ImageGallery,
                    simpleaudio: {
                        class:SimpleAudio,
                        inlineToolbar: true
                    },
                    simplevideo: {
                        class:SimpleVideo,
                        inlineToolbar: true
                    },
                    //imageparagraph: SimpleImageLeftRight,
                    quote: {
                        class: Quote,
                        inlineToolbar: true,
                        config: {
                            quotePlaceholder: 'Enter a quote',
                            captionPlaceholder: 'Quote\'s author',
                        },
                    },
                    simpleAlbum: {
                        class:SimpleAlbum,
                        inlineToolbar: true
                    },
                    simpleCollectageVideo: {
                        class:SimpleCollectageVideo,
                        inlineToolbar: true
                    },
                    simpleMorceau: {
                        class:SimpleMorceau,
                        inlineToolbar: true
                    },
					raw: RawTool,
					columns : {
						class : editorjsColumns,
						config : {
						tools : {
							header: Header,
							alert : Alert,
							delimiter : Delimiter
						},
						EditorJsLibrary : EditorJS //ref EditorJS - This means only one global thing
						}
					},
                },
                data:
                    <?= $article["blocs"] ?>,
                onReady: () => {
                    console.log('Editor.js is ready to work!');
                    console.log("Initial data :", <?= $article["blocs"] ?>);
                    // GM : Next lines are a DEBUG for stretched CSS class added on the wrapper.
                    $(".stretched").parent().parent().addClass("ce-block--stretched");
                    $(".simple-image").not(".stretched").parent().parent().removeClass("ce-block--stretched");
                    $(".ce-paragraph").not(".stretched").parent().parent().removeClass("ce-block--stretched");
                    // GM : required for float left & right image options
                    $(".simple-image").parent().removeClass("floatRight");
                    $(".simple-image").parent().removeClass("floatLeft");
                    $('.simple-image.floatLeft').parent().addClass('floatLeft');
                    $('.simple-image.floatRight').parent().addClass('floatRight');
										setTimeout(function() {
											editor.caret.focus();
										}, 200);
                }
            }
        );
        function articleSave(){
            editor.save().then((output) => {
                console.log('Data: ', output);
                let cog = jQuery('<i class="mdi mdi-cogs is-large savingicon"></i>');
                jQuery(".podcast-phoi").parent().append(cog);

                $("html, body").animate({ scrollTop: 30 }, "slow");
                jQuery(".podcast-phoi").css("opacity","0.1");
                //console.log(JSON.stringify(output));
                $.ajax({
                    method: "POST",
                    url: "<?php print __CA_URL_ROOT__; ?>/index.php/Articles/Editor/SaveArticleJson/id/<?= $id ?>",
                    data: output,
                    dataType: "json"
                })
                .done(function( result ) {
                        console.log("result");
                        $("html, body").animate({ scrollTop: 30 }, "slow");
                        jQuery(".savingicon").remove();
                        jQuery(".podcast-phoi").css("opacity","1");

                        console.log(output.blocks[4]);
                        //console.log(output);
                        if(result.result == "ok") {
                            alert("Article enregistré");
                        }
                });
            }).catch((error) => {
                alert("Erreur lors de l'enregistrement");
                jQuery(".savingicon").remove();
                jQuery(".podcast-phoi").css("opacity","1");
                console.log('Saving failed: ', error)
            });
        }
        function display() {
            window.location="<?= __CA_URL_ROOT__?>/index.php/Articles/Display/Details/id/<?= $id ?>";
        }

        jQuery(document).ready(function() {
            $(".stretched").parent().parent().addClass("ce-block--stretched");
            setTimeout(function() {
                $('#saveButton').prop("disabled", false);
            }, 750);

            // Make iframe sticky on scroll
            var iframe = $('#upload-iframe');
            if (iframe.length) {
                var iframeOffset = iframe.offset().top;
                var iframeHeight = iframe.outerHeight();

                $(window).scroll(function() {
                    var scrollTop = $(window).scrollTop();

                    if (scrollTop >= iframeOffset) {
                        if (!iframe.hasClass('sticky')) {
                            iframe.addClass('sticky');
                            // Add placeholder to prevent content jump
                            iframe.before('<div id="iframe-placeholder" style="height:' + iframeHeight + 'px;"></div>');
                        }
                    } else {
                        if (iframe.hasClass('sticky')) {
                            iframe.removeClass('sticky');
                            $('#iframe-placeholder').remove();
                        }
                    }
                });
            }
        })
    </script>
    <style>
        #upload-iframe {
            transition: all 0.3s ease;
        }

        #upload-iframe.sticky {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100% !important;
            margin: 0 !important;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

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
            padding: 3rem 0 2rem 0; 
        }

        blockquote div.cdx-quote__text:first-letter {
            color: #7dafca;
            font-size: 72px;
            line-height: 100%;
            float: left;
            padding-right: 0.1em; 
        }

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
        .ce-delimiter:before {
            content:"";
        }
        .ce-delimiter {
            clear:both;
            border-top:2px solid #eeeeee;
            line-height:10px;
            height:10px;
        }
        .ce-toolbox.ce-toolbox--opened {
            background-color: rgba(255,255,255,0.8);
        }
        .savingicon {
            position: absolute;
            top: 270px;
            left: 0;
            right: 0;
            margin: auto;
            font-size: 120px;
            text-align: center;
        }
				.ce-paragraph {
			    text-align: justify;
				}

		.simple-album .content p:not(:last-child),
		.simple-album .content dl:not(:last-child),
		.simple-album  .content ol:not(:last-child),
		.simple-album .content ul:not(:last-child),
		.simple-album .content blockquote:not(:last-child),
		.simple-album .content pre:not(:last-child),.simple-album 
		.content table:not(:last-child) {
			margin-bottom:4px;
		}
        .simple-album .card.is-horizontal .card-image, .is-horizontal.bResultItemCol .card-image{
            max-width: 250px;
        }

		.button {
			box-shadow: none !important;
			border-radius: 0 !important;
		}

		.ce-block__content, .ce-toolbar__content { max-width:calc(100% - 80px) !important; } .cdx-block { max-width: 100% !important; }
		.ce-paragraph {text-align: left;}
		#editorjs {
			margin-left: 40px;
			margin-right: 40px;
			width: calc(100% - 80px);
		}
		.ce-toolbar__actions {
    right: calc(100% + 10px) !important;
}


button.is-big.is-primary {
	color: #fff;
    background-color: #17a2b8;
    border-color: #17a2b8;
	    display: inline-block;
    font-weight: 400;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    border: 1px solid transparent;
    padding: .375rem .75rem;
    line-height: 1.5;
    border-radius: 5px !important;
    transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;

}


.container figure img {
	width:100%;
}
    </style>
<?php //die();
