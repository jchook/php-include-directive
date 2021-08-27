PHP Preprocessor
================

Write PHP templates with `#include` directives similar to those processed by
the C preprocessor, `cpp`.

Also optionally evaluates `<?php ?>` blocks.


Example
-------

Make a file that can contain cpp-like #include directives and/or PHP templating.

**Dockerfile.in**

```dockerfile
FROM alpine:3.14

#include "php.dockerfile"
#include "runit.dockerfile"

#<?php if ($_SERVER['DO_SPECIAL_THING'] ?? null): ?>
#  include "special.dockerfile"
#<?php endif; ?>
```

Then run the script to build it, similar to cpp.

```sh
phpp -o Dockerfile Dockerfile.in
```

Why?
----

Dockerfiles do not allow you to `INCLUDE` other Dockerfiles. This is a
[known and embraced limitation](https://github.com/moby/moby/issues/735).

Folks have suggested [using cpp to translate `#include`
directives](https://github.com/moby/moby/issues/735#issuecomment-37273719),
but this has critical issues:

- Cannot use normal `# comments`, as ccp will throw an error
- The `cpp` manual warns against using it for non-C code

This tool leverages PHP (a powerful, turing-complete templating language) to
provide a complete templating solution with a familiar #include shortcut.


Why not use plain ol' PHP?
--------------------------

PHP is a templating language. So it does gracefully solve this problem.

But, what if we could avoid cumbersome syntax for 90% of our template needs?
Compare the syntax for a simple relative include:

```dockerfile
# PHP include:
#<?php include __DIR__ . '/thing.dockerfile' ?>

# CPP-like include
#include "thing.dockerfile"
```

Using "just PHP" presents some awkward quirks:

- You need to comment out the include line to avoid raising syntax
  errors or strange highlighting issues in your code editors / IDE.

- Commenting out the include line means the first line of the included file
  is commented out.

- No automatic paper trail showing which code came from which include in the
  output file(s).


Caveat
------

If your Dockerfile or similar code contains the string `<?php` you PHP will
interpret that. If you do not want this behavior, be sure to escape at least
one of the characters or turn off `eval` mode.
