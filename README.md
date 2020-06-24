# Livewire Datatables

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mediconesystems/livewire-datatables.svg?style=flat-square)](https://packagist.org/packages/mediconesystems/livewire-datatables)
[![Build Status](https://img.shields.io/travis/mediconesystems/livewire-datatables/master.svg?style=flat-square)](https://travis-ci.org/mediconesystems/livewire-datatables)
[![Quality Score](https://img.shields.io/scrutinizer/g/mediconesystems/livewire-datatables.svg?style=flat-square)](https://scrutinizer-ci.com/g/mediconesystems/livewire-datatables)
[![Total Downloads](https://img.shields.io/packagist/dt/mediconesystems/livewire-datatables.svg?style=flat-square)](https://packagist.org/packages/mediconesystems/livewire-datatables)

Advanced datatable with sorting, filtering, searching ...

## Installation

You can install the package via composer:

```bash
composer require mediconesystems/livewire-datatables
```

## Basic Usage

``` php
<?php

namespace App\Http\Livewire;

use App\User;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Traits\LivewireDatatable;

class UsersTable extends Component
{
    use LivewireDatatable;

    public function model()
    {
        return User::class;
    }
}
```

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