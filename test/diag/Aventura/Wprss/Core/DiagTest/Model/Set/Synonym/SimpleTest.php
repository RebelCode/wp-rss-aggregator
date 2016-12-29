<?php

namespace Aventura\Wprss\Core\DiagTest\Model\Set\Synonym;

use \RebelCode\Wprss\Debug\Diagtest\Model\TestCase;
use Aventura\Wprss\Core\Model\Set\Synonym;

/**
 * Tests {@see \Aventura\Wprss\Core\Model\Set\Synonym\Simple}.
 *
 * @since 4.10
 */
class SimpleTest extends TestCase
{
    /**
     * Creates a new instance of the test subject.
     *
     * @since 4.10
     *
     * @return Synonym\Simple The new instance of the test subject.
     */
    public function createInstance()
    {
        $set = new Synonym\Simple();

        return $set;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since 4.10
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertTrue($subject instanceof Synonym\SynonymSetInterface, 'An valid instance of the test subject could not be created');
    }

    /**
     * Tests whether synonyms can be retrieved correctly.
     *
     * @since 4.10
     */
    public function testGetSynonyms()
    {
        $subject = $this->createInstance();

        $subject->addMany(array('apple', 'banana', 'orange'));
        $this->assertTrue($subject->getSynonyms('banana') == array('apple', 'orange'), 'A correct list of synonyms could not be retrieved');
    }

    /**
     * Tests whether the subject accepts only strings as its items.
     *
     * @since 4.10
     */
    public function testStringsOnlyAllowed()
    {
        $subject = $this->createInstance();

        $valid = true;
        try {
            $subject->add(new \stdClass());
        } catch (\RuntimeException $ex) {
            $valid = false;
        }

        $this->assertFalse($valid, 'A synonym set must only accept strings');
    }
}
