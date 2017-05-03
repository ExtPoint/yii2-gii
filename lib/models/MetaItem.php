<?php

namespace extpoint\yii2\gii\models;

use extpoint\yii2\gii\helpers\GiiHelper;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * @property-read string $dbType
 * @property-read string $parsedDbType
 * @property-read string $phpDocType
 * @property-read MetaItem[] $items
 */
class MetaItem extends Object implements Arrayable
{
    use ArrayableTrait;

    /**
     * @var ModelMetaClass
     */
    public $metaClass;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $hint;

    /**
     * @var string
     */
    public $appType = 'string';

    /**
     * @var bool
     */
    public $required;

    /**
     * @var int|string
     */
    public $defaultValue;

    /**
     * @var bool
     */
    public $showInForm;

    /**
     * @var bool
     */
    public $showInFilter;

    /**
     * @var bool
     */
    public $showInTable;

    /**
     * @var bool
     */
    public $showInView;

    /**
     * Property of AutoTimeType
     * @var bool
     */
    public $touchOnUpdate;

    /**
     * Property of MoneyType
     * @var string
     */
    public $currency;

    /**
     * Property of CustomType
     * @var string
     */
    public $dbType;

    /**
     * Property of DateTimeType and DateType
     * @var string
     */
    public $format;

    /**
     * Property of EnumType
     * @var string
     */
    public $enumClassName;

    /**
     * Property of ArrayType
     * @var string
     */
    public $relationName;

    /**
     * Property of IntegerType
     * @var bool
     */
    public $isDecimal;

    /**
     * Property of StringType
     * @var string
     */
    public $stringType;

    /**
     * Property of StringType
     * @var integer
     */
    public $stringLength;

    /**
     * @var string
     */
    public $subAppType;

    /**
     * @var string
     */
    public $refAttribute;

    /**
     * @param string $dbType
     * @return array|null
     */
    public static function parseDbType($dbType)
    {
        return preg_match('/^([^(]+)(\(([^)]+)\))?/', $dbType, $matches)
            ? count($matches) > 2 ? [$matches[1], $matches[3]] : [$matches[1]]
            : null;
    }

    /**
     * Formats:
     *  - string
     *  - string NOT NULL
     *  - string(32)
     *  - varchar(255) NOT NULL
     * @return string|null
     */
    public function getDbType()
    {
        if (!$this->appType) {
            return 'string';
        }
        return $this->dbType ?: \Yii::$app->types->getType($this->appType)->getGiiDbType($this);
    }

    /**
     * @return array
     */
    public function getParsedDbType()
    {
        $dbType = $this->getDbType();
        return $dbType ? self::parseDbType($dbType) : ['string'];
    }

    public function getItems() {
        return $this->appType ? \Yii::$app->types->getType($this->appType)->getItems($this) : [];
    }

    public function renderMigrationColumnType()
    {
        $map = [
            'pk' => 'primaryKey',
            'bigpk' => 'bigPrimaryKey',
            'char' => 'char',
            'string' => 'string',
            'text' => 'text',
            'smallint' => 'smallInteger',
            'integer' => 'integer',
            'bigint' => 'bigInteger',
            'float' => 'float',
            'double' => 'double',
            'decimal' => 'decimal',
            'datetime' => 'dateTime',
            'timestamp' => 'timestamp',
            'time' => 'time',
            'date' => 'date',
            'binary' => 'binary',
            'boolean' => 'boolean',
            'money' => 'money',
        ];
        $dbType = $this->getDbType() ?: 'string';
        $parts = self::parseDbType($dbType);

        if (isset($map[$parts[0]])) {
            $arguments = count($parts) > 1 ? implode(', ', array_slice($parts, 1)) : '';
            $notNull = $this->required ? '->notNull()' : '';
            $defaultValue = $this->defaultValue !== null && $this->defaultValue !== ''
                ? '->defaultValue(' . (preg_match('/^[0-9]+$/', $this->defaultValue) ? $this->defaultValue : "'" . $this->defaultValue . "'") . ')'
                : '';

            return '$this->' . $map[$parts[0]] . '(' . $arguments . ')' . $notNull . $defaultValue;
        } else {
            return "'$dbType'";
        }
    }

    /**
     * @return string
     */
    public function getPhpDocType()
    {
        static $typeMap = [
            'bigint' => 'integer',
            'integer' => 'integer',
            'smallint' => 'integer',
            'boolean' => 'boolean',
            'float' => 'double',
            'double' => 'double',
            'binary' => 'resource',
        ];
        $type = $this->getParsedDbType()[0];
        return isset($typeMap[$type]) ? $typeMap[$type] : 'string';
    }

    public function fields() {
        $classInfo = new \ReflectionClass($this);
        $fields = [];
        foreach ($classInfo->getProperties() as $property) {
            if ($property->isPublic() && $property->class === static::className() && $property->getName() !== 'metaClass') {
                $fields[] = $property->getName();
            }
        }
        $fields[] = 'items';
        return $fields;
    }

}