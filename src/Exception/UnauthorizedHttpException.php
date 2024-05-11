<?php
/**
 * @desc UnauthorizedHttpException represents an "Unauthorized" HTTP exception with status code 401.
 */
declare(strict_types=1);

namespace Woox\WebmanException\Exception;

class UnauthorizedHttpException extends BaseException
{
    /**
     * @var int
     */
    public int $statusCode = 401;

    /**
     * @var int
     */
    public int $errorCode = 401;

    /**
     * 错误消息.
     */
    public string $errorMessage = '认证错误';
}
