<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Fundraising Service Class.  
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.bol
 * @since 1.2.3
 */
final class OCSFUNDRAISING_BOL_Service
{
    /**
     * @var OCSFUNDRAISING_BOL_CategoryDao
     */
    private $categoryDao;
    /**
     * @var OCSFUNDRAISING_BOL_GoalDao
     */
    private $goalDao;
    /**
     * @var OCSFUNDRAISING_BOL_DonationDao
     */
    private $donationDao;
    /**
     * Class instance
     *
     * @var OCSFUNDRAISING_BOL_Service
     */
    private static $classInstance;
    
    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->goalDao = OCSFUNDRAISING_BOL_GoalDao::getInstance();
        $this->donationDao = OCSFUNDRAISING_BOL_DonationDao::getInstance();
        $this->categoryDao = OCSFUNDRAISING_BOL_CategoryDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return OCSFUNDRAISING_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @return array
     */
    public function getCategoryList()
    {
        $list = $this->categoryDao->getCategories();

        $result = array();
        if ( $list )
        {
            foreach ( $list as $cat )
            {
                $result[$cat->id] = $cat;
            }
        }

        return $result;
    }

    /**
     * @param $id
     * @return OW_Entity
     */
    public function findCategoryById( $id )
    {
        return $this->categoryDao->findById($id);
    }

    /**
     * @return array
     */
    public function getCategoriesProjectCount()
    {
        $counters = $this->goalDao->getCategoriesCount();

        $result = array();
        if ( $counters )
        {
            foreach ( $counters as $cat )
            {
                $result[$cat['categoryId']] = $cat['count'];
            }
        }

        return $result;
    }

    /**
     * Adds category
     *
     * @param string $title
     * @return boolean
     */
    public function addCategory( $title )
    {
        $title = trim($title);

        if ( !mb_strlen($title) )
        {
            return false;
        }

        $category = new OCSFUNDRAISING_BOL_Category();
        $category->sortOrder = $this->getCategoryNextOrder();

        $this->categoryDao->save($category);

        if ( $category->id )
        {
            $langService = BOL_LanguageService::getInstance();
            $currentLang = $langService->getCurrent();
            $key = $langService->findKey('ocsfundraising', 'category_' . $category->id);
            if ( $key && $langService->findValue($currentLang->getId(), $key->getId()) )
            {
                return true;
            }
            $langService->addValue($currentLang->getId(), 'ocsfundraising', 'category_' . $category->id, $title);
            $langService->generateCache($currentLang->getId());
            return $category->id;
        }

        return false;
    }

    /**
     * @param OCSFUNDRAISING_BOL_Category $cat
     * @return bool
     */
    public function updateCategory( OCSFUNDRAISING_BOL_Category $cat )
    {
        $this->categoryDao->save($cat);

        return true;
    }

    /**
     * Deletes category by Id
     *
     * @param int $categoryId
     * @return boolean
     */
    public function deleteCategory( $categoryId )
    {
        $this->categoryDao->deleteById($categoryId);

        $key = BOL_LanguageService::getInstance()->findKey('ocsfundraising', 'category_' . $categoryId);

        if ( $key )
        {
            BOL_LanguageService::getInstance()->deleteKey($key->id, true);
        }

        $this->goalDao->unsetCategory($categoryId);

        return true;
    }

    /**
     * Returns the order of a new category
     *
     * @return int
     */
    public function getCategoryNextOrder()
    {
        return 1 + $this->categoryDao->getMaxOrder();
    }

    /**
     * @param OCSFUNDRAISING_BOL_Goal $goal
     * @return int
     */
    public function addGoal( OCSFUNDRAISING_BOL_Goal $goal )
    {
    	$this->goalDao->save($goal);

        return $goal->id;
    }

    /**
     * @param OCSFUNDRAISING_BOL_Goal $goal
     */
    public function update( OCSFUNDRAISING_BOL_Goal $goal )
    {
        $this->goalDao->save($goal);
    }

    /**
     * @param $id
     */
    public function deleteGoal( $id )
    {
        $goal = $this->goalDao->findById($id);

    	$this->goalDao->deleteById($id);
    	$this->donationDao->deleteByGoalId($id);


        if( !empty($goal->image) )
        {
            $storage = OW::getStorage();
            $storage->removeFile($this->generateImagePath($goal->image));
            $storage->removeFile($this->generateImagePath($goal->image, false));
        }

        // delete comments
        BOL_CommentService::getInstance()->deleteEntityComments('ocsfundraising_project', $id);
    }
    
    /**
     *
     * @param int $id
     * @return OCSFUNDRAISING_BOL_Goal
     */
    public function getGoalById( $id )
    {
        /** @var OCSFUNDRAISING_BOL_Goal $dto */
    	$dto = $this->goalDao->findById($id);
    	if ( !$dto )
    	{
    		return null;
    	}
    	$dto->amountTarget = floatval($dto->amountTarget);
    	$dto->amountCurrent = floatval($dto->amountCurrent);
    	$return['dto'] = $dto;
    	$return['percent'] = round($dto->amountCurrent / $dto->amountTarget * 100);
    	
    	return $return;
    }

    /**
     * @param OCSFUNDRAISING_BOL_Donation $donation
     */
    public function registerDonation( OCSFUNDRAISING_BOL_Donation $donation )
    {
    	$this->donationDao->save($donation);
    }

    /**
     * @param $goalId
     * @return mixed|null|string
     */
    public function countGoalDonations( $goalId )
    {
        return $this->donationDao->countGoalDonations($goalId);
    }

    /**
     * @param $goalId
     * @return mixed|null|string
     */
    public function countGoalDonators( $goalId )
    {
    	return $this->donationDao->countGoalDonators($goalId);
    }

    /**
     * @param $goalId
     * @return mixed|null|string
     */
    public function getGoalDonationsSum( $goalId )
    {
        return $this->donationDao->getGoalDonationsSum($goalId);
    }

    /**
     * @return array
     */
    public function getGoalsList()
    {
    	return $this->goalDao->findAll();
    }

    /**
     * @param $list
     * @return array
     */
    public function prepareListData( $list )
    {
        $result = array();
        if ( $list )
        {
            $userIdList = array();
            foreach ( $list as $goal )
            {
                if ( !in_array($goal->ownerId, $userIdList) )
                {
                    $userIdList[] = $goal->ownerId;
                }
            }

            $userUrlList = BOL_UserService::getInstance()->getUserUrlsForList($userIdList);
            $userDisplayNames = BOL_UserService::getInstance()->getDisplayNamesForList($userIdList);

            $router = OW::getRouter();

            foreach ( $list as $goal )
            {
                $goal->description = mb_substr($goal->description, 0, 120);
                $result[$goal->id]['dto'] = $goal;
                $result[$goal->id]['imageSrc'] = $goal->image ? $this->generateImageUrl($goal->image, true) : $this->generateDefaultImageUrl();
                $result[$goal->id]['url'] = $router->urlForRoute('ocsfundraising.project', array('id' => $goal->id));
                $result[$goal->id]['userUrl'] = !empty($userUrlList[$goal->ownerId]) ? $userUrlList[$goal->ownerId] : null;
                $result[$goal->id]['name'] = !empty($userDisplayNames[$goal->ownerId]) ? $userDisplayNames[$goal->ownerId] : null;
                $result[$goal->id]['categoryUrl'] = $goal->categoryId ? $router->urlForRoute('ocsfundraising.category', array('id' => $goal->categoryId)) : null;
                $result[$goal->id]['days'] = $goal->endStamp && ($goal->endStamp > time()) ? ceil(($goal->endStamp - time()) / 3600 / 24) : null;
                $result[$goal->id]['percent'] = $goal->amountCurrent / $goal->amountTarget * 100;
            }
        }

        return $result;
    }

    /**
     * @param $status
     * @param $page
     * @param $limit
     * @param null $categoryId
     * @return array
     */
    public function getGoalListWithStatus( $status, $page, $limit, $categoryId = null )
    {
        $list = $this->goalDao->findGoalsWithStatus($status, $page, $limit, $categoryId);

        return $this->prepareListData($list);
    }

    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function getUserGoalList( $userId, $page, $limit )
    {
        $list = $this->goalDao->findUserGoals($userId, $page, $limit);

        return $this->prepareListData($list);
    }

    /**
     * @param $page
     * @param $limit
     * @return array
     */
    public function getPopularGoalList( $page, $limit )
    {
        $list = $this->goalDao->findPopularGoals($page, $limit);

        return $this->prepareListData($list);
    }

    /**
     * @param $status
     * @param null $categoryId
     * @return mixed|null|string
     */
    public function getGoalsWithStatusCount( $status, $categoryId = null )
    {
        return $this->goalDao->countGoalsWithStatus($status, $categoryId);
    }

    /**
     * @param $userId
     * @return int
     */
    public function getUserGoalsCount( $userId )
    {
        return $this->goalDao->countUserGoals($userId);
    }

    /**
     * @return int
     */
    public function getPopularGoalsCount()
    {
        return $this->goalDao->countPopularGoals();
    }

    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function getUserGoalsList( $userId, $page, $limit )
    {
        $list = $this->goalDao->findUserGoals($userId, $page, $limit);

        return $this->prepareListData($list);
    }

    /**
     * @param int $goalId
     * @param string $type
     * @param int $page
     * @param int $limit
     * @param bool $adminMode
     * @return array|bool
     */
    public function getDonationList( $goalId, $type, $page = 1, $limit = 3, $adminMode = false )
    {
    	if ( !$goalId ) { return false; }
    	
    	switch ( $type )
    	{
    		case 'top':
    			$list = $this->donationDao->getGoalTopDonations($goalId, $limit);
    			break;
    			
    		case 'latest':
    			$list = $this->donationDao->getGoalLatestDonations($goalId, $limit);
    			break;
    			
    		case 'all':
    			$list = $this->donationDao->getGoalDonations($goalId, $page, $limit);
    			break;
    	}
    	
        if ( empty($list) ) { return false; }
        
        $avatarService = BOL_AvatarService::getInstance();
        $userService = BOL_UserService::getInstance();
        $defAvatar = array('src' => $avatarService->getDefaultAvatarUrl());

        $userIdList = array();
        foreach ( $list as $donation )
        {
            if ( $donation->userId && !in_array($donation->userId, $userIdList) )
            {
                array_push($userIdList, $donation->userId);
            }
        }
        $avatars = $avatarService->getDataForUserAvatars($userIdList);
        $displayNames = $userService->getDisplayNamesForList($userIdList);
        $userNames = $userService->getUserNamesForList($userIdList);

        $res = array();
        $anonymous = OW::getLanguage()->text('ocsfundraising', 'anonymous');
        foreach ( $list as $donation )
        {
            $donation->donationStamp = UTIL_DateTime::formatDate($donation->donationStamp, false);
            $donation->amount = floatval($donation->amount);
            $res[$donation->id]['dto'] = $donation;
            if ( $donation->userId )
            {
                $res[$donation->id]['avatar'] = $donation->privacy == 'anonymous' && !$adminMode ? $defAvatar : (!empty($avatars[$donation->userId]) ? $avatars[$donation->userId] : $defAvatar);
                $res[$donation->id]['username'] = $donation->privacy == 'anonymous' && !$adminMode ? null : (!empty($userNames[$donation->userId]) ? $userNames[$donation->userId] : $userService->getUserName($donation->userId));
                $res[$donation->id]['displayName'] = $donation->privacy == 'anonymous' && !$adminMode ? $anonymous : (!empty($displayNames[$donation->userId]) ? $displayNames[$donation->userId] : $userService->getDisplayName($donation->userId));
            }
            else 
            {
                $res[$donation->id]['avatar'] = $defAvatar;
                $res[$donation->id]['username'] = $donation->username;
                $res[$donation->id]['displayName'] = $donation->username ? $donation->username : $anonymous;
            }


        }
        
        return $res;
    }
    
    public function checkCompleteGoals()
    {
    	$this->goalDao->checkComplete();
    }

    /**
     * @param $goalId
     * @param $imagePath
     * @param $imageId
     */
    public function saveImage( $goalId, $imagePath, $imageId )
    {
        $storage = OW::getStorage();

        /** @var OCSFUNDRAISING_BOL_Goal $goal */
        $goal = $this->goalDao->findById($goalId);

        if ( !$imageId )
        {
            $imageId = uniqid();

            $goal->image = $imageId;
            $this->goalDao->save($goal);
        }
        elseif ( $storage->fileExists($this->generateImagePath($imageId)) )
        {
            $storage->removeFile($this->generateImagePath($imageId));
            $storage->removeFile($this->generateImagePath($imageId, false));
            $imageId = uniqid();

            $goal->image = $imageId;
            $this->goalDao->save($goal);
        }

        $pluginfilesDir = Ow::getPluginManager()->getPlugin('ocsfundraising')->getPluginFilesDir();

        $tmpImgPath = $pluginfilesDir . 'project_image_' .$imageId . '.jpg';
        $tmpIconPath = $pluginfilesDir . 'project_icon_' . $imageId . '.jpg';

        $image = new UTIL_Image($imagePath);
        $image->resizeImage(300, null)->saveImage($tmpImgPath)
            ->resizeImage(170, 130, true)->saveImage($tmpIconPath);

        unlink($imagePath);

        $storage->copyFile($tmpIconPath, $this->generateImagePath($imageId));
        $storage->copyFile($tmpImgPath,$this->generateImagePath($imageId, false));

        unlink($tmpImgPath);
        unlink($tmpIconPath);
    }

    /**
     * Returns image and icon path.
     *
     * @param integer $imageId
     * @param boolean $icon
     * @return string
     */
    public function generateImagePath( $imageId, $icon = true )
    {
        $imagesDir = OW::getPluginManager()->getPlugin('ocsfundraising')->getUserFilesDir();
        return $imagesDir . ( $icon ? 'project_icon_' : 'project_image_' ) . $imageId . '.jpg';
    }

    /**
     * Returns image and icon url.
     *
     * @param integer $imageId
     * @param boolean $icon
     * @return string
     */
    public function generateImageUrl( $imageId, $icon = true )
    {
        return OW::getStorage()->getFileUrl($this->generateImagePath($imageId, $icon));
    }

    /**
     * Returns default event image url.
     */
    public function generateDefaultImageUrl()
    {
        return OW::getPluginManager()->getPlugin('ocsfundraising')->getStaticUrl() . 'img/no-picture.png';
    }
}