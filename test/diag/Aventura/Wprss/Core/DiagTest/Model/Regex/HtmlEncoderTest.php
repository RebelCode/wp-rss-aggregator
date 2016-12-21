<?php

namespace Aventura\Wprss\Core\DiagTest\Model\Regex;

use \RebelCode\Wprss\Debug\Diagtest\Model\TestCase;

/**
 * Tests {@see \Aventura\Wprss\Core\Model\Regex\HtmlEncoder}.
 *
 * @since [*next-version*]
 */
class HtmlEncoderTest extends TestCase
{
    /**
     * Creates an instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return \Aventura\Wprss\Core\Model\Regex\HtmlEncoder
     */
    public function createInstance()
    {
        $subject = new \Aventura\Wprss\Core\Model\Regex\HtmlEncoder();

        return $subject;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertTrue($subject instanceof \Aventura\Wprss\Core\Model\Regex\HtmlEncoder, 'A valid instance of the test subject could not be created');
    }

    /**
     * Tests whether the subject can encodify an expression in the right way.
     *
     * @since [*next-version*]
     */
    public function testEncodify()
    {
        $subject = $this->createInstance();
        $expr = '<div var="([\w\d]*)">';

        $encodified = $subject->encodify($expr);
        $expected = <<<'EOD'
(?:\<|&lt;)div var=(?<_sym1>"|'|&#039;|&apos;|&quot;)([\w\d]*)\g{_sym1}(?:\>|&gt;)
EOD;

        $this->assertTrue($encodified === $expected, 'Encodifying did not produce correct result');
    }

    /**
     * Tests whether expressions encodified by the subject match HTML, both regular and with entities.
     *
     * @since [*next-version*]
     */
    public function testVerifyEncodify()
    {
        $subject = $this->createInstance();
        $html = '<div var="val">';
        $expr = '<div var="([\w\d]*)">';
        $encodedHtml = htmlentities($html);

        $expr = sprintf('!%1$s!', $subject->encodify($expr));

        $this->assertTrue( preg_match($expr, $html) === 1, 'Encodified HTML did not match original');
        $this->assertTrue( preg_match($expr, $encodedHtml) === 1, 'Encodified HTML did not match encoded original');
    }
}
