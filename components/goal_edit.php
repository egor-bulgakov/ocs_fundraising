<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Goal edit component
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_fundraising.components
 * @since 1.2.3
 */
class OCSFUNDRAISING_CMP_GoalEdit extends OW_Component
{
    public function __construct( $goalId )
    {
        parent::__construct();        
       
        $form = new EditGoalForm();
        $this->addForm($form);
        
        $service = OCSFUNDRAISING_BOL_Service::getInstance();
        $goal = $service->getGoalById($goalId);

        $image = $goal['dto']->image ? $service->generateImageUrl($goal['dto']->image, true) : null;
        $this->assign('image', $image);

        $form->getElement('goalId')->setValue($goalId);
        $form->getElement('name')->setValue($goal['dto']->name);
        $form->getElement('description')->setValue($goal['dto']->description);
        $form->getElement('target')->setValue($goal['dto']->amountTarget);
        $form->getElement('current')->setValue($goal['dto']->amountCurrent);
        $form->getElement('min')->setValue(floatval($goal['dto']->amountMin));
        $form->getElement('category')->setValue(floatval($goal['dto']->categoryId));
        $form->getElement('fulfill')->setValue($goal['dto']->endOnFulfill);
        if ( $goal['dto']->endStamp )
        {
	        $date = date('Y/m/d', $goal['dto']->endStamp);
	        $form->getElement('end')->setValue($date);
        }
    }
}

/**
 * Edit goal form class
 */
class EditGoalForm extends Form
{
    /**
     * Class constructor
     */
    public function __construct( )
    {
        parent::__construct('edit-goal-form');

        $this->setAction(OW::getRouter()->urlFor('OCSFUNDRAISING_CTRL_Admin', 'editGoal'));
        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        
        $lang = OW::getLanguage();

        $id = new HiddenField('goalId');
        $this->addElement($id);

        $name = new TextField('name');
        $name->setRequired(true);
        $name->setLabel($lang->text('ocsfundraising', 'name'));
        $this->addElement($name);

        $btnSet = array(BOL_TextFormatService::WS_BTN_IMAGE, BOL_TextFormatService::WS_BTN_VIDEO, BOL_TextFormatService::WS_BTN_HTML);
        $desc = new WysiwygTextarea('description', $btnSet);
        $desc->setRequired(true);
        $sValidator = new StringValidator(1, 50000);
        $desc->addValidator($sValidator);
        $desc->setLabel($lang->text('ocsfundraising', 'description'));
        $this->addElement($desc);

        $category = new Selectbox('category');
        $category->setLabel($lang->text('ocsfundraising', 'category'));
        $list = OCSFUNDRAISING_BOL_Service::getInstance()->getCategoryList();
        if ( $list )
        {
            foreach ( $list as $cat )
            {
                $category->addOption($cat->id, $lang->text('ocsfundraising', 'category_'.$cat->id));
            }
        }
        $this->addElement($category);

        $target = new TextField('target');
        $target->setRequired(true);
        $target->setLabel($lang->text('ocsfundraising', 'target_amount'));
        $this->addElement($target);

        $current = new TextField('current');
        $current->setRequired(true);
        $current->setLabel($lang->text('ocsfundraising', 'current_amount'));
        $this->addElement($current);
        
        $min = new TextField('min');
        $min->setLabel($lang->text('ocsfundraising', 'min_amount'));
        $this->addElement($min);
        
        $end = new DateField('end');
        $end->setMinYear(date('Y'));
        $end->setMaxYear(date('Y') + 2);
        
        $end->setLabel($lang->text('ocsfundraising', 'end_date'));
        $this->addElement($end);

        $imageField = new FileField('image');
        $imageField->setLabel($lang->text('ocsfundraising', 'image_label'));
        $this->addElement($imageField);

        $endOnFulfill = new CheckboxField('fulfill');
        $endOnFulfill->setLabel($lang->text('ocsfundraising', 'end_if_fulfilled'));
        $this->addElement($endOnFulfill);
        
        $submit = new Submit('update');
        $submit->setLabel($lang->text('base', 'save'));
        $this->addElement($submit);
    }
}