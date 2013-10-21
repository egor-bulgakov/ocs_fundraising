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
 * @since 1.5.3
 */

$sql = "ALTER TABLE `".OW_DB_PREFIX."ocsfundraising_goal`
    ADD  `ownerType` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'admin';";

try {
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }

$sql = "ALTER TABLE `".OW_DB_PREFIX."ocsfundraising_goal` ADD `ownerId` INT NULL DEFAULT NULL;";

try {
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }

$sql = "ALTER TABLE `".OW_DB_PREFIX."ocsfundraising_goal` ADD `image` VARCHAR( 50 ) NULL DEFAULT NULL;";

try {
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }

$sql = "ALTER TABLE `".OW_DB_PREFIX."ocsfundraising_goal` ADD `categoryId` INT NULL DEFAULT NULL;";

try {
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }

$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."ocsinterests_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sortOrder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

try {
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }

// add new auth action
try {
    $authorization = OW::getAuthorization();
    $authorization->addAction('ocsfundraising', 'add_comment');
}
catch ( Exception $e ) { }

// add widget
try {
    $widget = BOL_ComponentAdminService::getInstance()->addWidget('OCSFUNDRAISING_CMP_UserProjectsWidget');
    $placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
    BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);
}
catch ( Exception $e ) { }

// add new menu item
try {
    OW::getNavigation()->addMenuItem(
        OW_Navigation::MAIN, 'ocsfundraising.list', 'ocsfundraising', 'projects', OW_Navigation::VISIBLE_FOR_ALL
    );
}
catch ( Exception $e ) { }

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'ocsfundraising');