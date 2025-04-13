<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value of class Support\\\\Domain\\\\ValueObjects\\\\OrderNo constructor expects int\\<1, max\\>, int given\\.$#',
	'identifier' => 'argument.type',
	'count' => 2,
	'path' => __DIR__ . '/packages/JourneyLog/Infrastructures/Repositories/JourneyLogRepository.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value of class Support\\\\Domain\\\\ValueObjects\\\\OrderNo constructor expects int\\<1, max\\>, int given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/packages/JourneyLogLinkType/Infrastructures/Repositories/JourneyLogLinkTypeRepository.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value of class Support\\\\Domain\\\\ValueObjects\\\\OrderNo constructor expects int\\<1, max\\>, int given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/packages/Song/Infrastructures/Repositories/SongRepository.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
