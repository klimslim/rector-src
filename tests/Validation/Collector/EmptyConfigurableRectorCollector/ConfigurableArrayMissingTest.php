<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Validation\Collector\EmptyConfigurableRectorCollector;

use Rector\Core\Kernel\RectorKernel;
use Rector\Core\Validation\Collector\EmptyConfigurableRectorCollector;
use Rector\Testing\PHPUnit\AbstractTestCase;

/**
 * array configurable with missing values config will show warning
 */
final class ConfigurableArrayMissingTest extends AbstractTestCase
{
    private EmptyConfigurableRectorCollector $emptyConfigurableRectorCollector;

    protected function setUp(): void
    {
        $rectorKernel = new RectorKernel();
        $containerBuilder = $rectorKernel->createBuilder([__DIR__ . '/config/configurable_array_missing.php']);
        $this->emptyConfigurableRectorCollector = $containerBuilder->get(EmptyConfigurableRectorCollector::class);
    }

    public function test(): void
    {
        $emptyConfigurableRectors = $this->emptyConfigurableRectorCollector->resolveEmptyConfigurableRectorClasses();
        $this->assertCount(1, $emptyConfigurableRectors);
    }
}
