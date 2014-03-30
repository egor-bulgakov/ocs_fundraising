<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Mobile fundraising event handler
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.plugin.ocs_fundraising.mobile.classes
 * @since 1.6.0
 */
class OCSFUNDRAISING_MCLASS_EventHandler
{
    /**
     * @var OCSFUNDRAISING_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return OCSFUNDRAISING_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() { }

    public function onFeedItemRenderDisableActions( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params["action"]["entityType"] != "ocsfundraising_project" )
        {
            return;
        }

        $data = $event->getData();

        $data["disabled"] = true;

        $event->setData($data);
    }

    public function init()
    {
        OCSFUNDRAISING_CLASS_EventHandler::getInstance()->genericInit();

        $em = OW::getEventManager();

        $em->bind('feed.on_item_render', array($this, 'onFeedItemRenderDisableActions'));
    }
}