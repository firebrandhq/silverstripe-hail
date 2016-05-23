<?php

class ExclusionHailList extends TagHailList
{
    private static $has_one = array(
        'ExcludeTag' => 'HailTag',
    );

    private static $api_access = true;

    private static $summary_fields = array(
        'Title', 'Type',
    );

    public function Articles()
    {
        $filterID = $this->ExcludeTag()->ID;

        $sqlQuery = new SQLQuery();
        $sqlQuery->setFrom('HailTag_Articles');
        $sqlQuery->setSelect('HailTag_Articles.HailArticleID');
        //$sqlQuery->addLeftJoin('HailTag_Articles','HailTag_Articles.HailArticleID = HailArticleID');
        //$sqlQuery->addWhere('HailTag_Articles.HailTagID = ' . intval($this->Tag()->ID));
        $sqlQuery->addWhere('HailTagID = '.intval($filterID));

        $map = $sqlQuery->execute()->map();
        $articles_ids = array_keys($map);

        $List = parent::Articles()->exclude('ID', $articles_ids);

        return $List;
    }

    public function Type()
    {
        return 'ExclusionList';
    }
}
