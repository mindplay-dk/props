<?php

/**
 * @var \Composer\Autoload\ClassLoader $autoloader
 */

$autoloader = require __DIR__ . '/vendor/autoload.php';

$autoloader->addPsr4('mindplay\session\\', __DIR__ . '/src');

use mindplay\props\Property;
use mindplay\props\PropertySet;

// TEST FIXTURES:

abstract class Column extends Property
{
    /**
     * @var string database column type
     */
    public $type;

    /**
     * @var bool true, if this column is required (e.g. has a NOT NULL constraint)
     */
    public $required = false;
}

class VarCharColumn extends Column
{
    /**
     * @var int maximum string length
     */
    public $length = 255;

    public function __construct()
    {
        $this->type = 'VARCHAR';
    }
}

class IntColumn extends Column
{
    /**
     * @var int default value (when inserting new records)
     */
    public $default;

    public function __construct()
    {
        $this->type = 'INT';
    }
}

abstract class Table extends PropertySet
{}

/**
 * @property IntColumn $id
 * @property VarCharColumn $first_name
 * @property VarCharColumn $last_name
 * @property IntColumn $balance
 */
class AccountTable extends Table
{
    protected function init()
    {
        parent::init(); // internally constructs all the properties

        $this->id->required = true;
        $this->first_name->length = 64;
        $this->last_name->length = 64;
        $this->balance->required = true;
        $this->balance->default = 0;
    }
}

// UNIT TEST:

header('Content-type: text/plain');

if (coverage()) {
    $filter = coverage('test')->filter();

    $filter->addDirectoryToWhitelist(__DIR__ . '/src');

    // exclude interfaces:
    $filter->addFileToBlacklist(__DIR__ . '/src/NameAware.php');
    $filter->addFileToBlacklist(__DIR__ . '/src/OwnerAware.php');
}

test(
    'Can initialize PropertySet type',
    function () {
        $account = new AccountTable();

        ok($account->id instanceof IntColumn, 'id property created');
        ok($account->first_name instanceof VarCharColumn, 'first_name property created');
        ok($account->last_name instanceof VarCharColumn, 'last_name property created');
        ok($account->balance instanceof IntColumn, 'balance property created');
    }
);

test(
    'Can initialize Property names',
    function () {
        $account = new AccountTable();

        eq($account->id->getPropertyName(), 'id', 'first_name property has a name');
        eq($account->first_name->getPropertyName(), 'first_name', 'first_name property has a name');
        eq($account->last_name->getPropertyName(), 'last_name', 'first_name property has a name');
        eq($account->balance->getPropertyName(), 'balance', 'first_name property has a name');
    }
);

test(
    'Can initialize Property owner reference',
    function () {
        $account = new AccountTable();

        eq($account->id->getPropertyOwner(), $account, 'id property has owner');
        eq($account->first_name->getPropertyOwner(), $account, 'first_name property has owner');
        eq($account->last_name->getPropertyOwner(), $account, 'last_name property has owner');
        eq($account->balance->getPropertyOwner(), $account, 'balance property has owner');
    }
);

test(
    'Can enumerate properties',
    function () {
        $account = new AccountTable();

        $expected = array(
            'id' => $account->id,
            'first_name' => $account->first_name,
            'last_name' => $account->last_name,
            'balance' => $account->balance,
        );

        eq($account->getProperties(), $expected, 'enumerated properties in order');
    }
);

test(
    'Expected exceptions',
    function () {
        $account = new AccountTable();

        expect(
            'RuntimeException',
            'throws on attempted write',
            function () use ($account) {
                $account->id = 'foo';
            }
        );
    }
);

if (coverage()) {
    $report = new PHP_CodeCoverage_Report_Text(10, 90, false, false);

    echo $report->process(coverage(), false);

    $report = new PHP_CodeCoverage_Report_Clover();

    $report->process(coverage(), 'build/logs/clover.xml');
}

exit(status()); // exits with errorlevel (for CI tools etc.)

// https://gist.github.com/mindplay-dk/4260582

/**
 * @param string   $name     test description
 * @param callable $function test implementation
 */
function test($name, $function)
{
    echo "\n=== $name ===\n\n";

    try {
        call_user_func($function);
    } catch (Exception $e) {
        ok(false, "UNEXPECTED EXCEPTION", $e);
    }
}

/**
 * @param bool   $result result of assertion
 * @param string $why    description of assertion
 * @param mixed  $value  optional value (displays on failure)
 */
function ok($result, $why = null, $value = null)
{
    if ($result === true) {
        echo "- PASS: " . ($why === null ? 'OK' : $why) . ($value === null ? '' : ' (' . format($value) . ')') . "\n";
    } else {
        echo "# FAIL: " . ($why === null ? 'ERROR' : $why) . ($value === null ? '' : ' - ' . format($value, true)) . "\n";
        status(false);
    }
}

/**
 * @param mixed  $value    value
 * @param mixed  $expected expected value
 * @param string $why      description of assertion
 */
function eq($value, $expected, $why = null)
{
    $result = $value === $expected;

    $info = $result
        ? format($value)
        : "expected: " . format($expected, true) . ", got: " . format($value, true);

    ok($result, ($why === null ? $info : "$why ($info)"));
}

/**
 * @param string   $exception_type Exception type name
 * @param string   $why            description of assertion
 * @param callable $function       function expected to throw
 */
function expect($exception_type, $why, $function)
{
    try {
        call_user_func($function);
    } catch (Exception $e) {
        if ($e instanceof $exception_type) {
            ok(true, $why, $e);
            return;
        } else {
            $actual_type = get_class($e);
            ok(false, "$why (expected $exception_type but $actual_type was thrown)");
            return;
        }
    }

    ok(false, "$why (expected exception $exception_type was NOT thrown)");
}

/**
 * @param mixed $value
 * @param bool  $verbose
 *
 * @return string
 */
function format($value, $verbose = false)
{
    if ($value instanceof Exception) {
        return get_class($value)
        . ($verbose ? ": \"" . $value->getMessage() . "\"" : '');
    }

    if (! $verbose && is_array($value)) {
        return 'array[' . count($value) . ']';
    }

    if (is_bool($value)) {
        return $value ? 'TRUE' : 'FALSE';
    }

    if (is_object($value) && !$verbose) {
        return get_class($value);
    }

    return print_r($value, true);
}

/**
 * @param bool|null $status test status
 *
 * @return int number of failures
 */
function status($status = null)
{
    static $failures = 0;

    if ($status === false) {
        $failures += 1;
    }

    return $failures;
}

/**
 * @param string|null $text description (to start coverage); or null (to stop coverage)
 * @return PHP_CodeCoverage|null
 */
function coverage($text = null)
{
    static $coverage = null;
    static $running = false;

    if ($coverage === false) {
        return null; // code coverage unavailable
    }

    if ($coverage === null) {
        try {
            $coverage = new PHP_CodeCoverage;
        } catch (PHP_CodeCoverage_Exception $e) {
            echo "# Notice: no code coverage run-time available\n";
            $coverage = false;
            return null;
        }
    }

    if (is_string($text)) {
        $coverage->start($text);
        $running = true;
    } else {
        if ($running) {
            $coverage->stop();
            $running = false;
        }
    }

    return $coverage;
}
