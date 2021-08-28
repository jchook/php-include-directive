PHP Preprocessor
================

Write PHP templates with `#include` directives similar to those processed by
the C preprocessor.

Also optionally evaluates `<?php ?>` blocks.


Install
-------

If you want to use composer:

```sh
composer require-dev jchook/phpp
```

**or** [download the phar](https://github.com/jchook/php-include-directive/releases)
and include it in your project


Example
-------

Make a file that can contain cpp-like #include directives and/or PHP templating.

**Dockerfile.in**

```dockerfile
FROM alpine:3.14

#include "php.dockerfile"
#include "runit.dockerfile"
```

Then run the script to build it, similar to cpp.

```sh
phpp -o Dockerfile Dockerfile.in
```

Command-Line Usage
------------------

```
USAGE

  phpp [options] PATH...

OPTIONS

  -h, --help  Show this help info
  -o PATH     Output processed file to PATH. (multi)
  -I PATH     Look here for included files (multi)
  --ext       Look for files with this extension (multi)
  --eval      Evaluate PHP in included files
  -v          Verbose mode
```

Options labeled (multi) can be invoked multiple times.


PHP Interface
-------------

See the source code for more info. Here's a simple example:

```php
<?php

use Jchook\Phpp\Preprocessor;

$pre = new Preprocessor();
$pre->makeFile('Dockerfile.in');
```


Motivation
----------

Dockerfiles do not allow you to `INCLUDE` other Dockerfiles. This is a
[known and embraced limitation](https://github.com/moby/moby/issues/735).

Folks have suggested [using cpp to translate #include
directives](https://github.com/moby/moby/issues/735#issuecomment-37273719),
but this has critical issues:

- Cannot use normal # comments, as ccp will throw an error
- The cpp manual warns against using it for non-C code

This tool leverages PHP (a powerful, turing-complete templating language) to
provide a complete templating solution with a familiar #include shortcut.


### Why not use plain ol' PHP?

Consider these two examples in a Dockerfile side-by-side:

```dockerfile
# PHP include:
#<?php include __DIR__ . '/thing.dockerfile' ?>

# CPP-like include
#include "thing.dockerfile"
```

Using "just PHP" presents some awkward quirks:

- Ideally you can comment out the include line to avoid raising syntax
  errors or strange highlighting issues in your code editors / IDE.

- Commenting out the include line means the first line of the included file
  is commented out.

- No automatic paper trail showing which code came from which include in the
  output file(s).

- More cumbersome syntax, requiring more explicit include paths.


Caveat
------

If your Dockerfile or similar code contains the string `<?php` you PHP will
interpret that. If you do not want this behavior, be sure to escape at least
one of the characters or disable `eval` mode.
