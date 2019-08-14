<?php
/**
 * Magento
 *
 * @category    Infoplus
 * @package     Infoplus_Connector
 */
class Infoplus_Connector_Sales_Model_Order extends Mage_Sales_Model_Order
{

   /*********************************************
   ** cancel                                   **
   **                                          **
   ** Calls parent cancel method, and if ok    **
   ** dispatches a custom event which our      **
   ** Observer class can handle                **
   **                                          **
   ** @return $this                            **
   *********************************************/
   public function cancel()
   {
       ###################################
       ## call parent to attempt cancel ##
       ###################################
       parent::cancel();

       #################################################
       ## if cancel successful, dispatch custom event ##
       #################################################
       if($this->getState() === self::STATE_CANCELED)
       {
           Mage::dispatchEvent('sales_order_cancel', array('order'=>$this));
       }

       return $this;
   }

}
