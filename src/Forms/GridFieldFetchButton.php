<?php

namespace Firebrand\Hail\Forms;

use Firebrand\Hail\Jobs\FetchJob;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\View\ViewableData;

class GridFieldFetchButton implements GridField_HTMLProvider
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
     * Returns the Fetch Button / Progress bar html
     */
    public function getHTMLFragments($gridField)
    {
        //Disable button if there is already a job running, and add a class to the progress button to trigger the display
        $jobs = FetchJob::get()->filter(['Status:not' => 'Done']);
        $current = $jobs->First();
        $global = $current && $current->ToFetch === "*" ? "global-fetch" : "";
        $disabled = $jobs->Count() > 0 ? "disabled" : "";
        $running = $jobs->Count() > 0 ? "hail-fetch-running" : "";
        $active = $jobs->Count() > 0 ? "state-active" : "";
        $vd = new ViewableData();
        $rendered = $vd
            ->customise([
                'Disabled' => $disabled,
                'Running' => $running,
                'Active' => $active,
                'Global' => $global,
            ])
            ->renderWith('GridFieldFetchButton')
            ->getValue();

        return [
            $this->targetFragment => $rendered,
        ];
    }
}
