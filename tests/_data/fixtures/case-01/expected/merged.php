<?php
$coverage = new SebastianBergmann\CodeCoverage\CodeCoverage;
$coverage->setData(array (
  '{{ baseDir }}/tests/_data/fixtures/case-01/src/A.php' =>
  array (
    11 =>
    array (
      0 => 'Sweetchuck\\CoverageMergerCli\\Test\\Fixtures\\Case01\\Tests\\ATest::testCreate',
    ),
  ),
  '{{ baseDir }}/tests/_data/fixtures/case-01/src/B.php' =>
  array (
    11 =>
    array (
      0 => 'Sweetchuck\\CoverageMergerCli\\Test\\Fixtures\\Case01\\Tests\\BTest::testCreate',
    ),
  ),
  '{{ baseDir }}/tests/_data/fixtures/case-01/src/C.php' =>
  array (
    11 =>
    array (
      0 => 'Sweetchuck\\CoverageMergerCli\\Test\\Fixtures\\Case01\\Tests\\CTest::testCreate',
    ),
  ),
));
$coverage->setTests(array (
  'UNCOVERED_FILES_FROM_WHITELIST' =>
  array (
    'size' => 'unknown',
    'status' => -1,
    'fromTestcase' => false,
  ),
  'Sweetchuck\\CoverageMergerCli\\Test\\Fixtures\\Case01\\Tests\\ATest::testCreate' =>
  array (
    'size' => 'unknown',
    'status' => 0,
    'fromTestcase' => false,
  ),
  'Sweetchuck\\CoverageMergerCli\\Test\\Fixtures\\Case01\\Tests\\BTest::testCreate' =>
  array (
    'size' => 'unknown',
    'status' => 0,
    'fromTestcase' => false,
  ),
  'Sweetchuck\\CoverageMergerCli\\Test\\Fixtures\\Case01\\Tests\\CTest::testCreate' =>
  array (
    'size' => 'unknown',
    'status' => 0,
    'fromTestcase' => false,
  ),
));

$filter = $coverage->filter();
$filter->setWhitelistedFiles(array (
  '{{ baseDir }}/tests/_data/fixtures/case-01/src/A.php' => true,
  '{{ baseDir }}/tests/_data/fixtures/case-01/src/B.php' => true,
  '{{ baseDir }}/tests/_data/fixtures/case-01/src/C.php' => true,
));

return $coverage;
