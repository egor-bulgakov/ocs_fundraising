<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Fundraising cron job.
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising
 * @since 1.2.3
 */
class OCSFUNDRAISING_Cron extends OW_Cron
{    
    public function __construct()
    {
        parent::__construct();

        $this->addJob('goalsCheckProcess', 60);
    }

    public function run() { }

    public function goalsCheckProcess()
    {
        OCSFUNDRAISING_BOL_Service::getInstance()->checkCompleteGoals();
    }
}