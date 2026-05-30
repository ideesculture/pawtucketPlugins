<?php
$blocks = $this->getVar("blocks");
$is_redactor = $this->getVar("is_redactor");
//var_dump($is_redactor);die();
?>
<script>
    // Ajout dans l'historique
    window.parent.history.pushState('', "<?= $browser_tab_label ?>", '/index.php/Articles/Show/Index');
    // Définition du titre de la page
    window.parent.document.title = "Arcanes - Articles";
</script>

<div class="index-articles-phoi">
    <h1 class="page-title" style="padding-bottom:24px">Articles</h1>

    <div class="rows">
        <?php print $blocks; ?>
    </div>

</div>

<style>
    .load-more {
        margin-top: 2rem;
    }
    .load-more button {
        border-radius: 0 !important;
    }
	.index-articles-phoi .card-content {
		cursor: pointer;
	}
    #pageArea {
        max-width: 1344px;
        margin: 0 auto;
        padding: 24px 26px;
    }
    .article-card {
        margin-bottom: 2rem;
    }
    .article-card .card {
        display: flex;
        align-items: start;
        border-radius: 0 !important;
		border-bottom: 1px solid #21272c;
		background-color: white !important;
		color: rgb(64, 70, 84) !important;
    }
    .article-card .card .card-image,
    .article-card .card .card-content {
        width: 100%;
        height: 100%;
		background-color: white !important;
    }
    .article-card .card .card-content {
        align-self: normal;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .article-card .card .card-image img {
        border-radius: 0 !important;
    }
    .article-card .card .card-content h2 {
        margin-top: 0 !important;
    }
	.article-card .card .card-content h2 a {
		color:#21272c !important;
	}
    .article-card .card .card-footer {
        margin-top: auto;
		border:none !important;
    }
	.card {
		box-shadow: none;
	}
</style>
