<?php

namespace extpoint\yii2\gii\models;

use extpoint\yii2\base\Model;
use extpoint\yii2\gii\helpers\GiiHelper;
use extpoint\yii2\types\RelationType;
use extpoint\yii2\types\StringType;
use yii\db\ActiveQuery;
use yii\db\Schema;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;

/**
 * @property MetaItem[] $meta
 * @property MetaItem[] $metaWithChild
 * @property Relation[] $relations
 * @property array $phpDocProperties
 * @property array $properties
 * @property string $jsFilePath
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
                            'oldName' => $name,
                            'metaClass' => $this,
                        ]);
                        foreach ($params as $key => $value) {
                            $metaItem->$key = $value;
                        }
                        $this->_meta[] = $metaItem;
                    }
                } else {
                    $this->_meta = array_map(function ($attribute) use ($model) {
                        $metaItem = new MetaItem([
                            'name' => $attribute,
                            'oldName' => $attribute,
                            'label' => $model->getAttributeLabel($attribute),
                            'hint' => $model->getAttributeHint($attribute),
                        ]);

                        switch ($attribute) {
                            case 'id':
                                $metaItem->appType = 'primaryKey';
                                break;

                            case 'createTime':
                                $metaItem->appType = 'autoTime';
                                break;

                            case 'updateTime':
                                $metaItem->appType = 'autoTime';
                                $metaItem->touchOnUpdate = true;
                                break;

                            case 'title':
                            case 'name':
                            case 'label':
                                $metaItem->appType = 'string';
                                $metaItem->stringType = StringType::TYPE_TEXT;
                                break;

                            case 'email':
                                $metaItem->appType = 'email';
                                break;

                            case 'phone':
                                $metaItem->appType = 'phone';
                                break;

                            case 'password':
                                $metaItem->appType = 'password';
                                break;
                        }

                        if ($metaItem->appType === 'string') {
                            if (strpos($attribute, 'dateTime') !== false) {
                                $metaItem->appType = 'dateTime';
                            } elseif (strpos($attribute, 'date') !== false) {
                                $metaItem->appType = 'date';
                            } else {
                                $schema = \Yii::$app->db->getTableSchema($model::tableName());
                                $column = $schema ? $schema->getColumn($attribute) : null;
                                $dbType = $column ? $column->dbType : null;

                                switch ($dbType) {
                                    case Schema::TYPE_TEXT:
                                        $metaItem->appType = 'text';
                                        break;

                                    case Schema::TYPE_STRING:
                                        $metaItem->appType = 'string';
                                        break;

                                    case Schema::TYPE_INTEGER:
                                        $metaItem->appType = 'integer';
                                        break;

                                    case Schema::TYPE_DOUBLE:
                                        $metaItem->appType = 'double';
                                        break;

                                    case Schema::TYPE_DATE:
                                        $metaItem->appType = 'date';
                                        break;

                                    case Schema::TYPE_DATETIME:
                                        $metaItem->appType = 'dateTime';
                                        break;

                                    case Schema::TYPE_BOOLEAN:
                                        $metaItem->appType = 'boolean';
                                        break;
                                }
                            }
                        }

                        return $metaItem;
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
            foreach ($metaItem->items as $subMetaItem) {
                $subMetaItem->oldName = $subMetaItem->name; // TODO Is hotfix, but not worked for rename functional in migration
                $items[] = $subMetaItem;
            }
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

    /**
     * @param string $indent
     * @param array $import
     * @return mixed|string
     */
    public function renderJsMeta($indent = '', &$import = [])
    {
        $result = [];
        foreach ($this->meta as $metaItem) {
            $type = \Yii::$app->types->getType($metaItem->appType);
            $result[$metaItem->name] = $type->getGiiJsMetaItem($metaItem, $import);
        }

        return GiiHelper::varJsExport($result, $indent);
    }

    /**
     * @param MetaItem[] $metaItems
     * @param $useClasses
     * @return array
     */
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
                if ($key === 'name' || $key === 'oldName') {
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
            $meta[$metaItem->name] = array_merge($meta[$metaItem->name], $metaItem->getCustomProperties());
        }
        return $meta;
    }

    public function renderRules(&$useClasses = [])
    {

        $types = [];
        $lengths = [];
        foreach ($this->modelClass->metaClass->metaWithChild as $metaItem) {
            $type = \Yii::$app->types->getType($metaItem->appType);
            if (!$type) {
                continue;
            }

            $rules = $type->giiRules($metaItem, $useClasses) ?: [];
            foreach ($rules as $rule) {
                /** @var array $rule */
                $attributes = (array) ArrayHelper::remove($rule, 0);
                $name = ArrayHelper::remove($rule, 1);
                $validatorRaw = GiiHelper::varExport($name);
                if (!empty($rule)) {
                    $validatorRaw .= ', ' . substr(GiiHelper::varExport($rule, '', true), 1, -1);
                }

                foreach ($attributes as $attribute) {
                    $types[$validatorRaw][] = $attribute;

                    if ($metaItem->required) {
                        $types["'required'"][] = $metaItem->name;
                    }
                }
            }
        }

        $rules = [];
        foreach ($types as $validatorRaw => $attributes) {
            $attributesRaw = "'" . implode("', '", $attributes) . "'";
            if (count($attributes) > 1) {
                $attributesRaw = "[$attributesRaw]";
            }

            $rules[] = "[$attributesRaw, $validatorRaw]";
        }

        // Exist rules for foreign keys
        foreach ($this->modelClass->metaClass->relations as $relation) {
            if (!$relation->isHasOne) {
                continue;
            }

            $attribute = $relation->name;
            $refClassName = $relation->relationClass->name;
            $useClasses[] = $relation->relationClass->className;
            $targetAttributes = "'{$relation->selfKey}' => '{$relation->relationKey}'";

            $rules[] = "['$attribute', 'exist', 'skipOnError' => true, 'targetClass' => $refClassName::className(), 'targetAttribute' => [$targetAttributes]]";
        }

        return $rules;
    }

    public function renderBehaviors($indent = '', &$useClasses = [])
    {
        $behaviors = [];
        foreach ($this->metaWithChild as $metaItem) {
            $appType = \Yii::$app->types->getType($metaItem->appType);
            if (!$appType) {
                continue;
            }

            foreach ($appType->giiBehaviors($metaItem) as $behaviour) {
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

            if ($appType instanceof RelationType && !$appType->giiDbType($metaItem)) {
                $relation = $metaItem->metaClass->getRelation($metaItem->relationName);
                $properties[$metaItem->name] = $relation && !$relation->isHasOne ? '[]' : null;
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

    /**
     * @return string
     */
    public function getJsFilePath()
    {
        return $this->getFolderPath() . '/' . $this->getName() . '.js';
    }

}