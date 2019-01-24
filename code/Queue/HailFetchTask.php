<?php

/**
 * Task used to fetch all Hail Content
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
        $is_cli = php_sapi_name() == "cli";
        if ($is_cli) {
            $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        }

        foreach (HailOrganisation::get() as $org) {
            if($is_cli){
                $output->writeln("<info>----Fetching $org->Title----</info>");
            }
            foreach (HailApiObject::fetchables() as $hailObjType) {
                if($is_cli){
                    $output->writeln("<comment>Fetching $hailObjType...</comment>");
                }

                $hailApiObject = singleton($hailObjType);
                $hailApiObject->fetch($org);
            }
            if($is_cli){
                $output->writeln("<info>-------------------------------------</info>");
            }
        }
    }

}
