<?php

/**
 * Apply this extension to a page so you can add Hail List to them. The Articles will be linked to the default HailHolder
 */
class HailListExtension extends DataExtension
{
    private static $has_many = array(
        'HailLists' => 'HailList'
    );

    public function updateCMSFields(FieldList $fields)
    {
        $config = GridFieldConfig_RecordEditor::create();

        $addButton = new GridFieldAddNewMultiClass();
        $addButton->setClasses(HailList::getSubClasses());
        $config->addComponent($addButton)->removeComponentsByType('GridFieldAddNewButton');


        $config->addComponent(new GridFieldSortableRows('SortOrder'));
        $list = new GridField("Lists", "Lists", $this->getOwner()->HailLists()->sort("SortOrder"), $config);
        $fields->addFieldToTab('Root.HailList', $list);
        $fields->addFieldToTab('Root.HailList', $list);
    }
}
