<?php

/**
 * Job that that will fetch all Hail content asyncronously. This class relies on
 * {@link silverstripe/queuejob https://github.com/silverstripe-australia/silverstripe-queuedjobs}
 */
class HailFetchAllQueueJob extends AbstractQueuedJob implements QueuedJob
{


    /**
     * Construct a new instance of HailFetchAllQueueJob
     */
    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return _t('Hail', 'Fetching everything from Hail');
    }

    /**
     * @return string
     */
    public function getJobType()
    {
        return QueuedJob::LARGE;
    }

    public function setup()
    {
        // just demonstrating how to get a job going...
        // $this->totalSteps = $this->startNumber;
        $this->times = array();
        $this->totalSteps = sizeof($this->getHailObjectTypes());
    }

    /**
     * Return a list of various subclasses of HailApiObject that can be fetched
     * @return string[]
     */
    protected function getHailObjectTypes()
    {
        return ['HailArticle', 'HailImage', 'HailPublication', 'HailTag', 'HailVideo'];
    }

    public function process()
    {
        $times = $this->times;
        // needed due to quirks with __set
        $times[] = date('Y-m-d H:i:s');
        $this->times = $times;

        $hailObjTypes = $this->getHailObjectTypes();
        $hailObjType = $hailObjTypes[$this->currentStep];


        $this->addMessage("Fetching $hailObjType");
        $hailApiObject = singleton($this->getHailObjectTypes()[$this->currentStep]);
        $hailApiObject->fetch();

        $this->currentStep++;
        if ($this->currentStep >= $this->totalSteps) {
            $this->isComplete = true;
        }
    }

    protected function HailObjectTypeIsValid()
    {
        return class_exists($hailObjectType) && is_subclass_of($hailObjectType, 'HailApiObject');
    }
}
