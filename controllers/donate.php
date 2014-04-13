<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Fundraising donation page controller.
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.controllers
 * @since 1.2.3
 */
class OCSFUNDRAISING_CTRL_Donate extends OW_ActionController
{

    public function index( array $params )
    {
    	if ( !$goalId = $params['goalId'] )
    	{
    		throw new Redirect404Exception();
    	}

    	$fundraisingService = OCSFUNDRAISING_BOL_Service::getInstance();
        $billingService = BOL_BillingService::getInstance();
        $this->assign('currency', $billingService->getActiveCurrency());    	
        if ( !$goal = $fundraisingService->getGoalById($goalId) )
        {
            throw new Redirect404Exception();
        }
        $this->assign('goal', $goal);
        
        $lang = OW::getLanguage();
        $userId = OW::getUser()->getId();
        $this->assign('userId', $userId);
        
        $form = new DonateForm($userId);
        $this->addForm($form);
        $form->getElement('amount')->setValue(floatval($goal['dto']->amountMin));

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
	        $values = $form->getValues();
	        
	        if ( empty($values['gateway']['url']) || empty($values['gateway']['key']) 
	                || !$gateway = $billingService->findGatewayByKey($values['gateway']['key'])
	                || !$gateway->active )
	        {
	            OW::getFeedback()->error($lang->text('base', 'billing_gateway_not_found'));
	            $this->redirectToAction('index');
	        }
	        
	        // create donation product adapter object
	        $productAdapter = new OCSFUNDRAISING_CLASS_DonationProductAdapter();
	        
	        // sale object
	        $sale = new BOL_BillingSale();
	        $sale->pluginKey = 'ocsfundraising';
	        $sale->entityDescription = $goal['dto']->name;
	        $sale->entityKey = $productAdapter->getProductKey();
	        $sale->entityId = $goalId;
	        $sale->price = floatval($values['amount']);
	        $sale->userId = $userId ? $userId : 0;
	        $sale->recurring = false;

            $extra = array();

	        if ( !$userId && !empty($values['username']) )
	        {
	            $extra['username'] = $values['username'];
	        }
            if ( $values['anonymous'] )
            {
                $extra['anonymous'] = $values['anonymous'];
            }

            $sale->setExtraData($extra);
	
	        $saleId = $billingService->initSale($sale, $values['gateway']['key']);
	
	        if ( $saleId )
	        {
	            // sale Id is temporarily stored in session
	            $billingService->storeSaleInSession($saleId);
	            $billingService->setSessionBackUrl(
	               OW::getRouter()->urlForRoute(OCSFUNDRAISING_CLASS_DonationProductAdapter::RETURN_ROUTE, array('goalId' => $goalId))
	            );
	
	            // redirect to gateway form page 
	            $this->redirect($values['gateway']['url']);
	        }
        }

        $this->setPageHeading($goal['dto']->name);
        $this->setPageHeadingIconClass('ow_ic_user');
    }
}

/**
 * Donate form class
 */
class DonateForm extends Form
{
    public function __construct( $userId )
    {
        parent::__construct('donate-form');
        
        $amountField = new TextField('amount');
        $amountField->setRequired();
        $this->addElement($amountField);
        
        if ( !$userId )
        {
        	$username = new TextField('username');
        	$this->addElement($username);
        }
        else
        {
            $anonymous = new CheckboxField('anonymous');
            $this->addElement($anonymous);
        }

        $gatewaysField = new BillingGatewaySelectionField('gateway');
        $gatewaysField->setRequired();
        $this->addElement($gatewaysField);

        $submit = new Submit('donate');
        $submit->setValue(OW::getLanguage()->text('ocsfundraising', 'donate'));
        $this->addElement($submit);
    }
}