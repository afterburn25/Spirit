<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
Spirit\PublicSite::render('newsletter',null,['mode'=>'signup']);
