<?php

class OCSFUNDRAISING_CLASS_GoalAddForm extends Form
{
    public function __construct()
    {
        parent::__construct('goal-add-form');

        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $lang = OW::getLanguage();

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

        $min = new TextField('min');
        $min->setLabel($lang->text('ocsfundraising', 'min_amount'));
        $min->setValue(1);
        $this->addElement($min);

        $end = new DateField('end');
        $end->setMinYear(date('Y'));
        $end->setMaxYear(date('Y') + 2);

        $end->setLabel($lang->text('ocsfundraising', 'end_date'));
        $this->addElement($end);

        $imageField = new FileField('image');
        $imageField->setLabel($lang->text('ocsfundraising', 'image_label'));
        $this->addElement($imageField);

        $submit = new Submit('add');
        $submit->setLabel($lang->text('ocsfundraising', 'add'));
        $this->addElement($submit);
    }
}
