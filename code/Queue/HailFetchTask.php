<?php

/**
 * Task used to fetch all Hail Content.
 *
 * @author Firebrand Holding Limited <developers@firebrand.nz>
 */
class HailFetchTask extends BuildTask
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return _t(
            'Hail',
            'Fetch all Hail Content for this site. This should be called via a cronjob or CLI as it can be quite a long request to run over an HTTP connection.'
        );
    }

    /**
     * @param SS_HTTPRequest $request
     */
    public function run($request)
    {
        foreach (HailApiObject::fetchables() as $hailObjType) {
            echo "Fetching $hailObjType";
            $hailApiObject = singleton($hailObjType);
            $hailApiObject->fetch();
        }
    }
}
