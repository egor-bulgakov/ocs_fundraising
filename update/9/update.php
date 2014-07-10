<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * /update.php
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.update
 * @since 1.6.0
 */

$sql = "ALTER TABLE `".OW_DB_PREFIX."ocsfundraising_goal` ADD `endOnFulfill` TINYINT(1) NOT NULL DEFAULT '0';";

try {
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }


$sql = "ALTER TABLE `".OW_DB_PREFIX."ocsfundraising_goal` ADD `paypal` VARCHAR( 50 ) NULL DEFAULT NULL;";

try {
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }


$sql = "ALTER TABLE `".OW_DB_PREFIX."ocsfundraising_donation` ADD `privacy` VARCHAR( 20 ) NULL DEFAULT 'name_and_amount';";

try {
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }


$sql = "SELECT * FROM `".OW_DB_PREFIX."ocsfundraising_donation`";

$donations = Updater::getDbo()->queryForList($sql);

if ( $donations )
{
    foreach ( $donations as $donation )
    {
        if ( $donation['anonymous'] )
        {
            $sql = "UPDATE `".OW_DB_PREFIX."ocsfundraising_donation` SET `privacy` = 'anonymous' WHERE `id` = " . $donation['id'];
            Updater::getDbo()->query($sql);
        }
    }
}

$sql = "ALTER TABLE `".OW_DB_PREFIX."ocsfundraising_donation` DROP `anonymous`;";

try {
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }


$config = Updater::getConfigService();

if ( !$config->configExists('ocsfundraising', 'allow_paypal') )
{
    $config->addConfig('ocsfundraising', 'allow_paypal', 0, 'Allow collecting funds via PayPal');
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'ocsfundraising');