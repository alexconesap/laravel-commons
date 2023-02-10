# Package laravel-commons

It includes general purpose libraries and classes.

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

## Classes
### ArrayList
Inspired on the Java's common ArrayList this PHP class allows to manage a Collection of elements being internally 
stored as an array.

### CURLClient
Wrapper to the PHP' systems CURL library it simplifies dealing with CURL requests and errors.


## Libraries
### ExceptionsLib
It contains just one method that dumps the exception in Java format's `printStackTrace()` that contains
much less verbosity than the generic PHP's trace.

### Firebase
Library to easily deal with Firebase for push notifications management.

### GFXLib
General functions for image and media processing.

### MathLib
General math functions.

### MD5Hasher
Extension for Laravel `Illuminate\Contracts\Hashing\Hasher` to manage MD5 hashes / passwords.

### NetLib
General network/communications functions.

### PHPCryptHasher
Extension for Laravel `Illuminate\Contracts\Hashing\Hasher` to manage generic PHP crypt hashes / passwords.

### StringBuilder
Inspired on the Java's `StringBuilder` class it allows to construct a `string` calling methods rather than
using `.`. Additionally it publishes lots of useful methods to add strings using parsing, parameter separators, etc.

Examples:
```php
(new StringBuilder())
 ->add('', '...')         >>> ''
 ->add('Hey')             >>> 'Hey'
 ->add('It is me')        >>> 'Hey, It is me'
 ->add(null)              >>> 'Hey, It is me'
 ->add('and you', '...')  >>> 'Hey, It is me...and you'

(new StringBuilder('', '&'))
 ->addKeyValue('', '', '...')          >>> ''
 ->addKeyValue('a', '')                >>> 'a='
 ->addKeyValue('m', 'It is me')        >>> 'a=&m=It is me'
 ->addKeyValue(null)                   >>> 'a=&m=It is me'
 ->addKeyValue('m2', 'It is me', true) >>> 'a=&m=It is me&m2=It+is+me'

(new StringBuilder())
 ->addPair('Hello', 'Alex', '*') >>> 'Hello*Alex'
 ->addPair('Bye', '', '*')       >>> 'Hello*Alex, Bye'
```

### StringLib
General string management functions.

### SysLib
General OS/System (hardware) functions.



# License and contributions
Any comments or contributions are welcomed!