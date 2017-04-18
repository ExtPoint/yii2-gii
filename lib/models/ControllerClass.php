<?php

namespace extpoint\yii2\gii\models;

use yii\helpers\Inflector;

/**
 * @property-read ModuleClass $moduleClass
 * @property-read string $id
 * @property-read string $routePrefix
 * @property-read string[] $requestFieldsArray
 * @property-read string[] $rolesArray
 */
class ControllerClass extends BaseClass
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $requestFields;

    /**
     * @var string
     */
    public $roles;

    /**
     * @return ModuleClass
     */
    public function getModuleClass()
    {
        $namespace = substr($this->className, 0, strpos($this->className, '\\controllers\\'));
        $id = str_replace('\\', '.', preg_replace('/^app\\\\/', '', $namespace));

        return new ModuleClass([
            'className' => self::idToClassName($id),
        ]);
    }

    public function getId() {
        return preg_replace('/-controller$/', '', Inflector::camel2id($this->name));
    }

    public function getRoutePrefix() {
        $modulePrefix = str_replace('.', '/', $this->moduleClass->id);
        return "/$modulePrefix/{$this->id}";
    }

    public function getRequestFieldsArray() {
        return preg_split('/[^\s-_]+/', $this->requestFields, -1, PREG_SPLIT_NO_EMPTY);
    }

    public function getRolesArray() {
        return preg_split('/[^\w\d@*-_]+/', $this->roles, -1, PREG_SPLIT_NO_EMPTY);
    }

    public function renderRoute($action, $params = []) {
        $requestParams = [];
        foreach ($this->requestFieldsArray as $key) {
            $requestParams[] = "'$key' => \$$key";
        }
        foreach ($params as $key => $value) {
            $requestParams[] = "'$key' => $value";
        }
        return "['$action'" . (count($requestParams) > 0 ? ', ' . implode(', ', $requestParams) : '') . ']';
    }

    public function renderActionArguments($names = []) {
        $arguments = [];
        foreach (array_merge($this->requestFieldsArray, $names) as $key) {
            $arguments[] = "\$$key";
        }
        return implode(', ', $arguments);
    }

    public function renderRoles() {
        if (count($this->rolesArray) === 1) {
            return "'{$this->rolesArray[0]}'";
        }

        return "['" . implode("', '", $this->rolesArray) . "']";
    }

}