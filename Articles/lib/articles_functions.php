<?php
/**
 * Shared helper functions for the Articles plugin.
 * Centralised here to avoid duplicate global declarations across controllers.
 */

if (!function_exists('date_compare')) {
    // Sort callback: most recent "date_from" first.
    function date_compare($a, $b) {
        $t1 = strtotime($a['date_from']);
        $t2 = strtotime($b['date_from']);
        return $t2 - $t1;
    }
}

if (!function_exists('articles_editorjs_tool_flags')) {
    /**
     * Merged enable/disable flags for the IdéesCulture Editor.js tools.
     * conf/articles.conf provides the defaults; conf/local/articles.conf
     * overrides them per key (only the keys it defines take precedence).
     *
     * @return array Map of tool name => 0|1
     */
    function articles_editorjs_tool_flags() {
        $vs_conf_dir = __CA_APP_DIR__ . '/plugins/Articles/conf';
        $va_flags = [];

        $o_default = Configuration::load($vs_conf_dir . '/articles.conf');
        $va_default = $o_default->getAssoc('editorjs_ideesculture_tools');
        if (is_array($va_default)) { $va_flags = $va_default; }

        $vs_local_path = $vs_conf_dir . '/local/articles.conf';
        if (file_exists($vs_local_path)) {
            $o_local = Configuration::load($vs_local_path);
            $va_local = $o_local->getAssoc('editorjs_ideesculture_tools');
            if (is_array($va_local)) { $va_flags = array_merge($va_flags, $va_local); }
        }

        return $va_flags;
    }
}

if (!function_exists('articles_render_inner_block')) {
    /**
     * Renders a single Editor.js block to HTML. Used to render the blocks nested
     * inside the columns tool (@aaaalrashd/editorjs-columns), whose saved data is
     * { columns, ratio, blocks: [ [block, ...], ... ], style }.
     *
     * Supports the subset of tools allowed inside columns (header, paragraph,
     * list, simpleimage, table, delimiter, quote). Unknown types are ignored.
     *
     * @param array $block        A single Editor.js block ({type, data})
     * @param array $strip_hosts  Hosts to strip from image URLs (see article view)
     * @return string HTML
     */
    function articles_render_inner_block($block, $strip_hosts = []) {
        if (!is_array($block) || !isset($block["type"])) { return ""; }
        $type = $block["type"];
        $data = isset($block["data"]) && is_array($block["data"]) ? $block["data"] : [];
        ob_start();
        switch ($type) {
            case "header":
                $level = isset($data["level"]) ? (int)$data["level"] : 2;
                if ($level < 1 || $level > 6) { $level = 2; }
                echo "<h{$level}>" . ($data["text"] ?? "") . "</h{$level}>";
                break;
            case "paragraph":
                echo "<p>" . ($data["text"] ?? "") . "</p>";
                break;
            case "list":
                $tag = (isset($data["style"]) && $data["style"] === "ordered") ? "ol" : "ul";
                echo "<{$tag}>";
                foreach (($data["items"] ?? []) as $item) {
                    if (is_array($item)) { $item = $item["content"] ?? ""; }
                    echo "<li>" . $item . "</li>";
                }
                echo "</{$tag}>";
                break;
            case "simpleimage":
                $url = isset($data["url"]) ? str_replace($strip_hosts, "/", $data["url"]) : "";
                $caption = $data["caption"] ?? "";
                $classes = "";
                foreach ($data as $k => $v) {
                    if (in_array($k, ["url", "caption", "content"], true)) { continue; }
                    if ($v) { $classes .= $k . " "; }
                }
                if ($url !== "") {
                    echo '<figure class="simple-image ' . trim($classes) . '">';
                    echo '<img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($caption) . '">';
                    if ($caption !== "") { echo '<figcaption>' . $caption . '</figcaption>'; }
                    echo '</figure>';
                }
                break;
            case "table":
                $rows = $data["content"] ?? [];
                if (is_array($rows) && count($rows) > 0) {
                    $with_headings = !empty($data["withHeadings"]);
                    echo "<table>";
                    foreach ($rows as $ri => $row) {
                        if (!is_array($row)) { continue; }
                        $ct = ($with_headings && $ri === 0) ? "th" : "td";
                        echo "<tr>";
                        foreach ($row as $cell) { echo "<{$ct}>" . $cell . "</{$ct}>"; }
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                break;
            case "delimiter":
                echo '<div class="delimiter"></div>';
                break;
            case "quote":
                echo '<blockquote>' . ($data["text"] ?? "") . '</blockquote>';
                break;
            default:
                // Unknown inner block type: ignore
                break;
        }
        return ob_get_clean();
    }
}

if (!function_exists('articles_editorjs_tool_enabled')) {
    /**
     * True if the given Editor.js tool is enabled. An absent flag defaults to
     * enabled.
     *
     * @param string $ps_tool   Tool name (e.g. "simpleAlbum")
     * @param array  $pa_flags  Optional pre-loaded flags (see articles_editorjs_tool_flags())
     * @return bool
     */
    function articles_editorjs_tool_enabled($ps_tool, $pa_flags = null) {
        if ($pa_flags === null) { $pa_flags = articles_editorjs_tool_flags(); }
        if (!array_key_exists($ps_tool, $pa_flags)) { return true; }
        return (bool)(int)$pa_flags[$ps_tool];
    }
}

if (!function_exists('articles_ca_card_conf')) {
    /**
     * Gabarit d'affichage d'une carte CA, lu depuis conf/articles.conf et
     * surchargé par conf/local/articles.conf (surcharge par clé entière).
     * Lit les clés <prefix>_title_template / <prefix>_image_template /
     * <prefix>_field_templates. Les codes varient selon l'instance.
     *
     * @param string $ps_prefix        ex. "ca_object", "ca_occurrence", "ca_set"
     * @param string $ps_default_title template de titre par défaut si non configuré
     * @return array { title: string, image: string, fields: string[] }
     */
    function articles_ca_card_conf($ps_prefix, $ps_default_title) {
        static $cache = [];
        if (isset($cache[$ps_prefix])) { return $cache[$ps_prefix]; }

        $vs_dir = __CA_APP_DIR__ . '/plugins/Articles/conf';
        $o_default = Configuration::load($vs_dir . '/articles.conf');
        $vs_local_path = $vs_dir . '/local/articles.conf';
        $o_local = file_exists($vs_local_path) ? Configuration::load($vs_local_path) : null;

        $get = function($key, $is_list = false) use ($o_default, $o_local) {
            if ($o_local) {
                $lv = $is_list ? $o_local->getList($key) : $o_local->get($key);
                if ($is_list ? (is_array($lv) && count($lv)) : ($lv !== null && $lv !== false && $lv !== '')) { return $lv; }
            }
            return $is_list ? $o_default->getList($key) : $o_default->get($key);
        };

        $title = trim((string)$get($ps_prefix . '_title_template'));
        $image = trim((string)$get($ps_prefix . '_image_template'));
        $fields = $get($ps_prefix . '_field_templates', true);

        $cache[$ps_prefix] = [
            'title'  => $title !== '' ? $title : $ps_default_title,
            'image'  => $image,
            'fields' => is_array($fields) ? $fields : [],
        ];
        return $cache[$ps_prefix];
    }
}

if (!function_exists('articles_load_ca_instance')) {
    /**
     * Charge une entité CA par clé primaire (si numérique) ou par identifiant
     * (idno, ou set_code pour ca_sets).
     *
     * @param string $ps_table  ca_objects | ca_occurrences | ca_sets
     * @param mixed  $pm_id      clé primaire ou idno/set_code
     * @return object|null instance du modèle chargée, ou null
     */
    function articles_load_ca_instance($ps_table, $pm_id) {
        require_once(__CA_MODELS_DIR__ . "/{$ps_table}.php");

        if (is_numeric($pm_id)) {
            $t = new $ps_table((int)$pm_id);
            if ($t->getPrimaryKey()) { return $t; }
        }
        $vs_id_field = ($ps_table === 'ca_sets') ? 'set_code' : 'idno';
        $t = new $ps_table();
        $t->load([$vs_id_field => (string)$pm_id]);
        return $t->getPrimaryKey() ? $t : null;
    }
}

if (!function_exists('articles_render_ca_card')) {
    /**
     * Rend une carte CA générique selon un gabarit (image à gauche,
     * preferred_labels en gras, puis les champs configurés, et un CTA optionnel).
     *
     * @param object $pt           instance du modèle CA chargée
     * @param array  $pa_conf      gabarit (cf. articles_ca_card_conf)
     * @param string $ps_detail    clé Detail (ex. "objects") pour le CTA, ou "" pour aucun
     * @return string HTML
     */
    function articles_render_ca_card($pt, $pa_conf, $ps_detail = '') {
        $title = $pa_conf['title'] !== '' ? trim($pt->getWithTemplate($pa_conf['title'])) : '';

        $img = '';
        if ($pa_conf['image'] !== '') {
            $url = trim($pt->getWithTemplate($pa_conf['image']));
            // Normalisation : certaines instances injectent le chemin filesystem
            // dans l'URL média -> on ne garde que la partie web à partir de /media/.
            $pos = strpos($url, '/media/');
            if ($pos !== false) { $url = substr($url, $pos); }
            if ($url !== '') {
                $img = '<img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($title) . '">';
            }
        }

        $lines = '';
        foreach ($pa_conf['fields'] as $tpl) {
            $tpl = trim($tpl);
            if ($tpl === '') { continue; }
            $v = trim($pt->getWithTemplate($tpl));
            if ($v === '') { continue; }
            // Classe = code du champ (1ère référence ^ca_<table>.<code> du template)
            $cls = '';
            if (preg_match('/\^ca_[a-z_]+\.([A-Za-z0-9_]+)/', $tpl, $m)) { $cls = $m[1]; }
            $lines .= '<p' . ($cls !== '' ? ' class="' . $cls . '"' : '') . '>' . $v . '</p>';
        }

        // CTA « En savoir plus » vers la fiche (clé primaire, même si saisie par idno)
        $cta = '';
        if ($ps_detail !== '') {
            $pk = (int)$pt->getPrimaryKey();
            $cta = '<p class="ca-object-card__cta"><a class="button is-primary" href="/index.php/Detail/'
                . $ps_detail . '/' . $pk . '">En savoir plus</a></p>';
        }

        $modifier = ($img === '') ? ' ca-object-card--simple' : '';
        $html  = '<div class="ca-object-card' . $modifier . '">';
        if ($img !== '') { $html .= '<div class="ca-object-card__image">' . $img . '</div>'; }
        $html .= '<div class="ca-object-card__body">';
        $html .= '<div class="ca-object-card__title">' . $title . '</div>';
        $html .= $lines;
        $html .= $cta;
        $html .= '</div></div>';
        return $html;
    }
}

if (!function_exists('articles_render_ca_entity')) {
    /**
     * Rend le HTML d'un bloc CA. Le gabarit de chaque type est configurable
     * (clés ca_object_*, ca_occurrence_*, ca_set_* dans articles.conf).
     * Utilisé à la fois par l'aperçu AJAX (éditeur) et le rendu public.
     *
     * @param string $ps_type objects | occurrences | sets
     * @param mixed  $pm_id    clé primaire ou idno/set_code
     * @return string HTML (vide si introuvable)
     */
    function articles_render_ca_entity($ps_type, $pm_id) {
        // type => [ table, préfixe conf, titre par défaut, clé Detail (CTA) ]
        $va_map = [
            'objects'     => ['ca_objects',     'ca_object',     '^ca_objects.preferred_labels',     'objects'],
            'occurrences' => ['ca_occurrences', 'ca_occurrence', '^ca_occurrences.preferred_labels', 'occurrences'],
            'sets'        => ['ca_sets',        'ca_set',        '^ca_sets.preferred_labels',        ''],
        ];
        if (!isset($va_map[$ps_type])) { return ''; }
        list($vs_table, $vs_prefix, $vs_default_title, $vs_detail) = $va_map[$ps_type];

        $t = articles_load_ca_instance($vs_table, $pm_id);
        if (!$t) { return ''; }

        $conf = articles_ca_card_conf($vs_prefix, $vs_default_title);
        return articles_render_ca_card($t, $conf, $vs_detail);
    }
}
