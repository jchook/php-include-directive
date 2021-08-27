<?php

namespace Jchook\Phpp;

use Exception;

interface ResolverInterface
{
  /**
   * @param string $include Path to an included file
   * @param string $basedir Resolve relative to this directory
   * @throws Exception
   */
  public function resolveInclude(
    string $path,
    string $basedir
  ): string;
}
