<?php
$article = $this->getVar("article");
$access = $this->getVar("access");
$is_redactor = $this->getVar("is_redactor");
$id = $this->getVar("id");
$article["blocs"]=str_replace('\\\n',"",$article["blocs"]);
$blocs = json_decode($article["blocs"],true);
//var_dump($blocs);die();
$content = $blocs["blocks"][0]["data"]["text"];
$content= strip_tags($content);
$content= mb_substr($content,0,119);
if(mb_strlen($content)==119) {
    $content=$content."...";
}

// Check if article is programmed in the past
$is_past = false;
if($article["date_to"]) {
    $date_to = $article["date_to"];
    // Ignore if the article is to be published in the future
    if(time() > strtotime($date_to)) $is_past = true;
}
// Check if article is programmed in the past
$is_future = false;
if($article["date_from"]) {
    $date_from = $article["date_from"];
    // Ignore if the article is to be published in the future
    if(time() < strtotime($date_from)) $is_future = true;
}

?>
<div class="row home-actu-cards">
    <div class="home-actu-card">
        <div class="card-image" onClick='window.location.href = "/index.php/Articles/Display/Details/id/<?php _p($id); ?>"'>
            <a href="/index.php/Articles/Display/Details/id/<?php _p($id); ?>"><?php 
                	$article["image"] = str_replace([__CA_SITE_PROTOCOL__ . "://" . __CA_SITE_HOSTNAME__ . "/", "https://phoi.ideesculture.fr/", "https://www.phoi.io/"], "/", $article["image"]);
                    if($article["image"]):
                ?><img
					src="<?= $article["image"] ?>" alt="image thumbnail" style="max-height:180px;max-width: 220px;"
				><?php else: ?><img
					style="background: linear-gradient(135deg, rgba(23,87,139,1) 0%, rgba(50,138,173,1) 35%, rgba(124,175,201,1) 100%);"
				><?php endif; ?>
            </a> 
			<h2 class="card-title"><?php _p($article["title"]); ?></h2>
        </div>  
    </div>
</div>