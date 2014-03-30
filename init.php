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
    new OW_Route('ocsfundraising.admin_categories', '/admin/plugins/ocsfundraising/categories', 'OCSFUNDRAISING_CTRL_Admin', 'categories')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfundraising.admin_settings', '/admin/plugins/ocsfundraising/settings', 'OCSFUNDRAISING_CTRL_Admin', 'settings')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfundraising.admin_donations', '/admin/plugins/ocsfundraising/donations/:goalId', 'OCSFUNDRAISING_CTRL_Admin', 'donations')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfundraising.donate', '/fundraising/donate/:goalId', 'OCSFUNDRAISING_CTRL_Donate', 'index')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfundraising.add_goal', '/crowdfunding/add-project', 'OCSFUNDRAISING_CTRL_Project', 'add')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfundraising.list', '/crowdfunding/projects', 'OCSFUNDRAISING_CTRL_Project', 'projects')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfundraising.archive', '/crowdfunding/projects/archive', 'OCSFUNDRAISING_CTRL_Project', 'archive')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfundraising.popular', '/crowdfunding/projects/popular', 'OCSFUNDRAISING_CTRL_Project', 'popular')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfundraising.category', '/crowdfunding/category/:id', 'OCSFUNDRAISING_CTRL_Project', 'category')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfundraising.project', '/crowdfunding/project/:id', 'OCSFUNDRAISING_CTRL_Project', 'project')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfundraising.edit_project', '/crowdfunding/edit-project/:id', 'OCSFUNDRAISING_CTRL_Project', 'edit')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfundraising.action_reorder', 'admin/plugins/ocsfundraising/ajax/reorder', 'OCSFUNDRAISING_CTRL_Admin', 'ajaxReorder')
);

OCSFUNDRAISING_CLASS_EventHandler::getInstance()->init();