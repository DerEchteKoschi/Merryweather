<?php
$keys = [];

if (file_exists(dirname(__DIR__) . '/public/ci.php')) {
    $keys[] = include_once dirname(__DIR__) . '/public/ci.php';
}

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="DeploymentHelper"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'wrong';
    exit;
}

if ($_SERVER['PHP_AUTH_USER'] === 'deployment' && in_array($_SERVER['PHP_AUTH_PW'], $keys, true)) {
    exec('../bin/console doctrine:migration:migrate -n --env=prod --no-debug 2>&1', $output, $code);
    if ($code !== 0) {
        header('HTTP/1.0 500 something went wrong');
    } else {
        header('HTTP/1.0 200 migration executed');
    }
    foreach ($output as $line) {
        echo $line . PHP_EOL;
    }
} else {
    header('HTTP/1.0 401 Unauthorized');
    exit;
}



