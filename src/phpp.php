<?php

use Jchook\Phpp\Preprocessor;
use Jchook\Phpp\Resolver;

require_once __DIR__ . '/autoload.php';

// Gonna use getopt, even though you cannot mix positional args and options
$rest = 0;
$opt = getopt('hI:o:v', ['eval', 'ext', 'help'], $rest);

// Files to build
$outs = (array) ($opt['o'] ?? []);
$ins = array_slice($argv, $rest) ?: [];

if (!$ins || isset($opt['h']) || isset($opt['help'])) {
	help();
	exit;
}

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

function help() {
	echo <<<EOF

phpp - PHP Preprocessor with #include directive support

USAGE

  phpp [options] PATH...

OPTIONS

  -h, --help  Show this help info
  -o PATH     Output processed file to PATH. (multi)
  -I PATH     Look here for included files (multi)
  --ext       Look for files with this extension (multi)
  --eval      Evaluate PHP in included files
  -v          Verbose mode

EOF;
}
