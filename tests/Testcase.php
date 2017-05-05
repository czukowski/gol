<?php
namespace Cz\GoL;
use Exception,
    PHPUnit_Framework_TestCase,
    ReflectionClass,
    ReflectionMethod,
    ReflectionProperty;

/**
 * Testcase
 * 
 * @author  czukowski
 */
abstract class Testcase extends PHPUnit_Framework_TestCase
{
    /**
     * @param   string  $filename
     * @return  string
     */
    protected function getFixturePath($filename)
    {
        return $this->getFixturesDir().$filename;
    }

    /**
     * @return  string
     */
    protected function getFixturesDir()
    {
        $class = new ReflectionClass($this);
        $path = $class->getFileName();
        return dirname($path).'/'.basename($path, '.php').'/';
    }

    /**
     * @param   object  $object
     * @param   string  $name
     * @return  ReflectionMethod
     */
    protected function getObjectMethod($object, $name)
    {
        $method = new ReflectionMethod($object, $name);
        $method->setAccessible(TRUE);
        return $method;
    }

    /**
     * @param   object  $object
     * @param   string  $name
     * @return  ReflectionProperty
     */
    protected function getObjectProperty($object, $name)
    {
        $property = new ReflectionProperty($object, $name);
        $property->setAccessible(TRUE);
        return $property;
    }

    /**
     * @param  Exception  $expected
     */
    protected function expectExceptionFromArgument($expected)
    {
        if ($expected instanceof Exception) {
            $this->expectException(get_class($expected));
        }
    }
}
