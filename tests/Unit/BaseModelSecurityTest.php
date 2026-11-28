<?php

namespace Tests\Unit;

use App\Common\Bmvc\BaseModel;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class BaseModelSecurityTest extends TestCase
{
    private BaseModel $model;
    private ReflectionMethod $quote;
    private ReflectionMethod $filter;

    protected function setUp(): void
    {
        $ref = new ReflectionClass(BaseModel::class);
        $this->model = $ref->newInstanceWithoutConstructor();
        $this->quote = $ref->getMethod('quoteIdentifier');
        $this->quote->setAccessible(true);
        $this->filter = $ref->getMethod('filterAssignable');
        $this->filter->setAccessible(true);
    }

    public function testQuoteIdentifierRejectsInjection(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->quote->invoke($this->model, 'users; DROP TABLE users--');
    }

    public function testQuoteIdentifierAcceptsSafeName(): void
    {
        $this->assertSame('`users`', $this->quote->invoke($this->model, 'users'));
    }

    public function testFilterAssignableDropsUnsafeKeys(): void
    {
        $out = $this->filter->invoke($this->model, [
            'name' => 'Ada',
            'evil;--' => 'x',
            0 => 'numeric',
        ]);
        $this->assertSame(['name' => 'Ada'], $out);
    }

    public function testFillableWhitelist(): void
    {
        $prop = (new ReflectionClass(BaseModel::class))->getProperty('fillable');
        $prop->setAccessible(true);
        $prop->setValue($this->model, ['name']);

        $out = $this->filter->invoke($this->model, [
            'name' => 'Ada',
            'role' => 'admin',
        ]);
        $this->assertSame(['name' => 'Ada'], $out);
    }
}
