<?php

class   DependencyTest
extends PHPUnit_Framework_TestCase
{
    static private  $opMapping  =   array(
                                        "<"     => "<",
                                        " lt "  => "<",
                                        "<="    => "<=",
                                        " le "  => "<=",
                                        ">"     => ">",
                                        " gt "  => ">",
                                        ">="    => ">=",
                                        " ge "  => ">=",
                                        "=="    => "=",
                                        "="     => "=",
                                        " eq "  => "=",
                                        "!="    => "!=",
                                        "<>"    => "!=",
                                        " ne "  => "!=",
                                    );

    public function testValidDependencySpecifications()
    {
        foreach (self::$opMapping as $mapped => $value) {
            $dep = new Erebot_Dependency('foo'.$mapped.'42');
            $this->assertEquals('foo '.$value.' 42', (string) $dep);
        }

        $dep = new Erebot_Dependency('foo');
        $this->assertEquals('foo', (string) $dep);

        // Same values with additional whitespaces.
        foreach (self::$opMapping as $mapped => $value) {
            $dep = new Erebot_Dependency('  foo  '.$mapped.'  42  ');
            $this->assertEquals('foo '.$value.' 42', (string) $dep);
        }

        $dep = new Erebot_Dependency('   foo   ');
        $this->assertEquals('foo', (string) $dep);
    }

    /**
     * @expectedException Erebot_InvalidValueException
     */
    public function testInvalidSpecification()
    {
        new Erebot_Dependency('foo ~= 42');
    }

    /**
     * @expectedException Erebot_InvalidValueException
     */
    public function testInvalidSpecification2()
    {
        new Erebot_Dependency('foo >');
    }
}

