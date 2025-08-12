<?php

require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$fontDir = __DIR__ . '/storage/fonts';
$fontName = 'notosanskhmer';
$ttfFile = $fontDir . '/NotoSansKhmer-Regular.ttf';

$options = new Options();
$options->set('fontDir', $fontDir);
$options->set('fontCache', $fontDir);
$options->set('defaultFont', $fontName);

$dompdf = new Dompdf($options);
$fontMetrics = $dompdf->getFontMetrics();
$fontMetrics->getFont($fontName, 'normal');
echo "✅ Font loaded successfully.\n";
