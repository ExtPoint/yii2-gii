<?php

namespace extpoint\yii2\gii\models;

use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * @property-read ModuleClass $moduleClass
 */
class MigrationMethods extends Object
{
    const MIGRATE_MODE_CREATE = 'create';
    const MIGRATE_MODE_UPDATE = 'update';

    /**
     * @var ModelClass
     */
    public $oldModelClass;

    /**
     * @var ModelClass
     */
    public $modelClass;

    /**
     * One of value: create, update, none
     * @var string
     */
    public $migrateMode;

    /**
     * @var MetaItem[]
     */
    public $createTable = [];

    /**
     * @var MetaItem[]
     */
    public $addColumn = [];

    /**
     * @var MetaItem[]
     */
    public $alterColumn = [];

    /**
     * @var MetaItem[]
     */
    public $alterColumnDown = [];

    /**
     * @var MetaItem[]
     */
    public $renameColumn = [];

    /**
     * @var MetaItem[]
     */
    public $dropColumn = [];

    /**
     * @var array
     */
    public $junctionTables = [];

    /**
     * @var Relation[]
     */
    public $foreignKeys = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!$this->oldModelClass || $this->migrateMode === self::MIGRATE_MODE_CREATE) {
            $this->processCreateTable();
        } else if ($this->migrateMode === self::MIGRATE_MODE_UPDATE) {
            $this->processAddColumn();
            $this->processUpdateColumn();
            $this->processDropColumn();
            $this->processJunction();
            $this->processForeignKeys();
        }
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->createTable) && empty($this->addColumn) && empty($this->alterColumn)
            && empty($this->alterColumn) && empty($this->alterColumnDown) && empty($this->renameColumn)
            && empty($this->dropColumn) && empty($this->foreignKeys);
    }

    /**
     * @return string
     */
    public function generateName()
    {
        $parts = [];

        if (!$this->oldModelClass) {
            $parts[] = 'create';
            $parts[] = $this->modelClass->tableName;
        } else {
            $parts[] = $this->modelClass->tableName;

            $metaItems = [];
            if (!empty($this->addColumn)) {
                $parts[] = 'add';
                $metaItems = $this->addColumn;
            } else if (!empty($this->alterColumn)) {
                $parts[] = 'upd';
                $metaItems = $this->alterColumn;
            } else if (!empty($this->renameColumn)) {
                $parts[] = 'rename';
                $metaItems = $this->renameColumn;
            } else if (!empty($this->dropColumn)) {
                $parts[] = 'drop';
                $metaItems = $this->dropColumn;
            }
            foreach ($metaItems as $metaItem) {
                $parts[] = $metaItem->name;
            }

            if (!empty($this->junctionTables)) {
                $parts[] = 'junction';
                foreach ($this->junctionTables as $junction) {
                    $parts[] = $junction['table'];
                }
            }

            if (!empty($this->foreignKeys)) {
                $parts[] = 'fk';
                foreach ($this->foreignKeys as $relation) {
                    $parts[] = $relation->name;
                }
            }
        }
        return 'M' . gmdate('ymdHis') . '_' . ucfirst(implode('_', array_slice($parts, 0, 6)));
    }

    protected function processCreateTable()
    {
        foreach ($this->modelClass->metaClass->meta as $metaItem) {
            if ($metaItem->getDbType()) {
                $this->createTable[] = $metaItem;
            }
        }
    }

    protected function processAddColumn()
    {
        $oldMetaNames = ArrayHelper::getColumn($this->oldModelClass->metaClass->meta, 'name');
        foreach ($this->modelClass->metaClass->meta as $metaItem) {
            if (!in_array($metaItem->name, $oldMetaNames) && $metaItem->getDbType()) {
                $this->addColumn[] = $metaItem;
            }
        }
    }

    protected function processUpdateColumn()
    {
        /** @var MetaItem $oldMeta [] */
        $oldMeta = ArrayHelper::index($this->oldModelClass->metaClass->meta, 'name');
        foreach ($this->modelClass->metaClass->meta as $metaItem) {
            if (!isset($oldMeta[$metaItem->name])) {
                continue;
            }

            // TODO renameColumn

            /** @var MetaItem $oldMetaItem */
            $oldMetaItem = $oldMeta[$metaItem->name];
            if ($oldMetaItem->renderMigrationColumnType() !== $metaItem->renderMigrationColumnType()) {
                $this->alterColumn[] = $metaItem;
                $this->alterColumnDown[] = $oldMetaItem;
            }
        }
    }

    protected function processDropColumn()
    {
        $metaNames = ArrayHelper::getColumn($this->modelClass->metaClass->meta, 'name');
        foreach ($this->oldModelClass->metaClass->meta as $oldMetaItem) {
            if (!in_array($oldMetaItem->name, $metaNames)) {
                $this->dropColumn[] = $oldMetaItem;
            }
        }
    }

    protected function processJunction()
    {
        $oldRelationNames = ArrayHelper::getColumn($this->oldModelClass->metaClass->relations, 'name');
        foreach ($this->modelClass->metaClass->relations as $relation) {
            if (!$relation->viaTable || in_array($relation->name, $oldRelationNames)) {
                continue;
            }

            $this->junctionTables[] = [
                'table' => $relation->viaTable,
                'columns' => [
                    $relation->viaRelationKey => $relation->viaRelationMetaItem
                        ? $relation->viaRelationMetaItem->renderMigrationColumnType()
                        : 'integer NOT NULL',
                    $relation->viaSelfKey => $relation->viaSelfMetaItem
                        ? $relation->viaSelfMetaItem->renderMigrationColumnType()
                        : 'integer NOT NULL',
                ],
            ];
        }
    }

    protected function processForeignKeys()
    {
        $oldRelationNames = ArrayHelper::getColumn($this->oldModelClass->metaClass->relations, 'name');
        foreach ($this->modelClass->metaClass->relations as $relation) {
            if ($relation->selfKey === 'id' || !$relation->isHasOne || in_array($relation->name, $oldRelationNames)) {
                continue;
            }

            $this->foreignKeys[] = $relation;
        }
    }

}