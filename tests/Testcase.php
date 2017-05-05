<?php
namespace Cz\GoL;
use Exception,
    PHPUnit_Framework_TestCase,
    ReflectionClass;

/**
 * Testcase
 * 
 * @author  czukowski
 */
class Testcase extends PHPUnit_Framework_TestCase
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
}
