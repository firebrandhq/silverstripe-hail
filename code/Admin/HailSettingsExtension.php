<?php


class HailSettingsExtension extends DataExtension {

	private static $has_many = array(
		'Organisations' => 'HailOrganisation'
	);

	public function updateCMSFields(FieldList $fields) {
        $fields->insertAfter(TabSet::create('Hail', 'Hail'), 'Main');
        $fields->addFieldToTab('Root.Hail', Tab::create('Organisations', 'Organisations'));
		$fields->addFieldToTab('Root.Hail.Organisations', GridField::create('Organisations', 'Hail Organisations')
			->setList(HailOrganisation::get())
			->setConfig(GridFieldConfig_RecordEditor::create())
		);

	}

}
