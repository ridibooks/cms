<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Tests;

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

    public function set(string $key, ?string $value)
    {
        $this->data[$key] = $value;
    }

    public function clearAll()
    {
        $this->data = [];
    }
}
