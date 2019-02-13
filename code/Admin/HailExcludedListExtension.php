<?php

class HailExcludedListExtension extends DataExtension
{

    private static $db = [
        'HailExcludedTagIDs' => 'Varchar(255)',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $orgs = [];

        $orgs = HailOrganisation::get();

        $tags = [];
        foreach ($orgs as $org) {
            //To avoid errors when you authorize the app with Hail
            if ($org->HailOrgID) {
                try {
                    $tags = array_merge(HailApi::getPrivateTagList($org), $tags);
                } catch (Exception $exception) {
                    //Fail silently
                }
            }
        }
        //Check if at least 1 organisation is authorized
        if (sizeof($tags) > 0) {
            $fields->addFieldToTab('Root.Hail', Tab::create('ExcludedTags', 'Excluded Private Tags'));
            $tagField = ListboxField::create('HailExcludedTagIDs', 'Hail Private Tags to exclude:', $tags)->setEmptyString('None')->setMultiple(true);
            $fields->addFieldToTab('Root.Hail.ExcludedTags', $tagField);

        }
    }
}
