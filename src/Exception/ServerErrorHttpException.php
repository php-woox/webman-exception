<?php
/**
 * @desc 服务器内部异常
 */
declare(strict_types=1);

namespace Woox\WebmanException\Exception;

/**
 * ServerErrorHttpException represents an "Internal Server Error" HTTP exception with status code 500.
 *
 */
class ServerErrorHttpException extends BaseException
{
    /**
     * @var int
     */
    public int $statusCode = 500;

    /**
     * @var int
     */
    public int $errorCode = 500;

    /**
     * @var string
     */
    public string $errorMessage = '服务端错误';
}
