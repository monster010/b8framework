<?php

namespace b8\View\Template;

use b8\Config;
use b8\Helper\KeyValue;
use b8\View;

class Variables
{
    use KeyValue;

    /**
     * @var \b8\View
     */
    protected $template;

    public function __construct(View $template)
    {
        $this->template = $template;
    }

    public function processVariableName($name)
    {
        // Check if we're calling a function:
        $rtn = $this->isFunctionCall($name);

        if (!is_null($rtn)) {
            return $rtn;
        }

        // Check if it is just a literal value:
        $rtn = $this->isLiteral($name);
        if (!is_null($rtn)) {
            return $rtn;
        }

        // Check if we're calling a helper:
        $rtn = $this->isHelperCall($name);
        if (!is_null($rtn)) {
            return $rtn;
        }

        // Try to process it as a variable:
        $rtn = $this->isVariable($name);
        if (!is_null($rtn)) {
            return $rtn;
        }

        return null;
    }

    protected function isFunctionCall($varName)
    {
        if (substr($varName, 0, 1) === '(' && substr($varName, -1) === ')') {
            $functionCall = substr($varName, 1, -1);
            $parts = explode(' ', $functionCall, 2);
            $functionName = $parts[0];
            $arguments = isset($parts[1]) ? $parts[1] : null;

            return $this->template->executeTemplateFunction($functionName, $arguments);
        }

        return null;
    }

    protected function isLiteral($varName)
    {
        // Test if it is just a string:
        if (substr($varName, 0, 1) === '"' && substr($varName, -1) === '"') {
            return substr($varName, 1, -1);
        }

        // Test if it is just a number:
        if (is_numeric($varName)) {
            return $varName;
        }

        // Test if it is a boolean:
        if ($varName === 'true' || $varName === 'false') {
            return ($varName === 'true') ? true : false;
        }

        return null;
    }

    protected function isHelperCall($varName)
    {
        if (strpos($varName, ':') !== false) {
            list($helper, $property) = explode(':', $varName);

            $helper = $this->template->{$helper}();

            if (property_exists($helper, $property) || method_exists($helper, '__get')) {
                return $helper->{$property};
            }
        }

        return null;
    }

    protected function isVariable($varName)
    {
        $varPart = explode('.', $varName);
        $thisPart = array_shift($varPart);

        if (!$this->contains($thisPart)) {
            return null;
        }

        $working = $this->get($thisPart);

        while (count($varPart)) {
            $thisPart = array_shift($varPart);

            if (is_object($working)) {
                // Check if we're working with an actual property:
                if (property_exists($working, $thisPart) || method_exists($working, '__get')) {
                    $working = $working->{$thisPart};
                    continue;
                }
            }

            if (is_array($working) && array_key_exists($thisPart, $working)) {
                $working = $working[$thisPart];
                continue;
            }

            $modifier = $this->isModifier($thisPart, $working);
            if (!is_null($modifier)) {
                $working = $modifier;
                break;
            }

            return null;
        }

        return $working;
    }

    protected function isModifier($thisPart, $working)
    {
        if ($thisPart == 'toLowerCase') {
            return strtolower($working);
        }

        if ($thisPart == 'toUpperCase') {
            return strtoupper($working);
        }

        if ($thisPart == 'toUcWords') {
            return ucwords($working);
        }

        if ($thisPart == 'isNumeric') {
            return is_numeric($working);
        }

        if ($thisPart == 'formatted' && $working instanceof \DateTime) {
            $format = Config::getInstance()->get('app.date_format', 'Y-m-d H:i');
            return $working->format($format);
        }

        if ($thisPart == 'yesNo') {
            return $working ? 'Yes' : 'No';
        }

        return null;
    }

    public function getVariables()
    {
        return $this->data;
    }
}