<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Tests\Stub;

use SilpoTech\ExceptionHandlerBundle\Exception\ApiCallExceptionInterface;

class ApiCallExceptionStub extends \Exception implements ApiCallExceptionInterface
{
    public function __construct(
        $message = '',
        $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
        $this->file = 'filename.php';
        $this->line = 300;
        $exceptionReflection = new \ReflectionObject($this);
        while (false !== $exceptionReflection->getParentClass()) {
            $exceptionReflection = $exceptionReflection->getParentClass();
        }
        $traceReflection = $exceptionReflection->getProperty('trace');
        $traceReflection->setAccessible(true);
        $traceReflection->setValue($this, []);
        $traceReflection->setAccessible(false);
    }

    public function getContents(): string
    {
        return json_encode(['field1' => 'value1']);
    }
}
