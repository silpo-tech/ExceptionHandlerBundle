<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Handler;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

// copied from ValidationHandler
class BadRequestHttpExceptionHandler extends ExceptionHandler
{
    public function __construct(
        bool $debug = false,
        private readonly array $messagesMap = [],
        private readonly bool $snakeCase = true,
    ) {
        parent::__construct($debug);
    }

    public function supports(): string
    {
        return BadRequestHttpException::class;
    }

    public function getBody(\Throwable $throwable): array
    {
        $data = parent::getBody($throwable);

        if ($throwable->getPrevious() instanceof ValidationFailedException) {
            /* @var BadRequestHttpException $throwable */
            $data['errors'] = $this->collectErrorsToArray($throwable->getPrevious()->getViolations());
        }

        return $data;
    }

    private function collectErrorsToArray(iterable $errors): array
    {
        $data = [];
        foreach ($errors as $error) {
            $field = $this->decorateField($error->getPropertyPath());
            $message = $this->messagesMap[$error->getCode()] ?? $error->getMessage();
            if (!isset($data[$field]) || !in_array($message, $data[$field])) {
                $data[$field][] = $message;
            }
        }

        return $data;
    }

    private function decorateField(string $field): string
    {
        if ($this->snakeCase) {
            return strtolower(preg_replace('/[A-Z]/', '_\\0', $field));
        }

        return lcfirst(str_replace('_', '', ucwords($field, '_')));
    }
}
