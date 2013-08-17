<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * /init.php
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising
 * @since 1.2.3
 */

OW::getRouter()->addRoute(
    new OW_Route('ocsfundraising.admin', '/admin/plugins/ocsfundraising', 'OCSFUNDRAISING_CTRL_Admin', 'index')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfundraising.admin_donations', '/admin/plugins/ocsfundraising/donations/:goalId', 'OCSFUNDRAISING_CTRL_Admin', 'donations')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfundraising.donate', '/fundraising/donate/:goalId', 'OCSFUNDRAISING_CTRL_Donate', 'index')
);