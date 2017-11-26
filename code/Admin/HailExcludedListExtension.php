<?php

class HailExcludedListExtension extends DataExtension
{

    private static $db = array(
        'HailExcludedTagIDs' => 'Varchar(255)',
    );

    public function updateCMSFields(FieldList $fields)
    {
//        $fields = parent::updateCMSFields($fields);

        $orgs = HailOrganisation::get();
        $tags = array();
        foreach ($orgs as $org) {
            if (HailProvider::isAuthorised($org)) {
                    $tags = array_merge(HailApi::getPrivateTagList($org), $tags);
            }
        }
        //Check if at least 1 organisation is authorized
        if(sizeof($tags) > 0) {
            $fields->addFieldToTab('Root.Hail',Tab::create('ExcludedTags', 'Excluded Private Tags'));
            $tagField = ListboxField ::create('HailExcludedTagIDs', 'Hail Private Tags to exclude:', $tags)->setEmptyString('None')->setMultiple(true);
            $fields->addFieldToTab('Root.Hail.ExcludedTags', $tagField);

        }
    }
}
