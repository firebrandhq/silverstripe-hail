<?php

namespace Firebrand\Hail\Forms;

use SilverStripe\Control\Controller;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_URLHandler;

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
     */
    public function handleFetchHail($gridField, $data, $request = null)
    {
        // Shchedule the job
        //todo: take a look at how we do the jobs
//        singleton('QueuedJobService')->queueJob(new HailFetchQueueJob($gridField->getModelClass()));

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

        // Redirect the user
        $controller = Controller::curr();
        $noActionURL = $controller->removeAction($data['url']);
        return $controller->redirect($noActionURL, 302);

    }
}
