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
 * @since 1.6.0
 */
class OCSFUNDRAISING_CTRL_Paypal extends OW_ActionController
{
    public function form()
    {
        $billingService = BOL_BillingService::getInstance();
        $adapter = new BILLINGPAYPAL_CLASS_PaypalAdapter();
        $lang = OW::getLanguage();

        $sale = $billingService->getSessionSale();

        if ( !$sale )
        {
            $url = $billingService->getSessionBackUrl();
            if ( $url != null )
            {
                OW::getFeedback()->warning($lang->text('base', 'billing_order_canceled'));
                $billingService->unsetSessionBackUrl();
                $this->redirect($url);
            }
            else 
            {
                $this->redirect($billingService->getOrderFailedPageUrl());
            }
        }

        $formId = uniqid('order_form-');
        $this->assign('formId', $formId);

        $js = '$("#' . $formId . '").submit()';
        OW::getDocument()->addOnloadScript($js);

        $fields = $adapter->getFields();

        // set custom fields
        $extra = $sale->getExtraData();
        $fields['notify_url'] = OW::getRouter()->urlForRoute('ocsfundraising.paypal_notify');
        $fields['business'] = $extra->business;

        $this->assign('fields', $fields);

        $this->setTemplate(OW::getPluginManager()->getPlugin('billingpaypal')->getCtrlViewDir() . 'order_form.html');

        if ( $billingService->prepareSale($adapter, $sale) )
        {
            $sale->totalAmount = floatval($sale->totalAmount);
            $this->assign('sale', $sale);
            $this->assign('monthPeriod', intval($sale->period / 30));

            $masterPageFileDir = OW::getThemeManager()->getMasterPageTemplate('blank');
            OW::getDocument()->getMasterPage()->setTemplate($masterPageFileDir);

            $billingService->unsetSessionSale();
        }
        else
        {
            $productAdapter = $billingService->getProductAdapter($sale->entityKey);

            if ( $productAdapter )
            {
                $productUrl = $productAdapter->getProductOrderUrl();
            }
            
            OW::getFeedback()->warning($lang->text('base', 'billing_order_init_failed'));
            $url = isset($productUrl) ? $productUrl : $billingService->getOrderFailedPageUrl();
            
            $this->redirect($url);
        }
    }

    public function notify()
    {
        $logger = OW::getLogger('billingpaypal');
        $logger->addEntry(print_r($_POST, true), 'ipn.data-array');
        $logger->writeLog();

        if ( empty($_POST['custom']) )
        {
            exit;
        }

        $hash = trim($_POST['custom']);

        $amount = !empty($_POST['mc_gross']) ? $_POST['mc_gross'] : $_POST['payment_gross'];
        $transactionId = trim($_POST['txn_id']);
        $status = mb_strtoupper(trim($_POST['payment_status']));
        $currency = trim($_POST['mc_currency']);
        $transactionType = trim($_POST['txn_type']);
        $business = trim($_POST['business']);

        $billingService = BOL_BillingService::getInstance();
        $adapter = new BILLINGPAYPAL_CLASS_PaypalAdapter();

        if ( $adapter->isVerified($_POST) )
        {
            $sale = $billingService->getSaleByHash($hash);

            if ( !$sale || !strlen($transactionId) )
            {
                exit;
            }

            if ( $amount != $sale->totalAmount )
            {
                $logger->addEntry("Wrong amount: " . $amount , 'notify.amount-mismatch');
                $logger->writeLog();
                exit;
            }

            $extra = $sale->getExtraData();
            if ( $extra->business != $business )
            {
                $logger->addEntry("Wrong PayPal account: " . $business , 'notify.account-mismatch');
                $logger->writeLog();
                exit;
            }

            if ( $status == 'COMPLETED' )
            {
                switch ( $transactionType )
                {
                    case 'web_accept':
                    case 'subscr_payment':
                        if ( !$billingService->saleDelivered($transactionId, $sale->gatewayId) )
                        {
                            $sale->transactionUid = $transactionId;

                            if ( $billingService->verifySale($adapter, $sale) )
                            {
                                $sale = $billingService->getSaleById($sale->id);
                                
                                $productAdapter = $billingService->getProductAdapter($sale->entityKey);

                                if ( $productAdapter )
                                {
                                    $billingService->deliverSale($productAdapter, $sale);
                                }
                            }
                        }
                        break;
                }
            }
        }
        else
        {
            exit;
        }
    }
}