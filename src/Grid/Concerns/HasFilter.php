<?php

namespace Dcat\Admin\Grid\Concerns;

use Closure;
use Dcat\Admin\Grid;
use Illuminate\Support\Collection;

trait HasFilter
{
    /**
     * The grid Filter.
     *
     * @var Grid\Filter
     */
    protected $filter;

    /**
     * @var array
     */
    protected $beforeApplyFilterCallbacks = [];

    /**
     * Setup grid filter.
     *
     * @return void
     */
    protected function setUpFilter()
    {
        $this->filter = new Grid\Filter($this->model());
    }

    /**
     * Process the grid filter.
     *
     * @param bool $toArray
     *
     * @return array|Collection|mixed
     */
    public function processFilter($toArray = true)
    {
        $this->callBuilder();
        $this->handleExportRequest();

        $this->fireOnce(new Grid\Events\Fetching($this));

        $this->applyQuickSearch();
        $this->applyColumnFilter();
        $this->applySelectorQuery();

        return $this->filter->execute($toArray);
    }

    /**
     * Get or set the grid filter.
     *
     * @param Closure $callback
     *
     * @return $this|Grid\Filter
     */
    public function filter(Closure $callback = null)
    {
        if ($callback === null) {
            return $this->filter;
        }

        call_user_func($callback, $this->filter);

        return $this;
    }

    /**
     * @param Closure $callback
     *
     * @return void
     */
    public function fetching(\Closure $callback)
    {
        $this->beforeApplyFilterCallbacks[] = $callback;
    }

    /**
     * @return void
     */
    protected function callFetchingCallbacks()
    {
        foreach ($this->beforeApplyFilterCallbacks as $callback) {
            $callback($this);
        }

        $this->beforeApplyFilterCallbacks = [];
    }

    /**
     * Render the grid filter.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function renderFilter()
    {
        if (! $this->options['show_filter']) {
            return '';
        }

        return $this->filter->render();
    }

    /**
     * Expand filter.
     *
     * @return $this
     */
    public function expandFilter()
    {
        $this->filter->expand();

        return $this;
    }

    /**
     * Disable grid filter.
     *
     * @return $this
     */
    public function disableFilter(bool $disable = true)
    {
        $this->filter->disableCollapse($disable);

        return $this->option('show_filter', ! $disable);
    }

    /**
     * Show grid filter.
     *
     * @param bool $val
     *
     * @return $this
     */
    public function showFilter(bool $val = true)
    {
        return $this->disableFilter(! $val);
    }

    /**
     * Disable filter button.
     *
     * @param bool $disable
     *
     * @return $this
     */
    public function disableFilterButton(bool $disable = true)
    {
        $this->tools->disableFilterButton($disable);

        return $this;
    }

    /**
     * Show filter button.
     *
     * @param bool $val
     *
     * @return $this
     */
    public function showFilterButton(bool $val = true)
    {
        return $this->disableFilterButton(! $val);
    }
}
