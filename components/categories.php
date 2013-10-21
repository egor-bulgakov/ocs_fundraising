<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Categories component
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.components
 * @since 1.2.3
 */
class OCSFUNDRAISING_CMP_Categories extends OW_Component
{
    public function __construct( )
    {
        parent::__construct();

        $service = OCSFUNDRAISING_BOL_Service::getInstance();

        $categories = $service->getCategoryList();
        $this->assign('categories', $categories);

        $counters = $service->getCategoriesProjectCount();
        $this->assign('counters', $counters);
    }
}