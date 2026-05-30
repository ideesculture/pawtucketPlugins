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
<div class="row article-card is-one-third">
    <div class="card">
        <div class="card-image" onClick='window.location.href = "./Details/id/<?php _p($id); ?>"' style="width:220px" >
            <figure class="image is-3by2">
            <a href="/index.php/Articles/Display/Details/id/<?php _p($id); ?>">
                <?php 
                	$article["image"] = str_replace("https://phoi.ideesculture.fr/", "/", $article["image"]);
                    if($article["image"]):
                ?>
                <img src="<?= $article["image"] ?>" alt="image thumbnail" style="max-height:180px;max-width: 220px;">
                <?php else: ?>
                    <img style="background: linear-gradient(135deg, rgba(23,87,139,1) 0%, rgba(50,138,173,1) 35%, rgba(124,175,201,1) 100%);">
                <?php endif; ?>
            </a> 
            </figure>
        </div>
        <div class="card-content"  onClick='window.location.href = "/index.php/Articles/Display/Details/id/<?php _p($id); ?>";return false;'>
            <div class="content" style="">
                <div class="card-details">
                    <p class="author" style="margin:0"><?php _p($article["author"]); ?></p>
                    <p class="date pull-right"><?php _p($article["date"]); ?></p>
                </div>
                <?php /*if(($article["date_from"]) && ($is_redactor)){ ?><span class="tag <?php 
                    if($is_future) {
                        print "is-success";
                    }
                    if(($is_past)) {
                        print "is-danger";
                    }
                    if((!$is_past) && (!$is_future)) {
                        print "is-success";
                    }
                    
                     ?>" style="margin-top:2px;margin-left:0px;margin-bottom:10px;"></span><br/>
                    <?php } */?>         
                    
                <h2 class="card-title">
                    <a style="color: inherit" href="/index.php/Articles/Display/Details/id/<?php _p($id); ?>">
                        <?php _p($article["title"]); ?>
                    </a>
                </h2>
                <h3 style="margin-top: 10px" class="card-subtitle"><?php _p($article["subtitle"]); ?></h3>
                <p><?php _p($content); ?></p>
            </div>
            <footer class="card-footer">
                <?php if(!$access): ?><span class="tag is-warning" style="margin-top:10px;border-radius:0">BROUILLON</span> <?php endif; ?>
                <?php if(($article["date_from"]) && ($is_redactor) && ($is_future)): ?>
                    <span class="tag is-success" style="margin-top:10px;border-radius:0">PROGRAMMÉ<br/><?= $article["date_from"]." - ".$article["date_to"]; ?></span>
                <?php endif; ?>  
            </footer>
        </div>  
    </div>
</div>