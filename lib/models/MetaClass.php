<?php

namespace extpoint\yii2\gii\models;

use extpoint\yii2\base\Model;
use extpoint\yii2\gii\helpers\GiiHelper;
use yii\db\ActiveQuery;

/**
 * Class MetaClass
 * @property MetaItem[] $meta
 * @property Relation[] $relations
 */
class MetaClass extends ModelClass
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
                        return [
                            'name' => $attribute,
                            'label' => $model->getAttributeLabel($attribute),
                            'hint' => $model->getAttributeHint($attribute),
                        ];
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
     * @param string $name
     * @return MetaItem|null
     */
    public function getMetaItem($name)
    {
        foreach ($this->meta as $metaItem) {
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
     * @return string
     */
    public function renderMeta($indent = '')
    {
        $meta = [];
        foreach ($this->meta as $metaItem) {
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

                $meta[$metaItem->name][$key] = $value;
            }
        }
        return GiiHelper::varExport($meta, $indent);
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