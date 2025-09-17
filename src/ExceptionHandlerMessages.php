<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle;

final class ExceptionHandlerMessages
{
    public const INTERNAL_ERROR = 'Sorry, something went wrong. We cannot complete this operation at this time.';

    public const VALIDATION_ERROR = 'Sorry, something went wrong. We cannot complete this operation at this time. Code: Invalid request parameters.';

    public const ACCESS_DENIED = 'Access denied.';

    public const UNAUTHORIZED = 'Unauthorized.';
}
