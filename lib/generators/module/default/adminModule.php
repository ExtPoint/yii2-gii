<?php

namespace app\views;

use extpoint\yii2\gii\generators\module\ModuleGenerator;
use yii\web\View;

/* @var $this View */
/* @var $generator ModuleGenerator */
/* @var $namespace string */
/* @var $className string */

echo "<?php\n";
?>

namespace <?= $namespace ?>;

use app\core\admin\base\AppAdminModule;

class <?= $className ?> extends AppAdminModule
{
    public function coreMenu()
    {
        return [
            'admin' => [
                'items' => [],
            ],
        ];
    }
}
