<?php
/**
 * @desc 路由地址不存在异常类
 */
declare(strict_types=1);

namespace Woox\WebmanException\Exception;

class RouteNotFoundException extends BaseException
{
    /**
     * HTTP 状态码
     */
    public int $statusCode = 404;

    /**
     * @var int
     */
    public int $errorCode = 404;

    /**
     * 错误消息.
     */
    public string $errorMessage = '请求的路由不存在';
}
