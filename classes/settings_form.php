<?php
/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * Plugin settings form
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.classes
 * @since 1.6.0
 */

class OCSFUNDRAISING_CLASS_SettingsForm extends Form
{
    public function __construct()
    {
        parent::__construct('settings-form');

        $lang = OW::getLanguage();

        $allowPaypal = new CheckboxField('allow_paypal');
        $allowPaypal->setLabel($lang->text('ocsfundraising', 'allow_paypal'));
        $this->addElement($allowPaypal);

        // submit
        $submit = new Submit('save');
        $submit->setValue($lang->text('ocsfundraising', 'btn_save'));
        $this->addElement($submit);
    }
}