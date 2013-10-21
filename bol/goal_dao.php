<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Data Access Object for `ocsfundraising_goal` table.
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.bol
 * @since 1.2.3
 */
class OCSFUNDRAISING_BOL_GoalDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var OCSFUNDRAISING_BOL_GoalDao
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
     * @return OCSFUNDRAISING_BOL_GoalDao
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
        return 'OCSFUNDRAISING_BOL_Goal';
    }
    
    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'ocsfundraising_goal';
    }
    
    public function checkComplete()
    {
    	$sql = "UPDATE `".$this->getTableName()."` SET `status` = 'complete'
    	   WHERE `endStamp` IS NOT NULL AND `endStamp` < :time";
    	
    	$this->dbo->query($sql, array('time' => time()));
    }

    public function findGoalsWithStatus( $status, $page, $limit, $categoryId )
    {
        $start = ($page - 1) * $limit;

        $example = new OW_Example();
        $example->andFieldEqual('status', $status);
        if ( $categoryId )
        {
            $example->andFieldEqual('categoryId', $categoryId);
        }
        $example->setOrder('`startStamp` DESC');
        $example->setLimitClause($start, $limit);

        return $this->findListByExample($example);
    }

    public function countGoalsWithStatus( $status, $categoryId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('status', $status);
        if ( $categoryId )
        {
            $example->andFieldEqual('categoryId', $categoryId);
        }

        return $this->countByExample($example);
    }

    public function findUserGoals( $userId, $page, $limit )
    {
        $start = ($page - 1) * $limit;

        $example = new OW_Example();
        $example->andFieldEqual('status', 'active');
        $example->andFieldEqual('ownerId', $userId);
        $example->setOrder('`startStamp` DESC');
        $example->setLimitClause($start, $limit);

        return $this->findListByExample($example);
    }

    public function getCategoriesCount()
    {
        $sql = "SELECT `status`, `categoryId`, COUNT(*) AS `count` FROM `".$this->getTableName()."`
            GROUP BY `categoryId`
            HAVING `status` = 'active'";

        return $this->dbo->queryForList($sql);
    }
}