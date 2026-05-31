<?php
    $id = $this->getVar("id");
?>
<head>
    <title>CollectiveAccess</title>
    <link rel='stylesheet' href='<?= __CA_THEME_URL__ ?>/assets/pawtucket/css/main.css' type='text/css' media='all'/>
</head>
<body>
    <div class="columns is-mobile">
        <div class="column is-two-fifths-mobile is-two-fifths-tablet">
            <img class="placeholder" />
        </div>
        <div class="column is-three-fifths-mobile is-three-fifths-tablet">
            <h3 class="title">Titre</h3>
            <h3 class="subtitle">Auteur</h3>
            <p>Objet <?= $id ?></p>
            <p>Description</p>
        </div>
    </div>
<style>
    html {
        background-color: #eee;
        padding:10px;
        overflow: hidden;
    }
    .title {

    }
    .subtitle {
        font-weight: 200;
    }
    .placeholder {
        background: rgb(49,78,89);
        background: linear-gradient(135deg, rgba(49,78,89,1) 0%, rgba(88,141,163,1) 35%, rgba(88,141,163,0.9290091036414566) 42%, rgba(104,201,250,0.4640231092436975) 100%);
        width: 100%;
        height:250px;
    }
</style>
</body>
