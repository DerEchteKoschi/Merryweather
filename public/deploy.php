<?php
if (!file_exists(dirname(__DIR__) . '/public/deploy/vendor/autoload.php')) {
    exec('COMPOSER_HOME=. php deploy/composer.phar install -n --working-dir=deploy', $output, $res);
    if ($res !== 0) {
        header('HTTP/1.0 500');
    } else {
        foreach ($output as $line) {
            echo $line . '<br>';
        }
        echo 'please reload';
    }
    exit;
}
require_once dirname(__DIR__) . '/public/deploy/vendor/autoload.php';

use lfkeitel\phptotp\{Base32, Totp};

if (!file_exists(dirname(__DIR__) . '/.env.local.php')) {
    header('HTTP/1.0 500');
    echo 'no .env.local.php file found';
    exit;
}
$env = require dirname(__DIR__) . '/.env.local.php';

if (!isset($env['POOR_MANS_DEPLOYMENT']) || $env['POOR_MANS_DEPLOYMENT'] === false) {
    header('HTTP/1.0 404 not found');
    exit;
}

//$secret = Base32::decode('ykyf76ukhwizm6s4zghdfhypcu');
$secret = Base32::decode(preg_replace('/[^2-7A-Z]/', "", strtoupper($env['APP_SECRET'])));
$keys = [];
$keys[] = (new Totp())->GenerateToken($secret, time() - 10);
$keys[] = (new Totp())->GenerateToken($secret);


if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="DeploymentHelper"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'wrong';
    exit;
}

if ($_SERVER['PHP_AUTH_USER'] === 'deployment' && in_array($_SERVER['PHP_AUTH_PW'], $keys, true)) {

    echo '<div style="font-family: monospace">';
    runShell('composer install', 'COMPOSER_HOME=. php deploy/composer.phar install -n --working-dir=..');
    runShell('cache warmup', '../bin/console cache:warmup --env=prod --no-debug');
    runShell('migrations', '../bin/console doctrine:migration:migrate -n --env=prod --no-debug');
    echo '</div>';
} else {
    header('HTTP/1.0 401 Unauthorized');
    exit;
}

function runShell($title, $string): void
{
    echo '<div><h4>executing ' . $title;
    exec($string . ' 2>&1', $output, $code);
    echo '<br><span style="color:' . ($code == 0 ? 'green' : 'red') . ';">Resultcode: ' . $code . '</span></h4><ul>';
    foreach ($output as $line) {
        echo '<li>' . $line . '</li>';
    }
    echo '</ul></div>';
}


