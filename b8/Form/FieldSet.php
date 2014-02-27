<?php

namespace b8\Form;

use b8\Form\Element,
    b8\Form\Input,
    b8\View;

class FieldSet extends Element
{
    protected $children = array();

    public function getValues()
    {
        $rtn = array();

        foreach ($this->children as $field) {
            if ($field instanceof FieldSet) {
                $fieldName = $field->getName();

                if (empty($fieldName)) {
                    $rtn = array_merge($rtn, $field->getValues());
                } else {
                    $rtn[$fieldName] = $field->getValues();
                }
            } elseif ($field instanceof Input) {
                if ($field->getName()) {
                    $rtn[$field->getName()] = $field->getValue();
                }
            }
        }

        return $rtn;
    }

    public function setValues(array $values)
    {
        foreach ($this->children as $field) {
            if ($field instanceof FieldSet) {
                $fieldName = $field->getName();

                if (empty($fieldName) || !isset($values[$fieldName])) {
                    $field->setValues($values);
                } else {
                    $field->setValues($values[$fieldName]);
                }
            } elseif ($field instanceof Input) {
                $fieldName = $field->getName();

                if (isset($values[$fieldName])) {
                    $field->setValue($values[$fieldName]);
                }
            }
        }
    }

    public function addField(Element $field)
    {
        $this->children[$field->getName()] = $field;
        $field->setParent($this);
    }

    public function validate()
    {
        $rtn = true;

        foreach ($this->children as $child) {
            if (!$child->validate()) {
                $rtn = false;
            }
        }

        return $rtn;
    }

    protected function onPreRender(View &$view)
    {
        $rendered = array();

        foreach ($this->children as $child) {
            $rendered[] = $child->render();
        }

        $view->children = $rendered;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getChild($fieldName)
    {
        return $this->children[$fieldName];
    }
}
