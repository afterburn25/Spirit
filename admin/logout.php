<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
Spirit\Security::checkPost(); Spirit\Auth::logout(); go('admin/login.php');
