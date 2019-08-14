<?php
/**
 * Magento
 *
 * @category    Infoplus
 * @package     Infoplus_Connector
 */
class Infoplus_Connector_Model_Infoplus_Product extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('infoplus/infoplus_product');
    }

}
