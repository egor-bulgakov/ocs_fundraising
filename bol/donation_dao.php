<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Data Access Object for `ocsfundraising_donation` table.
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.bol
 * @since 1.2.3
 */
class OCSFUNDRAISING_BOL_DonationDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var OCSFUNDRAISING_BOL_DonationDao
     */
    private static $classInstance;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an instance of class.
     *
     * @return OCSFUNDRAISING_BOL_DonationDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'OCSFUNDRAISING_BOL_Donation';
    }

    public function getGoalDonations( $goalId, $page, $limit )
    {
        $first = ( $page - 1 ) * $limit;
        
        $example = new OW_Example();
        $example->andFieldEqual('goalId', $goalId);
        $example->setOrder('`donationStamp` DESC');
        $example->setLimitClause($first, $limit);
        
        return $this->findListByExample($example);
    }
    
    public function getGoalTopDonations( $goalId, $limit )
    {
    	$example = new OW_Example();
        $example->andFieldEqual('goalId', $goalId);
        $example->setOrder('`amount` DESC');
        $example->setLimitClause(0, $limit);
        
        return $this->findListByExample($example);
    }
    
    public function getGoalLatestDonations( $goalId, $limit )
    {
        $example = new OW_Example();
        $example->andFieldEqual('goalId', $goalId);
        $example->setOrder('`donationStamp` DESC');
        $example->setLimitClause(0, $limit);
        
        return $this->findListByExample($example);
    }
    
    public function countGoalDonations( $goalId )
    {
    	$example = new OW_Example();
        $example->andFieldEqual('goalId', $goalId);
        
        return $this->countByExample($example);
    }
    
    public function countGoalDonators( $goalId )
    {
        $sql = "SELECT COUNT(DISTINCT(`userId`)) FROM `".$this->getTableName()."`
           WHERE `goalId` = :id";
        
        return $this->dbo->queryForColumn($sql, array('id' => $goalId));
    }
    
    public function getGoalDonationsSum( $goalId )
    {
    	$sql = "SELECT SUM(`amount`) FROM `".$this->getTableName()."`
    	   WHERE `goalId` = :id";
    	
    	return $this->dbo->queryForColumn($sql, array('id' => $goalId));
    }
    
    public function deleteByGoalId( $goalId )
    {
    	$sql = "DELETE FROM `".$this->getTableName()."`
           WHERE `goalId` = :id";
    	
    	$this->dbo->query($sql, array('id' => $goalId));
    }
    
    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'ocsfundraising_donation';
    }
}