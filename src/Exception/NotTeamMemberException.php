<?php
/**
 * @desc 团队隔离异常类
 */
declare(strict_types=1);

namespace Woox\WebmanException\Exception;

class NotTeamMemberException extends BaseException
{
    /**
     * HTTP 状态码
     */
    public int $statusCode = 403;

    /**
     * @var int
     */
    public int $errorCode = 403;

    /**
     * 错误消息.
     */
    public string $errorMessage = '不是团队成员';

    /**
     * @var array|string[]
     */
    public array $data = [
        'id' => 'woox2024',
        'name' => '超级喜欢coding'
    ];
}
