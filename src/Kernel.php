<?php

/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Taurus;

use Taurus\Traits\RequestTrait;
use Taurus\Traits\ResponseTrait;
use Taurus\Config\Config;
use Taurus\Database\MysqlException;
use Taurus\Exception\ErrorException;
use Taurus\Exception\Exception;
use Taurus\Exception\FatalException;
use Taurus\Exception\NotFoundException;
use Taurus\Exception\ServerErrorException;
use Taurus\Instance\InstanceTrait;
use Taurus\Logger\Handler\FileHandler;
use Taurus\Logger\Logger;
use Taurus\Memcached\MemcacheException;
use Taurus\Redis\RedisException;
use Taurus\Request\Request;
use Taurus\Router\Router;
use Taurus\Helper\HttpCode;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Throwable;

final class Kernel
{
    use InstanceTrait;
    use RequestTrait;
    use ResponseTrait;
    use LoggerAwareTrait;

    const ENV_DEVELOP = 'develop';      // 开发环境
    const ENV_TEST = 'test';            // 测试环境
    const ENV_BETA = 'beta';            // 线上测试环境
    const ENV_RELEASE = 'release';      // 线上生产环境

    /**
     * 站点根目录
     * @var string
     */
    private $rootPath;
    /**
     * 应用名称(英文,开头字母大写)
     * @var string
     */
    private $appName;
    /**
     * 环境变量
     * @var string
     */
    private $environment;
    /**
     * 环境配置目录
     * @var string
     */
    private $envPath;
    /**
     * 允许的URL后缀配置
     * @var array
     */
    private $suffixs = [];
    /**
     * 是否已经初始化过了,不能重复初始化
     * @var bool
     */
    private $isInitialized = false;

    /**
     * WEB模式
     * 
     * @param string $rootPath
     * @param string $appName
     * @throws \Exception
     */
    public function main($rootPath, $appName)
    {
        if ($this->isInitialized) {
            return;
        }
        $this->rootPath = $rootPath;
        $this->appName = $appName;
        $this->initialize();
        $this->bootstrap();
    }

    /**
     * 命令行模式
     *
     * @param string $rootPath
     * @return void
     */
    public function console($rootPath)
    {
        if ($this->isInitialized) {
            return;
        }
        $this->rootPath = $rootPath;
        $this->initialize();
    }

    /**
     * initialize from $env/app.php
     *
     * @throws \Exception
     */
    private function initialize()
    {
        $this->isInitialized = true;

        $config = $this->env()->get('app');
        // 设置错误显示
        error_reporting(isset($config['error_reporting']) ? $config['error_reporting'] : E_ALL);
        ini_set('display_errors', isset($config['display_errors']) ? boolval($config['display_errors']) : false);

        // 设置时区
        date_default_timezone_set(isset($config['timezone']) ? $config['timezone'] : 'Asia/Shanghai');

        // 设置捕捉句柄
        set_error_handler([$this, 'errorHandler'], error_reporting());
        set_exception_handler([$this, 'exceptionHandler']);
        register_shutdown_function([$this, 'shutdownHandler']);
    }

    /**
     * @throws \Exception
     */
    private function bootstrap()
    {
        $Router = Router::getInstance(Request::getInstance());
        $routerConfig = $this->config()->get('router/' . strtolower($this->getAppName())) ?: [];
        $Router->setConfig($routerConfig);
        $filePath = $Router->route();
        
        $fullClassName = sprintf('%s\\Controller\\%s', $this->getAppNamespace(), strtr($filePath, '/', '\\'));
        $controllerFile = sprintf('%s/Controller/%s.php', $this->getAppPath(), $filePath);
        if (!is_file($controllerFile) || !class_exists($fullClassName)) {
            throw new NotFoundException('Not Found', HttpCode::NOT_FOUND);
        }

        $controller = new $fullClassName();
        if (!method_exists($controller, 'main') || !is_callable([$controller, 'main'])) {
            throw new \RuntimeException('Method main Not Allowed', HttpCode::CONFLICT);
        }
        if (method_exists($controller, 'before') && is_callable([$controller, 'before'])) {
            call_user_func([$controller, 'before']);
        }
        call_user_func([$controller, 'main']);
        if (method_exists($controller, 'after') && is_callable([$controller, 'after'])) {
            call_user_func([$controller, 'after']);
        }
    }

    public function getRootPath()
    {
        return $this->rootPath;
    }

    public function getAppName()
    {
        return $this->appName;
    }

    public function getAppNamespace()
    {
        return '\\Boxun\\App\\' . $this->appName;
    }

    public function getAppPath()
    {
        return $this->getRootPath() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . $this->getAppName();
    }

    public function getEnvironment()
    {
        if ($this->environment) {
            return $this->environment;
        }
        return $this->environment = getenv('RAURUS_ENV') ? getenv('RAURUS_ENV') : self::ENV_RELEASE;
    }

    /**
     * @return Config
     */
    public function config()
    {
        static $config;
        if (is_null($config)) {
            $config = new Config();
            $config->setPath($this->getRootPath() . DIRECTORY_SEPARATOR . 'config');
        }
        return $config;
    }

    /**
     * @return Config
     * @throws \Exception
     */
    public function env()
    {
        static $env;
        if (is_null($env)) {
            $env = new Config();
            $env->setPath($this->getEnvPath());
        }
        return $env;
    }

    /**
     * @return LoggerInterface
     */
    public function logger()
    {
        static $logger;
        if (is_null($logger)) {
            $config = $this->env()->get('app');
            $level = $config['log_level'] ?? LogLevel::DEBUG;
            $handler = new FileHandler();
            $handler->setSavePath($config['log_save_path'] ?? $this->getRootPath() . DIRECTORY_SEPARATOR . 'logs');
            $logger = new Logger($level, $handler);
        }

        return $logger;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function getEnvPath()
    {
        if ($this->envPath) {
            return $this->envPath;
        }

        $environment = $this->getEnvironment();
        $this->envPath = $this->getRootPath() . DIRECTORY_SEPARATOR . 'env' . DIRECTORY_SEPARATOR . $environment;
        return $this->envPath;
    }

    /**
     * 代码抛出错误拦截
     * @param \Throwable $e
     */
    public function exceptionHandler(\Throwable $e)
    {
        $code = $e->getCode() ? (int) $e->getCode() : E_WARNING;
        $message = sprintf(
            'message: %s ( %d ), file: %s ( %d )',
            $e->getMessage(),
            $e->getCode(),
            $e->getFile(),
            $e->getLine()
        );

        // 日志
        $log = [
            'code' => $code,
            'message' => $message,
        ];

        // 资源参数
        if ($e instanceof MysqlException) {
            $log['sql'] = $e->getPrepareSql();
            $log['params'] = $e->getParams();
            $log['host'] = $e->getHost();
            $log['port'] = $e->getPort();
        } elseif ($e instanceof RedisException) {
            $log['method'] = $e->getMethod();
            $log['params'] = $e->getParams();
            $log['host'] = $e->getHost();
            $log['port'] = $e->getPort();
        } elseif ($e instanceof MemcacheException) {
            $log['method'] = $e->getMethod();
            $log['params'] = $e->getParams();
            $log['config'] = $e->getConfig();
        }

        // 记录日志
        if (
            in_array($code, [
                E_ERROR,
                E_CORE_ERROR,
                E_COMPILE_ERROR,
                E_USER_ERROR,
                E_PARSE,
                E_RECOVERABLE_ERROR
            ])
            || $e instanceof ErrorException
            || $e instanceof ServerErrorException
        ) {
            $this->logger()->error($log);
        } elseif (in_array($code, [E_NOTICE, E_USER_NOTICE])) {
            $this->logger()->info($log);
        } elseif ($e instanceof FatalException) {
            $this->logger()->critical($log);
        } else {
            $this->logger()->warning($log);
        }

        if ($this->request()->isXmlHttpRequest()) {
            $this->response()->error($code, $e->getMessage());
        } else {
            echo sprintf('%s[%d]', $message, $code);
        }
        $this->response()->stop();
    }

    /**
     * 语法错误信息拦截
     *
     * @param $errorCode
     * @param $errorMessage
     * @param $errorFile
     * @param $errorLine
     */
    public function errorHandler($errorCode, $errorMessage, $errorFile, $errorLine)
    {
        if (!(error_reporting() & $errorCode)) {
            return;
        }

        $e = new Exception($errorMessage, $errorCode);
        $e->setFile($errorFile);
        $e->setLine($errorLine);
        $this->exceptionHandler($e);
    }

    /**
     * 程序执行结束处理
     */
    public function shutdownHandler()
    {
        $error = error_get_last();
        if ($error) {
            $e = new Exception($error['message'], $error['type']);
            $e->setFile($error['file']);
            $e->setLine($error['line']);
            $this->exceptionHandler($e);
        }
    }
}
