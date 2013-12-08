<?php
/**
 * @package     Ts
 * @category    Ts_Phpids
 * @author      Christoph Frenes <c.frenes@triplesense.de>
 */
class Ts_Phpids_Model_Phpids
{
    const GENERAL_FILTER_TYPE_XML      = 'xml';
    const GENERAL_FILTER_TYPE_JSON     = 'json';

    const GENERAL_REQUEST_TYPE_REQUEST = 'REQUEST';
    const GENERAL_REQUEST_TYPE_COOKIE  = 'COOKIE';
    const GENERAL_REQUEST_TYPE_POST    = 'POST';
    const GENERAL_REQUEST_TYPE_GET     = 'GET';

    /**
     * @var IDS\Init
     */
    protected $_init;

    /**
     * @var Ts_Phpids_Helper_Data
     */
    protected $_tsHelper;

    public function __construct()
    {
        $this->_tsHelper = Mage::helper('ts_phpids');
    }

    /**
     * observer method: execute PHPIDS check on every page load if module is enabled in backend
     *
     * @param Varien_Event_Observer $observer
     */
    public function doPhpidsCheckIfEnabledInBackendAndRequestTypesAreSelected(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfigFlag('ts_phpids/general/enable')) {
            $request = $this->_prepareRequestTypes();

            if (!is_array($request)) {
                return;
            }

            $this->initPhpids();
            $result = $this->monitorRequestAndReturnResult($request);

            if (!$result->isEmpty()) {
                switch (true) {
                    case $result->getImpact() >= (int)Mage::getStoreConfig('ts_phpids/general/tolerance_block'):
                        require_once Mage::getBaseDir() . DS . 'errors' . DS . 'processor.php';
                        $processor = new Error_Processor();
                        $processor->process503();

                        $this->logReportIfEnabled($result, Zend_Log::ALERT);

                        $action = $observer->getEvent()->getControllerAction();
                        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                        break;
                    case $result->getImpact() >= (int)Mage::getStoreConfig('ts_phpids/general/tolerance_log'):
                        $this->logReportIfEnabled($result);
                        break;
                }
            }
        }
    }

    /**
     * @param \IDS\Report $report
     * @param int $logLevel Zend_Log
     */
    public function logReportIfEnabled(IDS\Report $report, $logLevel = Zend_Log::WARN)
    {
        if (Mage::getStoreConfigFlag('ts_phpids/general/logging')) {
            $logMsg = sprintf(
                'Store: %s | Impact: %s | Tags: %s | IP: %s',
                Mage::app()->getStore()->getCode(),
                $report->getImpact(),
                implode(', ', $report->getTags()),
                Mage::helper('core/http')->getRemoteAddr()
            );

            Mage::log($logMsg, $logLevel, 'ts_phpids.log');
        }
    }

    protected function _prepareRequestTypes()
    {
        $requestTypes = explode(',', Mage::getStoreConfig('ts_phpids/general/request_type'));

        if (empty($requestTypes)) {
            return false;
        }

        $types = array();

        foreach ($requestTypes AS $type) {
            switch ($type) {
                case self::GENERAL_REQUEST_TYPE_REQUEST:
                    $types[self::GENERAL_REQUEST_TYPE_REQUEST] = $_REQUEST;
                    break;
                case self::GENERAL_REQUEST_TYPE_COOKIE:
                    $types[self::GENERAL_REQUEST_TYPE_COOKIE]  = $_COOKIE;
                    break;
                case self::GENERAL_REQUEST_TYPE_POST:
                    $types[self::GENERAL_REQUEST_TYPE_POST]    = $_POST;
                    break;
                case self::GENERAL_REQUEST_TYPE_GET:
                    $types[self::GENERAL_REQUEST_TYPE_GET]     = $_GET;
                    break;
                default:
                    return false;
            }
        }

        return $types;
    }

    public function initPhpids()
    {
        Ts_Phpids_Model_Autoloader::addAutoloader();
        $this->_init = IDS\Init::init($this->_tsHelper->getModuleDir() . 'ids/config.ini');
        Ts_Phpids_Model_Autoloader::removeAutoloader();
        $this->changeConfigFileSettings();

        return $this->_init;
    }

    /**
     * @param array $request
     * @return \IDS\Report
     */
    public function monitorRequestAndReturnResult($request)
    {
        Ts_Phpids_Model_Autoloader::addAutoloader();
        $ids = new IDS\Monitor($this->_init);
        $result = $ids->run($request);
        Ts_Phpids_Model_Autoloader::removeAutoloader();

        return $result;
    }

    /**
     * exchange settings from config file with settings from Magento's backend and/or Magento paths
     */
    public function changeConfigFileSettings()
    {
        $this->_init->setConfig($this->_setGeneralConfig(), true);
        $this->_init->setConfig($this->_setCachingConfig(), true);
    }

    /**
     * dynamically set config for general section
     *
     * @return array
     */
    protected function _setGeneralConfig()
    {
        $general = array(
            'General' => array(
                'base_path'     => Mage::getBaseDir(),
                'tmp_path'      => Mage::getBaseDir('tmp'),
                'scan_keys'     => Mage::getStoreConfig('ts_phpids/general/scan_keys'),
                'use_base_path' => false
            )
        );

        if (Mage::getStoreConfig('ts_phpids/general/filter_type') == self::GENERAL_FILTER_TYPE_JSON) {
            $general['General']['filter_type'] = self::GENERAL_FILTER_TYPE_JSON;
            $general['General']['filter_path'] = $this->_tsHelper->getModuleDir() . 'ids/default_filter.json';
        } else {
            $general['General']['filter_type'] = self::GENERAL_FILTER_TYPE_XML;
            $general['General']['filter_path'] = $this->_tsHelper->getModuleDir() . 'ids/default_filter.xml';
        }

        $general = $this->_setGeneralMultilineTextareaSettings(
            $general,
            'html',
            Mage::getStoreConfig('ts_phpids/general/html')
        );

        $general = $this->_setGeneralMultilineTextareaSettings(
            $general,
            'json',
            Mage::getStoreConfig('ts_phpids/general/json')
        );

        $general = $this->_setGeneralMultilineTextareaSettings(
            $general,
            'exceptions',
            Mage::getStoreConfig('ts_phpids/general/exceptions')
        );

        return $general;
    }

    /**
     * @param array $configArray
     * @param string $configKey subvalue in 'General'
     * @param string $textArea content of a backend textarea
     * @return array
     */
    protected function _setGeneralMultilineTextareaSettings($configArray, $configKey, $textArea)
    {

        if (!empty($textArea)) {
            $lines = explode("\n", $textArea);
            $tmp   = array();

            foreach ($lines AS $line) {
                $tmp[] = trim($line);
            }

            $configArray['General'][$configKey] = $tmp;
        }

        return $configArray;
    }

    /**
     * dynamically set config for caching section
     *
     * @return array
     */
    protected function _setCachingConfig()
    {
        $caching = array(
            'Caching' => array(
                'caching'         => Mage::getStoreConfig('ts_phpids/caching/caching'),
                'expiration_time' => (int)Mage::getStoreConfig('ts_phpids/caching/expiration_time'),
                'path'            => Mage::getBaseDir('cache') . '/ts_phpids_filter.cache'
            )
        );

        return $caching;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        if ($this->_init instanceof IDS\Init) {
            return $this->_init->getConfig();
        }

        return array();
    }
}