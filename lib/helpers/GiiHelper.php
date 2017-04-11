<?php

namespace extpoint\yii2\gii\helpers;

use extpoint\yii2\base\Model;
use yii\base\Module;
use yii\db\ActiveQuery;
use yii\db\Schema;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

class GiiHelper
{
    public static function isModuleExists($moduleName)
    {
        return in_array($moduleName, ArrayHelper::getColumn(static::getModules(), 'id'));
    }

    public static function isModelExists($moduleName, $modelName)
    {
        return static::isModuleExists($moduleName) && in_array($modelName, ArrayHelper::getColumn(static::getModels(), 'name'));
    }

    public static function getModelClass($moduleName, $modelName)
    {
        $model = static::getModel($moduleName, $modelName);
        return $model ? $model['className'] : null;
    }

    public static function getModel($moduleName, $modelName)
    {
        foreach (static::getModels() as $model) {
            if ($model['module'] === $moduleName && $model['name'] === $modelName) {
                return $model;
            }
        }
        return null;
    }

    public static function getModelByClass($className)
    {
        foreach (static::getModels() as $model) {
            if ($model['className'] === trim($className, '\\')) {
                return $model;
            }
        }
        return null;
    }

    public static function getModules()
    {
        $modules = [];
        foreach (\Yii::$app->modules as $id => $module) {
            if (!is_dir(\Yii::getAlias('@app/' . $id))) {
                continue;
            }

            /** @type Module $module */
            $modules[] = [
                'id' => $id,
                'class' => $module::className(),
            ];

            foreach ($module->modules as $subId => $subModule) {
                /** @type Module $subModule */
                $modules[] = [
                    'id' => $id . '.' . $subId,
                    'className' => $subModule::className(),
                ];
            }
        }
        return $modules;
    }

    private static $models;

    public static function getModels()
    {
        if (self::$models === null) {
            self::$models = [];

            $modelsClasses = [];
            foreach (\Yii::$app->getModules() as $module) {
                if ($module->id === 'debug') {
                    continue;
                }

                $classInfo = new \ReflectionClass($module);
                $modulePath = dirname($classInfo->getFileName());
                $moduleNamespace = $classInfo->getNamespaceName();


                $modelFiles = FileHelper::findFiles($modulePath, [
                    'only' => [
                        'models/*.php',
                        '*/models/*.php',
                    ]
                ]);
                foreach ($modelFiles as $modelPath) {
                    $modelsClasses[$modelPath] = $moduleNamespace . str_replace('/', '\\', str_replace($modulePath, '', str_replace('.php', '', $modelPath)));
                }
            }

            foreach ($modelsClasses as $path => $className) {
                /** @type Model $model */
                $model = new $className();
                if ($model instanceof Model) {
                    $nameParts = explode('\\', $model::className());

                    $meta = [];
                    $modelMeta = $model::meta();
                    if ($modelMeta) {
                        foreach ($modelMeta as $name => $params) {
                            $params['name'] = $name;
                            $meta[] = $params;
                        }
                    } else {
                        $meta = array_map(function ($attribute) use ($model) {
                            return [
                                'name' => $attribute,
                                'label' => $model->getAttributeLabel($attribute),
                                'hint' => $model->getAttributeHint($attribute),
                            ];
                        }, $model->attributes());
                    }

                    $name = array_slice($nameParts, -1)[0];
                    $metaName = file_exists(dirname($path) . '/meta/' . $name . 'Meta.php') ? $name . 'Meta' : null;

                    $relations = [];
                    if ($metaName) {
                        $metaClassName = preg_replace('/[^\\\\]+$/', '', $className) . 'meta\\' . $name . 'Meta';
                        $info = new \ReflectionClass($metaClassName);
                        $modelInstance = new $model();

                        foreach ($info->getMethods() as $methodInfo) {
                            if ($methodInfo->class !== $metaClassName || strpos($methodInfo->name, 'get') !== 0) {
                                continue;
                            }

                            $activeQuery = $modelInstance->{$methodInfo->name}();
                            if ($activeQuery instanceof ActiveQuery) {
                                if ($activeQuery->multiple && $activeQuery->via) {
                                    $relations[] = [
                                        'type' => 'manyMany',
                                        'name' => lcfirst(substr($methodInfo->name, 3)),
                                        'relationModelClassName' => trim($activeQuery->modelClass, '\\'),
                                        'relationKey' => array_keys($activeQuery->link)[0],
                                        'selfKey' => array_values($activeQuery->via->link)[0],
                                        'viaTable' => $activeQuery->via->from[0],
                                        'viaRelationKey' => array_keys($activeQuery->via->link)[0],
                                        'viaSelfKey' => array_values($activeQuery->link)[0],
                                    ];
                                } else {
                                    $relations[] = [
                                        'type' => $activeQuery->multiple ? 'hasMany' : 'hasOne',
                                        'name' => lcfirst(substr($methodInfo->name, 3)),
                                        'relationModelClassName' => trim($activeQuery->modelClass, '\\'),
                                        'relationKey' => array_keys($activeQuery->link)[0],
                                        'selfKey' => array_values($activeQuery->link)[0],
                                    ];
                                }
                            }
                        }
                    }

                    self::$models[] = [
                        'className' => $model::className(),
                        'name' => $name,
                        'metaName' => $metaName,
                        'module' => implode('.', array_slice($nameParts, 1, -2)),
                        'tableName' => $model::tableName(),
                        'meta' => $meta,
                        'relations' => $relations,
                    ];
                }
            }
        }

        return self::$models;
    }

    public static function getDbTypes()
    {
        $classInfo = new \ReflectionClass(Schema::className());
        return array_values($classInfo->getConstants());
    }

    public static function getFieldWidgets()
    {
        $activeFieldClass = 'app\core\widgets\AppActiveField';
        if (!class_exists($activeFieldClass)) {
            return [];
        }
        $classInfo = new \ReflectionClass($activeFieldClass);
        return array_values(array_filter(array_map(function ($methodInfo) use ($activeFieldClass) {
            /** @type \ReflectionMethod $methodInfo */
            if (!$methodInfo->isPublic() || $methodInfo->class !== $activeFieldClass) {
                return null;
            }
            return [
                'name' => $methodInfo->name,
            ];
        }, $classInfo->getMethods())));
    }

    public static function getFormatters()
    {
        $classInfo = new \ReflectionClass(\Yii::$app->formatter);
        return array_values(array_filter(array_map(function ($methodInfo) {
            /** @type \ReflectionMethod $methodInfo */
            if (!$methodInfo->isPublic() || strpos($methodInfo->name, 'as') !== 0) {
                return null;
            }
            return [
                'name' => lcfirst(substr($methodInfo->name, 2)),
            ];
        }, $classInfo->getMethods())));
    }

    public static function getTableNames()
    {
        return \Yii::$app->db->schema->tableNames;
    }

    public static function varExport($var, $indent = '')
    {
        $type = gettype($var);
        if (in_array($var, ['true', 'false'])) {
            $type = 'boolean';
        }
        switch ($type) {
            case 'string':
                return "'" . addcslashes($var, "\\\$\'\r\n\t\v\f") . "'";
            case 'array':
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = $indent . '    '
                        . ($indexed ? '' : static::varExport($key) . ' => ')
                        . static::varExport($value, $indent . '    ');
                }
                return "[\n" . implode(",\n", $r) . "\n" . $indent . ']';
            case 'boolean':
                return $var ? 'true' : 'false';
            default:
                return var_export($var, TRUE);
        }
    }
}