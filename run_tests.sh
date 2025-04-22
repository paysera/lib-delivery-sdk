#!/bin/bash

PHPUNIT="./vendor/bin/phpunit"
CONFIG_FILE="phpunit.xml"

if [ ! -f "$PHPUNIT" ]; then
    echo "PHPUnit not found at $PHPUNIT"
    echo "Try running: composer install"
    exit 1
fi

echo "Found PHPUnit at $PHPUNIT"

if [ ! -f "$CONFIG_FILE" ]; then
    echo "PHPUnit config file not found: $CONFIG_FILE"
    exit 1
fi

echo "Running tests with config: $CONFIG_FILE"
echo "----------------------------------------"

$PHPUNIT --configuration "$CONFIG_FILE"
status=$?

echo "----------------------------------------"
if [ $status -eq 0 ]; then
    echo "All tests passed."
else
    echo "Some tests failed or are risky/skipped."
fi

exit $status
