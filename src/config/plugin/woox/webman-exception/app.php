<?php

return [
    'enable' => true,
    // 错误异常配置
    'exception' => [
        // 不需要记录错误日志
        'dont_report' => [
            Woox\WebmanException\Exception\BadRequestHttpException::class,
            Woox\WebmanException\Exception\UnauthorizedHttpException::class,
            Woox\WebmanException\Exception\ForbiddenHttpException::class,
            Woox\WebmanException\Exception\NotFoundHttpException::class,
            Woox\WebmanException\Exception\RouteNotFoundException::class,
            Woox\WebmanException\Exception\TooManyRequestsHttpException::class,
            Woox\WebmanException\Exception\ServerErrorHttpException::class,
            Woox\WebmanValidate\Exception\ValidateException::class,
            Woox\WebmanJwt\Exception\JwtTokenException::class
        ],
        // 自定义HTTP状态码
        // 配置项不存在时，使用默认的http状态码；
        // 配置项为[]时，所有http状态码为200；
        // 配置项为[...]时，优先使用配置的http状态码，否则使用默认的http状态码。
        'status_code' => [
            'validate' => 400, // 验证器异常
            'jwt_token' => 401, // 认证失败
            'jwt_token_expired' => 401, // 访问令牌过期
            'jwt_refresh_token_expired' => 402, // 刷新令牌过期
            'server_error' => 500, // 服务器内部错误
        ],

        // 自定义错误码 非http
        // 配置项不存在时，使用默认的错误码；
        // 配置项为[]时，所有错误为0；
        // 配置项为[...]时，优先使用配置的错误码，否则使用默认的错误码。
        'error_code' => [
            'validate' => 400, // 验证器异常
            'jwt_token' => 401, // 认证失败
            'jwt_token_expired' => 401, // 访问令牌过期
            'jwt_refresh_token_expired' => 402, // 刷新令牌过期
            'server_error' => 500, // 服务器内部错误
        ],
        // 自定义响应消息
        'body' => [
            'code' => 0,
            'msg' => '服务器内部异常',
            'data' => null
        ]
    ],

];
