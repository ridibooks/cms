<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Session;

interface SessionStorageInterface
{
    public function get(string $key): ?string;

    public function set(string $key, ?string $value, ?array $options);

    public function clear(string $key_name, ?array $options);

    public function clearAll();
}
