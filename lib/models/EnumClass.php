<?php

namespace extpoint\yii2\gii\models;

/**
 * @property-read ModuleClass $moduleClass
 */
class EnumClass extends BaseClass
{
    private static $_models;

    /**
     * @return EnumClass[]
     */
    public static function findAll()
    {
        if (self::$_models === null) {
            self::$_models = [];

            foreach (self::findFiles('enums') as $path => $className) {
                self::$_models[] = new EnumClass([
                    'className' => $className,
                ]);
            }
        }
        return self::$_models;
    }

    /**
     * @return ModuleClass
     */
    public function getModuleClass()
    {
        $namespace = substr($this->className, 0, strpos($this->className, '\\enums\\'));
        $id = str_replace('\\', '.', preg_replace('/^app\\\\/', '', $namespace));

        return new ModuleClass([
            'className' => self::idToClassName($id),
        ]);
    }

}