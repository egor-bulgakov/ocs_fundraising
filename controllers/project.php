<?php

/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Fundraising goals controller.
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.controllers
 * @since 1.5.3
 */
class OCSFUNDRAISING_CTRL_Project extends OW_ActionController
{
    public function add()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticationException();
        }

        if ( !OW::getUser()->isAuthorized('ocsfundraising', 'add_goal') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('ocsfundraising', 'add_goal');
            throw new AuthorizationException($status['msg']);
        }

        $service = OCSFUNDRAISING_BOL_Service::getInstance();
        $lang = OW::getLanguage();
        $userId = OW::getUser()->getId();

        $form = new OCSFUNDRAISING_CLASS_GoalAddForm($userId);
        $this->addForm($form);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $values = $form->getValues();

            $goal = new OCSFUNDRAISING_BOL_Goal();
            $goal->name = trim($values['name']);
            $goal->description = UTIL_HtmlTag::stripJs($values['description']);
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
            $goal->ownerType = 'user';
            $goal->ownerId = OW::getUser()->getId();
            $goal->categoryId = (int) $values['category'];
            $goal->endOnFulfill = (int) $values['fulfill'];
            $goal->paypal = !empty($values['paypal']) ? trim($values['paypal']) : null;

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

            $id = $service->addGoal($goal);

            // newsfeed
            $event = new OW_Event('feed.action', array(
                'pluginKey' => 'ocsfundraising',
                'entityType' => 'ocsfundraising_project',
                'entityId' => $id,
                'userId' => $goal->ownerId
            ));
            OW::getEventManager()->trigger($event);

            if ( $imagePosted && $imageValid )
            {
                $service->saveImage($goal->id, $_FILES['image']['tmp_name'], $goal->image);
            }

            OW::getFeedback()->info($lang->text('ocsfundraising', 'goal_added'));
            $this->redirect(OW::getRouter()->urlForRoute('ocsfundraising.list'));
        }

        $this->assign('currency', BOL_BillingService::getInstance()->getActiveCurrency());
        $this->assign('paypalAllowed', OW::getConfig()->getValue('ocsfundraising', 'allow_paypal') && OW::getPluginManager()->isPluginActive('billingpaypal'));

        $this->setPageHeading($lang->text('ocsfundraising', 'add_project'));
    }

    public function projects()
    {
        $service = OCSFUNDRAISING_BOL_Service::getInstance();
        $lang = OW::getLanguage();

        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;
        $limit = 9;
        $list = $service->getGoalListWithStatus('active', $page, $limit);
        $this->assign('list', $list);

        $total = $service->getGoalsWithStatusCount('active');
        $pages = (int) ceil($total / $limit);
        $paging = new BASE_CMP_Paging($page, $pages, $limit);
        $this->assign('paging', $paging->render());

        $this->addComponent('categories', new OCSFUNDRAISING_CMP_Categories());
        $this->addComponent('menu', $this->getMenu());

        // check auth
        $showAddButton = true;
        $status = BOL_AuthorizationService::getInstance()->getActionStatus('ocsfundraising', 'add_goal');

        if ( $status['status'] == BOL_AuthorizationService::STATUS_AVAILABLE )
        {
            $script = '$("#btn-add-project").click(function(){
                document.location.href = ' . json_encode(OW::getRouter()->urlForRoute('ocsfundraising.add_goal')) . ';
            });';

            OW::getDocument()->addOnloadScript($script);
        }
        else if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
        {
            $script = '$("#btn-add-project").click(function(){
                OW.authorizationLimitedFloatbox('.json_encode($status['msg']).');
            });';

            OW::getDocument()->addOnloadScript($script);
        }
        else
        {
            $showAddButton = false;
        }

        $this->assign('showAddButton', $showAddButton);

        $this->setPageHeading($lang->text('ocsfundraising', 'crowdfunding_projects'));
    }

    public function userProjects( array $params )
    {
        if ( empty($params['username']) )
        {
            throw new Redirect404Exception();
        }

        $username = trim($params['username']);

        $user = BOL_UserService::getInstance()->findByUsername($username);
        if ( !$user )
        {
            throw new Redirect404Exception();
        }

        $service = OCSFUNDRAISING_BOL_Service::getInstance();
        $lang = OW::getLanguage();

        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;
        $limit = 9;
        $list = $service->getUserGoalList($user->id, $page, $limit);
        $this->assign('list', $list);

        $total = $service->getUserGoalsCount($user->id);
        $pages = (int) ceil($total / $limit);
        $paging = new BASE_CMP_Paging($page, $pages, $limit);
        $this->assign('paging', $paging->render());

        $this->addComponent('categories', new OCSFUNDRAISING_CMP_Categories());
        $this->addComponent('menu', $this->getMenu());

        // check auth
        $showAddButton = true;
        $status = BOL_AuthorizationService::getInstance()->getActionStatus('ocsfundraising', 'add_goal');

        if ( $status['status'] == BOL_AuthorizationService::STATUS_AVAILABLE )
        {
            $script = '$("#btn-add-project").click(function(){
                document.location.href = ' . json_encode(OW::getRouter()->urlForRoute('ocsfundraising.add_goal')) . ';
            });';

            OW::getDocument()->addOnloadScript($script);
        }
        else if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
        {
            $script = '$("#btn-add-project").click(function(){
                OW.authorizationLimitedFloatbox('.json_encode($status['msg']).');
            });';

            OW::getDocument()->addOnloadScript($script);
        }
        else
        {
            $showAddButton = false;
        }

        $this->assign('showAddButton', $showAddButton);

        if ( OW::getUser()->getId() == $user->id )
        {
            $this->setPageHeading($lang->text('ocsfundraising', 'my_projects'));
        }
        else
        {
            $displayName = BOL_UserService::getInstance()->getDisplayName($user->id);
            $this->setPageHeading($lang->text('ocsfundraising', 'user_projects', array('user' => $displayName)));
        }

        $this->setTemplate(
            OW::getPluginManager()->getPlugin('ocsfundraising')->getCtrlViewDir() . 'project_projects.html'
        );
    }

    public function category( array $params )
    {
        if ( empty($params['id']) )
        {
            throw new Redirect404Exception();
        }

        $categoryId = (int) $params['id'];

        $service = OCSFUNDRAISING_BOL_Service::getInstance();
        $lang = OW::getLanguage();

        $category = $service->findCategoryById($categoryId);
        if ( !$category )
        {
            throw new Redirect404Exception();
        }

        $this->setTemplate(
            OW::getPluginManager()->getPlugin('ocsfundraising')->getCtrlViewDir() . 'project_projects.html'
        );

        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;
        $limit = 9;
        $list = $service->getGoalListWithStatus('active', $page, $limit, $categoryId);
        $this->assign('list', $list);

        $total = $service->getGoalsWithStatusCount('active', $categoryId);
        $pages = (int) ceil($total / $limit);
        $paging = new BASE_CMP_Paging($page, $pages, $limit);
        $this->assign('paging', $paging->render());

        $this->addComponent('categories', new OCSFUNDRAISING_CMP_Categories());
        $this->addComponent('menu', $this->getMenu());

        // check auth
        $showAddButton = true;
        $status = BOL_AuthorizationService::getInstance()->getActionStatus('ocsfundraising', 'add_goal');

        if ( $status['status'] == BOL_AuthorizationService::STATUS_AVAILABLE )
        {
            $script = '$("#btn-add-project").click(function(){
                document.location.href = ' . json_encode(OW::getRouter()->urlForRoute('ocsfundraising.add_goal')) . ';
            });';

            OW::getDocument()->addOnloadScript($script);
        }
        else if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
        {
            $script = '$("#btn-add-project").click(function(){
                OW.authorizationLimitedFloatbox('.json_encode($status['msg']).');
            });';

            OW::getDocument()->addOnloadScript($script);
        }
        else
        {
            $showAddButton = false;
        }

        $this->assign('showAddButton', $showAddButton);

        $heading = $lang->text('ocsfundraising', 'crowdfunding_projects')
            . ': ' . $lang->text('ocsfundraising', 'category_' . $categoryId);

        $this->setPageHeading($heading);
    }

    public function archive( )
    {
        $this->setTemplate(
            OW::getPluginManager()->getPlugin('ocsfundraising')->getCtrlViewDir() . 'project_projects.html'
        );

        $service = OCSFUNDRAISING_BOL_Service::getInstance();
        $lang = OW::getLanguage();

        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;
        $limit = 9;
        $list = $service->getGoalListWithStatus('complete', $page, $limit);
        $this->assign('list', $list);

        $total = $service->getGoalsWithStatusCount('complete');
        $pages = (int) ceil($total / $limit);
        $paging = new BASE_CMP_Paging($page, $pages, $limit);
        $this->assign('paging', $paging->render());

        $this->addComponent('categories', new OCSFUNDRAISING_CMP_Categories());
        $this->addComponent('menu', $this->getMenu());

        // check auth
        $showAddButton = true;
        $status = BOL_AuthorizationService::getInstance()->getActionStatus('ocsfundraising', 'add_goal');

        if ( $status['status'] == BOL_AuthorizationService::STATUS_AVAILABLE )
        {
            $script = '$("#btn-add-project").click(function(){
                document.location.href = ' . json_encode(OW::getRouter()->urlForRoute('ocsfundraising.add_goal')) . ';
            });';

            OW::getDocument()->addOnloadScript($script);
        }
        else if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
        {
            $script = '$("#btn-add-project").click(function(){
                OW.authorizationLimitedFloatbox('.json_encode($status['msg']).');
            });';

            OW::getDocument()->addOnloadScript($script);
        }
        else
        {
            $showAddButton = false;
        }

        $this->assign('showAddButton', $showAddButton);

        $this->setPageHeading($lang->text('ocsfundraising', 'archived_projects'));
    }

    public function popular( )
    {
        $this->setTemplate(
            OW::getPluginManager()->getPlugin('ocsfundraising')->getCtrlViewDir() . 'project_projects.html'
        );

        $service = OCSFUNDRAISING_BOL_Service::getInstance();
        $lang = OW::getLanguage();

        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;
        $limit = 9;
        $list = $service->getPopularGoalList($page, $limit);
        $this->assign('list', $list);

        $total = $service->getPopularGoalsCount();
        $pages = (int) ceil($total / $limit);
        $paging = new BASE_CMP_Paging($page, $pages, $limit);
        $this->assign('paging', $paging->render());

        $this->addComponent('categories', new OCSFUNDRAISING_CMP_Categories());
        $this->addComponent('menu', $this->getMenu());

        // check auth
        $showAddButton = true;
        $status = BOL_AuthorizationService::getInstance()->getActionStatus('ocsfundraising', 'add_goal');

        if ( $status['status'] == BOL_AuthorizationService::STATUS_AVAILABLE )
        {
            $script = '$("#btn-add-project").click(function(){
                document.location.href = ' . json_encode(OW::getRouter()->urlForRoute('ocsfundraising.add_goal')) . ';
            });';

            OW::getDocument()->addOnloadScript($script);
        }
        else if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
        {
            $script = '$("#btn-add-project").click(function(){
                OW.authorizationLimitedFloatbox('.json_encode($status['msg']).');
            });';

            OW::getDocument()->addOnloadScript($script);
        }
        else
        {
            $showAddButton = false;
        }

        $this->assign('showAddButton', $showAddButton);

        $this->setPageHeading($lang->text('ocsfundraising', 'popular_projects'));
    }

    public function project( array $params )
    {
        if ( empty($params['id']) )
        {
            throw new Redirect404Exception();
        }

        $projectId = (int) $params['id'];
        $service = OCSFUNDRAISING_BOL_Service::getInstance();

        $project = $service->getGoalById($projectId);

        if ( !$project )
        {
            throw new Redirect404Exception();
        }

        $this->assign('project', $project);
        $image = $project['dto']->image ? $service->generateImageUrl($project['dto']->image, false) : null;
        $this->assign('imageSrc', $image);

        $lang = OW::getLanguage();

        $viewerId = OW::getUser()->getId();
        $isOwner = $viewerId && ($project['dto']->ownerId == $viewerId);
        $this->assign('isOwner', $isOwner);

        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($project['dto']->ownerId));
        $this->assign('avatar', $avatar[$project['dto']->ownerId]);
        $this->assign('ownerUrl', BOL_UserService::getInstance()->getUserUrl($project['dto']->ownerId));
        $this->assign('categoryUrl', $project['dto']->categoryId ? OW::getRouter()->urlForRoute('ocsfundraising.category', array('id' => $project['dto']->categoryId)) : null);

        $cmntParams = new BASE_CommentsParams('ocsfundraising', 'ocsfundraising_project');
        $cmntParams->setEntityId($project['dto']->id);
        $cmntParams->setOwnerId($project['dto']->ownerId);
        $this->addComponent('comments', new BASE_CMP_Comments($cmntParams));

        // donations
        $userIdList = array();
        $latest = $service->getDonationList($projectId, 'latest', 1, 6);
        if ( $latest )
        {
            foreach ( $latest as $d )
            {
                if ( $d['dto']->userId && !in_array($d['dto']->userId, $userIdList) )
                {
                    array_push($userIdList, $d['dto']->userId);
                }
            }
        }
        $this->assign('latest', $latest);

        $this->assign('currency', BOL_BillingService::getInstance()->getActiveCurrency());

        $script =
        '$("#btn-edit-project").click(function(){
            document.location.href = '.json_encode(OW::getRouter()->urlForRoute('ocsfundraising.edit_project', array('id' => $projectId))).';
        });

        $("#btn-delete-project").click(function(){
            if ( confirm('.json_encode($lang->text('ocsfundraising', 'project_delete_confirm')).') )
            {
                $.ajax({
                    url: '.json_encode(OW::getRouter()->urlFor('OCSFUNDRAISING_CTRL_Project', 'ajaxDeleteProject')).',
                    type: "POST",
                    data: { projectId: '.json_encode($projectId).' },
                    dataType: "json",
                    success: function(data)
                    {
                        if ( data.result == true ) {
                            if ( data.url )
                                document.location.href = data.url;
                        }
                        else if ( data.error != undefined ) {
                            OW.warning(data.error);
                        }
                    }
                });
            }
        });

        $("#btn-donate").click(function(){
            document.location.href = '.json_encode(OW::getRouter()->urlForRoute('ocsfundraising.donate', array('goalId' => $projectId))).'
        });
        ';
        OW::getDocument()->addOnloadScript($script);

        $this->setPageHeading($project['dto']->name);
        $this->setPageTitle($lang->text('ocsfundraising', 'page_meta_title', array('name' => strip_tags(($project['dto']->name)))));
        OW::getDocument()->setDescription(UTIL_String::truncate(strip_tags($project['dto']->description), 100, '...'));

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'ocsfundraising', 'projects');
    }

    public function edit( array $params )
    {
        if ( empty($params['id']) )
        {
            throw new Redirect404Exception();
        }

        $projectId = (int) $params['id'];
        $service = OCSFUNDRAISING_BOL_Service::getInstance();


        $project = $service->getGoalById($projectId);

        if ( !$project )
        {
            throw new Redirect404Exception();
        }

        $viewerId = OW::getUser()->getId();
        $isOwner = $viewerId && ($project['dto']->ownerId == $viewerId);

        if ( !$isOwner )
        {
            throw new Redirect404Exception();
        }

        $lang = OW::getLanguage();

        $form = new OCSFUNDRAISING_CLASS_GoalEditForm();
        $this->addForm($form);

        $service = OCSFUNDRAISING_BOL_Service::getInstance();
        $goal = $service->getGoalById($projectId);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $values = $form->getValues();
            $goal = $goal['dto'];

            $goal->name = trim($values['name']);
            $goal->description = UTIL_HtmlTag::stripJs($values['description']);
            $goal->amountMin = floatval($values['min']);
            $goal->amountTarget = floatval($values['target']);
            $goal->ownerType = 'user';
            $goal->ownerId = OW::getUser()->getId();
            $date = explode('/', $values['end']);
            if ( !empty($date[1]) && !empty($date[2]) && !empty($date[0]) )
            {
                $goal->endStamp = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
            }
            $goal->categoryId = (int) $values['category'];
            $goal->endOnFulfill = (int) $values['fulfill'];

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
                $service->saveImage($goal->id, $_FILES['image']['tmp_name'], $goal->image);
            }

            $service->update($goal);

            OW::getFeedback()->info($lang->text('ocsfundraising', 'goal_updated'));
            $this->redirect(OW::getRouter()->urlForRoute('ocsfundraising.project', array('id' => $projectId)));
        }

        $form->getElement('projectId')->setValue($projectId);
        $form->getElement('name')->setValue($goal['dto']->name);
        $form->getElement('description')->setValue($goal['dto']->description);
        $form->getElement('target')->setValue($goal['dto']->amountTarget);
        $form->getElement('min')->setValue(floatval($goal['dto']->amountMin));
        $form->getElement('category')->setValue(floatval($goal['dto']->categoryId));
        $form->getElement('fulfill')->setValue($goal['dto']->endOnFulfill);
        if ( $goal['dto']->endStamp )
        {
            $date = date('Y/m/d', $goal['dto']->endStamp);
            $form->getElement('end')->setValue($date);
        }

        $image = $goal['dto']->image ? $service->generateImageUrl($goal['dto']->image, true) : null;
        $this->assign('image', $image);

        $this->setPageHeading($lang->text('ocsfundraising', 'edit_project'));
        $this->setPageTitle($lang->text('ocsfundraising', 'edit_project'));
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'ocsfundraising', 'projects');
    }

    public function ajaxDeleteProject()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect403Exception;
        }

        if ( empty($_POST['projectId']) )
        {
            throw new Redirect404Exception;
        }

        $projectId = (int) $_POST['projectId'];
        $service = OCSFUNDRAISING_BOL_Service::getInstance();
        $proj = $service->getGoalById($projectId);

        if ( !$proj )
        {
            throw new Redirect404Exception;
        }

        $viewerId = OW::getUser()->getId();
        if ( $viewerId &&  ($viewerId != $proj['dto']->ownerId) )
        {
            throw new Redirect403Exception;
        }

        $service->deleteGoal($projectId);

        exit(json_encode(array('result' => true, 'url' => OW::getRouter()->urlForRoute('ocsfundraising.list'))));
    }

    private function getMenu()
    {
        $items = array();
        $lang = OW::getLanguage();
        $router = OW::getRouter();

        $item = new BASE_MenuItem();
        $item->setLabel($lang->text('ocsfundraising', 'recent'));
        $item->setUrl($router->urlForRoute('ocsfundraising.list'));
        $item->setIconClass('ow_ic_files');
        $item->setOrder(0);
        $items[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel($lang->text('ocsfundraising', 'popular'));
        $item->setUrl($router->urlForRoute('ocsfundraising.popular'));
        $item->setIconClass('ow_ic_chat');
        $item->setOrder(1);
        $items[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel($lang->text('ocsfundraising', 'archive'));
        $item->setUrl($router->urlForRoute('ocsfundraising.archive'));
        $item->setIconClass('ow_ic_folder');
        $item->setOrder(2);
        $items[] = $item;

        $menu = new BASE_CMP_ContentMenu($items);

        return $menu;
    }
}