<?php

namespace Fostam\GetOpts\Tests\Config;

use Fostam\GetOpts\Exception\OptionConfigException;
use PHPUnit\Framework\TestCase;
use Fostam\GetOpts\Config\Option;

class OptionTest extends TestCase {
    /** @var Option */
    private $option;

    public function setUp() {
        parent::setUp();
        $this->option = new Option();
    }

    public function testShort() {
        $this->option->short('t');
        $this->assertEquals('t', $this->option->get(Option::SHORT));
        $this->assertEquals('t', $this->option->get()[Option::SHORT]);
    }

    public function testShortInvalid() {
        $this->expectException(OptionConfigException::class);
        $this->option->short('test');
    }

    public function testLong() {
        $this->option->long('test');
        $this->assertEquals('test', $this->option->get(Option::LONG));
        $this->assertEquals('test', $this->option->get()[Option::LONG]);
    }

    public function testLongInvalid() {
        $this->expectException(OptionConfigException::class);
        $this->option->long('');
    }

    public function testDescription() {
        $this->option->short('t')->description('test');
        $this->assertEquals('test', $this->option->get(Option::DESCRIPTION));
        $this->assertEquals('test', $this->option->get()[Option::DESCRIPTION]);
    }

    public function testArgument() {
        $this->option->short('t')->argument('test');
        $this->assertEquals('test', $this->option->get(Option::ARGUMENT));
        $this->assertEquals('test', $this->option->get()[Option::ARGUMENT]);
    }

    public function testRequired() {
        $this->option->short('t')->required();
        $this->assertTrue($this->option->get(Option::REQUIRED));
        $this->assertTrue($this->option->get()[Option::REQUIRED]);
    }

    public function testDefaultValue() {
        $this->option->short('t')->defaultValue('test');
        $this->assertEquals('test', $this->option->get(Option::DEFAULT_VALUE));
        $this->assertEquals('test', $this->option->get()[Option::DEFAULT_VALUE]);
    }

    public function testMultiple() {
        $this->option->short('t')->argument('test')->multiple();
        $this->assertTrue($this->option->get(Option::MULTIPLE));
        $this->assertTrue($this->option->get()[Option::MULTIPLE]);
    }

    public function testNegatable() {
        $this->option->long('test')->negatable();
        $this->assertTrue($this->option->get(Option::NEGATABLE));
        $this->assertTrue($this->option->get()[Option::NEGATABLE]);
    }

    public function testIncrementable() {
        $this->option->short('t')->incrementable();
        $this->assertEquals(1, $this->option->get(Option::INCREMENTABLE));
        $this->assertEquals(1, $this->option->get()[Option::INCREMENTABLE]);
    }

    public function testIncrementableValue() {
        $this->option->short('t')->incrementable(2);
        $this->assertEquals(2, $this->option->get(Option::INCREMENTABLE));
        $this->assertEquals(2, $this->option->get()[Option::INCREMENTABLE]);
    }

    public function testValidator() {
        $validator = function() {};
        $this->option->short('t')->validator($validator);
        $this->assertEquals($validator, $this->option->get(Option::VALIDATOR));
        $this->assertEquals($validator, $this->option->get()[Option::VALIDATOR]);
    }

    public function testValidateShort() {
        $this->assertTrue(Option::validateShort('a'));
        $this->assertTrue(Option::validateShort('5'));
        $this->assertFalse(Option::validateShort('aa'));
        $this->assertFalse(Option::validateShort('_'));
        $this->assertFalse(Option::validateShort(null));
        $this->assertFalse(Option::validateShort(''));
    }

    public function testValidateLong() {
        $this->assertTrue(Option::validateLong('test'));
        $this->assertTrue(Option::validateLong('t'));
        $this->assertTrue(Option::validateLong('33333'));
        $this->assertFalse(Option::validateLong('*'));
        $this->assertFalse(Option::validateLong(null));
        $this->assertFalse(Option::validateLong(''));
    }

    public function testArgumentIncrementableConstraint() {
        $this->expectException(OptionConfigException::class);
        $this->option->argument('test')->incrementable();
        $this->option->get();
    }

    public function testArgumentNegatableConstraint() {
        $this->expectException(OptionConfigException::class);
        $this->option->argument('test')->negatable();
        $this->option->get();
    }

    public function testArgumentlessMultipleConstraint() {
        $this->expectException(OptionConfigException::class);
        $this->option->multiple();
        $this->option->get();
    }

    public function testNegatableLongConstraint() {
        $this->expectException(OptionConfigException::class);
        $this->option->short('t')->negatable();
        $this->option->get();
    }

    public function testRequiredDefaultValueConstraint() {
        $this->expectException(OptionConfigException::class);
        $this->option->required()->defaultValue('test');
        $this->option->get();
    }

    public function testShortLongConstraint() {
        $this->expectException(OptionConfigException::class);
        $this->option->get();
    }

    public function testArgumentOnInvalidCharacter() {
        $this->expectException(OptionConfigException::class);
        $this->option->argument('*****');
    }

    public function testGetOnInvalidParam() {
        $this->expectException(OptionConfigException::class);
        $this->option->short('t')->required();
        $this->option->get('******');
    }
}
