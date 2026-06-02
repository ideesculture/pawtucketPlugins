# pawtucketPlugins

Ensemble de plugins pour [CollectiveAccess Pawtucket2](https://www.collectiveaccess.org/), développés par [idéesculture](https://www.ideesculture.com).

Chaque plugin se déploie en copiant son dossier dans `app/plugins/` de l'installation Pawtucket. Il est ensuite détecté automatiquement et peut être activé/désactivé via la clé `enabled` de son fichier de configuration (`conf/<plugin>.conf`).

## Plugins

### Articles

Permet d'afficher des contenus riches (PHOI) au sein des pages de site CollectiveAccess. Les contenus sont stockés en JSON dans un unique conteneur, ce qui autorise la hiérarchie, la répétabilité et la structuration des blocs de contenu.

- Contrôleurs : `Display`, `Editor`, `Front`, `Show`
- Configuration : `conf/articles.conf`
- Gère également le versionnage des articles (sauvegarde et restauration des versions).
- Voir [Articles/README.md](Articles/README.md) et [Articles/CODING.md](Articles/CODING.md).

### Glossaire

Gestion d'un glossaire de termes et de leurs définitions, avec affichage public et interface d'édition réservée aux rédacteurs (rôle `redactor`).

- Contrôleur : `List`
- Configuration : `conf/glossaire.conf`
- URLs principales :
  - `/index.php/Glossaire/Display/Index` — vue publique
  - `/index.php/Glossaire/Editor/Index` — gestion (rédacteurs)
- Voir [Glossaire/README.md](Glossaire/README.md).

### carteCMN

Affichage cartographique (Leaflet) de couches géographiques pour le Centre des monuments nationaux. Agrandit la carte et superpose plusieurs jeux de données vectorielles servis en GeoJSON :

- Monuments historiques (`mh/`)
- Sites patrimoniaux remarquables — SPR (`spr/`)
- Cadastre / parcelles (`cadastre/`)
- Bâtiments de l'État (`batiments-etat/`)
- Départements (`departements/`)
- Natura 2000 (`natura2000/`)

- Contrôleur : `Show` (actions `mhGeojson`, `sprGeojson`, `communeSprGeojson`, `batimentsEtatGeojson`, `Index`)
- Bibliothèques embarquées : `Leaflet.VectorGrid`, `leaflet-omnivore`
- Configuration : `conf/carteCMN.conf`

## Licence

GPL v3 (voir [LICENSE](LICENSE)).
