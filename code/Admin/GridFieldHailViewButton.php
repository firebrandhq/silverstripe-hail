<?php
/**
 * A button that allows a user to view readonly details of a record. This is
 * disabled by default and intended for use in readonly {@link GridField}
 * instances.
 */
class GridFieldHailViewButton extends GridFieldViewButton
{
    public function getColumnContent($field, $record, $col)
    {
        if ($record->canView()) {
            $data = new ArrayData(array(
                'Link' => singleton('HailModelAdmin')->Link(
                    $record->ClassName.
                    '/EditForm/field/'.
                    $record->ClassName.
                    '/item/'.$record->ID),

            ));

            return $data->renderWith('GridFieldViewButton');
        }
    }
}
