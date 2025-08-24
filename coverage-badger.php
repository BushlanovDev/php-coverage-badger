<?php

declare(strict_types=1);

$isSquare = false;
$squareFlagKey = array_search('--square', $argv);

if ($squareFlagKey !== false) {
    $isSquare = true;
    unset($argv[$squareFlagKey]);
    $argv = array_values($argv);
}

if (count($argv) < 3) {
    echo "usage: php " . $argv[0] . " <inputFile> <outputFile> [text] [--square]\n";
    exit(1);
}

$inputFile = $argv[1];
$outputFile = $argv[2];

$text = count($argv) > 3 ? $argv[3] : 'coverage';

if (!file_exists($inputFile)) {
    throw new InvalidArgumentException('Invalid input file provided');
}

$xml = new SimpleXMLElement(file_get_contents($inputFile));
$metrics = $xml->xpath('//metrics');
$totalElements = 0;
$checkedElements = 0;

foreach ($metrics as $metric) {
    $totalElements += (int)$metric['elements'];
    $checkedElements += (int)$metric['coveredelements'];
}

$coverage = (int)(($totalElements === 0) ? 0 : ($checkedElements / $totalElements) * 100);

$templateName = $isSquare ? 'badge-flat-square.svg' : 'badge-flat.svg';
$templatePath = __DIR__ . '/templates/' . $templateName;
$template = file_get_contents($templatePath);

$color = '#e05d44';      // Red
if ($coverage >= 95) {
    $color = '#4c1';     // Bright Green
} elseif ($coverage >= 90) {
    $color = '#97ca00';  // Green
} elseif ($coverage >= 75) {
    $color = '#a4a61d';  // Yellow-Green
} elseif ($coverage >= 60) {
    $color = '#dfb317';  // Yellow
} elseif ($coverage >= 40) {
    $color = '#fe7d37';  // Orange
}

$template = str_replace('{{ total }}', (string)$coverage, $template);
$template = str_replace('{{ color }}', $color, $template);
$template = str_replace('{{ text }}', $text, $template);

file_put_contents($outputFile, $template);
