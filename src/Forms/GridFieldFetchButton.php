<?php

namespace Firebrand\Hail\Forms;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\ORM\ValidationResult;

class GridFieldFetchButton implements GridField_HTMLProvider, GridField_ActionProvider, GridField_URLHandler
{
    /**
     * Fragment to write the button to
     */
    protected $targetFragment;

    public function __construct($targetFragment = "before")
    {
        $this->targetFragment = $targetFragment;
    }

    /**
     * Place the export button in a <p> tag below the field
     */
    public function getHTMLFragments($gridField)
    {


        $btnLabel = _t(
            'Hail',
            'Fetch {type}',
            ['type' => singleton($gridField->getModelClass())->i18n_plural_name()]
        );

        $button = new GridField_FormAction(
            $gridField,
            'fetchhail',
            $btnLabel,
            'fetchhail',
            null
        );

        $button->setAttribute('data-icon', 'fetchHail');
        $button->addExtraClass("btn btn-secondary font-icon-sync btn--icon-large action_fetch");

        return [
            $this->targetFragment => $button->Field(),
        ];
    }

    /**
     * export is an action button
     */
    public function getActions($gridField)
    {
        return ['fetchhail'];
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        if ($actionName == 'fetchhail') {
            return $this->handleFetchHail($gridField, $data);
        }
        return null;
    }

    /**
     * it is also a URL
     */
    public function getURLHandlers($gridField)
    {
        return [
            'fetchhail' => 'handleFetchHail',
        ];
    }

    /**
     * Handle the export, for both the action button and the URL
     * @param GridField $gridField
     */
    public function handleFetchHail($gridField, $data, $request = null)
    {
        $classname = $gridField->getModelClass();
        //Fetch it from Hail API
//        $classname::fetchAll();

        // Set a message so the smelly user isn't confused as fuck
        $form = $gridField->getForm();
        $form->sessionMessage(
            _t(
                'Hail',
                '{type} will be fetched momentarily. Please allow at least 30 minutes for this background process to complete.',
                ['type' => singleton($gridField->getModelClass())->i18n_plural_name()]
            ),
            'good'
        );

//        $gridField->setTitle("Fetch successful !");

        // Redirect the user
        $controller = Controller::curr();
        $url = $controller->getRequest()->getURL();
        $noActionURL = $controller->removeAction($url);
        return $controller->redirect($noActionURL);
    }
}
