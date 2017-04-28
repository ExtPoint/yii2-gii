<?php

namespace extpoint\yii2\views;

use extpoint\megamenu\MegaMenuItem;
use extpoint\yii2\gii\helpers\GiiHelper;

/* @var $this \yii\web\View */
/* @var $megaMenuItem MegaMenuItem */
/* @var $index int */
/* @var $level int */
/* @var $id string */

?>

<tr>
    <td><?= $index + 1 ?></td>
    <td>
        <code>
            <?= $id ?>
        </code>
    </td>
    <td>
        <code>
            <?= $megaMenuItem->url ? GiiHelper::varExport($megaMenuItem->url) : '' ?>
        </code>
    </td>
    <td>
            <?= str_repeat('—', $level) ?>
        <?= $megaMenuItem->label ?>
    </td>
    <td>
        <?= $megaMenuItem->icon ?>
    </td>
    <td>
        <?= implode(', ', (array) $megaMenuItem->roles) ?>
    </td>
    <td class="<?= $megaMenuItem->visible ? 'text-success' : 'text-danger' ?>">
        <?= \Yii::$app->formatter->asBoolean($megaMenuItem->visible) ?>
    </td>
    <td>
        <?= \Yii::$app->formatter->asInteger($megaMenuItem->order) ?>
    </td>
    <td>
        <?= $megaMenuItem->redirectToChild
            ? ($megaMenuItem->redirectToChild === true ? 'child' : GiiHelper::varExport($megaMenuItem->redirectToChild))
            : '' ?>
    </td>
</tr>