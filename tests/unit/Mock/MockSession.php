<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Tests\Mock;

use Ridibooks\Cms\Service\Auth\Session\SessionStorageInterface;

class MockSession implements SessionStorageInterface
{
    private $data;

    public function __construct(array $data = null)
    {
        $this->data = $data ?? [];
    }

    public function get(string $key): ?string
    {
        return $this->data[$key] ?? null;
    }

    public function set(string $key, ?string $value, ?array $options = [])
    {
        $this->data[$key] = $value;
    }

    public function clear(string $key, ?array $options = [])
    {
        $this->data[$key] = null;
    }

    public function clearAll()
    {
        $this->data = [];
    }
}
