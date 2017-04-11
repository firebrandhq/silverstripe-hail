<?php

/**
 * Sets twitter configuration in the SiteConfig
 *
 * @author Damian Mooyman
 *
 * @package twitter
 */
class HailLoginExtension extends DataExtension {

	private static $has_many = array(
		'Organisations' => 'HailOrganisation'
	);

	public function updateCMSFields(FieldList $fields) {

		$fields->addFieldToTab('Root.Hail', GridField::create('Organisations', 'Hail Organisations')
			->setList(HailOrganisation::get())
			->setConfig(GridFieldConfig_RecordEditor::create())
		);

	}

}
