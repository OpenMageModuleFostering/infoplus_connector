<?php
/**
 * Magento
 *
 * @category    Infoplus
 * @package     Infoplus_Connector
 */
class Infoplus_Connector_Block_Adminhtml_Catalog_Product_Edit_Tab_Infoplusitem extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

   /*********************************************
   ** _construct                               **
   **                                          **
   ** constructor, set the template            **
   **                                          **
   ** @return void                             **
   *********************************************/
   protected function _construct()
   {
      parent::_construct();
      $this->setTemplate('infoplus_connector/catalog/product/edit/tab/infoplus_item.phtml');
   }



   /*********************************************
   ** getTabLabel                              **
   **                                          **
   ** specifies tab label for template         **
   **                                          **
   ** @return string                           **
   *********************************************/
   public function getTabLabel()
   {
      return $this->__('Infoplus Product Info');
   }



   /*********************************************
   ** getTabTitle                              **
   **                                          **
   ** specifies tab title for template         **
   **                                          **
   ** @return string                           **
   *********************************************/
   public function getTabTitle()
   {
      return $this->__('Infoplus Product Info');
   }



   /*********************************************
   ** canShowTab                               **
   **                                          **
   ** Determinies if tab can be shown          **
   **                                          **
   ** @return boolean                          **
   *********************************************/
   public function canShowTab()
   {
      return true;
   }



   /*********************************************
   ** isHidden                                 **
   **                                          **
   ** Determinies if tab is hidden             **
   **                                          **
   ** @return boolean                          **
   *********************************************/
   public function isHidden()
   {
      return false;
   }


   /*********************************************
   ** getProduct                               **
   **                                          **
   ** Fetches current product from registry    **
   **                                          **
   ** @return boolean                          **
   *********************************************/
   public function getProduct()
   {
      return Mage::registry('product');
   }

}
