<?php

namespace extpoint\yii2\gii\models;

use extpoint\yii2\base\FormModel;
use extpoint\yii2\base\Model;
use extpoint\yii2\traits\ISearchModelTrait;
use extpoint\yii2\traits\SearchModelTrait;

/**
 * @property-read ModuleClass $moduleClass
 * @property-read FormModelMetaClass $metaClass
 */
class FormModelClass extends BaseClass
{
    private static $_formModels;
    private $_metaClass;

    public static function idToClassName($moduleId, $modelName = null) {
        if ($modelName !== null) {
            return 'app\\' . str_replace('.', '\\', $moduleId) . '\\forms\\' . ucfirst($modelName);
        } else {
            return parent::idToClassName($moduleId, $modelName);
        }
    }

    /**
     * @return static[]
     */
    public static function findAll()
    {
        if (self::$_formModels === null) {
            self::$_formModels = [];

            foreach (self::findFiles('forms') as $path => $className) {
                if (is_subclass_of($className, FormModel::className())) {
                    self::$_formModels[] = new FormModelClass([
                        'className' => $className,
                    ]);
                }
            }
        }
        return self::$_formModels;
    }

    /**
     * @param string $className
     * @return static|null
     */
    public static function findOne($className) {
        foreach (static::findAll() as $modelClass) {
            if ($modelClass->className === $className) {
                return $modelClass;
            }
        }
        return null;
    }

    /**
     * @return ModuleClass
     */
    public function getModelClass()
    {
        $className = $this->className;
        if (class_exists($className)) {
            /** @var SearchModelTrait $instance */
            $instance = new $className();
            if ($instance instanceof ISearchModelTrait) {
                $query = $instance->createQuery();
                return $query ? $query->modelClass : null;
            }
        }
        return null;
    }

    /**
     * @return ModuleClass
     */
    public function getModuleClass()
    {
        $namespace = substr($this->className, 0, strpos($this->className, '\\forms\\'));
        $id = str_replace('\\', '.', preg_replace('/^app\\\\/', '', $namespace));

        return new ModuleClass([
            'className' => self::idToClassName($id),
        ]);
    }

    /**
     * @return FormModelMetaClass
     */
    public function getMetaClass() {
        if ($this->_metaClass === null) {
            $this->_metaClass = new FormModelMetaClass([
                'className' => $this->getNamespace() . '\\meta\\' . $this->getName() . 'Meta',
                'modelClass' => $this,
            ]);
        }
        return $this->_metaClass;
    }

    public function fields() {
        return [
            'className',
            'name',
            'moduleClass',
            'metaClass',
            'modelClass',
        ];
    }

}