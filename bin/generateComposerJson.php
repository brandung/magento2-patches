<?php

$composerConfig = [
    'name' => 'brandung/magento2-patches',
    'license' => 'MIT',
    'type' => 'metapackage',
    'description' => 'Install M2 Patches',
    'require' => [
        'cweagans/composer-patches' => '^1.6.5'
    ]
];

$options = [
    'http' => [
        'method' => 'GET',
        'header' => [
            'User-Agent: PHP'
        ]
    ]
];

/** @noinspection PhpComposerExtensionStubsInspection */
$patchRepoContents = json_decode(file_get_contents(
    'https://api.github.com/repos/ConvertGroupsAS/magento2-patches/contents',
    false,
    stream_context_create($options)
), JSON_OBJECT_AS_ARRAY);

$readMeFileInfo = array_reduce($patchRepoContents, function ($readmeFile, $currentFile) {
    return $currentFile['name'] === 'README.md' ? $currentFile : $readmeFile;
}, null);

if (null === $readMeFileInfo) {
    /** @noinspection PhpUnhandledExceptionInspection */
    throw new \RuntimeException('README.md File not found in repository');
}

$readmeFileContents = file_get_contents($readMeFileInfo['download_url']);

$pattern = '
/
\{              # { character
    (?:         # non-capturing group
        [^{}]   # anything that is not a { or }
        |       # OR
        (?R)    # recurses the entire pattern
    )*          # previous group zero or more times
\}              # } character
/x
';

$matches = null;
preg_match_all($pattern, $readmeFileContents, $matches);

if (false === isset($matches[0][0])) {
    /** @noinspection PhpUnhandledExceptionInspection */
    throw new \RuntimeException('Could not find composer example in README.md File');
}

/** @noinspection PhpComposerExtensionStubsInspection */
$composerExample = json_decode($matches[0][0], JSON_OBJECT_AS_ARRAY);
$composerConfig['extra'] = $composerExample['extra'];

/** @noinspection PhpComposerExtensionStubsInspection */
file_put_contents(dirname(__DIR__) . '/composer.json', json_encode($composerConfig, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));