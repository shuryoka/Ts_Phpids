<?php
/**
 * Default helper
 *
 * @package     Ts
 * @category    Ts_Phpids
 * @author      Christoph Frenes <c.frenes@triplesense.de>
 */
class Ts_Phpids_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * get path for the Ts_Phpids module
     *
     * @return string
     */
    public function getModuleDir()
    {
        return dirname(Mage::getModuleDir('etc', 'Ts_Phpids')) . DS;
    }
} 