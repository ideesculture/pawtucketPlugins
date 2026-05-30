<?php

/** ---------------------------------------------------------------------
 * themes/default/Front/front_page_html : Front page of site 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2013 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * @package CollectiveAccess
 * @subpackage Core
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License version 3
 *
 * ----------------------------------------------------------------------
 */
require_once(__CA_MODELS_DIR__."/ca_site_pages.php");	
$vt_page = new ca_site_pages(29);
$medias = $vt_page->getPageMedia("page");
$paths = [];
foreach($medias as $media) {
	$media = $media["info"]["page"];
	$paths[] = __CA_URL_ROOT__."/media/collectiveaccess/images/".$media["HASH"]."/".$media["MAGIC"]."_".$media["FILENAME"];
}
$banner_path = $paths[array_rand($paths)];
?>
<div class="grid has-5-cols">
	<div class="cell is-col-span-2 graybox-container">
		<div class="graybox" style="
			background-color: #bdbfc3;
			padding: 20px 20px 0px 20px;
			padding-right: 130px;
			margin-right: -130px;
			margin-top: 40px;
			margin-left:-16px;
			position:relative;
			z-index:1;">
			<div class="main-title" style="
			font-family: lemonde-journal, serif;font-style: normal;padding:40px;text-align:right;font-weight:500;font-size: 44px;line-height: 49px;color:black;
			">
				Recherche, <br />
				mémoire & <br />
				transmission<br />
				d’une histoire <br />
				collective<br />
				<div style="margin-top:18px;">
					<a href='/index.php/Articles/Show/Index'><button class="button"
						style="font-size: 14px;font-family:barlow;background:transparent;margin-top:10px;color:#2f333c;border-color: currentColor;font-weight:600;">
						<span>Inscription</span>
						<span class="icon">
							<i class="fas fa-right-long"></i>
						</span>
					</button></a>
				</div>
			</div>

		</div>
	</div>

	<div class="cell is-col-span-3" style="margin-right: 26px">
		<div class="home-carousel" style="position:relative;z-index:2">
			<!-- random home carousel -->

			<div><img src="<?= $banner_path ?>" /></div>
		</div>

		<div class="blue-box"
			style="padding:60px 30px 45px 30px;background-color:#469cde;margin-left:-90px;padding-left:90px;margin-top:70px;">
			<div>
				<div class="columns is-flex-direction-row is-justify-content-space-between oversearch">
					<div class="p-3" data-searchtarget='MultiSearch/Index/Activate/entities'> Personnes </div>
					<div class="p-3" data-searchtarget='MultiSearch/Index/Activate/occurrences'> Événements </div>
					<div class="p-3" data-searchtarget='MultiSearch/Index/Activate/places'> Sites </div>
					<a class='archiveLink p-3' style="color:rgb(33, 39, 45)" href="/index.php/archives/Archives/Index"> Fonds d'archives </a>
					<a class="p-3" style="color: inherit" href="index.php/Search/advanced/objects"> Recherche avancée <span class="icon">
							<i class="far fa-circle-right"></i>
						</span></a>
				</div>
				<div class="field search" style="margin-bottom:22px">
					<form class="control has-icons-right" method="get" target="_self" action="MultiSearch/Index" id="search-second">
						<input id="searchinput" style="border:none;outline:none" class="input has-background-white has-text-black" name="search" placeholder="" type="search">
						<span class="icon is-small is-left is-pulled-right is-clickable recherche is-hoverable" style="position: absolute; right: 0">
							<button class="is-clickable" style="color:#fff" type="submit">Recherche</button>
						</span>
					</form>
				</div>
				<div class="columns is-flex-direction-row is-justify-content-space-between undersearch" style="clear:both">
					<div class="p-3"><a href="/index.php/classement_des_ressources">Comment sont classées nos ressources ?</a></div>
					<div class="p-3"><a href="/index.php/types_d_archives">Quels types d'archives ?</a></div>
					<div class="p-3"><a href="/index.php/recommandations">Recommandation pour les utilisateurs</a></div>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="grid has-5-cols" style="padding-top:20px;padding-bottom:30px;">
	<div class="cell is-col-span-3 actus-box about-box" style="height:520px">
		<div id="actus" style="">
			<div class="" style="font-size: 20px;font-weight: 600;line-height: 44px;margin-top:20px;color:#2f333c">
				Dernières actualités
			</div>
			<?php
    $blocks = $this->getVar("blocks");
    ?>
    <div class="derniers-contenus">
        <?php print $blocks; ?>
    </div>
			<div class="pull-right actus-mobile" style="margin-top: 20px;">
				<a href="/index.php/Articles/Show/Index" class="" style="font-size: 16px;font-family:barlow;background:transparent;margin-top:10px;font-weight:600;">
					<span>Voir plus d'actualités</span>
				</a>
			</div>
		</div>

		<div class="is-pulled-right" style="font-weight: 600;font-size:20px;line-height:44px;margin-top:20px;
    width: 213px;">À propos
			<span style="width:108px;border-bottom:1px solid #65686c;display:inline-block;"></span>
		</div>
	</div>
	<div class="cell is-col-span-3">
		<div class="intro-text">
			{{{bienvenue}}}
		</div>
	</div>
</div><!-- end row -->

<script>
	jQuery(document).ready(function() {
		jQuery('.oversearch > div.p-3').click(function() {
			if(jQuery(this).hasClass('active'))  {
				// remove active class from all divs
				jQuery(this).removeClass('active');
				// set value of searchtarget to form action
				jQuery('#search-second').attr('action', "MultiSearch/Index");
			} else {
				// remove active class from all divs
				jQuery('.oversearch > div.p-3').removeClass('active');
				// add active class to this
				jQuery(this).addClass('active');
				// get data-searchtarget attribute value from this
				var searchtarget = jQuery(this).attr('data-searchtarget');
				// set value of searchtarget to form action
				jQuery('#search-second').attr('action', searchtarget);
			}
			
		})
	})
</script>

<style>

	.archiveLink:hover {
		color: #333333;
		text-decoration: underline;
	}
	.blue-box a {
		color: black;

	}
	.blue-box a:hover {
		color: #333333;
		text-decoration: underline;
	}
	#actus {
		padding-left: 26px;
		display: inline-block;
		position: relative;
		top: -213px;
		width: 388px;
	}
	@media (max-width: 1200px) {
		#actus {
			display:none !important;
		}
		.actus-mobile {
			display: block;
		}
	}

	div.card-image {
		display: inline-block;
		border-bottom: 1px solid #65686c;
		margin-bottom: 10px;
		height:80px !important;
		width:100%;
		margin-top:20px;
		cursor: pointer;
	}
	div.card-image:first {
		margin-top: 30px;
	}
	.card-image img {
		width: 120px !important;
		height:80px !important;
		object-fit: cover;
	}
	.card-image a {
		display: inline-block;
		height: 80px !important;
		width: fit-content;
		
	}
	.card-content {
		display: inline-block;
		margin-left:120px;
	}
	h2.card-title {
		float: right;
		display: block;
		padding-top: 18px;
		padding-left: 0px;
		margin-left:0px;
		width: calc(100% - 140px);
	}
</style>