<?php
/**
 * Magento
 *
 * @category    Infoplus
 * @package     Infoplus_Connector
 */
class Infoplus_Connector_Model_Observer
{

   public function on_eav_load_before($observer)
   {
      Mage::log('Inside Infoplus_Connector_Model_Observer:on_eav_load_before($event)');

      $collection = $observer->getCollection();
      if (!isset($collection)) return;

      if (is_a($collection, 'Mage_Catalog_Model_Resource_Product_Collection'))
      {
         $infoplusTable = Mage::getSingleton('core/resource')->getTableName('infoplus_product');
         $collection->getSelect()->joinLeft(array("ip" => $infoplusTable), "ip.magento_sku=e.sku", array("wms_sku as fulfilled_by_infoplus"));
      }
   }



   public function admin_html_block_html_before($observer)
   {
      Mage::log('Inside Infoplusor_Connector_Model_Observer:admin_html_block_html_before($event)');

      $block = $observer->getEvent()->getBlock();
      if (!isset($block)) return;

      switch ($block->getType())
      {
         case 'adminhtml/catalog_product_grid':
            /* @var $block Mage_Adminhtml_Block_Catalog_Product_Grid */
            $block->addColumnAfter('fulfilled_by_infoplus', array(
               'header' => Mage::helper('catalog')->__('Fulfilled By Infoplus'),
               'type' => 'text',
               'width' => '20px',
               'filter' => false,
               'index'  => 'fulfilled_by_infoplus',
            ),"status")->sortColumnsByOrder();
            break;
      }
   }



   public function admin_html_widget_container_html_before($observer)
   {
      Mage::log('Inside Infoplus_Connector_Model_Observer:admin_html_widget_container_html_before($event)');

      $block = $observer->getEvent()->getBlock();

      if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View)
      {
           $orderId = Mage::app()->getRequest()->getParam('order_id');
           $order = Mage::getModel('sales/order')->load($orderId);
           $incrementId = $order->getIncrementId();

           $message = Mage::helper('sales')->__('Are you sure you want to resend order ' . $incrementId . ' to Infoplus?');

           $resendURL = Mage::getStoreConfig('infoplus_options/infoplus_options_general/infoplus_url');
           $resendURL .= "/infoplus-wms/api/magento/repost";
           $resendURL .= "?order_id=" . $incrementId;
           $resendURL .= "&store_url=" . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

           $viewURL = Mage::getStoreConfig('infoplus_options/infoplus_options_general/infoplus_url');
           $viewURL .= "/infoplus-wms/order/req/query/";
           $viewURL .= "{%22criteriaFields%22:[{%22fieldName%22:%22customerOrderNo%22,%22values%22:[%22" . $incrementId . "%22],%22operator%22:%22EQUALS%22},{%22fieldName%22:%22reqLoadProgramId%22,%22operator%22:%22IN%22,%22values%22:[{%22id%22:4,%22text%22:%22magento (4)%22,%22idValue%22:4}],%22isAdvancedPVS%22:false}],%22orderByFields%22:[{%22fieldName%22:%22reqNo%22,%22direction%22:%22DESC%22}],%22startAt%22:null,%22limit%22:null}";

           $block->addButton('resend_to_infoplus', array(
               'label'     => Mage::helper('sales')->__('Resend To Infoplus'),
               'onclick'   => "if(confirm('{$message}')){window.open('{$resendURL}','_blank','width=350,height=250');}",
               'class'     => 'go'
           ));

           $block->addButton('view_in_infoplus', array(
               'label'     => Mage::helper('sales')->__('View In Infoplus'),
               'onclick'   => "window.open('". ${viewURL} . "')",
               'class'     => 'go'
           ));
      }
   }


   /*********************************************
   ** save_product_tab_data                    **
   **                                          **
   ** called when product is saved             **
   **                                          **
   ** @param  class $observer                  **
   ** @return void                             **
   *********************************************/
   public function save_product_tab_data($observer)
   {
      Mage::log('Inside Infoplus_Connector_Model_Observer:save_product_tab_data()...');

      #################################
      ## get the product being saved ##
      #################################
      $product = $observer->getEvent()->getProduct();

      ####################################
      ## get the field values passed in ##
      ####################################
      $request = Mage::app()->getRequest();

      #######################################################################################
      ## if the save is not coming from a form (ie the api), then dont worry about wms_sku ##
      #######################################################################################
      $formKey =  $request->getPost('form_key');
      if(! $formKey)
      {
         Mage::log('No form key present on POST, not considering infoplus_product data...');
         return;
      }

      $wmsSku =  $request->getPost('infoplus_wms_sku');
      if(! $wmsSku)
      {
         $wmsSku = "off";
      }

      ####################################################
      ## look for existing infoplus_record for this sku ##
      ####################################################
      $collection = Mage::getModel('infoplus/infoplus_product')->getCollection()->addFieldToFilter('magento_sku', $product->getSku());

      ######################################
      ## if any records found, do updates ##
      ######################################
      if(count($collection) > 0)
      {
         foreach($collection as $infoplusProduct)
         {
            #############################
            ## load product from model ##
            #############################
            $_product = Mage::getModel('infoplus/infoplus_product')->load($infoplusProduct->getId());
            $_product->setWmsSku     ($wmsSku);
            $_product->setUpdateTime (now());
            $_product->save();
         }
      }
      else
      {
         ###############################################
         ## if no records found, and we were provided ##
         ## a value to store, insert a record         ##
         ###############################################
         if(isset($wmsSku) && $wmsSku != "")
         {
            $infoplusProduct = Mage::getModel('infoplus/infoplus_product');
            $infoplusProduct->setMagentoSku  ($product->getSku());
            $infoplusProduct->setWmsSku      ($wmsSku);
            $infoplusProduct->setCreatedTime (now());
            $infoplusProduct->setUpdateTime  (now());
            $infoplusProduct->save();
         }
      }

      Mage::app()->getCacheInstance()->cleanType('block_html');
   }



   /*********************************************
   ** post_new_order_queue_message             **
   **                                          **
   ** called when new order event occurs       **
   **                                          **
   ** @param  class $observer                  **
   ** @return void                             **
   *********************************************/
   public function post_new_order_queue_message($observer)
   {
      Mage::log('Inside Infoplus_Connector_Model_Observer:post_new_order_queue_message()...');
      $orderId = $observer->getEvent()->getOrder()->getIncrementId();
      $this->post_event("order-created", $orderId);
   }



   /*********************************************
   ** post_cancel_order_queue_message          **
   **                                          **
   ** called when order cancelled event occurs **
   **                                          **
   ** @param  class $observer                  **
   ** @return void                             **
   *********************************************/
   public function post_cancel_order_queue_message($observer)
   {
      Mage::log('Inside Infoplus_Connector_Model_Observer:post_cancel_order_queue_message()...');
      $orderId = $observer->getEvent()->getOrder()->getIncrementId();
      $this->post_event("order-cancelled", $orderId);
   }



   /*********************************************
   ** post_queue_message                       **
   **                                          **
   ** Posts message to given queue name        **
   **                                          **
   ** @param  string $event                    **
   ** @param  string $orderId                  **
   ** @return void                             **
   *********************************************/
   private function post_event($event, $orderId)
   {
      Mage::log('Inside Infoplus_Connector_Model_Observer:post_event("' . $event . '","' . $orderId . '")...');

      #################################
      ## get the URL from the config ##
      #################################
      $url = Mage::getStoreConfig('infoplus_options/infoplus_options_general/infoplus_url');
      if(! $url)
      {
         Mage::log('Store Configuration Infoplus URL was not set, not posting webhook event...');
         return;
      }
    	$url .= "/infoplus-wms/api/magento/orders";

     	$data = array("event" => $event, "increment_id" => $orderId, "store_url" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB));
      $jsonData = Mage::helper('core')->jsonEncode($data);

      Mage::log('Attempting to post event=['.$event.'] for order=['.$orderId.'] to url=['.$url.'].');
      $curlHandle = curl_init();
      curl_setopt($curlHandle, CURLOPT_URL, $url);
      curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $jsonData);
      curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
      ## curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
      ## curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array(                                                                          
         'Content-Type: application/json',                                                                                
         'Content-Length: ' . strlen($jsonData))                                                                       
      );

      $output = curl_exec($curlHandle);
      Mage::log('Post complete!');

      #################################
      ## check if any error occurred ##
      #################################
      if(curl_errno($curlHandle))
      {
         ##############################################
         ## send message with error and close handle ##
         ##############################################
         $this->send_error_email('Curl error: ['.curl_error($curlHandle).'] when posting message=['.$message.'] to url=['.$url.'], received output=['.$output.']');
         curl_close($curlHandle);
         return;
      }
      else
      {
         ####################################
         ## retrieve info and close handle ##
         ####################################
         $curlInfo = curl_getinfo($curlHandle);
         curl_close($curlHandle);

         #######################################
         ## get the http code for the request ##
         #######################################
         $httpCode = $curlInfo['http_code'];

         ###########################################################
         ## if none returned send email, or if not 200 send email ##
         ###########################################################
         if(empty($httpCode))
         {
            $this->send_error_email('No HTTP code was returned when posting message=['.$message.'] to url=['.$url.'], received output=['.$output.']');
            return;
         }
         else if($httpCode != "200")
         {
            $this->send_error_email('Unexpected HTTP code=['.$httpCode.'] was returned when posting message=['.$message.'] to url=['.$url.'], received output=['.$output.']');
            return;
         }

         #############################################################
         ## ensure we received the expected response text from call ##
         #############################################################
         if(strpos($output, 'SUCCESS') === false)
         {
            $this->send_error_email('Did not receive expected output when posting message=['.$message.'] to url=['.$url.'], received output=['.$output.']');
            return;
         } 
      }
   }



   /*********************************************
   ** send_error_email                         **
   **                                          **
   ** Sends an error message                   **
   **                                          **
   ** @param  string $errorMessage             **
   ** @return void                             **
   *********************************************/
   private function send_error_email($errorMessage)
   {
      Mage::log('ERROR: ' . $errorMessage);

/*
      $errorEmailFromAddress = Mage::getStoreConfig('infoplus/connector/error_email_from_address');
      $errorEmailToAddress   = Mage::getStoreConfig('infoplus/connector/error_email_to_address');

      if($errorEmailFromAddress && $errorEmailToAddress)
      {
         $mail = new Zend_Mail();
         $mail->setBodyText ($errorMessage);
         $mail->setFrom     ($errorEmailFromAddress);
         $mail->addTo       ($errorEmailToAddress);
         $mail->setSubject  ('Magento Module Exception : Infoplus_Connector_Model_Observer');
         $mail->send();
      }
*/
   }

}
