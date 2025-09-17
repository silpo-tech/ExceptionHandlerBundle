<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Tests\Handler;

use PHPUnit\Framework\TestCase;
use SilpoTech\ExceptionHandlerBundle\Exception\FormValidationException;
use SilpoTech\ExceptionHandlerBundle\Exception\FormValidationExceptionInterface;
use SilpoTech\ExceptionHandlerBundle\Handler\FormValidationHandler;
use SilpoTech\ExceptionHandlerBundle\Tests\Stub\Form\ProductTypeStub;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

class FormValidationHandlerTest extends TestCase
{
    public function testSupports(): void
    {
        $handler = new FormValidationHandler();
        $this->assertEquals(FormValidationExceptionInterface::class, $handler->supports());
    }

    public function testGetBody(): void
    {
        $form = $this->createTestForm();
        $handler = new FormValidationHandler();
        $body = $handler->getBody(new FormValidationException($form));
        $this->assertEquals([
            'code' => 400,
            'message' => 'Sorry, something went wrong. We cannot complete this operation at this time. Code: Invalid request parameters.',
            'errors' => [
                'productName' => 'Product name empty',
                'category' => [
                    'categoryName' => 'Category name empty',
                ],
                'Extra field exists',
            ],
        ], $body);
    }

    private function createTestForm(): FormInterface
    {
        $validator = Validation::createValidator();
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension($validator))
            ->getFormFactory()
        ;
        $form = $formFactory->create(ProductTypeStub::class, options: ['extra_fields_message' => 'Extra field exists']);
        $form->submit(['extra_field' => true]);

        return $form;
    }
}
