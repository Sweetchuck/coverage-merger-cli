# coverage-merger CLI

[![CircleCI](https://circleci.com/gh/Sweetchuck/coverage-merger-cli/tree/1.x.svg?style=svg)](https://circleci.com/gh/Sweetchuck/coverage-merger-cli/?branch=1.x)
[![codecov](https://codecov.io/gh/Sweetchuck/coverage-merger-cli/branch/1.x/graph/badge.svg?token=HSF16OGPyr)](https://app.codecov.io/gh/Sweetchuck/coverage-merger-cli/branch/1.x)

CLI tool to merge two or more coverage report files into one.


## Requirements

* PHP `>=7.4 <8.0`
* The input coverage report PHP files generated by `phpunit/php-code-coverage ^7.0 || ^8.0`

For support of `phpunit/php-code-coverage ^9.0` check branch [2.x](https://github.com/Sweetchuck/coverage-merger-cli/tree/2.x)


## Install as dependency

`composer require --dev 'sweetchuck/coverage-merger-cli'`


## Install independently

1. Download the `coverage-merger.phar` from the latest [release](https://github.com/Sweetchuck/coverage-merger-cli/releases)
2. ```bash
   mv ~/Downloads/coverage-merger.phar ~/bin/coverage-merger
   chmod +x ~/bin/coverage-merger
   ```


## Usage

```bash
phpunit --coverage-php='reports/coverage/ATest.php' 'tests/src/Unit/ATest.php'
phpunit --coverage-php='reports/coverage/BTest.php' 'tests/src/Unit/BTest.php'
phpunit --coverage-php='reports/coverage/CTest.php' 'tests/src/Unit/CTest.php'

coverage-merger merge:files \
    'reports/coverage/ATest.php' \
    'reports/coverage/BTest.php' \
    'reports/coverage/CTest.php' \
    > 'reports/coverage.php'

# or
find ./reports/coverage/ -type f | coverage-merger merge:files --output-file='./reports/coverage.php'
```
