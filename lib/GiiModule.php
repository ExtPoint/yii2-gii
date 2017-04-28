<?php

namespace extpoint\yii2\gii;

use extpoint\yii2\base\Module;
use extpoint\yii2\gii\controllers\GiiController;
use extpoint\yii2\gii\controllers\SiteMapController;

class GiiModule extends Module
{
    public $layout = '@app/core/admin/layouts/web';

    /**
     * @var integer the permission to be set for newly generated code files.
     * This value will be used by PHP chmod function.
     * Defaults to 0666, meaning the file is read-writable by all users.
     */
    public $newFileMode = 0666;

    /**
     * @var integer the permission to be set for newly generated directories.
     * This value will be used by PHP chmod function.
     * Defaults to 0777, meaning the directory can be read, written and executed by all users.
     */
    public $newDirMode = 0777;

    public function coreMenu()
    {
        return [
            'admin' => [
                'items' => array_merge(
                    GiiController::coreMenuItems(),
                    SiteMapController::coreMenuItems()
                )
            ],
        ];
    }
}