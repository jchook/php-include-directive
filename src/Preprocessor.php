<?php

namespace Jchook\Phpp;

use Exception;

class Preprocessor
{

  // Regex to capture filenames from #include statements
  const INCLUDE_PATTERN = '/^\s*#\s*include\s+["]([^"]+)["]\s*$/im';
  const INCLUDE_PATTERN_PATH_INDEX = 1;

  /**
   * Output debug information
   */
  function vlog($line): void
  {
    fwrite(STDERR, sprintf("%s\n", $line));
  }

  /**
   * Read a file to be included.
   *
   * TODO: There is an issue here... Should run all #include directives
   * recursively, store the result in a file, then evaluate that file with PHP.
   *
   * OR DON'T! Could let the user run PHP on the result if they choose.
   *
   * OR leave it how it is. We know you have PHP!! You're running this file!
   * Leaving it this way would let you have conditional #include statements.
   * And if you cared about using PHP instead of directives, you could just
   * use # <?php include ... ?> instead.
   */
  function readInclude(string $in): string
  {
    // return file_get_contents($in);
    ob_start();
    include $in;
    return ob_get_clean();
  }

  /**
   * Given a base directory, resolve the path to an include.
   * Also prevent recursive include loops by passing fully resolved parent
   * includes.
   */
  function resolveInclude(
    string $basedir,
    string $path,
    array $parents = []
  ): string
  {
    if (!$path) throw new Exception('Expected a path');

    // Relative path?
    $resolved = $path[0] !== '/'
      ? $basedir . DIRECTORY_SEPARATOR . $path
      : $path;

    // Canonicalize path
    $resolved = realpath($resolved);

    // File exists?
    if (!$resolved) {
      throw new Exception("$path does not exist");
    }

    // Can read it?
    if (!is_readable($resolved)) {
      throw new Exception("$resolved is not readable");
    }

    // Not a recursive include
    if (in_array($resolved, $parents)) {
      throw new Exception(
        "Recusive include: " . implode(" -> ", $parents) . " -> $path"
      );
    }

    return $resolved;
  }

  function makeFile(string $in, ?string $out = null): void
  {
    $path = $this->resolveInclude('.', $in);
    $ext = '/\\.[^.\\s]{1,16}$/';
    if (!$out) {
      $out = preg_match($ext, $path)
        ? preg_replace($ext, '', $path)
        : $path . '.out';
    }
    $this->vlog(__FUNCTION__ . " $path -> $out");
    file_put_contents($out, $this->replaceIncludes($path));
  }

  function replaceIncludes(string $in, array $parents = []): string
  {
    $data = $this->readInclude($in);
    $this->vlog(str_repeat(" | ", count($parents)) . " |-- " . "$in");
    return preg_replace_callback(
      self::INCLUDE_PATTERN,
      function($matches) use ($in, $parents) {
        $path = $matches[self::INCLUDE_PATTERN_PATH_INDEX];
        $path = $this->resolveInclude(dirname($in), $path, $parents);
        $prefix = "# BEGIN $path\n";
        $suffix = "# END $path\n";
        return $prefix
          . $this->replaceIncludes($path, array_merge($parents, [$path]))
          . $suffix;
      },
      $data,
    );
  }

}
