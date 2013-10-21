<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * /deactivate.php
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising
 * @since 1.2.3
 */

BOL_BillingService::getInstance()->deactivateProduct('ocsfundraising_donation');

BOL_ComponentAdminService::getInstance()->deleteWidget('OCSFUNDRAISING_CMP_GoalWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('OCSFUNDRAISING_CMP_UserProjectsWidget');