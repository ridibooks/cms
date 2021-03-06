<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Session;

interface SessionStorageInterface
{
    public function get(string $key): ?string;

    public function set(string $key, ?string $value);

    public function clearAll();
}
