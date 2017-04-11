<?php

namespace extpoint\yii2\gii\generators\model;

use extpoint\yii2\gii\helpers\GiiHelper;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\gii\Generator;
use yii\helpers\ArrayHelper;

class ModelGenerator extends Generator
{
    public $moduleId;
    public $modelName;
    public $tableName;
    public $meta = [];
    public $relations = [];

    public function getName()
    {
        return 'model';
    }

    public function requiredTemplates()
    {
        return ['meta', 'migrationCreate', 'migrationUpdate'];
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        if (!is_array($this->meta)) {
            $this->meta = [];
        }
        if (!is_array($this->relations)) {
            $this->relations = [];
        }

        $moduleDir = \Yii::getAlias('@app') . '/' . str_replace('.', '/', $this->moduleId);

        // Create/update meta information
        (new CodeFile(
            $moduleDir . '/models/meta/' . ucfirst($this->modelName) . 'Meta.php',
            $this->render('meta.php', [
                'namespace' => 'app\\' . str_replace('.', '\\', $this->moduleId) . '\\models\\meta',
                'className' => ucfirst($this->modelName) . 'Meta',
                'tableName' => $this->tableName,
                'meta' => $this->meta,
                'relations' => array_map(function ($relation) {
                    $relation['model'] = GiiHelper::getModelByClass($relation['relationModelClassName']);
                    return $relation;
                }, $this->relations),
            ])
        ))->save();
        \Yii::$app->session->addFlash('success', 'Мета информция модели ' . ucfirst($this->modelName) . ' обновлена');

        // Create model, if not exists
        $modelFilePath = $moduleDir . '/models/' . ucfirst($this->modelName) . '.php';
        if (!file_exists($modelFilePath)) {
            (new CodeFile(
                $moduleDir . '/models/' . ucfirst($this->modelName) . '.php',
                $this->render('model.php', [
                    'namespace' => 'app\\' . str_replace('.', '\\', $this->moduleId) . '\\models',
                    'className' => ucfirst($this->modelName),
                    'tableName' => $this->tableName,
                    'meta' => $this->meta,
                ])
            ))->save();
            \Yii::$app->session->addFlash('success', 'Добавлена модель ' . ucfirst($this->modelName));
        }

        // Create migration
        $tablesToCreate = [];
        $addColumn = [];
        $alterColumn = [];
        $alterColumnDown = [];
        $dropColumn = [];
        $foreignKeys = [];

        // Create table in migration
        $oldModel = GiiHelper::getModel($this->moduleId, $this->modelName);
        $oldMeta = $oldModel ? ArrayHelper::index($oldModel['meta'], 'name') : null;
        $oldRelationNames = $oldModel ? ArrayHelper::getColumn($oldModel['relations'], 'name') : [];
        if (!$oldMeta) {
            $columns = [];
            foreach ($this->meta as $item) {
                $columns[$item['name']] = $this->getColumnType($item);
            }
            $tablesToCreate[] = [
                'table' => $this->tableName,
                'columns' => $columns,
            ];
        } else {
            // Find changes
            foreach ($this->meta as $item) {
                if (!isset($oldMeta[$item['name']])) {
                    $addColumn[] = $item;
                } else if ($this->getColumnType($item) !== $this->getColumnType($oldMeta[$item['name']])) {
                    $alterColumn[] = $item;
                    $alterColumnDown[] = $oldMeta[$item['name']];
                }
            }

            $metaNames = ArrayHelper::getColumn($this->meta, 'name');
            foreach ($oldMeta as $oldItem) {
                if (!in_array($oldItem['name'], $metaNames)) {
                    $dropColumn[] = $oldItem;
                }
            }
        }

        // Junction table in migration
        foreach ($this->relations as $relation) {
            if (!empty($relation['viaTable']) && \Yii::$app->db->getTableSchema($relation['viaTable']) === null && !in_array($relation['name'], $oldRelationNames)) {
                $relationModel = GiiHelper::getModelByClass($relation['relationModelClassName']);
                $relationKeyType = isset($relationModel['meta'][$relation['viaRelationKey']]) && !empty($relationModel['meta'][$relation['viaRelationKey']]['dbType'])
                    ? static::parseDbType($relationModel['meta'][$relation['viaRelationKey']]['dbType'])[0]
                    : 'integer';
                $selfKeyType = isset($this->meta[$relation['viaSelfKey']]) && !empty($this->meta[$relation['viaSelfKey']]['dbType'])
                    ? static::parseDbType($this->meta[$relation['viaSelfKey']]['dbType'])[0]
                    : 'integer';

                $tablesToCreate[] = [
                    'table' => $relation['viaTable'],
                    'columns' => [
                        $relation['viaRelationKey'] => $relationKeyType . ' NOT NULL',
                        $relation['viaSelfKey'] => $selfKeyType . ' NOT NULL',
                    ],
                ];
            }
        }

        // Foreign keys
        foreach ($this->relations as $relation) {
            // Skip primary key
            if ($relation['selfKey'] === 'id') {
                continue;
            }

            // Only hasOne relation
            if ($relation['type'] !== 'hasOne') {
                continue;
            }

            // Check exists
            if (in_array($relation['name'], $oldRelationNames)) {
                continue;
            }

            $foreignKeys[] = [
                'table' => $this->tableName,
                'key' => $relation['selfKey'],
                'refTable' => GiiHelper::getModelByClass($relation['relationModelClassName'])['tableName'],
                'refKey' => $relation['relationKey'],
            ];
        }

        // Create migration
        if (!empty($addColumn) || !empty($alterColumn) || !empty($alterColumnDown) || !empty($dropColumn) || !empty($tablesToCreate) || !empty($foreignKeys)) {
            $migrationClassName = 'm' . gmdate('ymd_His') . '_upd_' . $this->tableName;
            (new CodeFile(
                $moduleDir . '/migrations/' . $migrationClassName . '.php',
                $this->render('migration.php', [
                    'className' => $migrationClassName,
                    'tableName' => $this->tableName,
                    'meta' => $this->meta,
                    'addColumn' => $addColumn,
                    'alterColumn' => $alterColumn,
                    'alterColumnDown' => $alterColumnDown,
                    'dropColumn' => $dropColumn,
                    'tablesToCreate' => $tablesToCreate,
                    'foreignKeys' => $foreignKeys,
                ])
            ))->save();
            \Yii::$app->session->addFlash('success', 'Добавлена миграция ' . $migrationClassName);
        }

    }

    /**
     * @param $dbType
     * @return string
     */
    public function getPhpDocType($dbType)
    {
        $type = static::parseDbType($dbType)[0];
        static $typeMap = [
            'bigint' => 'integer',
            'integer' => 'integer',
            'smallint' => 'integer',
            'boolean' => 'boolean',
            'float' => 'double',
            'double' => 'double',
            'binary' => 'resource',
        ];
        return isset($typeMap[$type]) ? $typeMap[$type] : 'string';
    }

    public function exportMeta($indent = '')
    {
        $meta = [];
        foreach ($this->meta as $metaItem) {
            $meta[$metaItem['name']] = [];
            foreach ($metaItem as $key => $value) {
                if ($key !== 'name' && $value !== '') {
                    $meta[$metaItem['name']][$key] = $value;
                }
            }
        }
        return GiiHelper::varExport($meta, $indent);
    }

    public function getColumnType($item)
    {
        $calls = ['$this'];
        if (!empty($item['dbType'])) {
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
            $params = static::parseDbType($item['dbType']);
            if (!$params) {
                return $item['dbType'] . (!empty($item['notNull']) ? ' NOT NULL' : '');
            }
            $calls[] = $map[$params[0]] . '(' . (count($params) > 1 ? implode(', ', array_slice($params, 1)) : '') . ')';
        } else {
            $calls[] = 'string()';
        }

        if (!empty($item['notNull']) && (empty($item['dbType']) || $item['dbType'] !== 'pk')) {
            $calls[] = 'notNull()';
        }

        return implode('->', $calls);
    }

    public function exportRules(&$useClasses = [])
    {
        $types = [];
        $lengths = [];
        foreach ($this->meta as $item) {
            if ($item['dbType'] === 'pk') {
                continue;
            }
            if (!empty($item['notNull'])) {
                $types['required'][] = $item['name'];
            }
            switch (static::parseDbType($item['dbType'])[0]) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    $types['integer'][] = $item['name'];
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $item['name'];
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $item['name'];
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $types['safe'][] = $item['name'];
                    break;
                default: // strings
                    // TODO size
                    //if ($column->size > 0) {
                    //    $lengths[$column->size][] = $item['name'];
                    //} else {
                    $types['string'][] = $item['name'];
                //}
            }
        }
        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }
        foreach ($lengths as $length => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], 'string', 'max' => $length]";
        }

        /*$db = $this->getDbConnection();

        // Unique indexes rules
        try {
            $uniqueIndexes = $db->getSchema()->findUniqueIndexes($table);
            foreach ($uniqueIndexes as $uniqueColumns) {
                // Avoid validating auto incremental columns
                if (!$this->isColumnAutoIncremental($table, $uniqueColumns)) {
                    $attributesCount = count($uniqueColumns);

                    if ($attributesCount === 1) {
                        $rules[] = "[['" . $uniqueColumns[0] . "'], 'unique']";
                    } elseif ($attributesCount > 1) {
                        $labels = array_intersect_key($this->generateLabels($table), array_flip($uniqueColumns));
                        $lastLabel = array_pop($labels);
                        $columnsList = implode("', '", $uniqueColumns);
                        $rules[] = "[['$columnsList'], 'unique', 'targetAttribute' => ['$columnsList'], 'message' => 'The combination of " . implode(', ', $labels) . " and $lastLabel has already been taken.']";
                    }
                }
            }
        } catch (NotSupportedException $e) {
            // doesn't support unique indexes information...do nothing
        }*/

        // Exist rules for foreign keys
        foreach ($this->relations as $relation) {
            if ($relation['type'] !== 'hasOne') {
                continue;
            }
            $attribute = $relation['name'];
            $refClassName = GiiHelper::getModelByClass($relation['relationModelClassName'])['name'];
            $useClasses[] = $relation['relationModelClassName'];
            $targetAttributes = "'{$relation['selfKey']}' => '{$relation['relationKey']}'";
            $rules[] = "['$attribute', 'exist', 'skipOnError' => true, 'targetClass' => $refClassName::className(), 'targetAttribute' => [$targetAttributes]]";
        }

        return $rules;
    }

    public static function parseDbType($dbType)
    {
        return preg_match('/^([^(]+)(\(([^)]+)\))?$/', $dbType, $matches)
            ? count($matches) > 2 ? [$matches[1], $matches[3]] : [$matches[1]]
            : null;
    }

}