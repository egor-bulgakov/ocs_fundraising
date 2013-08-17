<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Fundraising plugin administration action controller
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.controllers
 * @since 1.2.3
 */
class OCSFUNDRAISING_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * Default action
     */
    public function index()
    {
    	$lang = OW::getLanguage();
        $service = OCSFUNDRAISING_BOL_Service::getInstance();
        
    	if ( !empty($_GET['delete-goal']) )
    	{
    		$service->deleteGoal($_GET['delete-goal']);
    		OW::getFeedback()->info($lang->text('ocsfundraising', 'goal_deleted'));
    		$this->redirectToAction('index');
    	}
    	
        $form = new FormAddGoal();
        $this->addForm($form);
        
        $list = $service->getGoalsList();
        $donations = array();
        
        foreach ( $list as &$g )
        {
        	$g->endStamp = $g->endStamp ? UTIL_DateTime::formatDate($g->endStamp) : '-';
        	$g->amountTarget = floatval($g->amountTarget);
        	$g->amountCurrent = floatval($g->amountCurrent);
        	$donations[$g->id] = $service->countGoalDonations($g->id);
        }
        $this->assign('list', $list);
        $this->assign('donations', $donations);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
        	$values = $form->getValues();
        	
        	$goal = new OCSFUNDRAISING_BOL_Goal();
        	$goal->name = trim($values['name']);
        	$goal->description = !empty($values['description']) ? $values['description'] : null;
        	$goal->amountMin = floatval($values['min']);
        	$goal->amountTarget = floatval($values['target']);
        	$goal->amountCurrent = 0.0;
        	$goal->startStamp = time();
        	$date = explode('/', $values['end']);
        	if ( !empty($date[1]) && !empty($date[2]) && !empty($date[0]) )
        	{
        	   $goal->endStamp = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
        	}
        	$goal->status = 'active';
        	
        	$service->addGoal($goal);
        	OW::getFeedback()->info($lang->text('ocsfundraising', 'goal_added'));
        	$this->redirect();
        }
        
        $this->setPageHeading($lang->text('ocsfundraising', 'page_heading_admin'));
        $this->setPageHeadingIconClass('ow_ic_app');
        
        $js = 
'$("a.ocs_goal_edit").click(function(){
    var goalId = $(this).attr("rel");
    var fb = OW.ajaxFloatBox(
        "OCSFUNDRAISING_CMP_GoalEdit", 
        [goalId], 
        {width: 550, title: '.json_encode($lang->text('admin', 'btn_label_edit')).'}
    );
});

$("a.ocs_goal_delete").click(function(){
    var goalId = $(this).attr("rel");
    if ( confirm('.json_encode($lang->text('base', 'are_you_sure')).') )
    {
        document.location.href = "'.OW::getRouter()->urlForRoute('ocsfundraising.admin').'?delete-goal=" + goalId;
    }
    else
    {
        return false;
    }
});
';
        OW::getDocument()->addOnloadScript($js);

        $this->assign('currency', BOL_BillingService::getInstance()->getActiveCurrency());
        
        $logo = OW::getPluginManager()->getPlugin('ocsfundraising')->getStaticUrl() . 'img/oxwallcandystore-logo.jpg';
        $this->assign('logo', $logo);
    }
    
    public function editGoal( )
    {
    	if ( OW::getRequest()->isPost() && $_POST['form_name'] == 'edit-goal-form' )
    	{
    		$service = OCSFUNDRAISING_BOL_Service::getInstance();
    		$goalId = $_POST['goalId'];
    		if ( !$goalId || ! $goal = $service->getGoalById($goalId) )
    		{
    			$this->redirect(OW::getRouter()->urlForRoute('ocsfundraising.admin'));
    		}
    		
    		$goal['dto']->name = trim($_POST['name']);
    		$goal['dto']->description = trim($_POST['description']);
    		$goal['dto']->amountTarget = floatval($_POST['target']);
            $goal['dto']->amountCurrent = floatval($_POST['current']);
            $goal['dto']->amountMin = floatval($_POST['min']);
            if ( !empty($_POST['month_end']) && !empty($_POST['day_end']) && !empty($_POST['year_end']) )
            {
                $goal['dto']->endStamp = mktime(0, 0, 0, $_POST['month_end'], $_POST['day_end'], $_POST['year_end']);
            }
            else 
            {
            	$goal['dto']->endStamp = null;
            }
            
    		$service->update($goal['dto']);
    		OW::getFeedback()->info(OW::getLanguage()->text('ocsfundraising', 'goal_updated'));
    	}
    	
    	$this->redirect(OW::getRouter()->urlForRoute('ocsfundraising.admin'));
    }
    
    public function donations ( array $params )
    {
    	$goalId = (int) $params['goalId'];
    	$service = OCSFUNDRAISING_BOL_Service::getInstance();
    	
    	$goal = $service->getGoalById($goalId);
    	$this->assign('goal', $goal);
    	
    	$this->assign('currency', BOL_BillingService::getInstance()->getActiveCurrency());
    	
    	$page = !empty($_GET['page']) ? (int) $_GET['page'] : 1;
    	$donations = $service->getDonationList($goalId, 'all', $page, 20);
    	$count = $service->countGoalDonations($goalId);
    	$this->assign('donations', $donations);
    	
    	$sum = $service->getGoalDonationsSum($goalId);
    	$this->assign('sum', floatval($sum));
    	
        // Paging
        $pages = (int) ceil($count / 20);
        $paging = new BASE_CMP_Paging($page, $pages, 10);
        $this->addComponent('paging', $paging);
    	
        $this->setPageHeading(OW::getLanguage()->text('ocsfundraising', 'page_heading_donations'));
        $this->setPageHeadingIconClass('ow_ic_app');
        
        $js = 
'$("#view_goals_btn").click(function(){
    document.location.href = '.json_encode(OW::getRouter()->urlForRoute('ocsfundraising.admin')).'
});';        
        OW::getDocument()->addOnloadScript($js);
        
        $this->assign('defaultAvatar', BOL_AvatarService::getInstance()->getDefaultAvatarUrl());
    }
}

class FormAddGoal extends Form 
{
	public function __construct()
	{
		parent::__construct('form-add-goal');
		
		$lang = OW::getLanguage();
		
		$name = new TextField('name');
		$name->setRequired(true);
		$name->setLabel($lang->text('ocsfundraising', 'name'));
		$this->addElement($name);
		
		$desc = new Textarea('description');
		$desc->setLabel($lang->text('ocsfundraising', 'description'));
		$this->addElement($desc);
		
		$target = new TextField('target');
		$target->setRequired(true);
		$target->setLabel($lang->text('ocsfundraising', 'target_amount'));
		$this->addElement($target);
		
		$min = new TextField('min');
		$min->setLabel($lang->text('ocsfundraising', 'min_amount'));
		$min->setValue(1);
		$this->addElement($min);
		
		$end = new DateField('end');
		$end->setMinYear(date('Y'));
		$end->setMaxYear(date('Y') + 2);
		
		$end->setLabel($lang->text('ocsfundraising', 'end_date'));
		$this->addElement($end);
		
		$submit = new Submit('add');
		$submit->setLabel($lang->text('ocsfunraising', 'add'));
		$this->addElement($submit);
	}
}
