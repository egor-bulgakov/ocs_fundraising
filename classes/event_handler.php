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
        $resultArray = array(
            BASE_CMP_AddNewContent::DATA_KEY_ICON_CLASS => 'ow_ic_files',
            BASE_CMP_AddNewContent::DATA_KEY_URL => OW::getRouter()->urlForRoute('ocsfundraising.add_goal'),
            BASE_CMP_AddNewContent::DATA_KEY_LABEL => OW::getLanguage()->text('ocsfundraising', 'crowdfunding')
        );

        if ( !OW::getUser()->isAuthorized('ocsfundraising', 'add_goal') )
        {
            try
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('ocsfundraising', 'add_goal');

                if ( $status['status'] != BOL_AuthorizationService::STATUS_PROMOTED )
                {
                    return;
                }

                $id = uniqid('add-goal-');
                $resultArray[BASE_CMP_AddNewContent::DATA_KEY_ID] = $id;

                $script = '$("#'.$id.'").click(function(){
                    OW.authorizationLimitedFloatbox('.json_encode($status['msg']).');
                    return false;
                });';
                OW::getDocument()->addOnloadScript($script);
            }
            catch ( Exception $e ) { }
        }

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
                "image" => $project['dto']->image ? $service->generateImageUrl($project['dto']->image, false) : $service->generateDefaultImageUrl(),
                "thumbnail" => $project['dto']->image ? $service->generateImageUrl($project['dto']->image) : $service->generateDefaultImageUrl(),
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

    public function addCrowdfundingSearchResultGroup( BASE_CLASS_EventCollector $ec )
    {
        $group = array(
            'pluginKey' => 'ocsfundraising',
            'key' => 'ocsfundraising',
            'priority' => 100,
            'label' => OW::getLanguage()->text('ocsfundraising', 'projects'),
            'url' => OW::getRouter()->urlForRoute('ocsfundraising.list')
        );

        $ec->add($group);
    }

    public function searchCrowdfundingGroup( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'ocsfundraising' && OW::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];
            $offset = (int) $params['offset'];
            $limit = (int) $params['limit'];

            $projectDao = OCSFUNDRAISING_BOL_GoalDao::getInstance();
            $sql =
                "SELECT * FROM `" . $projectDao->getTableName() . "`
                WHERE `status` = 'active'
                    AND (`name` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)
                ORDER BY `startStamp` DESC LIMIT :offset, :limit";

            $found = OW::getDbo()->queryForObjectList(
                $sql,
                $projectDao->getDtoClassName(),
                array('query' => '%'.$query.'%', 'offset' => $offset, 'limit' => $limit)
            );

            $router = OW::getRouter();
            $service = OCSFUNDRAISING_BOL_Service::getInstance();
            $list = array();
            if ( $found )
            {
                foreach ( $found as $item )
                {
                    $data = array(
                        'id' => $key . '_' . $item->id,
                        'url' => $router->urlForRoute('ocsfundraising.project', array('id' => $item->id))
                    );
                    $image = $item->image ? $service->generateImageUrl($item->image, true) : $service->generateDefaultImageUrl();
                    $data['avatar'] = array('src' => $image, 'url' => $data['url']);
                    $data['text'] = $item->name;
                    $data['info'] = $item->description;

                    $list[$data['id']] = $data;
                }
            }

            $result[$key] = $list;

            $ec->add($result);
        }
    }

    public function countCrowdfundingGroupResult( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'ocsfundraising' && OW::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];

            $projectDao = OCSFUNDRAISING_BOL_GoalDao::getInstance();
            $sql =
                "SELECT COUNT(*) FROM `" . $projectDao->getTableName() . "`
                WHERE `status` = 'active'
                    AND (`name` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)";

            $count = OW::getDbo()->queryForColumn($sql, array('query' => '%'.$query.'%'));

            $result[$key] = $count;

            $ec->add($result);
        }
    }

    public function socialSharingGetCrowdfundingInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $data['display'] = false;

        if ( empty($params['entityId']) )
        {
            return;
        }

        if ( $params['entityType'] == 'ocsfundraising' )
        {
            $project = OCSFUNDRAISING_BOL_Service::getInstance()->getGoalById($params['entityId']);

            if ( !empty($project) )
            {
                $data['display'] = true;
            }

            $event->setData($data);
        }
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
        $em->bind('ocsqsearch.collect_group', array($this, 'addCrowdfundingSearchResultGroup'));
        $em->bind('ocsqsearch.search_in_groups', array($this, 'searchCrowdfundingGroup'));
        $em->bind('ocsqsearch.count_search_result', array($this, 'countCrowdfundingGroupResult'));
        $em->bind('socialsharing.get_entity_info', array($this, 'socialSharingGetCrowdfundingInfo'));
    }
}