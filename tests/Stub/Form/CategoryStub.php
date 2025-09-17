<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Tests\Stub\Form;

class CategoryStub
{
    protected string $categoryName;

    public function getCategoryName(): string
    {
        return $this->categoryName;
    }

    public function setCategoryName(string $categoryName): void
    {
        $this->categoryName = $categoryName;
    }
}
