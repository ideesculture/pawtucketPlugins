<?php

// sanitize page name for browse tab
$browser_tab_label = "PHOI";
?>
<script>
    window.parent.history.pushState('', "<?= $browser_tab_label ?>", 'https://www.phoi.io/');
    window.parent.document.title = "<?= $browser_tab_label ?>";
</script>

<div class="container">
	<H2>Derniers contenus ajoutés</H2>
    <?php
    $blocks = $this->getVar("blocks");
    ?>
    <div class="columns derniers-contenus">
        <?php print $blocks; ?>
    </div>
</div>

<style>
    .PODCAST {
        background-color:#5DAE9C !important;
    }
    .EXPOSITION {
        background-color:#EB9560 !important;
    }
</style>
</div>
<div style="background-color: #f2f2f2;margin-top:80px;">
<script type="text/javascript">
  function iframeLoaded() {
      var iFrameID = document.getElementById('audio-player');
      if(iFrameID) {
            // here you can make the height, I delete it first, then I make it again
            console.log(iFrameID.contentWindow.document.body.scrollHeight + "px");
            iFrameID.style.height = (iFrameID.contentWindow.document.body.scrollHeight*2) + "px";
      }   
  }
</script>  

    <div>
	    
<style>
.home-articles .card-image,
.home-articles .card-content {
		cursor:pointer;
}

.tag.is-primary.PLAYLIST {
	background-color: #d5d9b9;
}
.tag.is-primary.PODCAST {
	background-color: #5dae9c;
}
.tag.is-primary.ARTICLE {
	background-color: #bfd7e3;
}
.tag.is-primary.EXPOSITION {
	background-color: #eb9560;
}


</style>	    