# Livewire Datatables

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mediconesystems/livewire-datatables.svg?style=flat-square)](https://packagist.org/packages/mediconesystems/livewire-datatables)
[![Build Status](https://img.shields.io/travis/mediconesystems/livewire-datatables/master.svg?style=flat-square)](https://travis-ci.org/mediconesystems/livewire-datatables)
[![Quality Score](https://img.shields.io/scrutinizer/g/mediconesystems/livewire-datatables.svg?style=flat-square)](https://scrutinizer-ci.com/g/mediconesystems/livewire-datatables)
[![Total Downloads](https://img.shields.io/packagist/dt/mediconesystems/livewire-datatables.svg?style=flat-square)](https://packagist.org/packages/mediconesystems/livewire-datatables)

### Features
- Use a model or query builder to supply data
- Mutate and format fields using preset or cutom callbacks
- Sort data using field or computed field
- Filter using booleans, times, dates, selects or free text
- Show / hide columns

## [Demo App](https://github.com/MedicOneSystems/demo-livewire-datatables)

![screenshot](resources/images/screenshot.png "Screenshot")

## Requirements
- [Laravel 7](https://laravel.com/docs/7.x)
- [Livewire](https://laravel-livewire.com/)
- [Tailwind](https://tailwindcss.com/)


## Installation

You can install the package via composer:

```bash
composer require mediconesystems/livewire-datatables
```
### Optional
You don't need to, but if you like you can publish the config file and blade template assets:
```bash
php artisan vendor:publish
```
This will enable you to modify the blade views and apply your own styling. The config file contains the default time and date formats used throughout
> - This can be useful if you're using Purge CSS on your project, to make sure all the livewire-datatables classes get included

## Basic Usage

- Use the ```livewire-datatables``` component in your blade view, and pass in a model:
```html
...

<livewire:livewire-datatables model="App\User" />

...
```

## Template Syntax
- There are many ways to modify the table by passing additional properties into the component:
```html
<livewire:livewire-datatable
    model="App\User"
    :hide-show="true"
    :except="['users.updated_at', 'users.email_verified_at']"
    :uppercase="['users.id', 'users.dob']"
    :truncate="['users.bio']"
    :formatDates="['users.dob']"
    :dateFilters="['users.dob']"
    :rename="['users.created_at' => 'Created']"
/>
```

### Props
| Property | Arguments | Result | Example |
|----|----|----|----|
|**include**|*String\|Array* of column definitions|only these columns are shown in table| ```include="name, email, dob, role"```|
|**exclude**|*String\|Array* of column definitions|columns are excluded from table| ```:exlcude="['created_at', 'updated_at']"```|
|**hidden**|*String\|Array* of column definitions|columns are present, but start hidden|```:hidden="email_verified_at"```|
|**dates**|*String\|Array* of column definitions [ and optional format in \| delimited string]|field values are formatted as per the default date format, or format can be included in string with \| separator | ```:dates="['dob|lS F y', 'created_at']"```|
|**times**|*String\|Array* of column definitions [ and optional format in \| delimited string]|field values are formatted as per the default time format, or format can be included in string with \| separator | ```'bedtime|g:i A'```|
|**renames**|*String\|Array* of column definitions and desired name in \| delimited string |Applies custom field names | ```renames="email_verified_at|Verififed"```|
|**search**|*String\|Array* of column definitions and desired name in \| delimited string |Applies custom field names | ```:search="['name', 'email']"```|
|**sort**|*String* of column definition [and optional 'asc' or 'desc' (default: 'desc') in \| delimited string]|Specifies the field and direction for initial table sort. Default is column 0 descending | ```sort="name|asc"```|
|**hide-toggles**|*Boolean* default: *false*|Panel of buttons to show/hide columns in table is removed if this is ```true```| |
|**hide-header**|*Boolean* default: *false*|The top row of the table including the column titles is removed if this is ```true```| |
|**hide-pagination**|*Boolean* default: *false*|Pagination controls are removed if this is ```true```| |
|**per-page**|*Integer* default: 10|Number of rows per page| ```per-page="20"``` |


---


## Component Syntax

To get full control over your datatable:

- create a livewire component that extends ```Mediconesystems\LivewireDatatables\LivewireDatatable```
- Provide a datasource by declaring public property ```$model``` **OR**** public method ```builder()``` that returns an instance of ```Illuminate\Database\Eloquent\Builder```
- Declare a public method ```fields``` that returns a ```Mediconesystems\LivewireDatatables\Fieldset``` containing one or more ```Mediconesystems\LivewireDatatables\Field```

```php
class ComplexDemoTable extends LivewireDatatable
{

    public function builder()
    {
        return User::query()
            ->leftJoin('planets', 'planets.id', 'users.planet_id');
    }

    public function fieldset()
    {
        return Fieldset::fromArray([
            Field::fromColumn('users.id')
                ->name('ID')
                ->linkTo('job', 6),

            Field::fromColumn('users.email_verified_at')
                ->name('Email Verified')
                ->formatBoolean()
                ->withBooleanFilter(),

            Field::fromColumn('users.name')
                ->defaultSort('asc')
                ->globalSearch()
                ->withTextFilter(),

            Field::fromColumn('planets.name')
                ->name('Planet')
                ->globalSearch()
                ->withSelectFilter($this->planets),

            Field::fromColumn('users.dob')
                ->name('DOB')
                ->withDateFilter()
                ->formatDate()
                ->hidden()
        ]);
    }
}
```

### Field Methods
| Method | Arguments | Result | Example |
|----|----|----|----|
|_static_ **fromColumn**| *String* $column |Builds a field from column definition|```Field::fromColumn('users.name')```|
|_static_ **fromRaw**| *String* $rawSqlStatement|Builds a field from raw SQL statement. Must include "... AS _alias_"|```Field::fromRaw("CONCAT(ROUND(DATEDIFF(NOW(), users.dob) / planets.orbital_period, 1) AS `Native Age (SQL)`")```|
|_static_ **fromScope**|*String* $scope, *String* $alias|Builds a field from a scope on the parent model|```Field::fromScope('selectLastLogin', 'Last Login')```|
|**name**|*String* $name|Changes the display name of a field|```Field::fromColumn('users.id')->name('ID)```|
|**formatBoolean**| |Passes the field value to ```view('livewire::datatables.boolean')``` to display icons for boolean values|```Field::fromColumn('users.email_verified_at')->withBooleanFilter(),```|
|**formatDate**|[*String* $format (default: 'd/m/Y')]|Formats date to given format or default|```FIeld::fromColumn('users.created_at')->formatDate()```|
|**formatTime**|[*String* $format (default: 'H:i')]|Formats time to given format or default|```FIeld::fromColumn('users.bedtime')->formatTime('g:i A')```|
|**hidden**| |Marks field to start as hidden|```Field::fromColumn('users.id')->hidden()```|
|**truncate**|[*Integer* $length (default: 16)]Truncates field to $length and provides full-txt in a tooltip. Uses ```view('livewire-datatables::tooltip)```|```Field::fromColumn('users.biography)->truncate(30)```|
|**linkTo**|*String* $model, [*Integer* $pad]|Replaces the value with a link to ```"/$model/$value"```. Useful for ID fields. Optional zero-padding. Uses ```view('livewire-datatables::link)```|```Field::fromColumn('users.id')->linkTo('user', 6)```|
|**round**|[*Integer* $precision (default: 0)]|Rounds value to given precision|```Field::fromColumn('patients.age')->round()```|
|**sortBy**|*String\|Expression* $column|Changes the query by which the field is sorted|```Field::fromColumn('users.dob')->sortBy(DB::raw('DATE_FORMAT(users.dob, "%m%d%Y")')),```|
|**defaultSort**|[*String* $direction (default: 'desc')]|Marks the field as the default search column|```Field::fromColumn('users.name')->defaultSort('asc')```|
|**globalSearch**| |Includes the field in global searches|```Field::fromColumn('users.name')->globalSearch()```|
|**withSelectFilter**|*Array* $options |Adds a select-based filter on the field using options provided|```Field::fromColumn('users.allegiance')->withSelectFilter(['Rebellion', 'Empire'])```|
|**withScopeSelectFilter**|*string* $scope, *Array* $options |Adds a select-based filter that applies the selected option to the given scope|```Field::fromScope('selectLastLogin', 'Weapons')->withScopeSelectFilter('filterWeaponNames', ['Blaster', Bowcaster', 'Light saber'])```|
|**withBooleanFilter**| |Adds a yes/no filter that the field|```Field::fromColumn('users.email_verified_at')->withBooleanFilter()```|
|**withScopeBooleanFilter**| |Adds a yes/no filter that applies the true/false value to the given scope|```Field::fromScope('withLoginLastMonth', 'Recent Login')->withScopeBooleanFilter('filterLoginLastMonth')```|
|**withTextFilter**| |Adds an input-based free text filter on the field. Will be loosely matched using ```...LIKE "%$value%"```|```Field::fromColumn('users.email')->withTextFilter(['Rebellion', 'Empire'])```|
|**withDateFilter**| |Adds a date filter on the field.|```Field::fromColumn('users.dob')->witDateFilter()```|
|**withTimeFilter**| |Adds a time filter on the field.|```Field::fromColumn('users.bedtime)->withTimeFilter()```|
|**callback**|*String* $callback [, *Array* $params (default: [])]| Passes the field value, whole row of values, and any additional parameters to a callback to allow custom mutations| _(see below)_|


### Callbacks
Callbacks give you the freedom to perform any mutations you like on the data before displaying in the table.
- The callbacks are performed to the paginated results of the database query
- Callbacks will receive the field's value as their first argument, and the whole query row as the second, followed by any specified.

```php
class CallbackDemoTable extends LivewireDatatable
{
    public model = User::class

    public function fieldset()
    {
        return Fieldset::fromArray([
            Field::fromColumn('users.id'),

            Field::fromColumn('users.dob')->formatDate(),

            Field::fromColumn('users.signup_date')->callback('ageAtSignup', 10, 'red'),
        ]);
    }

    public function ageAtSignup($value, $row, $threshold, $colour)
    {
        $age = $value->diffInYears($row->dob);
        return age > $threshold
            ? '<span class="text-red-500">' .$age . '</span>'
            : $age;
    }
}
```
