<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * User projects widget
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.components
 * @since 1.5.3
 */
class OCSFUNDRAISING_CMP_UserProjectsWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $userId = $params->additionalParamList['entityId'];
        $service = OCSFUNDRAISING_BOL_Service::getInstance();

        $projects = $service->getUserGoalsList($userId, 1, 2);
        $this->assign('projects', $projects);

        $this->assign('currency', BOL_BillingService::getInstance()->getActiveCurrency());

        $username = BOL_UserService::getInstance()->getUserName($userId);

        $this->setSettingValue(self::SETTING_TOOLBAR, array(array(
            'label' => OW::getLanguage()->text('base', 'view_all'),
            'href' => OW::getRouter()->urlForRoute('ocsfundraising.user_list', array('username' => $username))
        )));
    }

    public static function getStandardSettingValueList()
    {
        return array(
        	self::SETTING_WRAP_IN_BOX => true,
        	self::SETTING_SHOW_TITLE => true,
        	self::SETTING_ICON => self::ICON_FOLDER,
        	self::SETTING_TITLE => OW::getLanguage()->text('ocsfundraising', 'my_projects')
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}