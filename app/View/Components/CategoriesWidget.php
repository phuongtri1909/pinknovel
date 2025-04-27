<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CategoriesWidget extends Component
{
    public $categories;
    public $currentCategory;
    public $isSearch;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($categories, $currentCategory = null, $isSearch = false)
    {
        $this->categories = $categories;
        $this->currentCategory = $currentCategory;
        $this->isSearch = $isSearch;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.categories-widget');
    }
}