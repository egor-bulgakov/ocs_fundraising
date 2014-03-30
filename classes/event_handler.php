<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Event handler
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.classes
 * @since 1.6.0
 */
class OCSFUNDRAISING_CLASS_EventHandler
{
    /**
     * Class instance
     *
     * @var OCSFUNDRAISING_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct() { }

    /**
     * Returns class instance
     *
     * @return OCSFUNDRAISING_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function addNewContentItem( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('ocsfundraising', 'add_goal') )
        {
            return;
        }

        $resultArray = array(
            BASE_CMP_AddNewContent::DATA_KEY_ICON_CLASS => 'ow_ic_files',
            BASE_CMP_AddNewContent::DATA_KEY_URL => OW::getRouter()->urlForRoute('ocsfundraising.add_goal'),
            BASE_CMP_AddNewContent::DATA_KEY_LABEL => OW::getLanguage()->text('ocsfundraising', 'crowdfunding')
        );

        $event->add($resultArray);
    }

    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'ocsfundraising' => array(
                    'label' => $language->text('ocsfundraising', 'auth_group_label'),
                    'actions' => array(
                        'add_goal' => $language->text('ocsfundraising', 'auth_action_label_add_goal'),
                        'add_comment' => $language->text('ocsfundraising', 'auth_action_label_add_comment'),
                    )
                )
            )
        );
    }

    public function feedOnProjectAdd( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['entityType'] != 'ocsfundraising_project' )
        {
            return;
        }

        $service = OCSFUNDRAISING_BOL_Service::getInstance();
        $project = $service->getGoalById($params['entityId']);

        if ( !$project )
        {
            return;
        }

        $content = array(
            "format" => "image_content",
            "vars" => array(
                "image" => $project['dto']->image ? $service->generateImageUrl($project['dto']->image, false) : null,
                "thumbnail" => $project['dto']->image ? $service->generateImageUrl($project['dto']->image) : null,
                "title" => UTIL_String::truncate(strip_tags($project['dto']->name), 100, '...'),
                "description" => UTIL_String::truncate(strip_tags($project['dto']->description), 150, '...'),
                "url" => array("routeName" => "ocsfundraising.project", "vars" => array('id' => $project['dto']->id)),
                "iconClass" => "ow_ic_folder"
            )
        );

        $data = array(
            'time' => (int) $project['dto']->startStamp,
            'ownerId' => $project['dto']->ownerId,
            'string' => array('key' => 'ocsfundraising+feed_add_project_label'),
            'content' => $content,
            'view' => array(
                'iconClass' => 'ow_ic_folder'
            )
        );

        $e->setData($data);
    }

    public function init()
    {
        $this->genericInit();

        $em = OW::getEventManager();

        $em->bind(BASE_CMP_AddNewContent::EVENT_NAME, array($this, 'addNewContentItem'));
        $em->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));
    }

    public function genericInit()
    {
        $em = OW::getEventManager();
        
        $em->bind('feed.on_entity_add', array($this, 'feedOnProjectAdd'));
    }
}