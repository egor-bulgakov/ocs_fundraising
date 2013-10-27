<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * /install.php
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising
 * @since 1.2.3
 */

$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."ocsfundraising_donation` (
`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
`userId` int( 11 ) DEFAULT NULL ,
`goalId` int( 11 ) NOT NULL ,
`username` varchar( 100 ) DEFAULT NULL ,
`amount` float( 9, 2 ) NOT NULL ,
`donationStamp` int( 11 ) NOT NULL DEFAULT '0',
PRIMARY KEY ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET = utf8;";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."ocsfundraising_goal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `amountMin` float(9,2) NOT NULL DEFAULT '1.00',
  `amountTarget` float(9,2) NOT NULL,
  `amountCurrent` float(9,2) NOT NULL DEFAULT '0.00',
  `startStamp` int(11) NOT NULL,
  `endStamp` int(11) DEFAULT NULL,
  `status` enum('active','complete') NOT NULL DEFAULT 'active',
  `ownerType` VARCHAR(50) NULL DEFAULT 'admin',
  `ownerId` INT NULL DEFAULT NULL,
  `image` VARCHAR( 50 ) NULL DEFAULT NULL,
  `categoryId` INT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."ocsfundraising_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sortOrder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

try {
    $product = new BOL_BillingProduct();
    $product->active = 1;
    $product->productKey = 'ocsfundraising_donation';
    $product->adapterClassName = 'OCSFUNDRAISING_CLASS_DonationProductAdapter';
    
    BOL_BillingService::getInstance()->saveProduct($product);
}
catch ( Exception $e ) { }

$authorization = OW::getAuthorization();
$groupName = 'ocsfundraising';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'add_goal');
$authorization->addAction($groupName, 'add_comment');

OW::getPluginManager()->addPluginSettingsRouteName('ocsfundraising', 'ocsfundraising.admin');

$path = OW::getPluginManager()->getPlugin('ocsfundraising')->getRootDir() . 'langs.zip';
BOL_LanguageService::getInstance()->importPrefixFromZip($path, 'ocsfundraising');