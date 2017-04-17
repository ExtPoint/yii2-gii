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
 */
class MetaItem extends Object implements Arrayable
{
    use ArrayableTrait;

    /**
     * @var MetaClass
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
     * @var bool
     */
    public $showInForm;

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
     * @var
     */
    public $touchOnUpdate;

    /**
     * Property of CurrencyType
     * @var
     */
    public $currency;

    /**
     * Property of CustomType
     * @var
     */
    public $dbType;

    /**
     * Property of DateTimeType and DateType
     * @var
     */
    public $format;

    /**
     * Property of EnumType
     * @var
     */
    public $enumClassName;

    /**
     * Property of ArrayType
     * @var
     */
    public $relationName;

    /**
     * Property of IntegerType
     * @var
     */
    public $isDecimal;

    /**
     * Property of StringType
     * @var
     */
    public $stringType;

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
            return '$this->' . $map[$parts[0]] . '(' . $arguments . ')' . ($this->required ? 'notNull()' : '');
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
        return $fields;
    }

}