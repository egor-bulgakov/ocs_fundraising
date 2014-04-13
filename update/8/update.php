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

$sql = "ALTER TABLE `".OW_DB_PREFIX."ocsfundraising_donation` ADD `anonymous` TINYINT( 1 ) NULL DEFAULT '0';";

try {
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'ocsfundraising');