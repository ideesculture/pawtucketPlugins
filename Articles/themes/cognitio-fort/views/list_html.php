<?php
$is_redactor = $this->getVar("is_redactor");
$articles = $this->getVar("articles");
//var_dump($articles);die();
?>


<h1 class="page-title"><?php _p("Articles"); ?></h1>


<div class="display-options level is-flex-desktop">
    <div class="level-left">
        <?php if($is_redactor): ?>
        <button class="button action-btn add-new is-uppercase has-text-centered">
          <span class="icon">
            <i class="mdi mdi-plus"></i>
          </span>
            &nbsp <?php _p("Nouveau"); ?>
        </button>
        <?php endif; ?>
    </div>
    <div class="level-right">
        <div class="dropdown is-hoverable">
            <div class="dropdown-trigger">
                <button class="button" aria-haspopup="true" aria-controls="dropdown-menu">
          <span>
            <p class="level-item"><?php _p("présentation par"); ?> &nbsp
              <em class="has-text-weight-semibold"><?php _p("liste"); ?></em>
          </span>
                    <span class="icon red">
            <i class="fa fa-caret-down"></i>
          </span>
                </button>
            </div>
            <div class="dropdown-menu" id="dropdown-menu" role="menu">
                <div class="dropdown-content">
                    <a href="<?php print __CA_URL_ROOT__; ?>/index.php/Articles/Show/index" class="dropdown-item">
                        <?php _p("vignettes"); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<hr>


<link href="//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css" rel="stylesheet" />
<script src="//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready( function () {
        $('#revueDePresse').DataTable({
            "language": {"url": "/datatables_french.json"},
            "info": false
        });
    } );
</script>
<table class="table" id="revueDePresse">
    <thead>
    <tr>
        <th><?php _p("Titre"); ?></th>
        <th><?php _p("Date"); ?></th>
        <th><?php _p("Auteur"); ?></th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <th><?php _p("Titre"); ?></th>
        <th><?php _p("Date"); ?></th>
        <th><?php _p("Auteur"); ?></th>
    </tr>
    </tfoot>
    <tbody>
    <?php foreach($articles as $key=>$article): ?>
        <tr>
            <td><a href="<?php print __CA_URL_ROOT__; ?>/index.php/Articles/Show/Details/id/<?php print $article['page_id']; ?>"><?php print $article["title"]; ?></a></td>
            <td><?php print $article["content"]["date"]; ?></td>
            <td><?php print $article["content"]["author"]; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<style>
    .ariane {
        padding-left: 0.5rem;
    }

    .breadcrumb {
        background-color: #BFD7E3;
        padding: 1rem;
        line-height: 19px;
        color: #232425 !important;
        width: 100vw;
        position: relative;
        left: 50%;
        right: 50%;
        margin-left: -50vw;
        margin-right: -50vw;
    }

    .breadcrumb a {
        color: #232425 !important;
    }

    .breadcrumb li+li::before {
        color: #232425 !important;
    }

    .page-title {
        margin-top: 50px;
        margin-bottom: 32px;
        font-size: 2.25rem !important;
        font-weight: 200 !important;
        line-height: 42px;
        padding-left: 0.6rem;
    }

    .level-item em {
        color: #E4675F;
        font-style: normal !important;
    }

    .display-options {
        padding-left: 0.6rem;
        justify-content: space-between;
        margin-bottom: 1rem !important;
    }

    .display-options .button,
    .pagination .button {
        border: none !important;
    }

    .level-item .red {
        color: #E4675F !important;
    }

    .add-new {
        font-size: 0.8rem;
        line-height: 0.9rem;
        border-radius: 2px;
    }

    hr {
        margin: 0 0.6rem 3.125rem 0.6rem !important;
    }

    .action-btn {
        background-color: #E4675F !important;
        color: #FFFFFF !important;
        border-radius: 2px;
        text-align: center;
    }

    .action-btn:hover,
    .action-btn.is-hovered {
        background-color: #FFFFFF !important;
        color: #E4675F !important;
        border: 2px solid #E4675F;
        box-sizing: border-box;
        border-radius: 2px;
    }

    .more {
        padding-left: 3rem;
        padding-right: 3rem;
    }

    .table {
        font-size: 0.8rem;
        padding: 1.5rem 0.5rem;
    }

    .table th abbr {
        color: #8D9DA0 !important;
        font-size: 1rem;
        text-decoration: none;
    }

    .table td,
    .table th {
        padding: 1rem 0.75rem !important;
    }

    .table abbr {
        color: #8D9DA0;
    }

    .pagination .level-item,
    .pagination .dropdown-item {
        font-size: 0.75rem;
        text-align: center;
    }

    .pagination-link {
        border: none;
        color: #232425;
    }

    .pagination-link.is-current {
        background-color: #E4675F;
        border: none;
        color: #fff;
    }

    .pagination-link:hover {
        cursor: pointer;
        background-color: #EFEFEF;
    }

    @media screen and (max-width: 768px) {
        .display-options {
            flex-direction: column;
            margin-top: 0 !important;
        }

        .level-right button {
            padding: 0 !important;
        }

        .level-item {
            justify-content: flex-start;
        }
    }

    table.dataTable .even {
        background-color:#fafafa;
    }
    table.dataTable th {
        color: #8D9DA0 !important;
        font-size:1.3em;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #E4675F;
        border: none;
        color: #fff !important;
        border-radius: 8px;
    }
    .dataTables_wrapper .dataTables_paginate {
        font-size:0.9em !important;
    }
</style>