<?php
/**
 * Magento
 *
 * @category    Infoplus
 * @package     Infoplus_Connector
 */
class Infoplus_Connector_Model_Mysql4_Infoplus_Product extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {   
        $this->_init('infoplus/infoplus_product', 'id');
    }

}
