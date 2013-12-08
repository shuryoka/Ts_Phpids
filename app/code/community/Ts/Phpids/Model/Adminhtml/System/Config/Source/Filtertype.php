<?php
/**
 * Provider for backend options
 *
 * @package     Ts
 * @category    Ts_Phpids
 * @author      Christoph Frenes <c.frenes@triplesense.de>
 */
class Ts_Phpids_Model_Adminhtml_System_Config_Source_Filtertype
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Ts_Phpids_Model_Phpids::GENERAL_FILTER_TYPE_XML,
                'label' => Mage::helper('ts_phpids')->__('XML')
            ),
            array(
                'value' => Ts_Phpids_Model_Phpids::GENERAL_FILTER_TYPE_JSON,
                'label' => Mage::helper('ts_phpids')->__('JSON')
            )
        );
    }
} 