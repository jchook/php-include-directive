<?php

namespace Jchook\Phpp;

use RuntimeException;

require_once __DIR__ . '/ResolverInterface.php';

class Resolver implements ResolverInterface
{
	protected array $tryPaths;
	protected array $tryExtensions;
	protected array $tryFiles;

	public function __construct(
		array $tryPaths = [],
		array $tryExtensions = [],
		array $tryFiles = []
	) {
		$this->tryPaths = $tryPaths;
		$this->tryExtensions = $tryExtensions;
		$this->tryFiles = $tryFiles;
	}

	protected function isAbsolutePath(string $path): bool
	{
		return $path[0] === DIRECTORY_SEPARATOR;
	}

	/**
	 * @param string $paths
	 */
	protected function join(...$paths): string
	{
		return implode(DIRECTORY_SEPARATOR, $paths);
	}

	protected function withExtension(string $path, string $ext = ''): string
	{
		if ($ext) {
			if ($ext[0] !== '.') {
				$ext = ".$ext";
			}
			return $path . $ext;
		}
		return $path;
	}


	public function resolveInclude(
		string $basedir,
		string $path
	): string
	{
		// Try each of the include paths, starting with the basedir
		$tryPaths = [$basedir, ...$this->tryPaths];
		foreach ($tryPaths as $tryPath) {
			try {
				return $this->resolveIncludeBase($tryPath, $path);
			} catch (RuntimeException $e) {}
		}

		// Didn't find it :(
		throw new RuntimeException("Unable to resolve $path");
	}

	protected function resolveIncludeBase(
		string $basedir,
		string $path
	): string
	{
		$tryExts = ['', ...$this->tryExtensions];
		$tryFiles = $this->tryFiles;

		// Try to find the path with any of the given extensions
		$found = $this->resolveIncludeWithExtensions(
			$this->isAbsolutePath($path) ? $path : $this->join($basedir, $path),
			$tryExts
		);

		// If a directory was found, try the index files...
		if (is_dir($found)) {
			foreach ($tryFiles as $tryFile) {
				try {
					$foundIndex = $this->resolveIncludeWithExtensions(
						$this->join($found, $tryFile),
						$tryExts
					);

					// Ensure this one isn't a directory...
					if (is_file($foundIndex)) {
						return $foundIndex;
					}
				} catch (RuntimeException $e) {}
			}
		}

		// Otherwise it's a file! We found it!
		else {
			return $found;
		}

		throw new RuntimeException("File not found: $path");
	}


	protected function resolveIncludeWithExtensions(
		string $path,
		array $tryExtensions = []
	): string
	{
		foreach ($tryExtensions as $ext) {
			try {
				return $this->realpath($this->withExtension($path, $ext));
			} catch (RuntimeException $e) {}
		}
		throw new RuntimeException('Unable to locate path with exts: ' . $path);
	}


	/**
	 * Given a base directory, resolve the path to an include.
	 * Also prevents recursive include loops if you pass-in $parents.
	 */
	protected function realpath(
		string $path
	): string
	{
		$resolved = realpath($path);

		// DEBUG
		// fwrite(STDERR, "resolve: $path --> ${resolved}\n");

		// File exists?
		if (!$resolved) {
			throw new RuntimeException("$path does not exist");
		}

		return $resolved;
	}

}
