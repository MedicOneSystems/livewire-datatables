<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Carbon Formats
    |--------------------------------------------------------------------------
    | The default formats that are used for TimeColumn & DateColumn.
    | You can use the formatting characters from the PHP DateTime class.
    | More info: https://www.php.net/manual/en/datetime.format.php
    |
    */

    'default_time_format' => 'H:i',
    'default_date_format' => 'd/m/Y',

    /*
    |--------------------------------------------------------------------------
    | Surpress Search Highlights
    |--------------------------------------------------------------------------
    | When enabled, matching text won't be highlighted in the search results
    | while searching.
    |
    */

    'suppress_search_highlights' => false,

    /*
    |--------------------------------------------------------------------------
    | Per Page Options
    |--------------------------------------------------------------------------
    | Sets the options to choose from in the `Per Page`dropdown.
    |
    */

    'per_page_options' => [10, 25, 50, 100],

    /*
    |--------------------------------------------------------------------------
    | Default Per Page
    |--------------------------------------------------------------------------
    | Sets the default amount of rows to display per page.
    |
    */

    'default_per_page' => 10,

    /*
    |--------------------------------------------------------------------------
    | Model Namespace
    |--------------------------------------------------------------------------
    | Sets the default namespace to be used when generating a new Datatables
    | component.
    |
    */

    'model_namespace' => 'App',

    /*
    |--------------------------------------------------------------------------
    | Default Sortable
    |--------------------------------------------------------------------------
    | Should a column of a datatable be sortable by default ?
    |
    */

    'default_sortable' => true,

    /*
    |--------------------------------------------------------------------------
    | Default CSS classes
    |--------------------------------------------------------------------------
    |
    | Sets the default classes that will be applied to each row and class
    | if the rowClasses() and cellClasses() functions are not overrided.
    |
    */

    'default_classes' => [
        'row' => [
            'even' => 'divide-x divide-gray-100 text-sm text-gray-900 bg-gray-100',
            'odd' => 'divide-x divide-gray-100 text-sm text-gray-900 bg-gray-50',
            'selected' => 'divide-x divide-gray-100 text-sm text-gray-900 bg-yellow-100',
        ],
        'cell' => 'whitespace-no-wrap text-sm text-gray-900 px-6 py-2',
    ],
];
