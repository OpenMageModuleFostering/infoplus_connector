<?php
   ###################################
   ## file mysql4-install-2.0.0.php ##
   ###################################
 
   $installer = $this;
   $installer->startSetup();

   $installer->run
   ("
      DROP TABLE IF EXISTS {$this->getTable('infoplus_product')};
      CREATE TABLE {$this->getTable('infoplus_product')}
      (
         `id`           int(11) unsigned NOT NULL auto_increment,
         `magento_sku`  varchar(64) NOT NULL default '',
         `wms_sku`      varchar(20) NOT NULL default '',
         `created_time` datetime NULL,
         `update_time`  datetime NULL,
         PRIMARY KEY    (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
   ");

   $installer->endSetup();

?>
