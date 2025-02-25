<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
use Rector\Php70\NodeAnalyzer\Php4ConstructorClassMethodAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/remove_php4_constructors
 * @see \Rector\Tests\Php70\Rector\ClassMethod\Php4ConstructorRector\Php4ConstructorRectorTest
 */
final class Php4ConstructorRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    public function __construct(
        private readonly Php4ConstructorClassMethodAnalyzer $php4ConstructorClassMethodAnalyzer,
        private readonly ParentClassScopeResolver $parentClassScopeResolver
    ) {
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::NO_PHP4_CONSTRUCTOR;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes PHP 4 style constructor to __construct.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function SomeClass()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct()
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactorWithScope(Node $node, Scope $scope): ClassMethod|int|null
    {
        if (! $this->php4ConstructorClassMethodAnalyzer->detect($node, $scope)) {
            return null;
        }

        $classLike = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (! $classLike instanceof Class_) {
            return null;
        }

        // process parent call references first
        $this->processClassMethodStatementsForParentConstructorCalls($node, $scope);

        // not PSR-4 constructor
        if (! $this->nodeNameResolver->areNamesEqual($classLike, $node)) {
            return null;
        }

        $classMethod = $classLike->getMethod(MethodName::CONSTRUCT);

        // does it already have a __construct method?
        if (! $classMethod instanceof ClassMethod) {
            $node->name = new Identifier(MethodName::CONSTRUCT);
        }

        $classMethodStmts = $node->stmts;
        if ($classMethodStmts === null) {
            return null;
        }

        if (count($classMethodStmts) === 1) {
            $stmt = $node->stmts[0];
            if (! $stmt instanceof Expression) {
                return null;
            }

            if ($this->isLocalMethodCallNamed($stmt->expr, MethodName::CONSTRUCT)) {
                return NodeTraverser::REMOVE_NODE;
            }
        }

        return $node;
    }

    private function processClassMethodStatementsForParentConstructorCalls(ClassMethod $classMethod, Scope $scope): void
    {
        if (! is_iterable($classMethod->stmts)) {
            return;
        }

        foreach ($classMethod->stmts as $methodStmt) {
            if (! $methodStmt instanceof Expression) {
                continue;
            }

            $methodStmt = $methodStmt->expr;
            if (! $methodStmt instanceof StaticCall) {
                continue;
            }

            $this->processParentPhp4ConstructCall($methodStmt, $scope);
        }
    }

    private function processParentPhp4ConstructCall(StaticCall $staticCall, Scope $scope): void
    {
        $parentClassReflection = $this->parentClassScopeResolver->resolveParentClassReflection($scope);

        // no parent class
        if (! $parentClassReflection instanceof ClassReflection) {
            return;
        }

        if (! $staticCall->class instanceof Name) {
            return;
        }

        // rename ParentClass
        if ($this->isName($staticCall->class, $parentClassReflection->getName())) {
            $staticCall->class = new Name(ObjectReference::PARENT);
        }

        if (! $this->isName($staticCall->class, ObjectReference::PARENT)) {
            return;
        }

        // it's not a parent PHP 4 constructor call
        if (! $this->isName($staticCall->name, $parentClassReflection->getName())) {
            return;
        }

        $staticCall->name = new Identifier(MethodName::CONSTRUCT);
    }

    private function isLocalMethodCallNamed(Expr $expr, string $name): bool
    {
        if (! $expr instanceof MethodCall) {
            return false;
        }

        if ($expr->var instanceof StaticCall) {
            return false;
        }

        if ($expr->var instanceof MethodCall) {
            return false;
        }

        if (! $this->isName($expr->var, 'this')) {
            return false;
        }

        return $this->isName($expr->name, $name);
    }
}
