# Package laravel-commons

It includes general purpose libraries and classes.

## Packages required

`intervention/image` for using ImageLib.

## Setup

Install using composer:
```
composer require alexconesap/laravel-commons
```

Since it is not currently available at `https://packagist.org/`, follow up these steps:

- 1 Clone this repository in a local folder. A simple idea is to create a folder in the parent folder for your project.
  Let us say you clone the repo in a folder named `dev-laravel-commons` (or any other you want) following this structure:

```text
   | your_project_folder
   | composer_local_repos
    --| dev-laravel-commons
```

- 2 Reference the package locally at the host project `composer.json` file as follows (extract of the file):

```json
{
  ...
  "require": {
    "alexconesap/laravel-commons": "~1.0"
  },
  "repositories": [
    {
      "type": "path",
      "url": "../composer_local_repos/dev-laravel-commons"
    }
  ],
  ...
}
```

Running now the composer command will gather the repository from your local disk.
```
composer update
```

# Classes

## [ArrayList](src/ArrayList.php)
Inspired on the Java's common ArrayList this PHP class allows to manage a Collection of elements being internally stored as an array.

```php
$example_array = ['one' => '123', 'two' => 'Alex', 'three' => false, 'four' => '2_$ax1'];

$alist = ArrayList::valueOf($example_array);

$alist->has('one');            // true
$alist->has('test');           // false
$alist->has(null);             // false

$alist->filled('one');         // true
$alist->set('one', null);
$alist->has('one');            // true
$alist->filled('one');         // false
$alist->count();               // 4

$alist->remove('one');
$alist->has('one');            // false
$alist->count();               // 3

$alist->replaceAll($example_array);
$alist->count();               // 4
$alist->set('five', 'Hello');
$alist->set('six',  true);
$alist->set('seven', 33);
$alist->count();               // 7

$alist->get('four');           // '2_$ax1'
$alist->input('four');         // '2_$ax1'
$alist->post('four');          // '2_$ax1'
$alist->getAlpha('four');      // 'ax'
$alist->getAlnum('four');      // '2ax1'
$alist->getDigits('four');     // '21'
$alist->getInt('four');        // 2

$alist->getInt('three');       // 0
$alist->getBoolean('three');   // false

$alist->clear();
$alist->count();               // 0
$alist->isEmpty();             // true
```

## [StringBuilder](src/StringBuilder.php)
Inspired on the Java's `StringBuilder` class it allows to construct a `string` calling methods rather than
using PHP `.`. By default this class concats the 'added' strings using a coma.

Additionally it publishes lots of useful methods to add strings using parsing, parameter separators, etc.

Examples:
```php
(new StringBuilder())
 ->add('', '...')                       >>> ''
 ->add('Hey')                           >>> 'Hey'
 ->add('It is me')                      >>> 'Hey, It is me'
 ->add(null)                            >>> 'Hey, It is me'
 ->add('and you', '...')                >>> 'Hey, It is me...and you'

(new StringBuilder('', '&'))
 ->addKeyValue('', '', '...')           >>> ''
 ->addKeyValue('a', '')                 >>> 'a='
 ->addKeyValue('m', 'It is me')         >>> 'a=&m=It is me'
 ->addKeyValue(null)                    >>> 'a=&m=It is me'
 ->addKeyValue('m2', 'It is me', true)  >>> 'a=&m=It is me&m2=It+is+me'

(new StringBuilder())
 ->addPair('Hello', 'Alex', '*')        >>> 'Hello*Alex'
 ->addPair('Bye', '', '*')              >>> 'Hello*Alex, Bye'

(new StringBuilder('', ''))
 ->add('Hello')                         >>> 'Hello'
 ->add('Bye')                           >>> 'HelloBye'
```

## [ExceptionsLib](src/ExceptionsLib.php)
It contains just one method that dumps the exception in Java format's `printStackTrace()` that contains
much less verbosity than the generic PHP's trace.

Example:
```php
try {
  // whatever
} catch (Exception $ex) {
  Log::debug(ExceptionsLib::toJavaStyleTrace($ex));
}
```

```php
if ($want_to_log_trace) Log::debug(ExceptionsLib::arrayToJavaStyleTrace( debug_backtrace() ));
```

## [ImageLib](src/ImageLib.php)
General functions for image processing.
> Requires the `intervention/image` package.


## [Tools/ClassFinder](src/Tools/ClassFinder.php)
Allows to find out PHP class files in a given folder. 
> Requires the Laravel framework.

```php
$classes_found = ClassFinder::findClasses(
  app_path('Marketing/CampaignProcessors')
);

foreach ($classes_found as $one_class) {
    $obj = new $one_class;
    // ...
}
```

```php
$classes_found = collect(
    ClassFinder::findClasses(app_path('Marketing/CampaignProcessors'))
)->filter(function ($className) {
    return is_subclass_of($className, CampaignProcessorInterface::class);
});
```

## [Sockets/ClientSocket](src/Sockets/ClientSocket.php)
Simple communications with a network socket.

```php
$socket = new ClientSocket('127.0.0.1', 15001);
return $socket->send(json_encode(['version' => '1', 'data' => 'some data|some more info|222']));
``` 

> Requires `ext-sockets` in your composer.json file.

## [Models/BasicBean](src/Models/BasicBean.php)

## [Models/BaseBean](src/Models/BaseBean.php)

## [Models/BasicBeanValidated](src/Models/BasicBeanValidated.php)

# License and contributions
MIT License. 
Any comments or contributions are welcomed!
