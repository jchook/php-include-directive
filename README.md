PHP Include Directives
======================

Write PHP templates with `#include` directives similar to those
processed by the C preprocessor, `cpp`.

Also interprets all `<?php ?>` blocks.


Example
-------

Make a file that can contain cpp-like #include directives.

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
./build -o Dockerfile Dockerfile.in
```


Why?
----

Dockerfiles do not allow you to `INCLUDE` other Dockerfiles. This is a
[known and embraced limitation](https://github.com/moby/moby/issues/735).

Folks have suggested [using cpp to translate `#include`
directives](https://github.com/moby/moby/issues/735#issuecomment-37273719),
but this has many issues:

- Cannot use `# comments` at all, lol
- The `cpp` manual mentions that it might choke on lexically non-C code
- Not a turing-complete preprocessor

**This script lets you use both:**

- cpp-like `#include` directives
- Arbitrary PHP -- a turing-complete templating language


Why not use plain ol' PHP?
--------------------------

For one you get to avoid cumbersome syntax. Comare the two...

```dockerfile
# PHP include:
#<?php include __DIR__ . DIRECTORY_SEPARATOR . 'thing.dockerfile' ?>

# CPP-like include
#include "thing.dockerfile"
```

Using pure PHP has lots of awkward quirks too:

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
one of the characters, e.g. (notice the backslash):

```dockerfile
FROM alpine:3.14
RUN apk add --no-cache php8 \
  && echo "<\?php echo 'hello world';" > hello.php
```
