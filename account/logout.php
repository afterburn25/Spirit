<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
Spirit\Storefront::boot();
Spirit\Security::checkPost();
Spirit\Customers::logout();
go('account/login.php');
