<?php

namespace XcooBee\Test;

use XcooBee\XcooBee;
use XcooBee\Http\Response;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Return mocked object
     *
     * @param $className
     * @param $methods
     * @param bool $constructorArgs
     * @return \PHPUnit\Framework\MockObject\MockObject
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
     * @param array $methods
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function _getXcooBeeMock($methods = [])
    {
        return $this->_getMock(XcooBee::class, $methods);
    }
    
    protected function _createResponse($code, $data = null, $errors = []) 
    {
        $response = new Response();
        $response->code = $code;
        $response->result = $data;
        $response->errors = $errors;

        return $response;
    }
    
}
