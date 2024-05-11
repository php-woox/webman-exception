<?php

declare(strict_types=1);

namespace Woox\WebmanException;

use FastRoute\BadRouteException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Woox\WebmanJwt\Exception\JwtRefreshTokenExpiredException;
use Woox\WebmanJwt\Exception\JwtTokenException;
use Woox\WebmanJwt\Exception\JwtTokenExpiredException;
use Throwable;
use Woox\WebmanException\Exception\BaseException;
use Woox\WebmanValidate\Exception\ValidateException;
use Webman\Exception\ExceptionHandler;
use Webman\Http\Request;
use Webman\Http\Response;

class Handler extends ExceptionHandler
{
    /**
     * 不需要记录错误日志.
     *
     * @var string[]
     */
    public $dontReport = [];

    /**
     * HTTP Response Status Code.
     *
     * @var int
     */
    public int $statusCode = 200;

    /**
     * HTTP Response Header.
     *
     * @var array
     */
    public array $header = [];

    /**
     * Business Error code.
     *
     * @var int
     */
    public int $errorCode = 400;

    /**
     * Business Error message.
     *
     * @var string
     */
    public string $errorMessage = 'no error';

    /**
     * 响应结果数据.
     *
     * @var array
     */
    protected array $responseData = [];

    /**
     * config下的配置.
     *
     * @var array
     */
    protected array $config = [];

    /**
     * Log Error message.
     *
     * @var string
     */
    public string $error = 'no error';

    /**
     * @param Throwable $exception
     */
    public function report(Throwable $exception): void
    {
        $this->dontReport = config('plugin.woox.webman-exception.app.exception.dont_report', []);
        parent::report($exception);
    }

    /**
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     */
    public function render(Request $request, Throwable $exception): Response
    {
        $this->config = array_merge($this->config, config('plugin.woox.webman-exception.app.exception', []) ?? []);

        $this->addRequestInfoToResponse($request);
        $this->solveAllException($exception);
        $this->addDebugInfoToResponse($exception);
        $this->triggerTraceEvent($exception);

        return $this->buildResponse();
    }

    /**
     * 触发 trace 事件.
     *
     * @param Throwable $e
     * @return void
     */
    protected function triggerTraceEvent(Throwable $e): void
    {
        if (isset(request()->tracer) && isset(request()->rootSpan)) {
            $samplingFlags = request()->rootSpan->getContext();
            $this->header['Trace-Id'] = $samplingFlags->getTraceId();
            $exceptionSpan = request()->tracer->newChild($samplingFlags);
            $exceptionSpan->setName('exception');
            $exceptionSpan->start();
            $exceptionSpan->tag('error.code', (string)$this->errorCode);
            $value = [
                'event' => 'error',
                'message' => $this->errorMessage,
                'stack' => 'Exception:' . $e->getFile() . '|' . $e->getLine(),
            ];
            $exceptionSpan->annotate(json_encode($value));
            $exceptionSpan->finish();
        }
    }

    /**
     * 请求的相关信息.
     *
     * @param Request $request
     * @return void
     */
    protected function addRequestInfoToResponse(Request $request): void
    {
        $this->responseData = array_merge($this->responseData, [
            'domain' => $request->host(),
            'method' => $request->method(),
            'request_url' => $request->method() . ' ' . $request->uri(),
            'timestamp' => date('Y-m-d H:i:s'),
            'client_ip' => $request->getRealIp(),
            'request_param' => $request->all(),
        ]);
    }

    /**
     * 处理异常数据.
     *
     * @param Throwable $e
     */
    protected function solveAllException(Throwable $e): void
    {
        if ($e instanceof BaseException) {
            $this->header = $e->header;
            $this->errorMessage = $e->errorMessage;
            $this->error = $e->error;
            if (isset($e->data)) {
                $this->responseData = array_merge($this->responseData, $e->data);
            }
        }
        $this->solveExtraException($e);
    }

    /**
     * @desc: 处理扩展的异常
     * @param Throwable $e
     */
    protected function solveExtraException(Throwable $e): void
    {
        $statusCode = $errorCode = [];
        $useHttpCode = $useErrorCode = true;
        if (isset($this->config['status_code'])) {
            $statusCode = $this->config['status_code'];
            if (empty($statusCode)) {
                $useHttpCode = false;
            }
        }
        if (isset($this->config['error_code'])) {
            $errorCode = $this->config['error_code'];
            if (empty($errorCode)) {
                $useErrorCode = false;
            }
        }
        $this->errorMessage = $e->getMessage();
        if ($e instanceof BadRouteException) {
            $useHttpCode && $this->statusCode = $statusCode['route'] ?? 404;
            $useErrorCode && $this->errorCode = $errorCode['route'] ?? 404;
        } elseif ($e instanceof ValidateException) {
            $useHttpCode && $this->statusCode = $statusCode['validate'] ?? 400;
            $useErrorCode && $this->errorCode = $errorCode['validate'] ?? 400;
        } elseif ($e instanceof JwtTokenException) {
            $useHttpCode && $this->statusCode = $statusCode['jwt_token'] ?? 401;
            $useErrorCode && $this->errorCode = $errorCode['jwt_token'] ?? 401;
        } elseif ($e instanceof JwtTokenExpiredException) {
            $useHttpCode && $this->statusCode = $statusCode['jwt_token_expired'] ?? 401;
            $useErrorCode && $this->errorCode = $errorCode['jwt_token_expired'] ?? 401;
        } elseif ($e instanceof JwtRefreshTokenExpiredException) {
            $useHttpCode && $this->statusCode = $statusCode['jwt_refresh_token_expired'] ?? 402;
            $useErrorCode && $this->errorCode = $errorCode['jwt_refresh_token_expired'] ?? 402;
        } elseif ($e instanceof \InvalidArgumentException) {
            $useHttpCode && $this->statusCode = $statusCode['invalid_argument'] ?? 415;
            $useErrorCode && $this->errorCode = $errorCode['invalid_argument'] ?? 415;
            $this->errorMessage = '预期参数配置异常：' . $e->getMessage();
        } elseif ($e instanceof QueryException || $e instanceof ModelNotFoundException) {
            $useHttpCode && $this->statusCode = 500;
            $useErrorCode && $this->errorCode = 500;
            $this->errorMessage = 'Db：' . $e->getMessage();
            $this->error = $e->getMessage();
        } elseif ($e instanceof BaseException) {
            $useHttpCode && $this->statusCode = $e->statusCode;
            $useErrorCode && $this->errorCode = $e->errorCode;
        } else {
            $useHttpCode && $this->statusCode = $statusCode['server_error'] ?? 500;
            $useErrorCode && $this->errorCode = $errorCode['server_error'] ?? 500;;
            $this->errorMessage = 'Internal Server Error';
            $this->error = $e->getMessage();
            Logger::error($this->errorMessage, array_merge($this->responseData, [
                'error' => $this->error,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]));
        }
        if (!$useHttpCode) {
            $this->statusCode = 200;
        }
        if (!$useErrorCode) {
            $this->errorCode = 0;
        }
    }

    /**
     * 调试模式：错误处理器会显示异常以及详细的函数调用栈和源代码行数来帮助调试，将返回详细的异常信息。
     * @param Throwable $e
     * @return void
     */
    protected function addDebugInfoToResponse(Throwable $e): void
    {
        if (config('app.debug', false)) {
            $this->responseData['error_message'] = $this->errorMessage;
            $this->responseData['error_trace'] = explode("\n", $e->getTraceAsString());
            $this->responseData['file'] = $e->getFile();
            $this->responseData['line'] = $e->getLine();
        }
    }

    /**
     * 构造 Response.
     *
     * @return Response
     */
    protected function buildResponse(): Response
    {
        $bodyKey = array_keys($this->config['body']);
        $responseBody = [
                $bodyKey[0] ?? 'code' => $this->errorCode,
                $bodyKey[1] ?? 'msg' => $this->errorMessage,
                $bodyKey[2] ?? 'data' => $this->responseData,
        ];

        $header = array_merge(['Content-Type' => 'application/json;charset=utf-8'], $this->header);
        return new Response($this->statusCode, $header, json_encode($responseBody));
    }
}
