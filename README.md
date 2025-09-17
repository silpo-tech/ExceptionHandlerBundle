[![CI](https://github.com/silpo-tech/ExceptionHandlerBundle/actions/workflows/ci.yml/badge.svg)](https://github.com/silpo-tech/ExceptionHandlerBundle/actions)
[![codecov](https://codecov.io/gh/silpo-tech/ExceptionHandlerBundle/graph/badge.svg)](https://codecov.io/gh/silpo-tech/ExceptionHandlerBundle)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

# Exception Handler Bundle #

## About ##

The Exception Handler Bundle helps to catch and process output for different types of exceptions

## Installation ##

Require the bundle and its dependencies with composer:

```bash
$ composer require silpo-tech/exception-handler-bundle
```

Register the bundle:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        ...
        new SilpoTech\ExceptionHandlerBundle\ExceptionHandlerBundle(),
    );
}
```

## How to override standard validation messages?

Create Messages class:
```php
<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class MessageDictionary
{
    public const ERROR_MAP = [
        NotBlank::IS_BLANK_ERROR => 'validation.not_blank',
        Count::TOO_FEW_ERROR => 'validation.count.min',
        Count::TOO_MANY_ERROR => 'validation.count.max',
        Length::TOO_SHORT_ERROR => 'validation.length.min',
        Length::TOO_LONG_ERROR => 'validation.length.max'
    ];
}
```

Add configuration to services.yaml:
```yaml
parameters:
  validation_messages_map: !php/const App\Validator\MessageDictionary::ERROR_MAP
  #or use Symfony Standard SilpoTech\ExceptionHandlerBundle\ValidationHandlerMessages::ERROR_MAP
```
If you don't need to have properties in snake_case, you can use the following configuration:

```yaml
parameters:
  validation_snake_case: false
```

#### Run tests locally

Install composer

```bash
docker run --rm -v $(pwd):/workspace -w /workspace composer:2 composer install
```

And then run test

```bash
docker run --rm -v $(pwd):/workspace -w /workspace php:8.3-cli php bin/phpunit --color=always
```