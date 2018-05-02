<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Storage;

interface AuthStorageInterface
{
    public function get(string $key);

    public function set(string $key, ?string $value);

    public function clearAll();
}
