<?php
declare(strict_types=1);
namespace Spirit;
/** SMTP DATA was transmitted, but its final acknowledgement was not verified. */
final class DeliveryUncertain extends \RuntimeException {}
