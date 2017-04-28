<?php

namespace extpoint\yii2\gii\models;

use extpoint\yii2\base\ArrayType;
use extpoint\yii2\base\Model;
use extpoint\yii2\gii\helpers\GiiHelper;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * @property MetaItem[] $meta
 * @property MetaItem[] $metaWithChild
 * @property Relation[] $relations
 * @property array $phpDocProperties
 * @property array $properties
 */
class ModelMetaClass extends ModelClass
{
    /**
     * @var ModelClass
     */
    public $modelClass;

    /**
     * @var MetaItem[]
     */
    private $_meta;

    /**
     * @var Relation[]
     */
    private $_relations;

    public function getMeta()
    {
        if ($this->_meta === null) {
            $modelClass = str_replace('\\meta\\', '\\', preg_replace('/Meta$/', '', $this->className));

            if (class_exists($modelClass)) {
                /** @type Model $model */
                $model = new $modelClass();

                $modelMeta = $model::meta();
                if ($modelMeta) {
                    $this->_meta = [];
                    foreach ($modelMeta as $name => $params) {
                        $metaItem = new MetaItem([
                            'name' => $name,
                            'metaClass' => $this,
                        ]);
                        foreach ($params as $key => $value) {
                            if (property_exists($metaItem, $key)) {
                                $metaItem->$key = $value;
                            }
                        }
                        $this->_meta[] = $metaItem;
                    }
                } else {
                    $this->_meta = array_map(function ($attribute) use ($model) {
                        return new MetaItem([
                            'name' => $attribute,
                            'label' => $model->getAttributeLabel($attribute),
                            'hint' => $model->getAttributeHint($attribute),
                        ]);
                    }, $model->attributes());
                }
            }
        }
        return $this->_meta;
    }

    public function setMeta($value)
    {
        return $this->_meta = $value;
    }

    /**
     * @return array
     */
    public function getMetaWithChild()
    {
        $items = [];
        foreach ($this->getMeta() as $metaItem) {
            $items[] = $metaItem;
            $items = array_merge($items, $metaItem->items);
        }
        return $items;
    }

    /**
     * @param string $name
     * @return MetaItem|null
     */
    public function getMetaItem($name)
    {
        foreach ($this->metaWithChild as $metaItem) {
            if ($metaItem->name === $name) {
                return $metaItem;
            }
        }
        return null;
    }

    public function getRelations()
    {
        if ($this->_relations === null) {
            $this->_relations = [];

            $modelClass = str_replace('\\meta\\', '\\', preg_replace('/Meta$/', '', $this->className));

            if (class_exists($modelClass)) {
                $modelInstance = new $modelClass();

                foreach ((new \ReflectionClass($modelClass))->getMethods() as $methodInfo) {
                    if ($methodInfo->class !== $this->className || strpos($methodInfo->name, 'get') !== 0) {
                        continue;
                    }

                    $activeQuery = $modelInstance->{$methodInfo->name}();
                    if ($activeQuery instanceof ActiveQuery) {
                        if ($activeQuery->multiple && $activeQuery->via) {
                            $this->_relations[] = new Relation([
                                'type' => 'manyMany',
                                'name' => lcfirst(substr($methodInfo->name, 3)),
                                'relationClass' => ModelClass::findOne($activeQuery->modelClass),
                                'relationKey' => array_keys($activeQuery->link)[0],
                                'selfKey' => array_values($activeQuery->via->link)[0],
                                'viaTable' => $activeQuery->via->from[0],
                                'viaRelationKey' => array_keys($activeQuery->via->link)[0],
                                'viaSelfKey' => array_values($activeQuery->link)[0],
                            ]);
                        } else {
                            $this->_relations[] = new Relation([
                                'type' => $activeQuery->multiple ? 'hasMany' : 'hasOne',
                                'name' => lcfirst(substr($methodInfo->name, 3)),
                                'relationClass' => ModelClass::findOne($activeQuery->modelClass),
                                'relationKey' => array_keys($activeQuery->link)[0],
                                'selfKey' => array_values($activeQuery->link)[0],
                            ]);
                        }
                    }
                }
            }
        }
        return $this->_relations;
    }

    public function setRelations($value)
    {
        $this->_relations = $value;
    }

    /**
     * @param string $name
     * @return Relation|null
     */
    public function getRelation($name)
    {
        foreach ($this->relations as $relation) {
            if ($relation->name === $name) {
                return $relation;
            }
        }
        return null;
    }

    /**
     * @param string $indent
     * @param array $useClasses
     * @return mixed|string
     */
    public function renderMeta($indent = '', &$useClasses = [])
    {
        return GiiHelper::varExport($this->exportMeta($this->meta, $useClasses), $indent);
    }

    protected function exportMeta($metaItems, &$useClasses)
    {
        $meta = [];
        foreach ($metaItems as $metaItem) {
            $meta[$metaItem->name] = [];
            foreach ($metaItem as $key => $value) {
                // Skip defaults
                if ($key === 'appType' && $value === 'string') {
                    continue;
                }
                if ($key === 'stringType' && $value === 'text') {
                    continue;
                }

                // Skip array key
                if ($key === 'name') {
                    continue;
                }

                // Skip relation to parent
                if ($key === 'metaClass') {
                    continue;
                }

                // Skip null values
                if ($value === '' || $value === null) {
                    continue;
                }

                if ($key === 'enumClassName') {
                    $enumClass = EnumClass::findOne($value);
                    $value = new ValueExpression($enumClass->name . '::className()');
                    $useClasses[] = $enumClass->className;
                }

                // Items process
                if ($key === 'items') {
                    $value = $this->exportMeta($value, $useClasses);
                }

                $meta[$metaItem->name][$key] = $value;
            }
        }
        return $meta;
    }

    public function renderBehaviors($indent = '', &$useClasses = [])
    {
        $behaviors = [];
        foreach ($this->metaWithChild as $metaItem) {
            $appType = \Yii::$app->types->getType($metaItem->appType);
            if (!$appType) {
                continue;
            }

            foreach ($appType->getGiiBehaviors($metaItem) as $behaviour) {
                if (is_string($behaviour)) {
                    $behaviour = ['class' => $behaviour];
                }

                $className = ArrayHelper::remove($behaviour, 'class');
                if (!isset($behaviors[$className])) {
                    $behaviors[$className] = [];
                }
                $behaviors[$className] = ArrayHelper::merge($behaviors[$className], $behaviour);
            }
        }
        if (empty($behaviors)) {
            return '';
        }

        $items = [];
        foreach ($behaviors as $className => $params) {
            $nameParts = explode('\\', $className);
            $name = array_slice($nameParts, -1)[0];
            $useClasses[] = $className;

            if (empty($params)) {
                $items[] = "$name::className(),";
            } else {
                $params = array_merge([
                    'class' => new ValueExpression("$name::className()"),
                ], $params);
                $items[] = GiiHelper::varExport($params, $indent) . ",";
            }
        }
        return implode("\n" . $indent, $items) . "\n";
    }

    public function getPhpDocProperties()
    {
        $properties = [];
        foreach ($this->metaWithChild as $metaItem) {
            if ($metaItem->getDbType()) {
                $properties[$metaItem->name] = $metaItem->phpDocType;
            }
        }
        return $properties;
    }

    public function getProperties()
    {
        $properties = [];
        foreach ($this->metaWithChild as $metaItem) {
            $appType = \Yii::$app->types->getType($metaItem->appType);
            if (!$appType) {
                continue;
            }

            if ($appType instanceof ArrayType && !$appType->getGiiDbType($metaItem)) {
                $relation = $metaItem->metaClass->getRelation($metaItem->relationName);
                $properties[$metaItem->name] = $relation && !$relation->isHasOne ? '[]' : '';
            }
        }
        return $properties;
    }

    public function fields()
    {
        return [
            'className',
            'name',
            'meta',
            'relations',
        ];
    }
}