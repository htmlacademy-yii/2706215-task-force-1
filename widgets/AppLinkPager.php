<?php

declare(strict_types=1);

namespace app\widgets;

use yii\widgets\LinkPager;

/**
 * Applies the TaskForce markup classes to Yii pagination links.
 */
final class AppLinkPager extends LinkPager
{
    public $options = ['class' => 'pagination-list'];

    public $pageCssClass = 'pagination-item';

    public $activePageCssClass = 'pagination-item--active';

    public $prevPageCssClass = 'pagination-item mark';

    public $nextPageCssClass = 'pagination-item mark';

    public $linkOptions = ['class' => 'link link--page'];

    public $prevPageLabel = '';

    public $nextPageLabel = '';

    public $firstPageLabel = false;

    public $lastPageLabel = false;
}
