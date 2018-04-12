<?php

namespace Fostam\GetOpts\Tests;

use Fostam\GetOpts\Exception\ArgumentConfigException;
use Fostam\GetOpts\Exception\InvalidOptionException;
use PHPUnit\Framework\TestCase;
use Fostam\GetOpts\Config\Argument;
use Fostam\GetOpts\Exception\MissingArgumentsException;
use Fostam\GetOpts\Exception\MissingOptionArgumentException;
use Fostam\GetOpts\Exception\InvalidArgumentException;
use Fostam\GetOpts\Config\Option;
use Fostam\GetOpts\Parser;

class ParserTest extends TestCase {
    // bool options
    public function testShortOptionBoolGiven() {
        $opt = new Option();
        $opt->short('t');

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse(['-t']);

        $result = $parser->getResult();
        $this->assertEquals(['test' => true], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testShortOptionBoolOmitted() {
        $opt = new Option();
        $opt->short('t');

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse([]);

        $result = $parser->getResult();
        $this->assertEquals(['test' => null], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testShortOptionBoolOmittedWithDefault() {
        $opt = new Option();
        $opt->short('t')->defaultValue(false);

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse([]);

        $result = $parser->getResult();
        $this->assertEquals(['test' => false], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testLongOptionBoolGiven() {
        $opt = new Option();
        $opt->long('test');

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse(['--test']);

        $result = $parser->getResult();
        $this->assertEquals(['test' => true], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testLongOptionBoolOmitted() {
        $opt = new Option();
        $opt->long('test');

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse([]);

        $result = $parser->getResult();
        $this->assertEquals(['test' => null], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testLongOptionBoolOmittedWithDefault() {
        $opt = new Option();
        $opt->long('test')->defaultValue(false);

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse([]);

        $result = $parser->getResult();
        $this->assertEquals(['test' => false], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }


    // argument options
    public function testShortOptionArgGiven() {
        $opt = new Option();
        $opt->short('t')->argument('optarg');

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse(['-t', 'argval']);

        $result = $parser->getResult();
        $this->assertEquals(['test' => 'argval'], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testShortOptionArgOmitted() {
        $opt = new Option();
        $opt->short('t')->argument('optarg');

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse([]);

        $result = $parser->getResult();
        $this->assertEquals(['test' => null], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testShortOptionArgOmittedWithDefault() {
        $opt = new Option();
        $opt->short('t')->argument('optarg')->defaultValue('defval');

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse([]);

        $result = $parser->getResult();
        $this->assertEquals(['test' => 'defval'], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testLongOptionArgGiven() {
        $opt = new Option();
        $opt->long('test')->argument('optarg');

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse(['--test', 'argval']);

        $result = $parser->getResult();
        $this->assertEquals(['test' => 'argval'], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testLongOptionArgGivenWithEqual() {
        $opt = new Option();
        $opt->long('test')->argument('optarg');

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse(['--test=argval']);

        $result = $parser->getResult();
        $this->assertEquals(['test' => 'argval'], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testLongOptionArgOmitted() {
        $opt = new Option();
        $opt->long('test')->argument('optarg');

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse([]);

        $result = $parser->getResult();
        $this->assertEquals(['test' => null], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testLongOptionArgOmittedWithDefault() {
        $opt = new Option();
        $opt->long('test')->argument('optarg')->defaultValue('defval');

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse([]);

        $result = $parser->getResult();
        $this->assertEquals(['test' => 'defval'], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }


    // incrementables
    public function testIncrementable() {
        $opt = new Option();
        $opt->long('test')->short('t')->incrementable(2);

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse(['-t', '--test', '-t']);

        $result = $parser->getResult();
        $this->assertEquals(['test' => 6], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }


    // multiples
    public function testMultipleGiven() {
        $opt = new Option();
        $opt->long('test')->short('t')->multiple()->argument('optarg');

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse(['-t', 'a', '--test', 'b', '-t', 'c']);

        $result = $parser->getResult();
        $this->assertEquals(['test' => ['a', 'b', 'c']], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testMultipleOmitted() {
        $opt = new Option();
        $opt->long('test')->short('t')->multiple()->argument('optarg')->defaultValue(['a', 'b']);

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse([]);

        $result = $parser->getResult();
        $this->assertEquals(['test' => ['a', 'b']], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testMultipleArgumentMissingPartly() {
        $this->expectException(MissingOptionArgumentException::class);

        $opt = new Option();
        $opt->long('test')->short('t')->multiple()->argument('optarg')->defaultValue(['a', 'b']);

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse(['-t', 'a', '-t']);
    }


    // short option bundling
    public function testShortOptionBundling() {
        $opt1 = new Option();
        $opt1->short('a');

        $opt2 = new Option();
        $opt2->short('b')->incrementable();

        $opt3 = new Option();
        $opt3->short('c')->argument('optarg')->defaultValue('defval');

        $parser = new Parser([ 'a' => $opt1, 'b' => $opt2, 'c' => $opt3 ]);
        $parser->parse(['-bababc', 'argval']);

        $result = $parser->getResult();
        $this->assertEquals(['a' => true, 'b' => 3, 'c' => 'argval'], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testShortOptionBundlingMissingArgument() {
        $this->expectException(MissingOptionArgumentException::class);

        $opt1 = new Option();
        $opt1->short('a');

        $opt2 = new Option();
        $opt2->short('b')->incrementable();

        $opt3 = new Option();
        $opt3->short('c')->argument('optarg')->defaultValue('defval');

        $parser = new Parser([ 'a' => $opt1, 'b' => $opt2, 'c' => $opt3 ]);
        $parser->parse(['-bababc']);
    }


    // negatables
    public function testNegatableTrue() {
        $opt = new Option();
        $opt->long('test')->negatable();

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse(['--test']);

        $result = $parser->getResult();
        $this->assertEquals(['test' => true], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testNegatableFalse() {
        $opt = new Option();
        $opt->long('test')->negatable();

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse(['--notest']);

        $result = $parser->getResult();
        $this->assertEquals(['test' => false], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }


    // validators
    public function testOptionArgumentValidatorTrue() {
        $validator = function() {
            return true;
        };

        $opt = new Option();
        $opt->long('test')->validator($validator);

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse(['--test']);

        $result = $parser->getResult();
        $this->assertEquals(['test' => true], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals([], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testOptionArgumentValidatorFalse() {
        $this->expectException(InvalidOptionException::class);

        $validator = function() {
            return false;
        };

        $opt = new Option();
        $opt->long('test')->argument('optarg')->validator($validator);

        $parser = new Parser([ 'test' => $opt ]);
        $parser->parse(['--test', 'optval']);
    }

    public function testArgumentValidatorTrue() {
        $validator = function() {
            return true;
        };

        $arg = new Argument();
        $arg->validator($validator);

        $parser = new Parser([], [ 'test' => $arg ]);
        $parser->parse(['test']);

        $result = $parser->getResult();
        $this->assertEquals([], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals(['test' => 'test'], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testArgumentValidatorFalse() {
        $this->expectException(InvalidArgumentException::class);

        $validator = function() {
            return false;
        };

        $arg = new Argument();
        $arg->validator($validator);

        $parser = new Parser([], [ 'test' => $arg ]);
        $parser->parse(['test']);
    }


    // arguments
    public function testArgumentGiven() {
        $arg = new Argument();

        $parser = new Parser([], [ 'arg' => $arg ]);
        $parser->parse(['test']);
        $result = $parser->getResult();
        $this->assertEquals([], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals(['arg' => 'test'], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testArgumentOmitted() {
        $arg = new Argument();

        $parser = new Parser([], [ 'arg' => $arg ]);
        $parser->parse([]);
        $result = $parser->getResult();
        $this->assertEquals([], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals(['arg' => null], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testArgumentOmittedWithDefault() {
        $arg = new Argument();
        $arg->defaultValue('defval');

        $parser = new Parser([], [ 'arg' => $arg ]);
        $parser->parse([]);
        $result = $parser->getResult();
        $this->assertEquals([], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals(['arg' => 'defval'], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testArgumentRequiredGiven() {
        $arg = new Argument();
        $arg->required();

        $parser = new Parser([], [ 'arg' => $arg ]);
        $parser->parse(['test']);
        $result = $parser->getResult();
        $this->assertEquals([], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals(['arg' => 'test'], $result[Parser::RESULT_ARGUMENTS]);
    }

    public function testArgumentRequiredOmitted() {
        $this->expectException(MissingArgumentsException::class);

        $arg = new Argument();
        $arg->required();

        $parser = new Parser([], [ 'arg' => $arg ]);
        $parser->parse([]);
    }


    // end of option processing
    public function testEndOfOptionProcessing() {
        $opt = new Option();
        $opt->short('t')->long('test')->incrementable();

        $arg1 = new Argument();
        $arg2 = new Argument();
        $arg3 = new Argument();
        $arg4 = new Argument();

        $parser = new Parser([ 'test' => $opt ], ['a' => $arg1, 'b' => $arg2, 'c' => $arg3, 'd' => $arg4]);
        $parser->parse(['--test', '-t', 'a', '--', 'b', '-t', '--test']);

        $result = $parser->getResult();
        $this->assertEquals(['test' => 2], $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals(['a' => 'a', 'b' => 'b', 'c' => '-t', 'd' => '--test'], $result[Parser::RESULT_ARGUMENTS]);
    }


    // options / arguments mixed
    /**
     * @dataProvider optionsArgumentsMixedProvider
     * @param $args
     * @param $expectedOpts
     * @param $expectedArgs
     */
    public function testOptionsArgumentsMixed($args, $expectedOpts, $expectedArgs) {
        $opt1 = new Option();
        $opt1->short('a');

        $opt2 = new Option();
        $opt2->short('b')->incrementable();

        $opt3 = new Option();
        $opt3->short('c')->argument('optarg')->defaultValue('defval');

        $arg1 = new Argument();
        $arg1->required();

        $arg2 = new Argument();

        $parser = new Parser([ 'a' => $opt1, 'b' => $opt2, 'c' => $opt3 ], [ 'x' => $arg1, 'y' => $arg2 ]);
        $parser->parse($args);
        $result = $parser->getResult();
        $this->assertEquals($expectedOpts, $result[Parser::RESULT_OPTIONS]);
        $this->assertEquals($expectedArgs, $result[Parser::RESULT_ARGUMENTS]);
    }

    public function optionsArgumentsMixedProvider() {
        return [
            [ ['x'], ['a' => null, 'b' => null, 'c' => 'defval'], ['x' => 'x', 'y' => null] ],
            [ ['x', 'y'], ['a' => null, 'b' => null, 'c' => 'defval'], ['x' => 'x', 'y' => 'y'] ],
            [ ['-c', 'c', 'x'], ['a' => null, 'b' => null, 'c' => 'c'], ['x' => 'x', 'y' => null] ],
            [ ['x', '-c', 'c'], ['a' => null, 'b' => null, 'c' => 'c'], ['x' => 'x', 'y' => null] ],
            [ ['x', '-c', 'c', 'y'], ['a' => null, 'b' => null, 'c' => 'c'], ['x' => 'x', 'y' => 'y'] ],
            [ ['-bac', 'c', 'x', '-b'], ['a' => true, 'b' => 2, 'c' => 'c'], ['x' => 'x', 'y' => null] ],
        ];
    }


    // option / argument constraints
    public function testArgumentRequiredFollowsOptionalConstraint() {
        $this->expectException(ArgumentConfigException::class);

        $arg1 = new Argument();

        $arg2 = new Argument();
        $arg2->required();

        $parser = new Parser([], [ 'x' => $arg1, 'y' => $arg2 ]);
        $parser->parse(['a', 'b']);
    }

    public function testOptionOnlyLastMultipleConstraint() {
        $this->expectException(ArgumentConfigException::class);

        $arg1 = new Argument();
        $arg1->multiple();

        $arg2 = new Argument();

        $parser = new Parser([], [ 'x' => $arg1, 'y' => $arg2 ]);
        $parser->parse(['a', 'b']);
    }
}
