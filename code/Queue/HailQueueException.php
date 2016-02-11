<?php

/**
 * Exception thrown when there's a problem with processing a Hail Queue.
 */
class HailQueueException extends Exception
{
    protected $hailMessage = '';
}
