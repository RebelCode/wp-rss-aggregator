<?php

namespace Aventura\Wprss\Core\DiagTest\Model\Set\Synonym;

use RebelCode\Wprss\Debug\Diagtest\Model\TestCase;
use Aventura\Wprss\Core\Model\Set\Synonym;

/**
 * Tests {@see \Aventura\Wprss\Core\Model\Set\Synonym\Set}.
 *
 * @since 4.10
 */
class SetTest extends TestCase
{
    /**
     * Creates a new instance of the test subject.
     *
     * @since 4.10
     *
     * @return Synonym\Set The new instance of the test subject.
     */
    public function createInstance()
    {
        $set = new Synonym\Set();

        return $set;
    }

    /**
     * Creates a new synonym set.
     *
     * @since 4.10
     *
     * @param string[] $synonyms An array of synonyms to populate the set with.
     */
    public function createSynonymSet(array $synonyms = array())
    {
        $set = new Synonym\Simple($synonyms);

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

        $this->assertTrue($subject instanceof Synonym\SynonymSetSetInterface, 'An valid instance of the test subject could not be created');
    }

    /**
     * Tests whether an existing set can be retrieved for a term.
     *
     * @since 4.10
     */
    public function testGetSetForTerm()
    {
        $subject = $this->createInstance();

        $set1 = $this->createSynonymSet(array('apple', 'banana', 'orange'));
        $set2 = $this->createSynonymSet(array('tomato', 'cucumber', 'radish'));
        $subject->addMany(array($set1, $set2));

        $this->assertTrue($subject->getSetForTerm('banana') === $set1, 'Incorrect set retrieved for fruit term');
        $this->assertTrue($subject->getSetForTerm('cucumber') === $set2, 'Incorrect set retrieved for vegetable term');

        $animal = 'cat';
        $animalSet = $subject->getSetForTerm($animal);
        $this->assertTrue(iterator_to_array($animalSet) == array($animal), 'Incorrect set retrieved for animal term');
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
