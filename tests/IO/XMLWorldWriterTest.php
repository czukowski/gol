<?php
namespace Cz\GoL\IO;
use Cz\GoL\Organism,
    Cz\GoL\Testcase,
    InvalidArgumentException,
    RuntimeException,
    Vfs\FileSystem,
    Vfs\FileSystemInterface;

/**
 * XMLWorldWriterTest
 * 
 * @author  czukowski
 */
class XMLWorldWriterTest extends Testcase
{
    /**
     * @var  FileSystemInterface
     */
    private $fs;

    /**
     * @dataProvider  provideWrite
     */
    public function testWrite($organisms, $worldWidth, $worldHeight, $numberOfIterations, $numberOfSpecies, $expected)
    {
        $path = 'vfs://test.xml';
        $object = new XMLWorldWriter($path);
        $this->expectExceptionFromArgument($expected);
        $object->write($organisms, $worldWidth, $worldHeight, $numberOfIterations, $numberOfSpecies);
        $this->assertXmlFileEqualsXmlFile($this->getFixturePath($expected), $path);
    }

    public function provideWrite()
    {
        return [
            'SquareWorld' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(0, 1, 1),
                    new Organism(1, 0, 1),
                    new Organism(10, 0, 2),
                    new Organism(10, 1, 3),
                ],
                100, 100, 1000000, 3,
                'SampleWorld.xml',
            ],
            'RectangleWorld' => [
                [], 100, 50, 10000, 1, new InvalidArgumentException,
            ],
        ];
    }

    protected function setUp()
    {
        parent::setUp();
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();
    }

    protected function tearDown()
    {
        if ($this->fs && ! $this->fs->unmount()) {
            throw new RuntimeException('Unable to unmount vfs:// file system after test was completed');
        }
        parent::tearDown();
    }
}
