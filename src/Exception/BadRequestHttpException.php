<?php
/**
 * @desc 由于明显的客户端错误（例如，格式错误的请求语法，太大的大小，无效的请求消息或欺骗性路由请求），服务器不能或不会处理该请求。
 */
declare(strict_types=1);

namespace Woox\WebmanException\Exception;

class BadRequestHttpException extends BaseException
{
    /**
     * @var int
     */
    public int $statusCode = 400;

    /**
     * @var int
     */
    public int $errorCode = 400;

    /**
     * @var string
     */
    public string $errorMessage = '请求参数错误，服务器不能或不会处理该请求';
}
