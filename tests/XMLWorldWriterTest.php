<?php
namespace Cz\GoL;
use RuntimeException,
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
    public function testWrite($organisms, $worldDimension, $numberOfIterations, $numberOfSpecies, $expected)
    {
        $path = 'vfs://test.xml';
        $object = new XMLWorldWriter($path);
        $object->write($organisms, $worldDimension, $numberOfIterations, $numberOfSpecies);
        $this->assertXmlFileEqualsXmlFile($this->getFixturePath($expected), $path);
    }

    public function provideWrite()
    {
        return [
            [
                [
                    new Organism(0, 0, 1),
                    new Organism(0, 1, 1),
                    new Organism(1, 0, 1),
                    new Organism(10, 0, 2),
                    new Organism(10, 1, 3),
                ],
                100, 1000000, 3,
                'SampleWorld.xml',
            ]
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
