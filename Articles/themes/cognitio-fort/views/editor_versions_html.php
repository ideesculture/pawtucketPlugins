<?php
    $is_redactor = $this->getVar("is_redactor");
    $access = $this->getVar("access");
    $page = $this->getVar("page");
    $id = $this->getVar("id");
    $versions = $this->getVar("versions");
    if (!is_array($versions)) { $versions = []; }

    $template_id = $page->get('template_id');
    switch($template_id) {
        case "2": $template = "exposition"; break;
        case "3": $template = "playlist"; break;
        case "4": $template = "podcast"; break;
        default : $template = "article"; break;
    }
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
            <a href="/index.php/Articles/Editor/Article/id/<?= $id ?>">
                <button class="button action-btn add-new is-uppercase has-text-centered">
                    <span class="icon"><i class="mdi mdi-lead-pencil"></i></span>&nbsp; <?php _p("Éditeur"); ?>
                </button>
            </a>
            <a href="/index.php/Articles/Display/Details/id/<?= $id ?>">
                <button class="button action-btn add-new is-uppercase has-text-centered">
                    <span class="icon"><i class="mdi mdi-eye"></i></span>&nbsp; <?php _p("Afficher"); ?>
                </button>
            </a>
            <a href="/index.php/Articles/Editor/Versions/id/<?= $id ?>" class="active">
                <button class="button action-btn add-new is-uppercase has-text-centered">
                    <span class="icon"><i class="mdi mdi-history"></i></span>&nbsp; <?php _p("Versions"); ?>
                </button>
            </a>
        </div>
    </section>

    <section class="section" id="article">
        <div class="container">
            <h2 class="title"><?php _p("Versions enregistrées"); ?></h2>
            <p style="margin-bottom:20px;"><?php _p("Les dernières versions sauvegardées de l'article. Restaurer remplace le contenu actuel (qui est lui-même sauvegardé en version)."); ?></p>

            <?php if(count($versions) == 0): ?>
                <p><em><?php _p("Aucune version enregistrée pour le moment."); ?></em></p>
            <?php else: ?>
                <table class="table is-fullwidth is-striped">
                    <thead>
                        <tr>
                            <th><?php _p("Version"); ?></th>
                            <th><?php _p("Taille"); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($versions as $i => $v): ?>
                        <tr>
                            <td>
                                <?php _p("Version du"); ?> <?= date("d/m/Y", $v["mtime"]) ?> <?php _p("à"); ?> <?= date("H:i", $v["mtime"]) ?>
                                <?php if($i === 0): ?><span class="tag is-info" style="margin-left:8px;"><?php _p("la plus récente"); ?></span><?php endif; ?>
                            </td>
                            <td><?= number_format($v["size"] / 1024, 1, ',', ' ') ?> Ko</td>
                            <td style="text-align:right;">
                                <a href="/index.php/Articles/Editor/RestoreVersion/id/<?= $id ?>/version/<?= urlencode($v["version"]) ?>"
                                   onclick="return confirm('<?php _p("Restaurer cette version ? Le contenu actuel sera remplacé (et sauvegardé en version)."); ?>');">
                                    <button class="button is-warning is-small">
                                        <span class="icon"><i class="mdi mdi-restore"></i></span>&nbsp;<?php _p("Restaurer"); ?>
                                    </button>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
</div>

<style>
    #buttons button, span.tag { border-radius: 0; }
    #buttons button span.icon { display: none; }
    #article .container { max-width: 1000px; }
    #article table td { vertical-align: middle; }
</style>
