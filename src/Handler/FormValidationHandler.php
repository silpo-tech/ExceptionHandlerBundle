<?php

declare(strict_types=1);

namespace ExceptionHandlerBundle\Handler;

use ExceptionHandlerBundle\Exception\FormValidationExceptionInterface;
use Symfony\Component\Form\FormInterface;

class FormValidationHandler extends ExceptionHandler
{
    public function supports(): string
    {
        return FormValidationExceptionInterface::class;
    }

    public function getBody(\Throwable $throwable): array
    {
        $data = parent::getBody($throwable);

        /* @var FormValidationExceptionInterface $throwable */
        /* @phpstan-ignore-next-line */
        $data['errors'] = $this->collectErrorsToArray($throwable->getForm());

        return $data;
    }

    private function collectErrorsToArray(FormInterface $form): array|string
    {
        $data = $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors = $error->getMessageTemplate();
        }

        if ($errors) {
            $data = $errors;
        }

        $children = [];
        foreach ($form->all() as $child) {
            $res = $this->collectErrorsToArray($child);
            if ([] !== $res) {
                $children[$child->getName()] = $res;
            }
        }

        if ($children) {
            if (!is_array($data)) {
                $result[] = $data;
                $result += $children;

                return $result;
            } else {
                $data += $children;
            }
        }

        return $data;
    }
}
