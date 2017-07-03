<?php

namespace extpoint\yii2\gii\models;

use extpoint\yii2\base\FormModel;

/**
 * @property MetaItem[] $meta
 * @property MetaItem[] $metaWithChild
 * @property string $jsFilePath
 */
class FormModelMetaClass extends FormModelClass
{
    use MetaClassTrait;

    /**
     * @var FormModelClass
     */
    public $modelClass;

    /**
     * @var MetaItem[]
     */
    private $_meta;

    /**
     * @return MetaItem[]
     */
    public function getMeta() {
        if (!$this->_meta) {
            /** @var FormModel $modelClass */
            $modelClass = $this->modelClass->className;
            $modelMeta = $modelClass::meta();
            if (!empty($modelMeta)) {
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
            }
        }
        return $this->_meta;
    }

    /**
     * @param MetaItem[] $value
     */
    public function setMeta($value) {
        $this->_meta = $value;
    }

    public function renderRules(&$useClasses = [])
    {
        return ModelMetaClass::exportRules($this->metaWithChild, [], $useClasses);
    }

    public function fields()
    {
        return [
            'className',
            'name',
            'meta',
        ];
    }

}