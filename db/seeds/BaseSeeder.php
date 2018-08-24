<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class BaseSeeder extends AbstractSeed
{
    protected function isTableEmpty(string $table): bool
    {
        $stmt = $this->query('SELECT NULL FROM ' . $table . ' LIMIT 1');
        $rows = $stmt->fetchAll();

        return count($rows) == 0;
    }
}
