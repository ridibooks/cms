<?php
declare(strict_types=1);

use Ridibooks\Cms\Controller\CmsControllerProvider;

// web service
$app->mount('/', new CmsControllerProvider());
