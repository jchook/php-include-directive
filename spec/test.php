<?php

namespace Jchook\Phpp;

require_once __DIR__ . '/../src/autoload.php';

function join(...$paths): string {
	return implode(DIRECTORY_SEPARATOR, $paths);
}

try {

	$scratch = 'scratch';

	$files = [
		join($scratch, 'alpine', 'from.dockerfile') => [
			'FROM alpine:3.14',
		],
		join($scratch, 'app', 'deps.dockerfile') => [
			'RUN echo "install app"',
		],
		join($scratch, 'app', 'dist.dockerfile') => [
			'RUN echo "install dist"',
		],
		join($scratch, 'mx', 'Dockerfile.in') => [
			'#include "alpine/from"',
			'#include "app/deps"',
			'#include "app/dist"',
			'# Do stuff that is MX-only related here',
		],
	];

	foreach ($files as $path => $data) {
		if (!is_dir(dirname($path))) {
			mkdir(dirname($path), 0777, true);
		}
		file_put_contents($path, implode("\n", $data));
	}

	$pre = new Preprocessor(
		new Resolver(
			[$scratch],
			['dockerfile'],
		),
	);

	$infile = join($scratch, 'mx', 'Dockerfile.in');
	$target = join($scratch, 'mx', 'Dockerfile');
	$pre->makeFile($infile);

	assert(
		file_get_contents($target) === implode("\n", [
			'# BEGIN alpine/from',
			file_get_contents('./scratch/alpine/from.dockerfile'),
			'# END alpine/from',
			'',
			'# BEGIN app/deps',
			file_get_contents('./scratch/app/deps.dockerfile'),
			'# END app/deps',
			'',
			'# BEGIN app/dist',
			file_get_contents('./scratch/app/dist.dockerfile'),
			'# END app/dist',
			'',
			'# Do stuff that is MX-only related here',
		])
	);

} finally {
	if ($scratch && is_dir($scratch)) {
		`rm -r '$scratch'`;
	}
}
