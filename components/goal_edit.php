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

        $form->getElement('goalId')->setValue($goalId);
        $form->getElement('name')->setValue($goal['dto']->name);
        $form->getElement('description')->setValue($goal['dto']->description);
        $form->getElement('target')->setValue($goal['dto']->amountTarget);
        $form->getElement('current')->setValue($goal['dto']->amountCurrent);
        $form->getElement('min')->setValue(floatval($goal['dto']->amountMin));
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
        
        $lang = OW::getLanguage();

        $id = new HiddenField('goalId');
        $this->addElement($id);
        
        $name = new TextField('name');
        $name->setRequired(true);
        $name->setLabel($lang->text('ocsfundraising', 'name'));
        $this->addElement($name);
        
        $desc = new Textarea('description');
        $desc->setLabel($lang->text('ocsfundraising', 'description'));
        $this->addElement($desc);
        
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
        
        $submit = new Submit('update');
        $submit->setLabel($lang->text('base', 'save'));
        $this->addElement($submit);
    }
}