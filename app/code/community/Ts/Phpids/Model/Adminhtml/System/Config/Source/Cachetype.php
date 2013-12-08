<?php
/**
 * Provider for backend options
 *
 * @package     Ts
 * @category    Ts_Phpids
 * @author      Christoph Frenes <c.frenes@triplesense.de>
 */
class Ts_Phpids_Model_Adminhtml_System_Config_Source_Cachetype
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
                'value' => strtolower(Mage::helper('ts_phpids')->__('None')),
                'label' => Mage::helper('ts_phpids')->__('None')
            ),
            array(
                'value' => strtolower(Mage::helper('ts_phpids')->__('File')),
                'label' => Mage::helper('ts_phpids')->__('File')
            )
        );
    }
} 