<?php
/**
 * Tests for the autodiscovered ruleset file of the \PHP_CodeSniffer\Config class.
 *
 * @copyright 2026 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */

namespace PHP_CodeSniffer\Tests\Core\Config;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Tests\Core\Config\AbstractRealConfigTestCase;

/**
 * Tests the autodiscovered ruleset file in the current directory.
 *
 * @covers \PHP_CodeSniffer\Config::__construct
 */
final class ConfigFileDiscoveryTest extends AbstractRealConfigTestCase
{

    /**
     * The working directory before the test was run.
     *
     * @var string
     */
    private $originalDir;

    /**
     * Temporary directory in which the ruleset files are created.
     *
     * @var string
     */
    private $tempDir;


    /**
     * Create a temporary directory and make it the current working directory.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDir = getcwd();
        $this->tempDir     = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('phpcs-config-discovery-');
        mkdir($this->tempDir);
        chdir($this->tempDir);
    }


    /**
     * Restore the working directory and remove the temporary directory.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        chdir($this->originalDir);

        foreach (scandir($this->tempDir) as $file) {
            if ($file !== '.' && $file !== '..') {
                unlink($this->tempDir . DIRECTORY_SEPARATOR . $file);
            }
        }

        rmdir($this->tempDir);

        parent::tearDown();
    }


    /**
     * Verify the correct ruleset file is autodiscovered when one or more are present.
     *
     * @param array<string> $files    The ruleset file names to create.
     * @param string        $expected The file name expected to be selected.
     *
     * @dataProvider dataAutodiscoveredRulesetFile
     *
     * @return void
     */
    public function testAutodiscoveredRulesetFile(array $files, string $expected)
    {
        foreach ($files as $file) {
            file_put_contents($file, '<?xml version="1.0"?><ruleset name="Test"/>');
        }

        $config = new Config([]);

        $this->assertSame([getcwd() . DIRECTORY_SEPARATOR . $expected], $config->standards);
    }


    /**
     * Data provider.
     *
     * @see testAutodiscoveredRulesetFile()
     *
     * @return array<string, array<string, string|array<string>>>
     */
    public static function dataAutodiscoveredRulesetFile()
    {
        return [
            'phpcs.dist.xml on its own'             => [
                'files'    => ['phpcs.dist.xml'],
                'expected' => 'phpcs.dist.xml',
            ],
            '.phpcs.dist.xml on its own'            => [
                'files'    => ['.phpcs.dist.xml'],
                'expected' => '.phpcs.dist.xml',
            ],
            '.phpcs.dist.xml before phpcs.dist.xml' => [
                'files'    => [
                    'phpcs.dist.xml',
                    '.phpcs.dist.xml',
                ],
                'expected' => '.phpcs.dist.xml',
            ],
            'phpcs.dist.xml before .phpcs.xml.dist' => [
                'files'    => [
                    '.phpcs.xml.dist',
                    'phpcs.dist.xml',
                ],
                'expected' => 'phpcs.dist.xml',
            ],
            'phpcs.xml before .phpcs.dist.xml'      => [
                'files'    => [
                    '.phpcs.dist.xml',
                    'phpcs.xml',
                ],
                'expected' => 'phpcs.xml',
            ],
            'phpcs.xml before phpcs.dist.xml'       => [
                'files'    => [
                    'phpcs.dist.xml',
                    'phpcs.xml',
                ],
                'expected' => 'phpcs.xml',
            ],
            '.phpcs.xml before all other files'     => [
                'files'    => [
                    'phpcs.dist.xml',
                    '.phpcs.dist.xml',
                    'phpcs.xml.dist',
                    '.phpcs.xml.dist',
                    'phpcs.xml',
                    '.phpcs.xml',
                ],
                'expected' => '.phpcs.xml',
            ],
        ];
    }
}
