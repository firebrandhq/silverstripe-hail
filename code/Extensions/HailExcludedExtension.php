<?php
class HailExcludedExtension extends DataExtension {

    public function excluded($data) {
        if(isset($data->private_tags)) {
            foreach($data->private_tags as $tag) {
                if($tag->id == SiteConfig::current_site_config()->HailExcludedTagID) {
                    return true;
                }
            }
        }
    }
}
