<?php

class carteCMNPlugin extends BaseApplicationPlugin {
    # -------------------------------------------------------
    protected $description = 'Increase Map size for CollectiveAccess Pawtucket';
    # -------------------------------------------------------
    private $opo_config;
    private $ops_plugin_path;
    # -------------------------------------------------------
    public function __construct($ps_plugin_path) {
        $this->ops_plugin_path = $ps_plugin_path;
        $this->description = _t('Map Size plugin');
        parent::__construct();
        $this->opo_config = Configuration::load($ps_plugin_path.'/conf/carteCMN.conf');
    }
    # -------------------------------------------------------
    /**
     * Override checkStatus() to return true - the statisticsViewerPlugin always initializes ok... (part to complete)
     */
    public function checkStatus() {
        return array(
            'description' => $this->getDescription(),
            'errors' => array(),
            'warnings' => array(),
            'available' => ((bool)$this->opo_config->get('enabled'))
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
