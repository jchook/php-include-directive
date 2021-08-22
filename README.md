PHP Include Directives
======================

Allows you to write PHP code with `#include` directives similar to those
processed by the C preprocessor, `cpp`.

Also interprets all `<?php ?>` blocks.


Example
-------

This example **Dockerfile.in**  includes a couple extra partial dockerfiles,
then conditionally includes a 3rd partial dockerfile.

```php
FROM alpine:3.14

#include "php.dockerfile"
#include "runit.dockerfile"

<?php if ($_SERVER['DO_SPECIAL_THING'] ?? null): ?>
#include "special.dockerfile"
<?php endif; ?>
```

Why?
----

Dockerfiles do not allow you to `INCLUDE` other Dockerfiles. This is a
[known and embraced limitation](https://github.com/moby/moby/issues/735).

Folks have suggested [using cpp to translate `#include`
directives](https://github.com/moby/moby/issues/735#issuecomment-37273719),
but this has many issues:

- Cannot use `# comments` at all, lol
- The `cpp` manual mentions that it might choke on lexically non-C-like code
- Not a turing-complete preprocessor

**With this script you get the best of the both worlds:**

- Use cpp-like #include directives
- Use PHP, a turing-complete, generally available templating language

