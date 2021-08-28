<?php

namespace Jchook\Phpp;

use RuntimeException;

interface ResolverInterface
{
  /**
   * @param string $basedir Resolve relative to this directory
   * @param string $path Path to an included file
   * @throws RuntimeException
   */
  public function resolveInclude(
    string $basedir,
    string $path
  ): string;
}
