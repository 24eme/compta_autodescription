<?php

namespace Controllers;

use Base;

abstract class Controller
{
    public function __construct(Base $f3)
    {
        $f3->set('mainCssClass', ['col']);
        $f3->set('displaySideNavigation', 'd-none');
    }

    public function beforeroute(Base $f3, $params)
    {
        $filters = $f3->get('GET.filters') ?? [];
        $f3->set('filters', $filters);
    }

    public function afterroute($f3, $params) {}
}
