<?php

namespace extpoint\yii2\views;

use extpoint\megamenu\MegaMenuItem;
use yii\web\View;

/* @var $this View */
/* @var $items MegaMenuItem[] */

/**
 * @param MegaMenuItem[] $items
 * @param View $view
 * @param string[] $parentIds
 * @param int $index
 * @param int $level
 * @return string
 */
function renderTree($items, $view, $parentIds = [], &$index = 0, $level = 0) {
    $html = '';
    foreach ($items as $id => $megaMenuItem) {
        $ids = array_merge($parentIds, [$id]);

        $html .= $view->render('_item', [
            'megaMenuItem' => $megaMenuItem,
            'index' => $index++,
            'level' => $level,
            'id' => implode('.', $ids),
        ]);

        $html .= renderTree($megaMenuItem->items, $view, $ids, $index, $level + 1);
    }
    return $html;
}

?>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>#</th>
            <th>Id</th>
            <th>Url</th>
            <th>Label</th>
            <th>Icon</th>
            <th>Roles</th>
            <th>Visible</th>
            <th>Order</th>
            <th>Redirect to</th>
        </tr>
    </thead>
    <tbody>
        <?= renderTree($items, $this) ?>
    </tbody>
</table>
