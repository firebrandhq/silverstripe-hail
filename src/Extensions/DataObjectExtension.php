<?php

namespace Firebrand\Hail\Extensions;

use SilverStripe\ORM\DataExtension;

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
