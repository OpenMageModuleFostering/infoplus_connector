<?php
/**
 * Magento
 *
 * @category    Infoplus
 * @package     Infoplus_Connector
 */
class Infoplus_Connector_Catalog_Model_Product_Api extends Mage_Catalog_Model_Product_Api
{

   /***************************************
   ** Retrieve product info              **
   **                                    **
   ** @param int|string $productId       **
   ** @param string|int $store           **
   ** @param stdClass $attributes        **
   ** @return array                      **
   ***************************************/
   public function info($productId, $store = null, $attributes = null, $identifierType = null)
   {
      ###################################
      ## get array from call to parent ##
      ###################################
      $productInfo = parent::info($productId, $store, $attributes, $identifierType);

      ####################################################
      ## look for existing infoplus_record for this sku ##
      ####################################################
      $collection = Mage::getModel('infoplus/infoplus_product')->getCollection()->addFieldToFilter('magento_sku', $productInfo["sku"]);
      if(count($collection) > 0)
      {
         ######################################
         ## get infoplus product information ##
         ######################################
         $infoplusProduct = $collection->getFirstItem();
         $wmsSku          = $infoplusProduct->getWmsSku();

         ############################################################
         ## if value is set for sku add to the product information ##
         ############################################################
         if(isset($wmsSku) && $wmsSku != "")
         {
            $productInfo["fulfilled_by_infoplus"] = $infoplusProduct->getWmsSku();
         }
      }

      return $productInfo;
   }



   /**********************************************
   ** Retrieve product info for given wms sku   **
   **                                           **
   ** @param string $wmsSku                     **
   ** @param stdClass $attributes               **
   ** @return array                             **
   **********************************************/
   public function getProductInfoByWmsSku($wmsSku)
   {
      $result = array();

      ###############################################
      ## look for infoplus_record for this wms sku ##
      ###############################################
      $infoplusProductCollection = Mage::getModel('infoplus/infoplus_product')->getCollection()->addFieldToFilter('wms_sku', $wmsSku);

      #####################################################################
      ## if any found, iterate over here, adding product data to results ##
      #####################################################################
      foreach($infoplusProductCollection as $infoplusProduct)
      {
         #############################
         ## load product from model ##
         #############################
         $productCollection = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('sku', $infoplusProduct->getMagentoSku());

         ##############################
         ## add to our results array ##
         ##############################
         foreach($productCollection as $product)
         {
            $result[] = array
            (
               'product_id' => $product->getId(),
               'sku'        => $product->getSku(),
               'name'       => $product->getName(),
               'set'        => $product->getAttributeSetId(),
               'type'       => $product->getTypeId(),
               'category_ids' => $product->getCategoryIds(),
               'website_ids'  => $product->getWebsiteIds()
            );
         }
      }

      return $result;
   }

}
