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
 * @since 1.3
 */

$staticDir = OW_DIR_STATIC_PLUGIN . 'ocs_fundraising' . DS;
$staticImgDir = $staticDir  . 'img' . DS;

if ( !file_exists($staticDir) )
{
    @mkdir($staticDir);
    @chmod($staticDir, 0777);
}

if ( !file_exists($staticImgDir) )
{
    @mkdir($staticImgDir);
    @chmod($staticImgDir, 0777);
}

@copy(OW_DIR_PLUGIN . 'ocs_fundraising' . DS . 'static' . DS . 'img' . DS . 'oxwallcandystore-logo.jpg', $staticImgDir . 'oxwallcandystore-logo.jpg');