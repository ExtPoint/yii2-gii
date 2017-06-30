<?php

namespace extpoint\yii2\gii\models;

use extpoint\yii2\base\Enum;
use extpoint\yii2\gii\helpers\GiiHelper;

/**
 * @property EnumMetaItem[] $meta
 * @property string $jsFilePath
 */
class EnumMetaClass extends EnumClass
{
    /**
     * @var EnumClass
     */
    public $enumClass;

    /**
     * @var EnumMetaItem[]
     */
    private $_meta;

    /**
     * @return EnumMetaItem[]
     */
    public function getMeta() {
        if (!$this->_meta) {
            /** @var Enum $moduleClass */
            $moduleClass = $this->enumClass->className;

            $this->_meta = [];
            $cssClasses = $moduleClass::getCssClasses();

            foreach ($moduleClass::getLabels() as $name => $label) {
                $this->_meta[] = new EnumMetaItem([
                    'name' => $name,
                    'label' => $label,
                    'cssClass' => isset($cssClasses[$name]) ? $cssClasses[$name] : '',
                ]);
            }
        }
        return $this->_meta;
    }

    /**
     * @param EnumMetaItem[] $value
     */
    public function setMeta($value) {
        $this->_meta = $value;
    }

    /**
     * @param string $indent
     * @return mixed|string
     */
    public function renderLabels($indent = '') {
        $labels = [];
        foreach ($this->meta as $enumMetaItem) {
            $labels[$enumMetaItem->name] = $enumMetaItem->label;
        }
        return GiiHelper::varExport($labels, $indent);
    }

    /**
     * @param string $indent
     * @return mixed|string
     */
    public function renderJsLabels($indent = '') {
        $lines = [];
        foreach ($this->meta as $enumMetaItem) {
            $lines[] = $indent . '    [this.' . strtoupper($enumMetaItem->name) . ']: '
                . '\'' . str_replace("'", "\\'", $enumMetaItem->label) . '\',';
        }
        return "{\n" . implode("\n", $lines) . "\n" . $indent . '}';
    }

    /**
     * @param string $indent
     * @return mixed|string
     */
    public function renderCssClasses($indent = '') {
        $cssClasses = [];
        foreach ($this->meta as $enumMetaItem) {
            if ($enumMetaItem->cssClass) {
                $cssClasses[$enumMetaItem->name] = $enumMetaItem->cssClass;
            }
        }
        return !empty($cssClasses) ? GiiHelper::varExport($cssClasses, $indent) : '';
    }

    /**
     * @param string $indent
     * @return mixed|string
     */
    public function renderJsCssClasses($indent = '') {
        $lines = [];
        foreach ($this->meta as $enumMetaItem) {
            if ($enumMetaItem->cssClass) {
                $lines[] = $indent . '    [this.' . strtoupper($enumMetaItem->name) . ']: '
                    . '\'' . str_replace("'", "\\'", $enumMetaItem->cssClass) . '\',';
            }
        }
        return !empty($lines) ? "{\n" . implode("\n", $lines) . "\n" . $indent . '}' : '';
    }

    public function fields()
    {
        return [
            'className',
            'name',
            'meta',
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