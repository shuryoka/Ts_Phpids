<?php
/**
 * Provider for backend options
 *
 * @package     Ts
 * @category    Ts_Phpids
 * @author      Christoph Frenes <c.frenes@triplesense.de>
 */
class Ts_Phpids_Model_Adminhtml_System_Config_Source_Requesttype
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
                'value' => strtoupper(Mage::helper('ts_phpids')->__('Request')),
                'label' => Mage::helper('ts_phpids')->__('Request')
            ),
            array(
                'value' => strtoupper(Mage::helper('ts_phpids')->__('Post')),
                'label' => Mage::helper('ts_phpids')->__('Post')
            ),
            array(
                'value' => strtoupper(Mage::helper('ts_phpids')->__('Get')),
                'label' => Mage::helper('ts_phpids')->__('Get')
            ),
            array(
                'value' => strtoupper(Mage::helper('ts_phpids')->__('Cookie')),
                'label' => Mage::helper('ts_phpids')->__('Cookie')
            )
        );
    }
} 