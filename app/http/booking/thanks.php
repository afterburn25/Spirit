<?php
declare(strict_types=1);
require dirname(__DIR__, 3).'/includes/bootstrap.php';
if(!isset($_SESSION['booking_receipt'])){go('booking/');}
header('X-Robots-Tag: noindex, nofollow');
Spirit\PublicSite::render('booking-thanks',null,['reference'=>$_SESSION['booking_receipt']]);
