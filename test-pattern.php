<?php

function vlog($line): void {
  if (is_array($line)) {
    $line = json_encode($line, JSON_PRETTY_PRINT);
  } elseif (!is_string($line)) {
    $line = var_export($line);
  }
  fwrite(STDERR, sprintf("%s\n", $line));
}

function test($pattern, $subject): void {
  vlog("\n\nTEST: $pattern");
  preg_replace_callback($pattern, 'vlog', $subject);
}

$full = '/^#include\s+["]([^"]+)["]\s*$/im';

test($full, '#include "check"');

test($full, "\n\n#include \"check\"\n\n#include \"check2\"\n\n\n");
