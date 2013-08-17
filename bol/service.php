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
    
    public function addGoal( OCSFUNDRAISING_BOL_Goal $goal )
    {
    	$this->goalDao->save($goal);
    }
    
    public function update( OCSFUNDRAISING_BOL_Goal $goal )
    {
        $this->goalDao->save($goal);
    }
    
    public function deleteGoal( $id )
    {
    	$this->goalDao->deleteById($id);
    	$this->donationDao->deleteByGoalId($id);
    }
    
    /**
     *
     * @param int $id
     * @return OCSFUNDRAISING_BOL_Goal
     */
    public function getGoalById( $id )
    {
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
    
    public function registerDonation( OCSFUNDRAISING_BOL_Donation $donation )
    {
    	$this->donationDao->save($donation);
    }
       
    public function countGoalDonations( $goalId )
    {
        return $this->donationDao->countGoalDonations($goalId);
    }
    
    public function countGoalDonators( $goalId )
    {
    	return $this->donationDao->countGoalDonators($goalId);
    } 
    
    public function getGoalDonationsSum( $goalId )
    {
        return $this->donationDao->getGoalDonationsSum($goalId);
    }
    
    public function getGoalsList()
    {
    	return $this->goalDao->findAll();
    }
    
    public function getDonationList( $goalId, $type, $page = 1, $limit = 3 )
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
    	
        if ( !$list ) { return false; }
        
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
        foreach ( $list as $donation )
        {
            $donation->donationStamp = UTIL_DateTime::formatDate($donation->donationStamp, false);
            $donation->amount = floatval($donation->amount);
            $res[$donation->id]['dto'] = $donation;
            if ( $donation->userId )
            {
                $res[$donation->id]['avatar'] = !empty($avatars[$donation->userId]) ? $avatars[$donation->userId] : $defAvatar;
                $res[$donation->id]['username'] = !empty($userNames[$donation->userId]) ? $userNames[$donation->userId] : $userService->getUserName($donation->userId);
                $res[$donation->id]['displayName'] = !empty($displayNames[$donation->userId]) ? $displayNames[$donation->userId] : $userService->getDisplayName($donation->userId);
            }
            else 
            {
                $res[$donation->id]['avatar'] = $defAvatar;
                $res[$donation->id]['username'] = $donation->username;
                $res[$donation->id]['displayName'] = $donation->username;
            }
        }
        
        return $res;
    }
    
    public function checkCompleteGoals()
    {
    	$this->goalDao->checkComplete();
    }
}