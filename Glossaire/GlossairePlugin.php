<?php

class GlossairePlugin extends BaseApplicationPlugin {
    # -------------------------------------------------------
    protected $description = 'Glossaire for CollectiveAccess Pawtucket';
    # -------------------------------------------------------
    private $opo_config;
    private $ops_plugin_path;
    # -------------------------------------------------------
    public function __construct($ps_plugin_path) {
        $this->ops_plugin_path = $ps_plugin_path;
        $this->description = _t('Glossaire plugin');
        parent::__construct();
        $this->opo_config = Configuration::load($ps_plugin_path.'/conf/glossaire.conf');
    }
    # -------------------------------------------------------
    /**
     * Override checkStatus() to return true - the glossaire plugin always initializes ok
     */
    public function checkStatus() {
        $enabled = true;
        if (is_object($this->opo_config)) {
            $enabled = (bool)$this->opo_config->get('enabled');
        }

        return array(
            'description' => $this->getDescription(),
            'errors' => array(),
            'warnings' => array(),
            'available' => $enabled
        );
    }
    # -------------------------------------------------------
    /**
     * Add plugin user actions
     */
    static function getRoleActionList() {
        return array();
    }
    # -------------------------------------------------------
}
?>
