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

$sql = "UPDATE `".OW_DB_PREFIX."ocsfundraising_goal`
    SET `ownerId` = 1 `ownerType` = 'admin' WHERE `ownerId` IS NULL;";

try {
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }