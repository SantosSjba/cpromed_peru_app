<?php

namespace App\View\Components\common;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PageBreadcrumb extends Component
{
    /**
     * Current page title (used when $items is not provided).
     */
    public string $pageTitle;

    /**
     * Breadcrumb trail: array of ['label' => string, 'url' => string|null].
     * Last item should have url null (current page). Home is prepended automatically.
     */
    public array $items;

    /**
     * Full trail passed to the view (Home + items, or Home + pageTitle). Never null.
     */
    public array $trail;

    /**
     * Create a new component instance.
     */
    public function __construct(?string $pageTitle = 'Page', array $items = [])
    {
        $this->pageTitle = $pageTitle ?? 'Page';
        $this->items = is_array($items) ? $items : [];
        $home = [['label' => 'Home', 'url' => url('/')]];
        $this->trail = count($this->items) > 0
            ? array_merge($home, $this->items)
            : array_merge($home, [['label' => $this->pageTitle, 'url' => null]]);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.common.page-breadcrumb');
    }
}
