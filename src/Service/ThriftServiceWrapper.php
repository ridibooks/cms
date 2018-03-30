<?php
namespace Ridibooks\Cms\Service;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Ridibooks\Cms\Thrift\Errors\ErrorCode;
use Ridibooks\Cms\Thrift\Errors\SystemException;

class ThriftServiceWrapper
{
    private $service_class = null;
    private $service = null;
    private $logger = null;
    private $log_level = LogLevel::INFO;

    public function __construct($service, LoggerInterface $logger = null, string $log_level = LogLevel::INFO)
    {
        $this->service_class = get_class($service);
        $this->service = $service;
        $this->logger = $logger;
        $this->log_level = $log_level;
    }

    public function __call($name, $arguments)
    {
        try {
            if (isset($this->logger)) {
                $msg = sprintf('Thrift calling %s::%s(%s)', $this->service_class, $name, $this->printArguments($arguments));
                $this->logger->log($this->log_level, $msg);
            }

            return call_user_func_array([$this->service, $name], $arguments);
        } catch (\Exception $e) {
            throw new SystemException([
                'code' => ErrorCode::INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function printArguments(array $arguments): string
    {
        $arguments = array_map(function ($arg) {
            if ($arg === null) {
                return 'null';
            } elseif (is_string($arg)) {
                return "'" . $arg . "'";
            } elseif (is_array($arg)) {
                return $this->printArguments($arg);
            }

            return $arg;
        }, $arguments);

        return implode(',', $arguments);
    }
}
