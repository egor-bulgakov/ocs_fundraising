<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * /activate.php
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising
 * @since 1.2.3
 */

BOL_BillingService::getInstance()->activateProduct('ocsfundraising_donation');

$widget = BOL_ComponentAdminService::getInstance()->addWidget('OCSFUNDRAISING_CMP_GoalWidget', true);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);

$widget = BOL_ComponentAdminService::getInstance()->addWidget('OCSFUNDRAISING_CMP_UserProjectsWidget');
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'ocsfundraising.list', 'ocsfundraising', 'projects', OW_Navigation::VISIBLE_FOR_ALL);