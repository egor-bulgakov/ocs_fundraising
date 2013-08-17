<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Fundraising goal widget
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.components
 * @since 1.2.3
 */
class OCSFUNDRAISING_CMP_GoalWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $goalId = $params->customParamList['goal'];
        $service = OCSFUNDRAISING_BOL_Service::getInstance();
        
        if ( $goalId )
        {
        	$goal = $service->getGoalById($goalId);
        	if ( !$goal )
        	{
        		$this->assign('error', OW::getLanguage()->text('ocsfundraising', 'goal_not_found'));
        		return;
        	}
        	$this->assign('goal', $goal);
        }
        else 
        {
        	$this->assign('goal', null);
        	return;
        }
        
        $showTop = $params->customParamList['show_top'];
        if ( $showTop )
        {
        	$this->assign('top', $service->getDonationList($goalId, 'top', 1, 3));
        }
        $this->assign('showTop', $showTop);
        
        $showLatest = $params->customParamList['show_latest'];
        if ( $showLatest )
        {
        	$this->assign('latest', $service->getDonationList($goalId, 'latest', 1, 3));
        }
        $this->assign('showLatest', $showLatest);
        
        $this->assign('currency', BOL_BillingService::getInstance()->getActiveCurrency());
        
        $this->assign('donators', (int) $service->countGoalDonators($goalId));
        
        $js = UTIL_JsGenerator::newInstance()
            ->jQueryEvent('.btn-donate-goal-'.$goal['dto']->id, 'click', 'document.location.href = e.data.href', array('e'),
                array('href' => OW::getRouter()->urlForRoute('ocsfundraising.donate', array('goalId' => $goal['dto']->id))
            ));

        OW::getDocument()->addOnloadScript($js);
    }
    
    public static function getSettingList()
    {
        $settingList = array();

        $settingList['goal'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('ocsfundraising', 'widget_goal'),
            'optionList' => array('0' => OW::getLanguage()->text('ocsfundraising', 'no_goals')),
            'value' => null
        );
        
        $list = OCSFUNDRAISING_BOL_Service::getInstance()->getGoalsList();
        if ( $list )
        {
        	$optList = array();
        	foreach ( $list as $goal )
        	{
        		$optList[$goal->id] = $goal->name;
        	}
        	
        	$settingList['goal']['optionList'] = $optList;
        }
        
        $settingList['show_top'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => OW::getLanguage()->text('ocsfundraising', 'show_top_donations'),
            'value' => false
        );
        
        $settingList['show_latest'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => OW::getLanguage()->text('ocsfundraising', 'show_latest_donations'),
            'value' => false
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
        	self::SETTING_WRAP_IN_BOX => false,
        	self::SETTING_SHOW_TITLE => false,
        	self::SETTING_ICON => self::ICON_APP,
        	self::SETTING_TITLE => OW::getLanguage()->text('ocsfundraising', 'widget_title')
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}