<?php
/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * Goals category add form
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.classes
 * @since 1.5.3
 */

class OCSFUNDRAISING_CLASS_CategoryAddForm extends Form
{
    public function __construct()
    {
        parent::__construct('category-add-form');

        $lang = OW::getLanguage();

        $name = new TextField('name');
        $name->setRequired(true);
        $name->setLabel($lang->text('ocsfundraising', 'category'));
        $this->addElement($name);

        // submit
        $submit = new Submit('add');
        $submit->setValue($lang->text('ocsfundraising', 'btn_add'));
        $this->addElement($submit);
    }
}