<?php
declare(strict_types=1);
require __DIR__.'/includes/bootstrap.php';
if(isset($_GET['sitemap'])){Spirit\Discovery::sitemap();}
Spirit\PublicSite::render('home');
