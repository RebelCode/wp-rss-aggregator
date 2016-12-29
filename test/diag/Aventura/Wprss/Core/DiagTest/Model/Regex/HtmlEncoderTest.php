<?php

namespace Aventura\Wprss\Core\DiagTest\Model\Regex;

use \RebelCode\Wprss\Debug\Diagtest\Model\TestCase;

/**
 * Tests {@see \Aventura\Wprss\Core\Model\Regex\HtmlEncoder}.
 *
 * @since 4.10
 */
class HtmlEncoderTest extends TestCase
{
    /**
     * Creates an instance of the test subject.
     *
     * @since 4.10
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
     * @since 4.10
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertTrue($subject instanceof \Aventura\Wprss\Core\Model\Regex\HtmlEncoder, 'A valid instance of the test subject could not be created');
    }

    /**
     * Tests whether the subject can encodify an expression in the right way.
     *
     * @since 4.10
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
     * @since 4.10
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

    /**
     * Tests that encodifying preserves character class definitions literally.
     *
     * @since 4.10
     */
    public function testEncodifyCharClasses()
    {
        $subject = $this->createInstance();
        $html = <<<HTML
<p style="text-align: justify;">
    <img class=" wp-image-38034 alignleft" src="http://www.kedistan.net/wp-content/uploads/2016/12/Kino-Gabriel.jpg" alt="" width="184" height="186"
        srcset="http://www.kedistan.net/wp-content/uploads/2016/12/Kino-Gabriel.jpg 360w, http://www.kedistan.net/wp-content/uploads/2016/12/Kino-Gabriel-110x110.jpg 110w, http://www.kedistan.net/wp-content/uploads/2016/12/Kino-Gabriel-230x233.jpg 230w"
    sizes="(max-width: 184px) 100vw, 184px" />
    Kino Gabriel est le commandant du conseil militaire syriaque, qui participe également à l’offensive « colère de l’Euphrate ».
</p>
HTML;
        $expr = '<img[^<>]*?src="[^<>]*?abc[^<>]*?"[^<>]*?>';
        $result = $subject->encodify($expr);
        $expected = <<<REGEX
(?:\<|&lt;)img[^<>]*?src=(?<_sym1>"|'|&#039;|&apos;|&quot;)[^<>]*?abc[^<>]*?\g{_sym1}[^<>]*?(?:\>|&gt;)
REGEX;
        $this->assertTrue($expected === $result, 'Encodified expression is wrong, perhaps not preserving char classes');
    }

    /**
     * Tests that an encodified expression containing a character class with HTML special chars can correctly match HTML.
     *
     * @since 4.10
     */
    public function testValidateCharClasses()
    {
        $subject = $this->createInstance();
        $html = <<<HTML
<p style="text-align: justify;">
    <img class=" wp-image-38034 alignleft" src="http://www.kedistan.net/wp-content/uploads/2016/12/Kino-Gabriel.jpg" alt="" width="184" height="186"
        srcset="http://www.kedistan.net/wp-content/uploads/2016/12/Kino-Gabriel.jpg 360w, http://www.kedistan.net/wp-content/uploads/2016/12/Kino-Gabriel-110x110.jpg 110w, http://www.kedistan.net/wp-content/uploads/2016/12/Kino-Gabriel-230x233.jpg 230w"
    sizes="(max-width: 184px) 100vw, 184px" />
    Kino Gabriel est le commandant du conseil militaire syriaque, qui participe également à l’offensive « colère de l’Euphrate ».
</p>
HTML;
        $expr = '<img[^<>]*?srcset="[^<>]*?http://www\\.kedistan\\.net/wp-content/uploads/2016/12/Kino-Gabriel-110x110\\.jpg[^<>]*?"[^<>]*?>';
        $expr = $subject->encodify($expr);

        $this->assertTrue(preg_match('!' . $expr . '!Us', $html) === 1, 'Encodified expression did not match subject string, perhaps due to char classes being encodified');
    }
}
