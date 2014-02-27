<?php

namespace b8\Form;

use b8\View;
use b8\Config;

abstract class Element
{
    protected $name;
    protected $id;
    protected $label;
    protected $css;
    protected $ccss;
    protected $parent;

    public function __construct($name = null)
    {
        if (!is_null($name)) {
            $this->setName($name);
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = strtolower(preg_replace('/([^a-zA-Z0-9_\-])/', '', $name));
    }

    public function getId()
    {
        return !$this->id ? 'element-' . $this->name : $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getClass()
    {
        return $this->css;
    }

    public function setClass($class)
    {
        $this->css = $class;
    }

    public function getContainerClass()
    {
        return $this->ccss;
    }

    public function setContainerClass($class)
    {
        $this->ccss = $class;
    }

    public function setParent(Element $parent)
    {
        $this->parent = $parent;
    }

    public function render($viewFile = null)
    {
        $viewPath = Config::getInstance()->get('b8.view.path');

        if (is_null($viewFile)) {
            $class = explode('\\', get_called_class());
            $viewFile = end($class);
        }

        if (file_exists($viewPath . 'Form/' . $viewFile . '.phtml')) {
            $view = new View('Form/' . $viewFile);
        } else {
            $view = new View($viewFile, B8_PATH . 'Form/View/');
        }

        $view->name = $this->getName();
        $view->id = $this->getId();
        $view->label = $this->getLabel();
        $view->css = $this->getClass();
        $view->ccss = $this->getContainerClass();
        $view->parent = $this->parent;

        $this->onPreRender($view);

        return $view->render();
    }

    abstract protected function onPreRender(View &$view);
}
