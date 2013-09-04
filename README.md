#Slipstream

## Introduction

The composer autoloader is quite good, and quite fast when used with `composer.phar dump-autoload --optimize`. However, the `--optimize` option maps every single class in your dependency tree. If you are a using a framework such as zf2 or symfony, that can mean 3000+ of classes. Chances are that your is is only using a small fraction of those. Slipstram will rewrite the classmap in production to include only the classes you need, meaning faster page load times.

## Installation

Install the module using Composer into your application's vendor directory. Add the following line to your
`composer.json`.

```json
{
    "require": {
        "zoopcommerce/slipstream": "~0.1"
    }
}
```

## Usage

Your `vendor/composer/` directory must be writable.

Where your code previously called the composer autoloader with:

    $loader = include vendor/autoload.php;

Replace with:

    $loader = include vendor/zoopcommerce/slipstream/autoload.php;

That's it!

Enjoy.