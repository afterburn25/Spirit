<?php
declare(strict_types=1);
define('SPIRIT_MACHINE_REQUEST',true);
require dirname(__DIR__, 3).'/includes/bootstrap.php';
Spirit\WebhookInbox::receive('sandbox');
