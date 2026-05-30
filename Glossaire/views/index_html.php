<?php
$va_items = $this->getVar("items");

// Build items with term + description
$va_entries = [];
foreach ($va_items as $va_item) {
	$vt_item = new ca_list_items($va_item['item_id']);
	$vs_term = $vt_item->getWithTemplate("^ca_list_items.preferred_labels");
	$vs_description = $vt_item->getWithTemplate("^ca_list_items.preferred_labels.description");
	if (!$vs_term) { continue; }
	$va_rep = $vt_item->getPrimaryRepresentation(['thumbnail']);
	$vs_img_tag = ($va_rep && isset($va_rep['tags']['thumbnail'])) ? $va_rep['tags']['thumbnail'] : null;
	$va_entries[] = ['term' => $vs_term, 'desc' => ucfirst($vs_description), 'img' => $vs_img_tag];
}

// Sort alphabetically by the displayed (translated) label
usort($va_entries, function($a, $b) {
	return strcmp(strtolower(caRemoveAccents(trim($a['term']))), strtolower(caRemoveAccents(trim($b['term']))));
});

// Build letter index from sorted entries
$va_letters = [];
foreach ($va_entries as &$va_entry) {
	$vs_first = strtoupper(substr(caRemoveAccents(trim($va_entry['term'])), 0, 1));
	$va_entry['letter'] = $vs_first;
	$va_letters[$vs_first] = true;
}
unset($va_entry);
$va_letters = array_keys($va_letters);
?>
<section class="cf-glossaire-section" aria-labelledby="cf-glossaire-title">

  <header class="cf-glossaire-header">
    <h1 id="cf-glossaire-title"><?= _t('Glossaire'); ?></h1>
    <p><?= _t('Définitions des termes liés à l\'architecture fortifiée'); ?></p>
    <div class="cf-glossaire-rule" aria-hidden="true"></div>
  </header>

<?php if (count($va_entries) === 0): ?>
  <p class="cf-glossaire-empty"><?= _t('Aucun terme trouvé.'); ?></p>
<?php else: ?>

  <nav class="cf-glossaire-alpha" aria-label="<?= _t('Navigation alphabétique'); ?>">
    <?php foreach ($va_letters as $vs_l): ?>
      <a href="#cf-letter-<?= $vs_l; ?>" class="cf-glossaire-alpha__link"><?= $vs_l; ?></a>
    <?php endforeach; ?>
  </nav>

  <dl class="cf-glossaire-list">
    <?php
    $vs_current_letter = null;
    foreach ($va_entries as $va_entry):
      if ($va_entry['letter'] !== $vs_current_letter):
        if ($vs_current_letter !== null) { echo '</dl><dl class="cf-glossaire-list">'; }
        $vs_current_letter = $va_entry['letter'];
    ?>
      <dt class="cf-glossaire-letter" id="cf-letter-<?= $vs_current_letter; ?>"><?= $vs_current_letter; ?></dt>
    <?php endif; ?>
      <?php
        $vs_slug = strtolower(caRemoveAccents(trim($va_entry['term'])));
        $vs_slug = preg_replace('/\s+/', '_', $vs_slug);
        $vs_slug = preg_replace('/[^a-z0-9_]/', '', $vs_slug);
      ?>
      <div class="cf-glossaire-entry<?= $va_entry['img'] ? ' cf-glossaire-entry--has-img' : ''; ?>">
        <?php if ($va_entry['img']): ?>
        <div class="cf-glossaire-entry__img"><?= $va_entry['img']; ?></div>
        <?php endif; ?>
        <div class="cf-glossaire-entry__body">
          <dt class="cf-glossaire-term" id="cf-term-<?= $vs_slug; ?>"><?= htmlspecialchars($va_entry['term']); ?></dt>
          <?php if ($va_entry['desc']): ?>
          <dd class="cf-glossaire-desc"><?= $va_entry['desc']; ?></dd>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </dl>

<?php endif; ?>
</section>

<style>
  .cf-glossaire-section {
    padding: 50px 20px 80px;
    background: #dacab3;
    margin: -20px -15px 0;
  }

  .cf-glossaire-header {
    margin-bottom: 28px;
  }

  .cf-glossaire-header h1 {
    font-family: 'WantedSansBlack', sans-serif;
    font-weight: 900;
    font-size: 26px;
    color: #1d3a6a;
    letter-spacing: 0.04em;
    margin: 0;
    text-transform: uppercase;
  }

  .cf-glossaire-header p {
    font-family: 'WantedSans', sans-serif;
    font-size: 15px;
    color: #5a5548;
    margin: 8px 0 0;
  }

  .cf-glossaire-rule {
    width: 40px;
    height: 2px;
    background: #3a8a8a;
    margin-top: 10px;
  }

  /* Barre alphabétique */
  .cf-glossaire-alpha {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-bottom: 36px;
  }

  .cf-glossaire-alpha__link {
    font-family: 'WantedSansBlack', sans-serif;
    font-size: 14px;
    color: #1d3a6a;
    background: #f5f0e4;
    border-radius: 3px;
    padding: 4px 10px;
    text-decoration: none;
    transition: background 150ms ease, color 150ms ease;
    line-height: 1;
  }

  .cf-glossaire-alpha__link:hover,
  .cf-glossaire-alpha__link:focus-visible {
    background: #3a8a8a;
    color: #fff;
    text-decoration: none;
    outline: none;
  }

  /* Liste des termes */
  .cf-glossaire-list {
    max-width: 700px;
    margin: 0 0 0 0;
    padding: 0;
  }

  .cf-glossaire-letter {
    font-family: 'WantedSansBlack', sans-serif;
    font-size: 20px;
    color: #3a8a8a;
    margin: 32px 0 12px;
    padding-bottom: 4px;
    border-bottom: 1px solid #c4b49a;
    scroll-margin-top: 80px;
  }

  /* Entrée avec image */
  .cf-glossaire-entry {
    display: flex;
    align-items: flex-start;
    gap: 0;
  }

  .cf-glossaire-entry__img {
    flex: 0 0 60px;
    width: 60px;
    margin-top: 18px;
    margin-right: 10px;
  }

  .cf-glossaire-entry__img img {
    display: block;
    width: 60px;
    height: 40px;
    object-fit: contain;
    border-radius: 2px;
  }

  .cf-glossaire-entry__body {
    flex: 1 1 auto;
    min-width: 0;
  }

  .cf-glossaire-term {
    font-family: 'WantedSansBlack', sans-serif;
    font-size: 15px;
    color: #1d3a6a;
    margin: 16px 0 2px;
    padding: 6px 10px;
    border-radius: 2px;
    background: transparent;
    transition: background 150ms ease;
    scroll-margin-top: 80px;
  }

  .cf-glossaire-term:hover {
    background: #f5f0e4;
  }

  .cf-glossaire-desc {
    font-family: 'WantedSans', sans-serif;
    font-size: 14px;
    color: #5a5548;
    margin: 0 0 4px 10px;
    line-height: 1.55;
  }

  .cf-glossaire-empty {
    font-family: 'WantedSans', sans-serif;
    color: #5a5548;
    font-size: 15px;
  }

  @media (max-width: 767px) {
    .cf-glossaire-section { padding: 30px 12px 50px; }
    .cf-glossaire-header h1 { font-size: 21px; }
    .cf-glossaire-alpha__link { font-size: 13px; padding: 4px 8px; }
  }
</style>

<a href="#" id="cf-glossaire-back-to-top" aria-label="Retour en haut de page">
  <i class="fa fa-angle-up"></i>
</a>

<style>
#cf-glossaire-back-to-top {
  position: fixed;
  bottom: 36px;
  right: 36px;
  width: 45px;
  height: 45px;
  font-size: 55px;
  line-height: 45px;
  text-align: center;
  color: #fff;
  background-color: transparent;
  text-shadow: 0px 0px 10px #000000;
  text-decoration: none;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease;
  z-index: 9999;
}
#cf-glossaire-back-to-top.visible {
  opacity: 1;
  pointer-events: auto;
}
#cf-glossaire-back-to-top:hover,
#cf-glossaire-back-to-top:focus {
  color: #fff;
  text-decoration: none;
}
</style>

<script>
(function() {
  var btn = document.getElementById('cf-glossaire-back-to-top');
  if (!btn) return;

  window.addEventListener('scroll', function() {
    if (window.scrollY > 400) {
      btn.classList.add('visible');
    } else {
      btn.classList.remove('visible');
    }
  });

  btn.addEventListener('click', function(e) {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
})();
</script>

<div style="clear:both;height:80px;"></div>
