<?php

class HailExcludedListExtension extends DataExtension {

    private static $db = array(
        'HailExcludedTagID' => 'Varchar(255)',
    );

    public function updateCMSFields(FieldList $fields) {


        if(HailProvider::isAuthorised()) {
            if($this->getOwner()->HailOrgID) {
                $tags = HailApi::getPrivateTagList();
                $tags[''] = '';
                $tagField = DropdownField::create('HailExcludedTagID', 'Private Excluded Tag', $tags)->setEmptyString('None');
                $fields->addFieldsToTab('Root.Private Excluded Tag', $tagField);
            }
        }
    }
}
