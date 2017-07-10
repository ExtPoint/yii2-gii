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
trait MetaClassTrait
{
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

    /**
     * @param string $indent
     * @param array $useClasses
     * @return mixed|string
     */
    public function renderMeta($indent = '', &$useClasses = [])
    {
        return GiiHelper::varExport(static::exportMeta($this->meta, $useClasses), $indent);
    }

    /**
     * @param string $indent
     * @param array $import
     * @return mixed|string
     */
    public function renderJsMeta($indent = '', &$import = [])
    {
        $result = [];
        foreach (static::exportMeta($this->meta) as $name => $item) {
            $metaItem = $this->getMetaItem($name);
            $type = \Yii::$app->types->getType($metaItem->appType);
            $result[$metaItem->name] = $type->getGiiJsMetaItem($metaItem, $item, $import);
        }

        return GiiHelper::varJsExport($result, $indent);
    }

    /**
     * @param MetaItem[] $metaItems
     * @param $useClasses
     * @return array
     */
    public static function exportMeta($metaItems, &$useClasses = [])
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
                    $value = static::exportMeta($value, $useClasses);
                }

                $meta[$metaItem->name][$key] = $value;
            }
            $meta[$metaItem->name] = array_merge($meta[$metaItem->name], $metaItem->getCustomProperties());
        }
        return $meta;
    }

    public function renderRules(&$useClasses = [])
    {
        return static::exportRules($this->metaWithChild, $this->relations, $useClasses);
    }

    public static function exportRules($metaItems, $relations, &$useClasses = [])
    {
        $types = [];
        foreach ($metaItems as $metaItem) {
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
                }
            }

            if ($metaItem->required) {
                $types["'required'"][] = $metaItem->name;
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
        foreach ($relations as $relation) {
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
        return static::exportBehaviors($this->metaWithChild, $indent, $useClasses);
    }

    public static function exportBehaviors($metaItems, $indent = '', &$useClasses = [])
    {
        $behaviors = [];
        foreach ($metaItems as $metaItem) {
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

            if ($appType instanceof RelationType && $metaItem->metaClass instanceof ModelMetaClass && !$appType->giiDbType($metaItem)) {
                $relation = $metaItem->metaClass->getRelation($metaItem->relationName);
                $properties[$metaItem->name] = $relation && !$relation->isHasOne ? '[]' : null;
            }
        }
        return $properties;
    }

    /**
     * @return string
     */
    public function getJsFilePath()
    {
        return $this->getFolderPath() . '/' . $this->getName() . '.js';
    }

}