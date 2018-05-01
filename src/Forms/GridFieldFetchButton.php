<?php

namespace Firebrand\Hail\Forms;

use Firebrand\Hail\Jobs\FetchJob;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;

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
        //Disable button if there is already a job running, and add a class to the progress bar to trigger the display
        $job_count = FetchJob::get()->filter(['Status:not' => 'Done'])->Count();
        $disabled = $job_count > 0 ? "disabled" : "";
        $running = $job_count > 0 ? "hail-fetch-running" : "";

        $html = '<div id="hail-fetch-wrapper">';
        $html .= '<div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle ' . $disabled . '" type="button" id="hail-fetch-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Fetch
                    </button>
                    <div class="dropdown-menu hail-fetch-items" aria-labelledby="hail-fetch-button">
                        <a class="dropdown-item" data-to-fetch="*">All</a>
                        <a class="dropdown-item" data-to-fetch="Firebrand-Hail-Models-Article">Articles</a>
                        <a class="dropdown-item" data-to-fetch="Firebrand-Hail-Models-Publication">Publications</a>
                        <a class="dropdown-item" data-to-fetch="Firebrand-Hail-Models-PublicTag">Public tags</a>
                        <a class="dropdown-item" data-to-fetch="Firebrand-Hail-Models-PrivateTag">Private tags</a>
                    </div>
                </div>';
        $html .= '<div id="hail-fetch-progress" class="' . $running . '"></div> ';
        $html .= '</div>';
        return [
            $this->targetFragment => $html,
        ];
    }
}
