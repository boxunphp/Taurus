<?php

/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Boxunphp\Taurus\Response;

use Boxunphp\Taurus\Instance\InstanceTrait;
use Boxunphp\Taurus\Utils\HttpCode;

class Response
{
    use InstanceTrait;

    /**
     * 跳转
     *
     * @param string $url 链接地址
     * @param int $httpCode HTTP状态码
     */
    public function redirect($url, $httpCode = HttpCode::FOUND)
    {
        header('Location:' . $url, true, $httpCode);
    }

    public function error($code, $message, $params = [])
    {
        if ($params) {
            $message = vsprintf($message, $params);
        }
        $this->output($code, $message);
    }

    public function success(array $data = [])
    {
        $this->output(0, '成功', $data);
    }

    public function json($data)
    {
        ob_clean();
        header('Content-type:application/json;charset=utf-8');
        //指定JSON_PARTIAL_OUTPUT_ON_ERROR,避免$data中有非utf-8字符导致json编码返回false
        echo json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->stop();
    }

    public function stop()
    {
        exit;
    }

    protected function output($code, $message, array $data = [])
    {
        $code = intval($code);
        $response = [
            'flag' => $code ? 'failure' : 'success',
            'code' => $code,
            'message' => $message,
            'data' => $data ?: new \stdClass(),
        ];
        $this->json($response);
    }
}
