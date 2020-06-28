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

## Requirements
- [Laravel 7.x](https://laravel.com/docs/7.x)
- [Livewire 1.x](https://laravel-livewire.com/)


## Installation

You can install the package via composer:

```bash
composer require mediconesystems/livewire-datatables
```

## Basic Usage

- Use the ```livewire-datatables``` component in your blade view, and pass in a model:
```html
...

<livewire:livewire-datatables model="App\User" />

...
```

## Modifying Fields
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
| Property | Accepts | Result |
|----|----|----|
|**hide-show**|*Boolean* default: *false*|Panel of buttons to show/hide columns in table is removed if this is ```true```|
|**except**|*Array* of column definitions|columns are excluded from table|
|**uppercase**|*Array* of column definitions|field names are capitalised. Useful for ID fields or abbreviations|
|**truncate**|*Array* of column definitions|field values are truncated, the whole text can be seen in tooltip on hover|
|**formatDates**|*Array* of column definitions|field values are formatted as per the default date format|
|**dateFilters**|*Array* of column definitions|Date filters are made available on the table for these fields|
|**rename**|*Associative Array* of column definitions and desired name|Applies custom field names|










- To get more control over the table, create a new livewire component that extends ```Mediconesystems\LivewireDatatables\LivewireDatatable```

    (if you use the ```livewire:make``` artisan command you can delete the blade view file)

- The new compnent must have a




### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email mark.salmon@mediconesystems.com instead of using the issue tracker.

## Credits

- [Mark Salmon](https://github.com/mediconesystems)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).