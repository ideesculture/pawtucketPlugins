# Articles — Documentation technique (CODING.md)

Plugin CMS pour CollectiveAccess **Pawtucket2**. Permet d'éditer des articles
en JSON via **Editor.js** directement depuis Pawtucket. Le contenu est stocké
dans la table native **`ca_site_pages`** de CollectiveAccess.

## Vue d'ensemble

- Pas de table dédiée : tout repose sur **`ca_site_pages`**.
- Le contenu rédactionnel est sérialisé en **JSON Editor.js** dans
  `content.blocs`.
- Le `template_id` de la page sert de typologie.

## Modèle de données (`ca_site_pages`)

| Champ | Usage Articles |
|---|---|
| `page_id` | identifiant de l'article |
| `template_id` | type : `1`=article, `2`=exposition, `3`=playlist, `4`=podcast |
| `template_title` | filtré sur `"article"` dans les listings |
| `title` | titre onglet navigateur |
| `keywords` | langues (`en,fr,my,si`) |
| `access` | `0`=brouillon, `1`=publié |
| `content` (conteneur) | sous-champs : `title`, `subtitle`, `author`, `date`, `date_from`, `date_to`, `image`, et surtout **`blocs`** (JSON Editor.js) |

`documentation/templates.conf` documente les sous-champs du conteneur `content`
(à configurer côté Providence).

## Point d'entrée

- `ArticlesPlugin.php` — coquille minimale (`BaseApplicationPlugin`). Charge
  `conf/articles.conf`, expose `checkStatus()`. Aucune logique métier.
- `conf/articles.conf` — `enabled = 1`.

## Controllers (`controllers/`)

Beaucoup de logique dupliquée entre controllers (filtrage publié/futur/passé,
détection du rôle `redactor`, tri par `date_from`).

### `EditorController.php` — back-office rédacteur
- `Article()` → vue éditeur Editor.js
- `SaveArticleJson()` → POST AJAX, enregistre `content.blocs`
- `Properties()` / `SaveArticleProperties()` → métadonnées (titre, dates, image…)
- `New()` → crée une `ca_site_pages` puis redirige vers Properties
- `Publish()` / `Unpublish()` → bascule `access`
- `Upload()` → uploader d'images/médias autonome (HTML inline + Bootstrap),
  chargé dans une `<iframe>`

### `DisplayController.php` — affichage public principal
- `Details()` → **rendu d'un article** (`display/article_html.php`) +
  navigation prev/next via SQL direct
- `Index()`, `All()`, `List()` → listings
- `Object()` / `Set()` → fragments pour blocs liés aux objets CA
- `Publish` / `Unpublish` / `Delete`

### `ShowController.php`
Quasi-doublon de Display pour les listings, mais avec **pagination**
(`Index()` paginé par 6). `Details()` redirige vers Display.

### `FrontController.php`
Bloc « front page » (5 derniers articles triés par date, filtrés par langue).

## Vues (`themes/cognitio-fort/views/`)

- **Éditeur** : `editor_article_html.php` — instancie **Editor.js** (CDN v2.27)
  + outils (header, list, quote, embed, raw, columns…) et outils custom.
  Sauvegarde AJAX vers `SaveArticleJson`.
- **Propriétés** : `editor_properties_html.php` — formulaire métadonnées +
  bulma-calendar pour les dates.
- **Affichage article** : `display/article_html.php` — **cœur du rendu** : un
  gros `switch($bloc["type"])` qui transforme chaque bloc JSON en HTML
  (paragraph, header, list, quote, simpleimage, simpleaudio, simplevideo,
  simpleAlbum, simpleCollectageVideo, imageGallery, embed, raw, table, columns,
  AnyButton, references, large-image…). Gère aussi meta OG/Twitter, tooltips
  thésaurus, numérotation
  des H2, bloc « Nos expositions ».
- Listings : `index_html.php`, `all_articles_html.php`, `list_html.php`,
  `home_block_html.php`, `front/front_*`, `display/object_html.php`,
  `display/set_html.php`.
- Variantes de langue : fichiers `.en_US`, `.si`, `.my` (surcharges de
  traduction Pawtucket).

## Bibliothèque Editor.js (`lib/`)

Outils Editor.js, dont des outils **maison** « ideesculture » :
- `ideesculture-editorjs-image/` (simple-image) — image avec « tunes »
  (boutons de réglage). Voir détail ci-dessous.
- `ideesculture-editorjs-album/` (simple-album) — bloc lié à un `ca_objects`
- `ideesculture-editorjs-morceau/` (simple-morceau + `iframemorceau.php`)
- `editorjs-audio/` (simple-audio), `editorjs-video/` (simple-video)
- `editorjs-button/` (AnyButton, rapatrié + patché) ; `editorjs-columns/`
  (@aaaalrashd, bundle ESM local) — cf. « Blocs tiers ».
- `editor.js` (racine)

### Outil image (`simple-image.js`) — tunes

Chaque tune = une entrée `{name, icon}` de `this.settings` ; le mécanisme :
`renderSettings()` crée un bouton par tune, `_toggleTune()` bascule
`this.data[name]`, `_acceptTuneView()` applique la classe `name` sur le wrapper
`.simple-image`, et `save()` renvoie `this.data` (donc tous les flags persistent).
Le rendu public (`display/article_html.php`, `case "simpleimage"`) reconstruit
les classes à partir des flags `true` des données — **un nouveau tune n'a donc
besoin que d'une classe CSS, pas de code de rendu**.

Tunes : `withBorder`, `stretched`, `withBackground`, **`halfWidth`** (réduit
l'image à 50% max de son conteneur, centrée — CSS dans `simple-image.css` ET
dans le `<style>` de `display/article_html.php` avec `!important` pour battre
`.container figure img { width:100% }`), `floatLeft`, `floatRight`.

> Les boutons `floatLeft`/`floatRight` sont **masqués** : `renderSettings()` les
> saute (`if (tune.name === 'floatLeft' || ...) return`). Ils restent dans
> `this.settings` pour que `_acceptTuneView()` continue d'appliquer les classes
> au contenu existant (CSS + rendu conservés ; on ne peut juste plus les activer).

## Conventions

- Préfixes hongrois CollectiveAccess : `$vt_` (table instance), `$vs_` (string),
  `$va_` (array), `$opo_`/`$opa_` (propriétés objet), `$o_` (objet).
- Détection du rôle rédacteur dans `EditorController` : helper privé
  `isRedactor()`. Ailleurs (Display/Show), parcours de
  `getUser()->getUserGroups()` à la recherche du `code == "redactor"`.
- Toute action d'écriture (Publish/Unpublish/Delete/New/Save*/Upload) doit être
  gardée par un contrôle `redactor` (sinon `die()` / réponse d'erreur JSON).
- Rendu : `$this->view->setVar(...)` puis `$this->render('vue.php')`.
- Modèle écrit : `setMode(ACCESS_WRITE)` avant `set()` / `update()` / `insert()`.
- **URLs absolues** : construire avec
  `__CA_SITE_PROTOCOL__ . "://" . __CA_SITE_HOSTNAME__` (jamais de domaine en
  dur). Pour rendre relatif un contenu stocké, utiliser le tableau
  `$strip_hosts` (= host courant + hôtes hérités) avec `str_replace`.

## Corrections appliquées (mai 2026)

- Référence morte `ideesculture-editorjs-collectage-video/` retirée de l'éditeur
  (script + link + enregistrement de l'outil `simpleCollectageVideo`). Le
  `case "simpleCollectageVideo"` reste dans `display/article_html.php` pour le
  rendu du contenu existant.
- `display/article_html.php` : second `case "quote"` (mort) supprimé ;
  `case "columns"` rend désormais réellement les colonnes au lieu d'un
  `var_dump()`.
- Sécurité : `Upload()`, `SaveArticleJson()`, `SaveArticleProperties()`, `New()`,
  `Publish()`, `Unpublish()` (Editor) et `Publish()`/`Unpublish()` (Display,
  Show) sont gardés par un contrôle `redactor`.
- `session_start()` + bloc mort « partie froide/chaude » retirés
  (Editor::Index, Front::Index2). CA gère déjà la session.
- URLs : `og:url` via les constantes CA ; réécritures de contenu hérité
  généralisées via `$strip_hosts` ; matching des liens thésaurus/objets rendu
  host-agnostique ; `<link>` CSS `dev.phoi.io` supprimé.
- `date_compare()` centralisée dans `lib/articles_functions.php` (gardée par
  `function_exists`), `require_once` depuis Front et Show ; définitions inline
  supprimées.
- `display/article_html.php` (bloc `simpleAlbum`) : `<img src>` en dur remplacé
  par un placeholder blanc du plugin (`assets/placeholder-white.png`), URL
  calculée via `$site_root . __CA_URL_ROOT__`.
- `lib/ideesculture-editorjs-morceau/iframemorceau.php` : `require` cassé
  (`/var/www/phoi/...`) corrigé en `$_SERVER['DOCUMENT_ROOT']."/setup.php"` ;
  titres « phoi.io » remplacés par la constante `__CA_SITE_NAME__`.
- Assets du thème mort `phoi` (`iframemorceau.php`, `display/object_html.php`,
  `display/set_html.php`) repointés sur le thème actif via `__CA_THEME_URL__`
  (= `/themes/<theme>`). `theme.css`/`patch.css` (inexistants dans cognitio-fort)
  remplacés par `main.css` (la feuille déclarée dans `assets.conf`) ; `fonts.css`
  conservé.

## Configuration des outils Editor.js (IdéesCulture)

Les blocs Editor.js personnalisés sont activables/désactivables par instance.

- **Défauts** : `conf/articles.conf`, clé associative `editorjs_ideesculture_tools`
  (`simpleimage`, `simpleaudio`, `simplevideo`, `simpleAlbum`,
  `simpleCollectageVideo`, `simpleMorceau`) — `1` = activé (défaut), `0` = désactivé.
- **Surcharge par instance** : `conf/local/articles.conf` (interne au plugin) —
  n'indiquer que les clés à changer ; merge **par clé** sur les défauts. Une clé
  totalement absente partout = activée.
- **API** (dans `lib/articles_functions.php`) :
  `articles_editorjs_tool_flags()` retourne le tableau mergé ;
  `articles_editorjs_tool_enabled($tool, $flags)` teste un outil.
- **Câblage** : `EditorController::Article()` calcule les flags et les passe à la
  vue (`editorjs_tool_flags`) ; `editor_article_html.php` conditionne `<script>`,
  `<link>` et l'enregistrement dans l'objet `tools` de chaque outil via la
  closure `$tool_on(...)`.
- Le merge plugin-local est fait à la main (la surcharge native CA
  `__CA_LOCAL_CONFIG_DIRECTORY__` vise le répertoire local global, pas le
  `conf/local/` du plugin).
- `simpleCollectageVideo` est re-câblé mais gardé désactivé pour cette instance ;
  son JS n'est pas fourni dans `lib/` (l'activer nécessite d'ajouter le fichier).

## Blocs tiers chargés dans l'éditeur

Outils tiers de l'éditeur d'article (en plus des outils IdéesCulture toggables) :

- **table** : `editorjs-table` (codinova-tech), épinglé `@1.4.10` via CDN jsDelivr
  (global UMD `Table`). Données `{ content:[[..]], withHeadings }`. Rendu :
  `case "table"` dans `display/article_html.php`.
- **AnyButton** : `editorjs-button`, **rapatrié en local** dans
  `lib/editorjs-button/editorjs-button.js` (bundle 3.0.3 patché : libellé
  `'Edit'` au lieu du japonais, règle CSS `--text` background-image retirée,
  `background-color:white` ajouté). Données `{ link, text }`.
- **columns** : `@aaaalrashd/editorjs-columns` (2–4 colonnes redimensionnables,
  éditeurs imbriqués avec images). **ESM-only** → bundle autonome (249 Ko, sa
  dépendance EditorJS incluse, CSS auto-injecté) **rapatrié en local** dans
  `lib/editorjs-columns/editorjs-columns.js` et chargé par `import()` dynamique
  **same-origin** dans une IIFE `async` ; si le chargement échoue, l'éditeur
  démarre sans le bloc colonnes. Outils internes autorisés : header, list,
  table, et l'image (`simpleimage`) si activée. Données
  `{ columns, ratio, blocks:[ [block,..], .. ], style }` ; rendu par
  `case "columns"` + helper `articles_render_inner_block()`
  (`lib/articles_functions.php`), qui rend récursivement header/paragraph/list/
  simpleimage/table/delimiter/quote.

> ⚠️ Pawtucket applique une CSP/politique de domaines : les ressources externes
> (esm.sh notamment) sont bloquées côté navigateur. Tout outil ESM doit donc
> être **rapatrié en local** (same-origin) comme `editorjs-columns` et
> `editorjs-button`. Le bundle local provient de
> `https://esm.sh/@aaaalrashd/editorjs-columns@1.0.5/es2022/editorjs-columns.bundle.mjs`.

## Blocs CollectiveAccess (CA Object / Occurrence / Object set)

Trois blocs Editor.js maison pour insérer une entité CA, même ergonomie que le
bouton : saisir un ID → « Set » → aperçu figé (rendu HTML récupéré en AJAX).
Menu contextuel = Move up / Delete / Move down (cœur Editor.js) + bouton **Edit**
(revient à la saisie). Toggables via `editorjs_ideesculture_tools`
(`caObject`, `caOccurrence`, `caSet`).

- **JS** : `lib/ideesculture-editorjs-ca/ca-entity.js` (+ `.css`). Une classe de
  base `CAEntityTool` + 3 sous-classes exposées en globals : `CAObjectTool`
  (`objects`), `CAOccurrenceTool` (`occurrences`), `CASetTool` (`sets`). Chaque
  sous-classe surcharge `static get toolbox()` (titre/icône) et `get table()`.
  Données sauvegardées : `{ id }` seulement (le rendu est recalculé côté serveur).
- **ID accepté** : clé primaire **ou** identifiant (`idno`, ou `set_code` pour
  les sets) — résolu par `articles_load_ca_instance()`.
- **Rendu centralisé & générique** (`lib/articles_functions.php`) :
  `articles_render_ca_entity($type, $id)` → utilisé À LA FOIS par l'aperçu AJAX
  (éditeur) et le rendu public, pour un affichage identique. Il dispatche vers
  `articles_render_ca_card($t, $conf, $detailKey)` (rendu commun : image à
  gauche, titre en gras, champs, CTA), le gabarit `$conf` venant de
  `articles_ca_card_conf($prefix, $defaultTitle)`. Chaque type a son préfixe de
  config : `ca_object`, `ca_occurrence`, `ca_set`. La carte reçoit le modificateur
  `--simple` quand il n'y a pas d'image.
- **CTA « En savoir plus »** : bouton en bas de carte vers la fiche Pawtucket,
  construit avec la **clé primaire** (donc correct même si saisie par idno) :
  - objet → `/index.php/Detail/objects/<pk>`
  - occurrence → `/index.php/Detail/occurrences/<pk>`
  - set → **aucun CTA**.
  Markup `<p class="ca-object-card__cta"><a class="button is-primary">…</a></p>`
  (espacement défini dans `ca-entity.css` + le `<style>` de `display/article_html.php`).
- **Endpoint AJAX** : `DisplayController::CALabel()` —
  `/index.php/Articles/Display/CALabel/table/<objects|occurrences|sets>/id/<id>`
  → JSON `{ table, id, html }`.
- **Enregistrement** : clés `caObject` / `caOccurrence` / `caSet` dans
  `editorTools`, placées **juste sous `columns`**. `config.urlRoot` =
  `__CA_URL_ROOT__`.
- **Rendu public** : `display/article_html.php`, cases `caObject` /
  `caOccurrence` / `caSet` groupés → `articles_render_ca_entity(...)`.

### Gabarit des cartes (paramétrable — `conf/articles.conf`)

Trois jeux de clés (même schéma), `<prefix>` ∈ `ca_object` / `ca_occurrence` /
`ca_set`. Les codes varient selon l'instance CollectiveAccess :
- `<prefix>_title_template` — titre en gras (déf. `^ca_<table>.preferred_labels`)
- `<prefix>_image_template` — image à gauche (déf. objet & occurrence :
  `^ca_object_representations.media.preview170.url`). L'URL est **normalisée**
  (on ne garde que la partie à partir de `/media/`) car certaines instances
  injectent le chemin filesystem dans l'URL média.
- `<prefix>_field_templates` — **liste** de templates `getWithTemplate` rendus
  en lignes (valeurs vides ignorées ; pas de virgule dans un template).

Exemples fournis : objet (type_id, idno, campagnes_construction,
mission_interet_strategique) ; occurrence (type_id). Le set n'a pas de gabarit
configuré → titre seul. CSS de la carte (`.ca-object-card`) dans `ca-entity.css`
ET dans le `<style>` de `display/article_html.php`.

Chaque ligne de champ est un `<p>` portant une **classe = code du champ**,
dérivée de la 1ʳᵉ référence `^ca_<table>.<code>` du template (ex.
`^ca_objects.idno` → `<p class="idno">…</p>`). Permet de cibler chaque champ en
CSS (`.ca-object-card .mission_interet_strategique { … }`). Un template sans
référence de champ (texte libre) produit un `<p>` sans classe.

Pour enrichir un type, il suffit d'ajouter/d'éditer ses clés `<prefix>_*` dans
`conf/articles.conf` (ou `conf/local/articles.conf`) — aucun code à toucher.

## Conventions URLs/thème (rappel)

- CSS/asset du thème actif : `__CA_THEME_URL__ . "/assets/pawtucket/css/main.css"`
  (ne jamais coder un nom de thème en dur). La feuille principale de
  cognitio-fort est `css/main.css` (cf. `themes/cognitio-fort/conf/assets.conf`).

## Points d'attention / dette technique restante

- Beaucoup de logique dupliquée entre controllers (filtrage, tri, détection
  rôle) — candidat à une factorisation plus large.
- Fichiers morts : `views/article_html.php` (top-level, non rendu),
  `views/front/front_page_html copy.php`, `*.bak`.
</content>
</invoke>
