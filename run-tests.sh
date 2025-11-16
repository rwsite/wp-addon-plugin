#!/bin/bash

# Test runner script for WP Addon Plugin
# Usage: ./run-tests.sh [unit|integration|all]

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$(dirname "$SCRIPT_DIR")"

cd "$PLUGIN_DIR"

# Check if composer dependencies are installed
if [ ! -d "vendor" ]; then
    echo "Installing dependencies..."
    composer install
fi

# Check if phpunit is available
if [ ! -f "vendor/bin/phpunit" ]; then
    echo "PHPUnit not found. Installing dependencies..."
    composer install
fi

# Parse arguments
TEST_SUITE=${1:-all}

case $TEST_SUITE in
    "unit")
        echo "Running unit tests..."
        ./vendor/bin/phpunit --testsuite unit --colors=always
        ;;
    "integration")
        echo "Running integration tests..."
        ./vendor/bin/phpunit --testsuite integration --colors=always
        ;;
    "coverage")
        echo "Running tests with coverage..."
        ./vendor/bin/phpunit --coverage-html coverage --colors=always
        ;;
    "all"|*)
        echo "Running all tests..."
        ./vendor/bin/phpunit --colors=always
        ;;
esac

echo "Tests completed successfully!"
