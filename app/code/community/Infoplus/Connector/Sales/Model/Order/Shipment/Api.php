<?php
/**
 * Magento
 *
 * @category    Infoplus
 * @package     Infoplus_Connector
 */
class Infoplus_Connector_Sales_Model_Order_Shipment_Api extends Mage_Sales_Model_Order_Shipment_Api
{

   /*********************************************
   ** sendEmailToCustomer                      **
   **                                          **
   ** Override of validate method to allow     **
   ** optional address validation by ML        **
   **                                          **
   ** @param  string $shipmentIncrementId      **
   ** @return boolean                          **
   *********************************************/
   public function sendEmailToCustomer($shipmentIncrementId)
   {
      $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentIncrementId);

      ###################################
      ## make sure the shipment exists ##
      ###################################
      if (!$shipment->getId())
      {
         $this->_fault('not_exists');
      }

      $shipment->sendEmail(true, '');

      return null;
   }

}
