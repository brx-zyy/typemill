<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/system/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$root = dirname(__DIR__, 2);
chdir($root);

$directories = [
    'settings/users',
    'content/docs/private',
    'content/docs/internal',
    'content/_fr/docs',
    'content/_fr/private',
    'data/multilang',
    'data/navigation',
    'cache',
    'media/tmp',
    'media/original',
    'media/live',
    'media/thumbs',
    'media/custom',
    'media/files',
];
foreach ($directories as $directory) {
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException("Cannot create $directory");
    }
}

$settings = [
    'setup' => false,
    'author' => 'researcher',
    'mailfrom' => 'researcher@example.invalid',
    'projects' => 'languages',
    'baseprojectid' => 'en',
    'baseprojectlabel' => 'English',
    'projectinstances' => ['fr' => 'French'],
    'fqdn' => 'http://127.0.0.1:8080',
    'trustedipsforapi' => '',
    'trustedhostsforapi' => '',
];
file_put_contents('settings/settings.yaml', Yaml::dump($settings, 8));

$user = [
    'username' => 'researcher',
    'email' => 'researcher@example.invalid',
    'userrole' => 'editor',
    'password' => password_hash('ResearchPass123!', PASSWORD_DEFAULT),
    'apiaccess' => true,
    'folderaccess' => 'docs/private,fr/docs',
];
file_put_contents('settings/users/researcher.yaml', Yaml::dump($user, 8));

file_put_contents('content/docs/private/source.txt', "Base page draft\n");
file_put_contents('content/docs/internal/sibling.txt', "Restricted sibling draft\n");
file_put_contents('content/_fr/docs/decoy.txt', "In-scope decoy translation\n");
file_put_contents('content/_fr/docs/decoy.yaml', Yaml::dump([
    'meta' => [
        'title' => 'Decoy translation',
        'pageid' => 'decoy00000000001',
        'translation_for' => 'decoy00000000002',
    ],
], 6));
file_put_contents('content/_fr/private/victim.txt', "Out-of-scope translation\n");
file_put_contents('content/_fr/private/victim.yaml', Yaml::dump([
    'meta' => [
        'title' => 'Protected translation',
        'pageid' => 'victim0000000001',
        'translation_for' => 'a1b2c3d4e5f60718',
    ],
], 6));

$index = [
    'a1b2c3d4e5f60718' => [
        'en' => 'docs/private/source',
        'fr' => 'private/victim',
        'parent' => null,
    ],
];
file_put_contents('data/multilang/index.txt', serialize($index));

echo "Fixture ready: editor has docs/private and fr/docs only.\n";
