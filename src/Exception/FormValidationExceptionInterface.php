<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Exception;

use SilpoTech\ExceptionHandlerBundle\ExceptionHandlerMessages;
use Symfony\Component\Form\FormInterface;

interface FormValidationExceptionInterface
{
    public function __construct(FormInterface $form, string $message = ExceptionHandlerMessages::VALIDATION_ERROR);

    public function getForm(): FormInterface;
}
