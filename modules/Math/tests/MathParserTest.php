<?php

include_once(dirname(dirname(__FILE__)).'/src/lexer.php');

class   MathParserTest
extends PHPUnit_Framework_TestCase
{
	public function testConstants()
	{
		$lex	=	new MathLexer('42');
		$this->assertEquals(42, $lex->getResult());
	}

	public function testConstants2()
	{
		$lex	=	new MathLexer('4.2');
		$this->assertEquals(4.2, $lex->getResult());
	}

	public function testConstants3()
	{
		$lex	=	new MathLexer('4.');
		$this->assertEquals(4.0, $lex->getResult());
	}

	public function testConstants4()
	{
		$lex	=	new MathLexer('.2');
		$this->assertEquals(0.2, $lex->getResult());
	}

	// Test for undefined operations.

	/**
	 * Attempting a division by zero must
	 * result in an exception being thrown.
	 * @expectedException EMathDivisionByZero
	 */
	public function testDivisionByZero()
	{
		$lex	=	new MathLexer('1/0');
	}

	/**
	 * Attempting a division by zero must
	 * result in an exception being thrown.
	 * @expectedException EMathDivisionByZero
	 */
	public function testDivisionByZero2()
	{
		$lex	=	new MathLexer('1.0/0');
	}

	/**
	 * Trying to compute the rest modulo zero
	 * is the same as dividing by zero.
	 * @expectedException EMathDivisionByZero
	 */
	public function testDivisionByZero3()
	{
		$lex	= 	new MathLexer('1 % 0');
	}

	/**
	 * Computing a modulus on real numbers in undefined
	 * and must result in an exception being thrown.
	 * @expectedException EMathNoModulusOnReals
	 */
	public function testModulusOnReals()
	{
		$lex	= 	new MathLexer('1.0 % 2');
	}

	/**
	 * Computing a modulus on real numbers in undefined
	 * and must result in an exception being thrown.
	 * @expectedException EMathNoModulusOnReals
	 */
	public function testModulusOnReals2()
	{
		$lex	= 	new MathLexer('1 % 2.0');
	}

	/**
	 * Computing exponentiation with a negative exponent
	 * is undefined and must throw an exception.
	 * @expectedException EMathNegativeExponent
	 */
	public function testNegativeExponentiation()
	{
		$lex	= 	new MathLexer('1 ^ (-1)');
	}
}

?>
