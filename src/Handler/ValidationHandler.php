<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Handler;

use SilpoTech\ExceptionHandlerBundle\Exception\ValidationExceptionInterface;
use Symfony\Component\Validator\ConstraintViolation;

class ValidationHandler extends ExceptionHandler
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
        return ValidationExceptionInterface::class;
    }

    public function getBody(\Throwable $throwable): array
    {
        $data = parent::getBody($throwable);

        /* @var ValidationExceptionInterface $throwable */
        /* @phpstan-ignore-next-line */
        $data['errors'] = $this->collectErrorsToArray($throwable->getErrors());

        return $data;
    }

    private function collectErrorsToArray(iterable $errors): array
    {
        $data = [];
        foreach ($errors as $key => $error) {
            switch (true) {
                case $error instanceof ConstraintViolation:
                    $field = $this->decorateField($error->getPropertyPath());
                    $message = $this->messagesMap[$error->getCode()] ?? $error->getMessage();
                    if (!isset($data[$field]) || !in_array($message, $data[$field])) {
                        $data[$field][] = $message;
                    }

                    break;
                case is_array($error):
                    $field = $this->decorateField($key);
                    foreach ($error as $item) {
                        $message = $this->messagesMap[$item] ?? $item;
                        if (!isset($data[$field]) || !in_array($message, $data[$field])) {
                            $data[$field][] = $message;
                        }
                    }

                    break;
                default:
                    $field = $this->decorateField((string) $key);
                    $message = $error;
                    if (!isset($data[$field]) || !in_array($message, $data[$field])) {
                        $data[$field][] = $message;
                    }
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
