<?php

namespace XcooBee\Test;

use \XcooBee\XcooBee;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Return mocked object
     *
     * @param $className
     * @param $methods
     * @param bool $constructorArgs
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getMock($className, $methods, $constructorArgs = false)
    {
        $builder = $this->getMockBuilder($className)
            ->setMethods(count($methods) ? array_keys($methods) : null);

        if ($constructorArgs === false) {
            $builder->disableOriginalConstructor();
        }

        $stub = $builder->getMock();

        foreach ($methods as $method => $returnValue) {
            $stub->method($method)
                ->willReturn($returnValue);
        }

        return $stub;
    }

    /**
     * Set protected property
     *
     * @param $object
     * @param $key
     * @param $value
     */
    protected function _setProperty($object, $key, $value)
    {
        $refObject = new \ReflectionObject($object);
        $refProperty = $refObject->getProperty($key);
        $refProperty->setAccessible(true);
        $refProperty->setValue($object, $value);
    }
    
    /**
     * Return XcooBee mocked object
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getXcooBeeMock()
    {
        $reflection = new \ReflectionClass('\XcooBee\XcooBee');
        return $reflection->newInstanceWithoutConstructor();
    }
}
