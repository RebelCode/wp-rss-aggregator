<?php

namespace Aventura\Wprss\Core\DiagTest\Model\Set;

use RebelCode\Wprss\Debug\Diagtest\Model\TestCase;
use Aventura\Wprss\Core\Model\Set;

/**
 * Tests {@see \Aventura\Wprss\Core\Model\Set\Set}.
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
     * @return Set\Set The new instance of the test subject.
     */
    public function createInstance()
    {
        $set = new Set\Set();

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

        $this->assertTrue($subject instanceof Set\SetInterface, 'An valid instance of the test subject could not be created');
    }

    /**
     * Tests whether synonyms can be retrieved correctly.
     *
     * @since 4.10
     */
    public function testManipulateAndRead()
    {
        $subject = $this->createInstance();

        $items = array('apple', 'banana', 'orange');
        $subject->addMany($items);
        $this->assertTrue($subject->items() == $items, 'Incorrect item set retrieved after adding multiple items');

        $subject->add('pineapple');
        array_push($items, 'pineapple');
        $this->assertTrue($subject->items() == $items, 'Incorrect item set retrieved after adding an item');
        $this->assertTrue(count($subject) === 4, 'Incorrect item count retrieved');

        $subject->remove('orange');
        $this->assertTrue(array_values($subject->items()) == array('apple', 'banana', 'pineapple'), 'Incorrect item set retrieved after removing an item');
        $this->assertTrue(count($subject) === 3, 'Incorrect item count retrieved');

        $this->assertTrue($subject->has('apple'), 'Subject incorrectly determined having an item');
        $this->assertTrue($subject->has('banana'), 'Subject incorrectly determined having an item');
        $this->assertTrue($subject->has('pineapple'), 'Subject incorrectly determined having an item');
        $this->assertFalse($subject->has('orange'), 'Subject incorrectly determined not having an item');
        $this->assertFalse($subject->has('strawberry'), 'Subject incorrectly determined not having an item');

        $subject->clear();
        $this->assertTrue($subject->items() == array(), 'Incorrect item set retrieved after clearing items');
        $this->assertTrue(count($subject) === 0, 'Incorrect item count retrieved after clearing items');
    }

    /**
     * Tests that iteration over the subject produces correct results.
     *
     * @since 4.10
     */
    public function testIteration()
    {
        $subject = $this->createInstance();
        $items = array('apple', 'banana', 'strawberry');

        $subject->addMany($items);
        $itItems = array();
        foreach ($subject as $item) {
            $itItems[] = $item;
        }

        $this->assertTrue($items == $itItems, 'Iteration over set did not produce desired results');
    }
}
