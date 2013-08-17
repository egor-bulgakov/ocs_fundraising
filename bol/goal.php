<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Data Transfer Object for `ocsfundraising_goal` table.
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.bol
 * @since 1.2.3
 */
class OCSFUNDRAISING_BOL_Goal extends OW_Entity
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $description;
    /**
     * @var float
     */
    public $amountMin;
    /**
     * @var float
     */
    public $amountTarget;
    /**
     * @var float;
     */
    public $amountCurrent;
    /**
     * @var int
     */
    public $startStamp;
    /**
     * @var int
     */
    public $endStamp = null;
    /**
     * @var string
     */
    public $status;
}