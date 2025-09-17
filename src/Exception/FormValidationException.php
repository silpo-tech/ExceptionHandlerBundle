<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Exception;

use SilpoTech\ExceptionHandlerBundle\ExceptionHandlerMessages;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FormValidationException extends HttpException implements FormValidationExceptionInterface
{
    /**
     * FormValidationException constructor.
     */
    public function __construct(
        private readonly FormInterface $form,
        string $message = ExceptionHandlerMessages::VALIDATION_ERROR,
    ) {
        parent::__construct(Response::HTTP_BAD_REQUEST, $message);
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }
}
