<?php

use Jchook\Phpp\Preprocessor;
use Jchook\Phpp\Resolver;

require __DIR__ . '/../vendor/autoload.php';

// Gonna use getopt, even though you cannot mix positional args and options
$rest = 0;
$opt = getopt('vo:I:', ['eval', 'ext'], $rest);

// Files to build
$outs = (array) ($opt['o'] ?? []);
$ins = array_slice($argv, $rest) ?: [];

// Include paths
$tryPaths = isset($opt['I']) ? (array) $opt['I'] : ['.'];
$tryExts = isset($opt['ext']) ? (array) $opt['ext'] : [];

// Options
$eval = isset($opt['eval']);
$verbose = isset($opt['v']);

// Preprocessor
$phpp = new Preprocessor(
	new Resolver($tryPaths, $tryExts),
	$eval,
	$verbose,
);

// Do it
foreach ($ins as $idx => $in) {
  $phpp->makeFile($in, $outs[$idx] ?? null);
}

