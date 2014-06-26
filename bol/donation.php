<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Data Transfer Object for `ocsfundraising_donation` table.
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.bol
 * @since 1.2.3
 */
class OCSFUNDRAISING_BOL_Donation extends OW_Entity
{
    /**
     * @var int
     */
    public $userId;
    /**
     * @var int
     */
    public $goalId;
    /**
     * @var string
     */
    public $username;
    /**
     * @var float
     */
    public $amount;
    /**
     * @var int
     */
    public $donationStamp;
    /**
     * @var int
     */
    public $anonymous = 0;
    /**
     * @var string
     */
    public $privacy = 'name_and_amount';
}