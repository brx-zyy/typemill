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
    'language' => 'en',
    'langattr' => 'en',
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
file_put_contents('content/docs/private/source.yaml', Yaml::dump(['meta' => ['title' => 'Source', 'pageid' => 'a1b2c3d4e5f60718']], 6));
file_put_contents('content/docs/private/index.yaml', Yaml::dump(['meta' => ['title' => 'Private folder', 'pageid' => 'folderprivate001']], 6));
file_put_contents('content/docs/internal/sibling.txt', "Restricted sibling draft with media/files/restricted-secret.pdf reference.\n");
file_put_contents('content/docs/internal/sibling.yaml', Yaml::dump(['meta' => ['title' => 'Sibling', 'pageid' => 'sibling000000001']], 6));
file_put_contents('content/docs/internal/index.yaml', Yaml::dump(['meta' => ['title' => 'Internal folder', 'pageid' => 'folderinternal01']], 6));
file_put_contents('content/_fr/docs/index.yaml', Yaml::dump(['meta' => ['title' => 'French docs folder', 'pageid' => 'frdocs000000001']], 6));
file_put_contents('content/_fr/private/index.yaml', Yaml::dump(['meta' => ['title' => 'French private folder', 'pageid' => 'frprivate000001']], 6));
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
        'fr' => 'fr/private/victim',
        'parent' => null,
    ],
];
file_put_contents('data/multilang/index.txt', serialize($index));
$loadedIndex = (new \Typemill\Models\Multilang())->getMultilangIndex();
if (!is_array($loadedIndex) || !isset($loadedIndex['a1b2c3d4e5f60718'])) {
    throw new RuntimeException('Multilang index fixture is not readable through Typemill storage API: ' . var_export($loadedIndex, true));
}
echo "Multilang index preflight: readable through Typemill storage API.\n";

echo "Fixture ready: editor has docs/private and fr/docs only.\n";
