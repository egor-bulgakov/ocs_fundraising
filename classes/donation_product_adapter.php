<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Donation product adapter class.
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.classes
 * @since 1.2.3
 */
class OCSFUNDRAISING_CLASS_DonationProductAdapter implements OW_BillingProductAdapter
{
    const PRODUCT_KEY = 'ocsfundraising_donation';

    const RETURN_ROUTE = 'ocsfundraising.donate';

    public function getProductKey()
    {
        return self::PRODUCT_KEY;
    }

    public function getProductOrderUrl( )
    {
        return OW::getRouter()->urlForRoute(self::RETURN_ROUTE);
    }

    public function deliverSale( BOL_BillingSale $sale )
    {
        $goalId = $sale->entityId;
        
        $service = OCSFUNDRAISING_BOL_Service::getInstance();
        
        $goal = $service->getGoalById($goalId);
        
        if ( !$goal )
        {
            return false;
        }

        $extra = $sale->getExtraData();
        $donation = new OCSFUNDRAISING_BOL_Donation();
        $donation->amount = $sale->totalAmount;
        $donation->userId = !empty($sale->userId) ? $sale->userId : null;
        $donation->donationStamp = time();
        $donation->goalId = $sale->entityId;
        $donation->username = !empty($extra['username']) ? $extra['username'] : null;
        
        $service->registerDonation($donation);
        
        $goal['dto']->amountCurrent += $donation->amount;
        $service->update($goal['dto']);
        
        return true;
    }
}