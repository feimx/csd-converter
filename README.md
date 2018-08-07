# Convert pem required files for CFDI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/feimx/csd_converter.svg?style=flat-square)](https://packagist.org/packages/feimx/csd_converter)
[![Build Status](https://img.shields.io/travis/feimx/csd_converter/master.svg?style=flat-square)](https://travis-ci.org/feimx/csd_converter)
[![Quality Score](https://img.shields.io/scrutinizer/g/feimx/csd_converter.svg?style=flat-square)](https://scrutinizer-ci.com/g/feimx/csd_converter)
[![Total Downloads](https://img.shields.io/packagist/dt/feimx/csd_converter.svg?style=flat-square)](https://packagist.org/packages/feimx/csd_converter)

The `feimx/csd_converter` package provide a simple way for create .pem files for your CSD's.

## Installation

You can install the package via composer:

```bash
composer require feimx/csd_converter
```

## Usage

Firt need create a new instance of `CsdConverter`:

``` php
$converter = new FeiMx\Csd\CsdConverter();
echo $converter->serial_number();
```

Thats all, now you can access the cer info and save the news files to given path

``` php
echo $converter->serial_number;
echo $converter->tax_id;
echo $converter->valid_from; // instance of Carbon
echo $converter->valid_to; // instance of Carbon
echo $converter->getStatus();
```

`valid_from` and `valid_to` are instances of Carbon, so you can modify and format the dates:

``` php
echo $converter->valid_from->format('d/m/Y h:i a');
echo $converter->valid_to->format('d/m/Y h:i a');
```

`getStatus()` returns a expired, valid or invalid status:

``` php
if($converter->getStatus() === CsdConverter::VALID){}
if($converter->getStatus() === CsdConverter::INVALID){}
if($converter->getStatus() === CsdConverter::EXPIRED){}

```

You can verify if the files are a valid CSD:

``` php
var_dump($converter->isValidCsd()); // true or false
```

Now you can save the created files to a given path and assigne a optional filename:

``` php
$path = __DIR__.'/temp/';
$filename = 'VALIDCSD';
$converter->save($path, $filename);
//this create 4 files:
//__DIR__.'/temp/VALIDCSD.cer'
//__DIR__.'/temp/VALIDCSD.cer.pem'
//__DIR__.'/temp/VALIDCSD.key'
//__DIR__.'/temp/VALIDCSD.key.pem'
```

And last, but not less important, you can encryp the converted key into des3:

``` php
$file = __DIR__.'/temp/VALIDCSD.key.pem';
$password = 'secret';
$converter->encryptKey($file, $password);
//__DIR__.'/temp/VALIDCSD.enc.key'
```

_Note:_ This is very useful with third party provider such as Finkok for stamp the CFDI

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email yorch@feimexico.com.mx instead of using the issue tracker.

## Credits

- [Jorge Andrade](https://github.com/Yorchi)
- [All Contributors](../../contributors)

## Support us

FEI is a Digital Invoicing startup based in Yucatán, México. You'll find an overview of all our open source projects [on our website](https://fei.com.mx/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/jorge_andrade). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
