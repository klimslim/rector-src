<?php

declare(strict_types=1);

namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @implements NodeNameResolverInterface<Use_>
 */
final class UseNameResolver implements NodeNameResolverInterface
{
    private NodeNameResolver $nodeNameResolver;

    #[Required]
    public function autowire(NodeNameResolver $nodeNameResolver): void
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function getNode(): string
    {
        return Use_::class;
    }

    /**
     * @param Use_ $node
     */
    public function resolve(Node $node, ?Scope $scope): ?string
    {
        if ($node->uses === []) {
            return null;
        }

        $onlyUse = $node->uses[0];

        return $this->nodeNameResolver->getName($onlyUse);
    }
}
