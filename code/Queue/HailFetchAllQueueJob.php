<?php

/**
 * Job that that will fetch all Hail content asyncronously. This class relies on
 * {@link silverstripe/queuejob https://github.com/silverstripe-australia/silverstripe-queuedjobs}
 */
class HailFetchAllQueueJob extends AbstractQueuedJob implements QueuedJob
{

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
        $this->totalSteps = 1;
    }

    public function process()
    {
        $times = $this->times;
        // needed due to quirks with __set
        $times[] = date('Y-m-d H:i:s');
        $this->times = $times;
        $this->addMessage('Mt Object type is' . $this->hailObjectType);

        // call_user_func($this->hailObjectType . '::fetch()');
        $hailApiObject = singleton($this->hailObjectType);
        $hailApiObject->fetch();

        $this->isComplete = true;
    }

    protected function HailObjectTypeIsValid()
    {
        return class_exists($hailObjectType) && is_subclass_of($hailObjectType, 'HailApiObject');
    }
}
