<?php
class HailExcludedExtension extends DataExtension {

    public function excluded($data) {
        if(isset($data->private_tags)) {
            $excludedtags = explode(",", SiteConfig::current_site_config()->HailExcludedTagIDs);
            foreach($data->private_tags as $tag) {
                if(in_array($tag->id, $excludedtags)) {
                    return true;
                }
            }
        }
    }
}
