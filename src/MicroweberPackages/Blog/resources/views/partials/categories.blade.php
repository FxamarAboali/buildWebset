<?php
$randomId = uniqid();
?>

<div class="card-header bg-white px-1">
    <div data-bs-toggle="collapse" data-bs-target="#collapse_{{$randomId}}" data-target="" aria-expanded="true" class="d-flex">
        <h4><?php _e('Categories') ?></h4>
        <i class="mdi mdi-plus ms-auto align-self-center"></i>
    </div>
</div>

<div class="collapse show" id="collapse_{{$randomId}}">
    <div class="card-body px-1">
        @foreach($categories as $category)
            <ul class="js-filter-category-tree list-unstyled">
                <li class="mw-shop-attributes-li title pb-1">
                    <a href="?category={{$category->id}}" class="js-filter-category-link @if($request->get('category', false) == $category->id) active @endif ">{{$category->title}}</a>
                </li>
                @if($category->children()->count() > 0)
                    @include('blog::partials.categories_children', ['categories' => $category->children])
                @endif
            </ul>
        @endforeach

    </div>
</div>
