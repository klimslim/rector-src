<?php

declare(strict_types=1);

namespace Rector\Tests\Skipper\FileSystem;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rector\Core\Kernel\RectorKernel;
use Rector\Skipper\FileSystem\FnMatchPathNormalizer;

final class FnMatchPathNormalizerTest extends TestCase
{
    private FnMatchPathNormalizer $fnMatchPathNormalizer;

    protected function setUp(): void
    {
        $rectorKernel = new RectorKernel();
        $containerBuilder = $rectorKernel->createBuilder();

        $this->fnMatchPathNormalizer = $containerBuilder->get(FnMatchPathNormalizer::class);
    }

    #[DataProvider('providePaths')]
    public function testPaths(string $path, string $expectedNormalizedPath): void
    {
        $normalizedPath = $this->fnMatchPathNormalizer->normalizeForFnmatch($path);
        $this->assertSame($expectedNormalizedPath, $normalizedPath);
    }

    public static function providePaths(): Iterator
    {
        yield ['path/with/no/asterisk', 'path/with/no/asterisk'];
        yield ['*path/with/asterisk/begin', '*path/with/asterisk/begin*'];
        yield ['path/with/asterisk/end*', '*path/with/asterisk/end*'];
        yield ['*path/with/asterisk/begin/and/end*', '*path/with/asterisk/begin/and/end*'];
        yield [__DIR__ . '/Fixture/path/with/../in/it', __DIR__ . '/Fixture/path/in/it'];
        yield [__DIR__ . '/Fixture/path/with/../../in/it', __DIR__ . '/Fixture/in/it'];
    }
}
