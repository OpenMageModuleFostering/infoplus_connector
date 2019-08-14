<?php
/**
 * Magento
 *
 * @category    Infoplus
 * @package     Infoplus_Connector
 */
class Infoplus_Connector_Sales_Model_Order_Invoice_Api extends Mage_Sales_Model_Order_Invoice_Api
{

   /*********************************************
   ** create                                   **
   **                                          **
   ** creates a new invoice for an order       **
   **                                          **
   ** @param string $orderIncrementId          **
   ** @param array $itemsQty                   **
   ** @param string $comment                   **
   ** @param booleam $email                    **
   ** @param boolean $includeComment           **
   ** @param boolean $capturedOffline          **
   ** @return string                           **
   *********************************************/
   public function create($orderIncrementId, $itemsQty, $comment = null, $email = false, $includeComment = false, $capturedOffline = false)
   {
      $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

      ################################
      ## make sure the order exists ##
      ################################
      if (!$order->getId())
      {
         $this->_fault('order_not_exists');
      }

      #######################################
      ## ensure the invoice can be created ##
      #######################################
      if (!$order->canInvoice())
      {
         $this->_fault('data_invalid', Mage::helper('sales')->__('Can not do invoice for order.'));
      }

      $invoice = $order->prepareInvoice($itemsQty);

      if($capturedOffline)
      {
         $invoice->setRequestedCaptureCase("offline");
      }

      $invoice->register();

      if ($comment !== null)
      {
         $invoice->addComment($comment, $email);
      }

      if ($email)
      {
         $invoice->setEmailSent(true);
      }

      $invoice->getOrder()->setIsInProcess(true);

      try
      {
         $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();

            $invoice->sendEmail($email, ($includeComment ? $comment : ''));
      }
      catch (Mage_Core_Exception $e)
      {
         $this->_fault('data_invalid', $e->getMessage());
      }

      return $invoice->getIncrementId();
    }

}
