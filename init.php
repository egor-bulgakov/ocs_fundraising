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

function ocsfundraising_elst_add_new_content_item( BASE_CLASS_EventCollector $event )
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
OW::getEventManager()->bind(BASE_CMP_AddNewContent::EVENT_NAME, 'ocsfundraising_elst_add_new_content_item');

function ocsfundraising_add_auth_labels( BASE_CLASS_EventCollector $event )
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
OW::getEventManager()->bind('admin.add_auth_labels', 'ocsfundraising_add_auth_labels');


function ocsfundraising_feed_on_project_add( OW_Event $e )
{
    $params = $e->getParams();

    if ( $params['entityType'] != 'ocsfundraising_project' )
    {
        return;
    }

    $service = OCSFUNDRAISING_BOL_Service::getInstance();
    $project = $service->getGoalById($params['entityId']);

    $url = OW::getRouter()->urlForRoute('ocsfundraising.project', array('id' => $project['dto']->id));
    $image = $project['dto']->image ? $service->generateImageUrl($project['dto']->image) : null;
    $title = UTIL_String::truncate(strip_tags($project['dto']->name), 100, '...');
    $content = UTIL_String::truncate(strip_tags($project['dto']->description), 150, '...');

    if ( $image )
    {
        $markup  = '<div class="clearfix ow_newsfeed_large_image"><div class="ow_newsfeed_item_picture">';
        $markup .= '<a href="' . $url . '"><img src="' . $image . '" /></a>';
        $markup .= '</div><div class="ow_newsfeed_item_content"><a href="' . $url . '">' . $title . '</a><div class="ow_remark">';
        $markup .= $content . '</div></div></div>';
    }
    else {
        $markup = '<div class="ow_newsfeed_item_content"><a href="' . $url . '">' . $title . '</a><div class="ow_remark">';
        $markup .= $content . '</div></div>';
    }

    $data = array(
        'time' => (int) $project->startStamp,
        'ownerId' => $project->ownerId,
        'string' => OW::getLanguage()->text('ocsfundraising', 'feed_add_project_label'),
        'content' => '<div class="clearfix">' . $markup . '</div>',
        'view' => array(
            'iconClass' => 'ow_ic_folder'
        )
    );

    $e->setData($data);
}
OW::getEventManager()->bind('feed.on_entity_add', 'ocsfundraising_feed_on_project_add');