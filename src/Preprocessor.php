<?php

namespace Jchook\Phpp;

use RuntimeException;

/**
 * Preprocess files, resolving and replacing cpp-like #include directives,
 * and optionally evaluating php code.
 */

class Preprocessor
{

	// Regex to capture filenames from #include statements
	const INCLUDE_PATTERN = '/^\s*#\s*include\s+["]([^"]+)["]\s*$/im';
	const INCLUDE_PATTERN_PATH_INDEX = 1;

	protected ResolverInterface $resolver;
	protected bool $eval;
	protected bool $verbose;

	/**
	 * Configurable in PHP 8 style
	 */
	public function __construct(
		?ResolverInterface $resolver = null,
		bool $eval = false,
		bool $verbose = false
	)
	{
		$this->resolver = $resolver ?? new Resolver();
		$this->eval = $eval;
		$this->verbose = $verbose;
	}

	/**
	 * Make a file and output the result to a new file
	 */
	public function makeFile(string $in, ?string $out = null): void
	{
		$path = $this->resolver->resolveInclude('.', $in);
		$ext = '/\\.[^.\\s]{1,16}$/';
		if (!$out) {
			$out = preg_match($ext, $path)
				? preg_replace($ext, '', $path)
				: $path . '.out';
		}
		$this->vlog(__FUNCTION__ . " $path -> $out");
		file_put_contents($out, $this->replaceIncludes($path));
	}

	/**
	 * Read a file to be included. Optionally evaluate it.
	 */
	protected function readInclude(string $in, ?bool $eval = null): string
	{
		if ($eval ?? $this->eval) {
			ob_start();
			include $in;
			return ob_get_clean();
		}
		return file_get_contents($in);
	}

	/**
	 * Given an include file path, read it and replace all of the #include
	 * directives with the contents of the file they reference.
	 *
	 * Optionally evaluates PHP code in the templates.
	 */
	protected function replaceIncludes(
		string $in,
		array $parents = [],
		?bool $eval = null
	): string
	{
		$data = $this->readInclude($in, $eval);
		$this->vlog(str_repeat(" | ", count($parents)) . " |-- " . "$in");
		return preg_replace_callback(
			self::INCLUDE_PATTERN,
			function($matches) use ($eval, $in, $parents) {
				$path = $matches[self::INCLUDE_PATTERN_PATH_INDEX];
				$resolved = $this->resolver->resolveInclude(dirname($in), $path);
				$this->checkInclude($resolved, $parents);
				$prefix = "# BEGIN $path\n";
				$suffix = "\n# END $path\n";
				$nextParents = array_merge($parents, [$resolved]);
				return $prefix
					. $this->replaceIncludes($resolved, $nextParents, $eval)
					. $suffix;
			},
			$data,
		);
	}

	protected function checkInclude(string $resolved, array $parents = []): string
	{
		// Can read it?
		if (!is_readable($resolved)) {
			throw new RuntimeException("$resolved is not readable");
		}

		// Not a recursive include
		if (in_array($resolved, $parents)) {
			throw new RuntimeException(
				"Recusive include: " . implode(" -> ", $parents) . " -> $resolved"
			);
		}

		return $resolved;
	}

	/**
	 * Output debug information
	 */
	protected function vlog($line): void
	{
		if ($this->verbose) {
			fwrite(STDERR, sprintf("%s\n", $line));
		}
	}

}

