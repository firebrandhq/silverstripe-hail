<?php

namespace Firebrand\Hail\Extensions;

use SilverStripe\ORM\DataExtension;

/**
 * DataObject Extension that handles inline Has One fields
 *
 * If you add CMS Fields in a DataObject following this syntax: [RELATION][SEPARATOR][FIELDNAME]  (Author___Title for example)
 * This extension will handle writing to the has one relation instead of the current DataObject
 *
 * Forked from https://github.com/stevie-mayhew/hasoneedit and modified for our module
 *
 * @package silverstripe-hail
 * @author Simon Welsh, simon@simon.geek.nz
 * @author Marc Espiard, Firebrand
 * @version 1.0
 *
 */
class DataObjectExtension extends DataExtension
{
    /**
     * @var string
     */
    const SEPARATOR = '___';

    /**
     * @see {@link SilverStripe\ORM\DataObject->onBeforeWrite()}
     */
    public function onBeforeWrite()
    {
        $changed = $this->getOwner()->getChangedFields();
        $toWrite = [];

        foreach ($changed as $name => $value) {

            if (!strpos($name, self::SEPARATOR)) {
                // Also skip $name that starts with a separator
                continue;
            }

            $value = json_encode($value['after']);
            list($hasone, $key) = explode(self::SEPARATOR, $name, 2);
            if ($this->getOwner()->hasOne($hasone) || $this->getOwner()->belongsTo($hasone)) {
                $rel = $this->getOwner()->getComponent($hasone);

                // Get original:
                $original = $rel->__get($key);

                if ($original !== $value) {
                    $_value = json_decode($value);
                    $rel->$key = $_value ? $_value : $value;
                    $toWrite[$hasone] = $rel;
                }
            }
        }

        foreach ($toWrite as $rel => $obj) {
            $obj->write();

            $key = $rel . 'ID';

            if (!$this->getOwner()->$key) {
                $this->getOwner()->$key = $obj->ID;
            }
        }
    }
}
