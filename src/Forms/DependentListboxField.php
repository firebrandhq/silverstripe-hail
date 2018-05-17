<?php

namespace Firebrand\Hail\Forms;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\ListboxField;
use SilverStripe\ORM\Map;
use SilverStripe\View\Requirements;

/**
 * ListBoxDependentField is a field that will refresh its source depending on another field's value
 *
 * It is an adaptation of the DependentDropdown module to work with Listbox
 * Was also modified to work with ListBox as the depending field
 *
 * Heavily inspired by https://github.com/sheadawson/silverstripe-dependentdropdownfield
 *
 * @package silverstripe-hail
 * @author Marc Espiard, Firebrand
 * @version 1.0
 *
 */
class DependentListboxField extends ListboxField
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'load',
    ];

    /**
     * @var
     */
    protected $depends;

    /**
     * @var
     */
    protected $unselected;

    /**
     * @var \Closure
     */
    protected $sourceCallback;

    /**
     * DependentListboxField constructor.
     * @param string $name
     * @param string $title
     * @param \Closure $source
     * @param string $value
     * @param $size
     */
    public function __construct($name, $title = null, \Closure $source, $value = '', $size = null)
    {
        parent::__construct($name, $title, [], $value, $size);

        // we are unable to store Closure as a normal source
        $this->sourceCallback = $source;
        $this
            ->addExtraClass('dependent-dropdown')
            ->addExtraClass('dropdown');
    }

    /**
     * @param $request
     * @return HTTPResponse
     */
    public function load($request)
    {
        $response = new HTTPResponse();
        $response->addHeader('Content-Type', 'application/json');

        $items = call_user_func($this->sourceCallback, $request->getVar('val'));
        $results = [];
        if ($items) {
            foreach ($items as $k => $v) {
                $results[] = ['k' => $k, 'v' => $v];
            }
        }

        $response->setBody(Convert::array2json($results));

        return $response;
    }

    /**
     * @return mixed
     */
    public function getDepends()
    {
        return $this->depends;
    }

    /**
     * @param FormField $field
     * @return $this
     */
    public function setDepends(FormField $field)
    {
        $this->depends = $field;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUnselectedString()
    {
        return $this->unselected;
    }

    /**
     * @param $string
     * @return $this
     */
    public function setUnselectedString($string)
    {
        $this->unselected = $string;

        return $this;
    }

    /**
     * @return array|\ArrayAccess|mixed
     */
    public function getSource()
    {
        $val = $this->depends->Value();

        if (
            !$val
            && method_exists($this->depends, 'getHasEmptyDefault')
            && !$this->depends->getHasEmptyDefault()
        ) {
            $dependsSource = array_keys($this->depends->getSource());
            $val = isset($dependsSource[0]) ? $dependsSource[0] : null;
        }

        if (!$val) {
            $source = [];
        } else {
            $source = call_user_func($this->sourceCallback, $val);
            if ($source instanceof Map) {
                $source = $source->toArray();
            }
        }

        if ($this->getHasEmptyDefault()) {
            return ['' => $this->getEmptyString()] + (array)$source;
        } else {
            return $source;
        }
    }

    /**
     * @param array $properties
     * @return string
     */
    public function Field($properties = [])
    {
        Requirements::javascript(
            'firebrandhq/silverstripe-hail: client/dist/js/dependentlistboxfield.js'
        );

        $this->setAttribute('data-link', $this->Link('load'));
        $this->setAttribute('data-depends', $this->getDepends()->getName());
        $this->setAttribute('data-empty', $this->getEmptyString());
        $this->setAttribute('data-unselected', $this->getUnselectedString());

        return parent::Field($properties);
    }

    public function getHasEmptyDefault()
    {
        return false;
    }

    public function getEmptyString()
    {
        return '';
    }
}