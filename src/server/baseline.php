<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\.\\.\\.\\$constructors of method CuyZ\\\\Valinor\\\\MapperBuilder\\:\\:registerConstructor\\(\\) expects \\(pure\\-callable\\(\\)\\: mixed\\)\\|class\\-string, Closure\\(string, string\\)\\: Creator\\\\Domain\\\\Models\\\\Creator given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/packages/Support/Infrastructures/Mapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\.\\.\\.\\$constructors of method CuyZ\\\\Valinor\\\\MapperBuilder\\:\\:registerConstructor\\(\\) expects \\(pure\\-callable\\(\\)\\: mixed\\)\\|class\\-string, Closure\\(string, string, int\\)\\: Performer\\\\Domain\\\\Models\\\\Performer given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/packages/Support/Infrastructures/Mapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\.\\.\\.\\$constructors of method CuyZ\\\\Valinor\\\\MapperBuilder\\:\\:registerConstructor\\(\\) expects \\(pure\\-callable\\(\\)\\: mixed\\)\\|class\\-string, Closure\\(string, string, string\\)\\: AdminUser\\\\Domain\\\\Models\\\\AdminUser given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/packages/Support/Infrastructures/Mapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\.\\.\\.\\$constructors of method CuyZ\\\\Valinor\\\\MapperBuilder\\:\\:registerConstructor\\(\\) expects \\(pure\\-callable\\(\\)\\: mixed\\)\\|class\\-string, Closure\\(string, string, string, DateTimeImmutable, int\\)\\: Auth\\\\Domain\\\\Models\\\\Credential\\\\RefreshToken\\\\RefreshToken given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/packages/Support/Infrastructures/Mapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\.\\.\\.\\$constructors of method CuyZ\\\\Valinor\\\\MapperBuilder\\:\\:registerConstructor\\(\\) expects \\(pure\\-callable\\(\\)\\: mixed\\)\\|class\\-string, Closure\\(string, string, string, int, int, list\\<array\\{creatorId\\: string, orderNo\\: int\\}\\>, list\\<array\\{creatorId\\: string, orderNo\\: int\\}\\>, list\\<array\\{creatorId\\: string, orderNo\\: int\\}\\>\\)\\: Song\\\\Domain\\\\Models\\\\Song given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/packages/Support/Infrastructures/Mapper.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
