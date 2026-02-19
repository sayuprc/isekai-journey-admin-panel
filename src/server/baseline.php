<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\.\\.\\.\\$constructors of method CuyZ\\\\Valinor\\\\MapperBuilder\\:\\:registerConstructor\\(\\) expects \\(pure\\-callable\\(\\)\\: mixed\\)\\|class\\-string, Closure\\(.*\\)\\: .*\\.$#',
	'identifier' => 'argument.type',
	'count' => 5,
	'path' => __DIR__ . '/packages/Support/Infrastructures/Mapper.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
