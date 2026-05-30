<?php
$blocks = $this->getVar("blocks");
$is_redactor = $this->getVar("is_redactor");
$current_page = $this->getVar("current_page");
$pagination = $this->getVar("pagination");
$total_pages = $this->getVar("total_pages");
$total_articles = $this->getVar("total_articles");
//var_dump($is_redactor);die();
?>
<script>
    // Ajout dans l'historique
    window.parent.history.pushState('', "<?= $browser_tab_label ?>", '/index.php/Articles/Show/Index');
    // Définition du titre de la page
    window.parent.document.title = "";
</script>

<div class="index-articles-phoi">
    <h1 class="page-title" style="padding:24px 0">Liste des pages statiques</h1>
<section class="section" id="buttons" style="padding-top: 0;padding-bottom: 24px;">
            <div class="container">
<a href="/index.php/Articles/Editor/New/template_id/4">
                    <button class="button action-btn add-new is-uppercase has-text-centered">
                        <span class="icon"><i class="mdi mdi-plus"></i></span>&nbsp; Nouveau                    </button>
                </a>
</div>
</section>
    <div class="rows">
        <?php print $blocks; ?>
    </div>

</div>

<!-- Pagination : affiche les numéros de pages cliquables -->
<?php if ($total_articles > 0) : ?>
	<div class="load-more">
        <div class="row">
            <div class="col-xs-12 text-center">
                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                    <?php if ($i == $current_page) : ?>
                        <span class="btn btn-primary"><?= $i ?></span>
                    <?php else : ?>
                        <a href="/index.php/Articles/Show/Index/page/<?= $i ?>" class="btn btn-primary"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        </div>
    </div>
<?php else : ?>
	<div class="row">
		<div class="col-xs-12 text-center">
			<p>Aucun article trouvé.</p>
		</div>
	</div>
<?php endif; ?>

<style>
    .load-more {
        margin-top: 2rem;
		margin-bottom: 2rem;
		text-align: center;
		font-size:20px;
		font-weight: 600;
    }
    .load-more a,
	.load-more span {
        border-radius: 0 !important;
		margin: 0 10px;
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
