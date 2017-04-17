<?php

namespace extpoint\yii2\gii\models;

use extpoint\yii2\base\Model;

/**
 * @property-read MetaClass $metaClass
 * @property-read ModuleClass $moduleClass
 */
class ModelClass extends BaseClass
{
    /**
     * @var string
     */
    public $tableName;

    private $_metaClass;

    private static $_models;

    /**
     * @return ModelClass[]
     */
    public static function findAll()
    {
        if (self::$_models === null) {
            self::$_models = [];

            foreach (self::findFiles('models') as $path => $className) {
                /** @type Model $model */
                $model = new $className();

                if ($model instanceof Model) {
                    self::$_models[] = new ModelClass([
                        'className' => $model::className(),
                        'tableName' => $model::tableName(),
                    ]);
                }
            }
        }
        return self::$_models;
    }

    /**
     * @param string $className
     * @return ModelClass|null
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
     * @return bool
     */
    public function isFileMetaExists() {
        return file_exists($this->getMetaClass()->getFilePath());
    }

    /**
     * @return MetaClass
     */
    public function getMetaClass() {
        if ($this->_metaClass === null) {
            $this->_metaClass = new MetaClass([
                'className' => $this->getNamespace() . '\\meta\\' . $this->getName() . 'Meta',
                'modelClass' => $this,
            ]);
        }
        return $this->_metaClass;
    }

    /**
     * @return ModuleClass
     */
    public function getModuleClass()
    {
        $namespace = substr($this->className, 0, strpos($this->className, '\\models\\'));
        $id = str_replace('\\', '.', preg_replace('/^app\\\\/', '', $namespace));

        return new ModuleClass([
            'className' => self::idToClassName($id),
        ]);
    }

    public function fields() {
        return [
            'className',
            'name',
            'tableName',
            'moduleClass',
            'metaClass',
        ];
    }
}