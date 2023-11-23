<?php

declare(strict_types=1);

use Tester\Environment;

$loader = @include __DIR__ . '/../vendor/autoload.php';

if (!$loader) {
    echo 'Install Nette Tester using `composer install`';
    exit(1);
}

Environment::setup();
Environment::bypassFinals();

# disable E_DEPRECATED errors from the vendor code (some nette packages before v3.1 are not fully compatible with PHP 8.1/8.2)
$previousHandler = set_error_handler(static function (int $errNo, string $errStr, string $errFile, int $errLine) use (&$previousHandler): bool {
    $vendor = realpath(__DIR__ . '/../vendor');

    if (E_DEPRECATED === $errNo && 0 === strncmp($errFile, $vendor, strlen($vendor))) {
        return true;
    }

    return $previousHandler($errNo, $errStr, $errFile, $errLine);
});

return $loader;
