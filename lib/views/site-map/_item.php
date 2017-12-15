<?php

namespace extpoint\yii2\views;

use extpoint\megamenu\MegaMenuItem;
use extpoint\yii2\gii\helpers\GiiHelper;

/* @var $this \yii\web\View */
/* @var $megaMenuItem MegaMenuItem */
/* @var $index int */
/* @var $level int */
/* @var $id string */
/* @var $active bool */

?>

<tr <?= $active ? 'class="info"' : '' ?>>
    <td><?= $index + 1 ?></td>
    <td>
        <code>
            <?= $id ?>
        </code>
    </td>
    <td>
        <code>
            <?= $megaMenuItem->url ? str_replace('0 => ', '', GiiHelper::varExport($megaMenuItem->normalizedUrl)) : '' ?>
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
        <?php
        $access = [];
        foreach (array_merge((array) $megaMenuItem->roles, (array) $megaMenuItem->accessCheck) as $accessItem) {
            foreach ((array)$accessItem as $accessSubItem) {
                $access[] = is_callable($accessSubItem) ? 'func()' : (string) $accessSubItem;
            }
        }
        ?>
        <?= implode(', ', $access) ?>
    </td>
    <td class="<?= $megaMenuItem->getVisible() ? 'text-success' : 'text-danger' ?>">
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