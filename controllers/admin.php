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
            $goal->categoryId = $values['category'];
        	$date = explode('/', $values['end']);
        	if ( !empty($date[1]) && !empty($date[2]) && !empty($date[0]) )
        	{
        	   $goal->endStamp = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
        	}
        	$goal->status = 'active';

            $imageValid = true;
            $imagePosted = false;

            if ( !empty($_FILES['image']['name']) )
            {
                if ( (int) $_FILES['image']['error'] !== 0 || !is_uploaded_file($_FILES['image']['tmp_name']) || !UTIL_File::validateImage($_FILES['image']['name']) )
                {
                    $imageValid = false;
                    OW::getFeedback()->error($lang->text('base', 'not_valid_image'));
                }
                else
                {
                    $imagePosted = true;
                }
            }

            if ( $imagePosted && $imageValid )
            {
                $goal->image = uniqid();
            }

            $service->addGoal($goal);

            if ( $imagePosted && $imageValid )
            {
                $service->saveImage($goal->id, $_FILES['image']['tmp_name'], $goal->image);
            }

        	OW::getFeedback()->info($lang->text('ocsfundraising', 'goal_added'));
        	$this->redirect();
        }
        
        $this->setPageHeading($lang->text('ocsfundraising', 'page_heading_admin'));

        $js = 
'$("a.ocs_goal_edit").click(function(){
    var goalId = $(this).data("gid");
    var fb = OW.ajaxFloatBox(
        "OCSFUNDRAISING_CMP_GoalEdit", 
        [goalId], 
        {width: 700, title: '.json_encode($lang->text('admin', 'btn_label_edit')).'}
    );
});

$("a.ocs_goal_delete").click(function(){
    var goalId = $(this).data("gid");
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

        $this->addComponent('menu', $this->getMenu());
    }

    public function categories()
    {
        $lang = OW::getLanguage();
        $service = OCSFUNDRAISING_BOL_Service::getInstance();

        if ( !empty($_GET['del-cat-id']) )
        {
            $service->deleteCategory($_GET['del-cat-id']);
            OW::getFeedback()->info($lang->text('ocsfundraising', 'category_deleted'));
            $this->redirect(OW::getRouter()->urlForRoute('ocsfundraising.admin_categories'));
        }

        $form = new OCSFUNDRAISING_CLASS_CategoryAddForm();
        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $_POST['form_name'] == 'category-add-form' && $form->isValid($_POST) )
            {
                $values = $form->getValues();
                $service->addCategory($values['name']);

                OW::getFeedback()->info($lang->text('ocsfundraising', 'category_added'));
                $this->redirect();
            }
        }

        $categories = $service->getCategoryList();
        $this->assign('categories', $categories);

        $this->setPageHeading($lang->text('ocsfundraising', 'project_categories'));

        $this->addComponent('menu', $this->getMenu());

        OW::getDocument()->addScript(
            OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-ui-1.8.9.custom.min.js'
        );

        $script =
            '$(".cat_rows").sortable({
                items: ".category_tr",
                cursor: "move",
                placeholder: "ph",
                forcePlaceholderSize: true,
                connectWith: ".cat_rows",
                start: function(event, ui){
                    $(ui.placeholder).append("<td colspan=\"2\"></td>");
                    $(".category_rows").sortable("refreshPositions");
                },
                update: function(){
                    var cats = $(".cat_rows").sortable("serialize");
                    var url = '.json_encode(OW::getRouter()->urlForRoute('ocsfundraising.action_reorder')).';
		        $.post(url, cats).success( function() { document.location.reload(); } );
		    }
		});
		';

        $script .=
            '$(".cat_rows tr").hover(
                function(){
                    $("a.category_delete", $(this)).show();
                },
                function(){
                    $("a.category_delete", $(this)).hide();
                }
            );
            ';

        $script .=
            '$("a.category_delete").click(function(){
                var catId = $(this).data("ref");
                if ( confirm('.json_encode($lang->text('ocsfundraising', 'category_delete_confirm')).') )
		    {
		        document.location.href = "'.OW::getRouter()->urlForRoute('ocsfundraising.admin_categories').'?del-cat-id=" + catId;
		    }
		    else
		    {
		        return false;
		    }
		});';


        OW::getDocument()->addOnloadScript($script);
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
            $goal['dto']->categoryId = $_POST['category'];
            if ( !empty($_POST['month_end']) && !empty($_POST['day_end']) && !empty($_POST['year_end']) )
            {
                $goal['dto']->endStamp = mktime(0, 0, 0, $_POST['month_end'], $_POST['day_end'], $_POST['year_end']);
            }
            else 
            {
            	$goal['dto']->endStamp = null;
            }

            $imageValid = true;
            $imagePosted = false;

            if ( !empty($_FILES['image']['name']) )
            {
                if ( (int) $_FILES['image']['error'] !== 0 || !is_uploaded_file($_FILES['image']['tmp_name']) || !UTIL_File::validateImage($_FILES['image']['name']) )
                {
                    $imageValid = false;
                    OW::getFeedback()->error(OW::getLanguage()->text('base', 'not_valid_image'));
                }
                else
                {
                    $imagePosted = true;
                }
            }

            if ( $imagePosted && $imageValid )
            {
                $service->saveImage($goal['dto']->id, $_FILES['image']['tmp_name'], $goal['dto']->image);
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

    public function ajaxReorder( )
    {
        $cats = array_flip($_POST['cat']);

        $service = OCSFUNDRAISING_BOL_Service::getInstance();
        $cList = $service->getCategoryList();

        foreach ( $cList as $cat )
        {
            $cat->sortOrder = $cats[$cat->id] + 1;
            $service->updateCategory($cat);
        }

        exit;
    }

    private function getMenu()
    {
        $lang = OW::getLanguage();
        $router = OW::getRouter();

        $item = new BASE_MenuItem();
        $item->setLabel($lang->text('ocsfundraising', 'projects'));
        $item->setUrl($router->urlForRoute('ocsfundraising.admin'));
        $item->setIconClass('ow_ic_folder');
        $item->setOrder(0);
        $items[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel($lang->text('ocsfundraising', 'categories'));
        $item->setUrl($router->urlForRoute('ocsfundraising.admin_categories'));
        $item->setOrder(1);
        $items[] = $item;

        $cmp = new BASE_CMP_ContentMenu($items);

        return $cmp;
    }
}

class FormAddGoal extends Form 
{
	public function __construct()
	{
		parent::__construct('form-add-goal');

        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
		
		$lang = OW::getLanguage();
		
		$name = new TextField('name');
		$name->setRequired(true);
		$name->setLabel($lang->text('ocsfundraising', 'name'));
		$this->addElement($name);

        $btnSet = array(BOL_TextFormatService::WS_BTN_IMAGE, BOL_TextFormatService::WS_BTN_VIDEO, BOL_TextFormatService::WS_BTN_HTML);
        $desc = new WysiwygTextarea('description', $btnSet);
        $desc->setRequired(true);
        $sValidator = new StringValidator(1, 50000);
        $desc->addValidator($sValidator);
        $desc->setLabel($lang->text('ocsfundraising', 'description'));
        $this->addElement($desc);

        $category = new Selectbox('category');
        $category->setLabel($lang->text('ocsfundraising', 'category'));
        $list = OCSFUNDRAISING_BOL_Service::getInstance()->getCategoryList();
        if ( $list )
        {
            foreach ( $list as $cat )
            {
                $category->addOption($cat->id, $lang->text('ocsfundraising', 'category_'.$cat->id));
            }
        }
        $this->addElement($category);
		
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

        $imageField = new FileField('image');
        $imageField->setLabel($lang->text('ocsfundraising', 'image_label'));
        $this->addElement($imageField);
		
		$submit = new Submit('add');
		$submit->setLabel($lang->text('ocsfunraising', 'add'));
		$this->addElement($submit);
	}
}
