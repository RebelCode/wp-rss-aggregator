<?php

namespace Aventura\Wprss\Core\DiagTest\Model;

use RebelCode\Wprss\Debug\Diagtest\Model\TestCase;

/**
 * Tests {@see \Aventura\Wprss\Core\Model\ModelAbstract}.
 *
 * @since 4.10
 */
class ModelAbstractTest extends TestCase
{
    /**
     * Generate a concrete mock class and return a new instance of it.
     *
     * A mock class is a concrete class that extends or implements
     * a given class or interface.
     *
     * @since 4.10
     *
     * @param string $className Name of the class to create the mock for.
     * @param array $constructorArgs Arguments to be passed to the mock's constructor.
     *
     * @return object
     */
    public function createMock($className, $constructorArgs = array())
    {
        if (!class_exists($className)) {
            throw new \RuntimeException(sprintf('Could not create mock for class "%1$s": class does not exist.', $className));
        }

        $classRef = new \ReflectionClass($className);
        $parentRelationship = $classRef->isInterface()
                ? 'implements'
                : 'extends';

        $classBasename = explode('\\', $className);
        $classBasename = array_pop($classBasename);
        $mockClassName = sprintf('Mock_%1$s_%2$s', $classBasename, substr(md5($className), 0, 7));
        if (!class_exists($mockClassName)) {
            $classDefinition = <<<CLASS
class {$mockClassName} {$parentRelationship} {$className}
{}
CLASS;
            eval($classDefinition);
        }

        $ref = new \ReflectionClass($mockClassName);
        $mock = $ref->newInstanceArgs($constructorArgs);

        return $mock;
    }

    /**
     * Creates a new instance of the test subject.
     *
     * @since 4.10
     *
     * @return \Aventura\Wprss\Core\Model\ModelAbstract The new test subject instance.
     */
    public function createInstance()
    {
        return $this->createMock('Aventura\\Wprss\\Core\\Model\\ModelAbstract');
    }

    /**
     * Tests whether a valid instance of the test subject
     *
     * @since 4.10
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();
        $this->assertTrue($subject instanceof \Aventura\Wprss\Core\Model\ModelAbstract, 'Could not create a valid instance of the subject');
    }

    /**
     * Tests the `_getDataOrConst()` method.
     *
     * @since 4.10
     */
    public function testGetDataOrConst()
    {
        $subject = $this->createInstance();
        $key = 'my_var';
        $value = 'value1';
        $subject->setData($key, $value);
        $ref = new \ReflectionMethod($subject, '_getDataOrConst');
        $ref->setAccessible(true);
        $result = $ref->invoke($subject, $key);
        $this->assertTrue($result === $value, 'A correct data member value could not be retrieved');
    }
}
