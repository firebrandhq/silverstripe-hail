<?php

/**
 * Job that allows to fetch data from Hail asyncronously. This class relies on
 * {@link silverstripe/queuejob https://github.com/silverstripe-australia/silverstripe-queuedjobs}
 */
class HailFetchQueueJob extends AbstractQueuedJob implements QueuedJob
{

    /**
     * Construct a new instance of HailFetchQueueJob
     * @param string $hailObjectType Name of the class to fetch
     */
    public function __construct($hailObjectType)
    {
        $this->hailObjectType = $hailObjectType;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return sprintf(_t('Hail', 'Fetching %s'), $this->hailObjectType);
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
        $this->totalSteps = 1;
    }

    public function process()
    {
        $times = $this->times;
        // needed due to quirks with __set
        $times[] = date('Y-m-d H:i:s');
        $this->times = $times;

        $hailApiObject = singleton($this->hailObjectType);
        $hailApiObject->fetch();
        $this->addMessage('Done');

        $this->isComplete = true;
    }

    protected function HailObjectTypeIsValid()
    {
        return class_exists($hailObjectType) && is_subclass_of($hailObjectType, 'HailApiObject');
    }
}
