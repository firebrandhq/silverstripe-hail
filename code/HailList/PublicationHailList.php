<?php

class PublicationHailList extends HailList
{
    private static $db = array(
    );

    private static $has_one = array();

    private static $api_access = true;

    public function Articles()
    {
        return HailPublication::get()->sort('DueDate', 'DESC');
    }

    protected function fetchMethod()
    {
        HailPublication::fetch();
    }

    public function Type()
    {
        return 'Publication List';
    }
}
