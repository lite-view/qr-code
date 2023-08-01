<?php

require_once __DIR__ . '/vendor/autoload.php';

$qr = new \LiteView\QrCode\QrCodeGenerator();
$r = $qr->generate('aaaa')->getString(true);
echo $r;