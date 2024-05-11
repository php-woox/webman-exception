<?php
/**
 * @desc 请求不存在异常类
 */
declare(strict_types=1);

namespace Woox\WebmanException\Exception;

class NotFoundHttpException extends BaseException
{
    /**
     * @var int
     */
    public int $statusCode = 404;

    /**
     * @var int
     */
    public int $errorCode = 404;

    /**
     * @var string
     */
    public string $errorMessage = '请求的路由或者资源不存在';
}
