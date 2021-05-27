<?php


namespace MicroweberPackages\Blog;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use MicroweberPackages\Category\Models\Category;
use MicroweberPackages\CustomField\Models\CustomField;
use MicroweberPackages\CustomField\Models\CustomFieldValue;
use MicroweberPackages\Page\Models\Page;

class FrontendFilter
{

    public $allCustomFieldsForResults = [];
    //public $allCategoriesForResults = [];
    public $allTagsForResults = [];

    public $params = array();
    public $queryParams = array();
    protected $pagination;
    protected $query;
    protected $model;

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }

    public function pagination($theme = false)
    {
        //$filteringTheResults = get_option('filtering_the_results', $this->params['moduleId']);

        return $this->pagination->links($theme);
    }

    public function total()
    {
        return $this->pagination->total();
    }

    public function count()
    {
        return $this->pagination->count();
    }

    public function items()
    {
        return $this->pagination->items();
    }

    public function sort($template = false)
    {
        return false;
        $sortTheResults = get_option('sort_the_results', $this->params['moduleId']);
        if (!$sortTheResults) {
            return false;
        }

        if (!isset($this->model->sortable)) {
            return false;
        }

        $options = [];

        $fullUrl = URL::current();
        $request = $this->getRequest();

        $directions = [
          'desc'=>'NEWEST',
          'asc'=>'OLDEST',
        ];

        foreach($this->model->sortable as $field) {
            foreach($directions as $direction=>$directionName) {

                $isActive = 0;
                if (($request->get('order') == $direction) && $request->get('sort') == $field) {
                    $isActive = 1;
                }

                $buildLink = $this->queryParams;
                $buildLink['sort'] = $field;
                $buildLink['order'] = $direction;
                $buildLink = http_build_query($buildLink);

                $pageSort = new \stdClass;
                $pageSort->active = $isActive;
                $pageSort->link = $fullUrl . '?' . $buildLink;
                $pageSort->name = '' . $field .' '. $directionName;

                $options[] = $pageSort;
            }
        }

        return view($template,compact('options'));
    }

    public function categories($template = false)
    {
        return false;
        $show = get_option('filtering_by_categories', $this->params['moduleId']);
        if (!$show) {
            return false;
        }

        $categoryQuery = Category::query();
        $categoryQuery->where('rel_id', $this->getMainPageId());

        $categories = $categoryQuery->where('parent_id',0)->get();

        return view($template, compact('categories'));
    }

    public function tags($template = false)
    {

        return false;

        $show = get_option('filtering_by_tags', $this->params['moduleId']);
        if (!$show) {
            return false;
        }

        $tags = [];

        $fullUrl = URL::current();
        $request = $this->getRequest();
        $category = $request->get('category');

       foreach ($this->allTagsForResults as $tag) {
            $buildLink = [];
            if (!empty($category)) {
                $buildLink['category'] = $category;
            }
            $buildLink['tags'] = $tag->slug;
            $buildLink = http_build_query($buildLink);

            $tag->url = $fullUrl .'?'. $buildLink;
            $tags[$tag->slug] = $tag;
        }

        return view($template, compact('tags'));
    }

    public function limit($template = false)
    {
        return false;

        $limitTheResults = get_option('limit_the_results', $this->params['moduleId']);
        if (!$limitTheResults) {
            return false;
        }

        $options =[];

        $pageLimits = [
            1,
            2,
            3,
            4,
            5,
        ];

        $fullUrl = URL::current();
        $request = $this->getRequest();

        foreach ($pageLimits as $limit) {

            $buildLink = $this->queryParams;
            $buildLink['limit'] = $limit;
            $buildLink = http_build_query($buildLink);

            $isActive = 0;
            if ($request->get('limit') == $limit) {
                $isActive = 1;
            }

            $pageLimit = new \stdClass;
            $pageLimit->active = $isActive;
            $pageLimit->link = $fullUrl .'?'. $buildLink;
            $pageLimit->name = $limit;

            $options[] = $pageLimit;
        }

        return view($template, compact('options'));
    }

    public function search($template = false)
    {
        $fullUrl = URL::current();

        $searchUri = $this->queryParams;
        $searchUri['search'] = '';
        $searchUri = $fullUrl . '?'. http_build_query($searchUri);

        $search = $this->getRequest()->get('search', false);

        return view($template, compact('searchUri', 'search'));
    }

    public function results()
    {
        return $this->pagination->items();
    }

    public function getMainPageId()
    {
        $contentFromId = get_option('content_from_id', $this->params['moduleId']);
        if ($contentFromId) {
            return $contentFromId;
        }

        $findFirtBlog = Page::where('content_type', 'page')
            ->where('subtype','dynamic')
            ->where('is_shop', 0)
            ->first();

        if ($findFirtBlog) {
            return $findFirtBlog->id;
        }

        return 0;
    }



    public function buildFilter()
    {
        $query = $this->model::query();
        $query->select(['id']);

        // $query->with('tagged');
        $query->where('parent', $this->getMainPageId());

        $query->with('customField', function ($query) {
            $query->with('fieldValue',function ($query) {
                $query->whereNotNull('value');
                $query->groupBy('value');
            });
        });

        $results = $query->get();

        if (!empty($results)) {
            foreach ($results as $result) {

                $resultCustomFields = $result->customField;

                if (!empty($resultCustomFields)) {
                    foreach ($resultCustomFields as $resultCustomField) {

                        $customFieldOptionName = 'filtering_by_custom_fields_' . $resultCustomField->name_key;
                        if (get_option($customFieldOptionName, $this->params['moduleId']) != '1') {
                            continue;
                        }

                        $customFieldValues = $resultCustomField->fieldValue;

                        if (!empty($customFieldValues)) {
                            $this->allCustomFieldsForResults[$resultCustomField->id] = [
                                'customField'=>$resultCustomField,
                                'customFieldValues'=>$customFieldValues,
                            ];
                        }
                    }
                }
            }
        }

    }

    public function filters($template = false)
    {

       // return false;

        $show = get_option('filtering_by_custom_fields', $this->params['moduleId']);
        if (!$show) {
            return false;
        }

        $requestFilters = $this->getRequest()->get('filters', false);

        $filters = [];

        if (!empty($this->allCustomFieldsForResults)) {
            $filterOptions = [];
            foreach ($this->allCustomFieldsForResults as $result) {
                foreach ($result['customFieldValues'] as $customFieldValue) {

                    $filterOption = new \stdClass();
                    $filterOption->active = 0;

                    // Mark as active
                    if (!empty($requestFilters)) {
                        foreach ($requestFilters as $requestFilterKey => $requestFilterValues) {
                            if (is_array($requestFilterValues)) {
                                if ($requestFilterKey == $result['customField']->name_key) {
                                    foreach ($requestFilterValues as $requestFilterValue) {
                                        if ($requestFilterValue == $customFieldValue->value) {
                                            $filterOption->active = 1;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $filterOption->id = $customFieldValue->id;
                    $filterOption->value = $customFieldValue->value;
                    $filterOptions[$result['customField']->name_key][$customFieldValue->value] = $filterOption;
                }
            }
            foreach ($this->allCustomFieldsForResults as $result) {
                if (isset($filterOptions[$result['customField']->name_key])) {

                    $readyFilterOptions = $filterOptions[$result['customField']->name_key];

                    $controlType = get_option('filtering_by_custom_fields_control_type_' . $result['customField']->name_key, $this->params['moduleId']);
                    if (empty($controlType)) {
                        $controlType = 'checkbox';
                    }

                    $filter = new \stdClass();
                    $filter->type = $result['customField']->type;
                    $filter->controlType = $controlType;
                    $filter->name = $result['customField']->name;
                    $filter->options = $readyFilterOptions;

                    if ($result['customField']->type == 'price') {

                        $allPrices = [];
                        foreach($readyFilterOptions as $priceVal=>$priceOption) {
                            $allPrices[] = $priceVal;
                        }

                        $minPrice = 0;
                        $maxPrice = 0;
                        if (isset($allPrices[0])) {
                            $sortedPrices = [];
                            asort($allPrices, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
                            foreach($allPrices as $sortPrice) {
                                $sortedPrices[] = $sortPrice;
                            }
                            $minPrice = $sortedPrices[0];
                            $maxPrice = end($sortedPrices);
                        }

                        $filter->minPrice = round($minPrice);
                        $filter->maxPrice = round($maxPrice);
                    }

                    $filters[$result['customField']->name_key] = $filter;
                }
            }
        }

        $readyOrderedFilters = [];
        $orderFiltersOption = get_option('filtering_by_custom_fields_order', $this->params['moduleId']);
        if (!empty($orderFiltersOption)) {
            $orderFilters = parse_query($orderFiltersOption);
            foreach ($orderFilters as $filter) {
                if (isset($filters[$filter])) {
                    $readyOrderedFilters[$filter] = $filters[$filter];
                }
            }
            $filters = $readyOrderedFilters;
        }

        $moduleId = $this->params['moduleId'];

        return view($template, compact( 'filters','moduleId'));
    }

    public function getRequest()
    {
        $request = new \Illuminate\Http\Request($_REQUEST);

        $ajaxFilter = $request->get('ajax_filter');
        if (!empty($ajaxFilter)) {
            parse_str($ajaxFilter, $ajaxFilterDecoded);
            $request->merge($ajaxFilterDecoded);
        }

        return $request;
    }

    public function apply()
    {
        $request = $this->getRequest();

        $limit = $request->get('limit', false);
        if ($limit) {
            $this->queryParams['limit'] = $limit;
        }

        $page = $request->get('page', false);
        if ($page) {
            $this->queryParams['page'] = $page;
        }

        $this->query->where('parent', $this->getMainPageId());

        // Search
        $search = $request->get('search');
        if (!empty($search)) {
            $this->query->where('title','LIKE','%'.$search.'%');
        }

        // Sort & Order
        $sort = $request->get('sort', false);
        $order = $request->get('order', false);

        if ($sort && $order) {

            $this->queryParams['sort'] = $sort;
            $this->queryParams['order'] = $order;

            $this->query->orderBy($sort, $order);
        }

        // Tags
        $this->query->with('tagged');
        $tags = $request->get('tags', false);

        if (!empty($tags)) {
            $this->queryParams['tags'] = $tags;
            $this->query->withAllTags($tags);
        }

        // Categories
        $category = $request->get('category');
        if (!empty($category)) {
            $this->queryParams['category'] = $category;
            $this->query->whereHas('categoryItems', function ($query) use($category) {
                $query->where('parent_id', '=', $category);
            });
        }

        $this->buildFilter();

        // filters
        $filters = $customFieldFilters = $request->get('filters');

        // except keys
        if (isset($customFieldFilters['from_price'])) {
            unset($customFieldFilters['from_price']);
        }
        if (isset($customFieldFilters['to_price'])) {
            unset($customFieldFilters['to_price']);
        }

        if (!empty($customFieldFilters)) {
            $this->queryParams['filters'] = $customFieldFilters;
            $this->query->whereCustomField($customFieldFilters);
        }

        if (isset($filters['from_price']) && isset($filters['to_price'])) {
            $this->query->filter([
                'priceBetween'=> $filters['from_price'] . ',' . $filters['to_price']
            ]);
        }

        $this->query->select(['id','parent', 'url','title','content','content_body']);
/*
        $this->query->limit(50);

        dd($this->query->get());*/

        $this->pagination = $this->query->paginate($limit)->withQueryString();

        return $this;
    }
}