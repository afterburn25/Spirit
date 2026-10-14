<?php
declare(strict_types=1);
// Compatibility with the separately delivered dev10 preview. The canonical
// handler enforces active-owner authorization, methods and CSRF on every request.
require dirname(__DIR__).'/launch/index.php';
