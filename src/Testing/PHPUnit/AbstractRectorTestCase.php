<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rector\Application\FileProcessor;
use Rector\DependencyInjection\ContainerFactory;
use Rector\FileSystem\FileGuard;
use Symfony\Component\Finder\SplFileInfo;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

abstract class AbstractRectorTestCase extends TestCase
{
    /**
     * @var FileProcessor
     */
    protected $fileProcessor;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ParameterProvider
     */
    protected $parameterProvider;

    /**
     * @var bool
     */
    protected $rebuildFreshContainer = false;

    /**
     * @var FileGuard
     */
    private $fileGuard;

    /**
     * @var ContainerInterface[]
     */
    private static $containersPerConfig = [];

    protected function setUp(): void
    {
        $configFile = $this->provideConfig();
        $this->fileGuard = new FileGuard();
        $this->fileGuard->ensureFileExists($configFile, get_called_class());

        $key = md5_file($configFile);

        if (isset(self::$containersPerConfig[$key]) && $this->rebuildFreshContainer === false) {
            $this->container = self::$containersPerConfig[$key];
        } else {
            self::$containersPerConfig[$key] = $this->container = (new ContainerFactory())->createWithConfigFiles(
                [$configFile]
            );
        }

        $this->fileProcessor = $this->container->get(FileProcessor::class);
        $this->parameterProvider = $this->container->get(ParameterProvider::class);
    }

    protected function doTestFileMatchesExpectedContent(string $file, string $reconstructedFile): void
    {
        $this->fileGuard->ensureFileExists($file, get_called_class());
        $this->fileGuard->ensureFileExists($reconstructedFile, get_called_class());

        $this->parameterProvider->changeParameter('source', [$file]);

        $splFileInfo = new SplFileInfo($file, '', '');
        $reconstructedFileContent = $this->fileProcessor->processFileToString($splFileInfo);

        $this->assertStringEqualsFile($reconstructedFile, $reconstructedFileContent, sprintf(
            'Original file "%s" did not match the result.',
            $file
        ));
    }

    abstract protected function provideConfig(): string;
}
