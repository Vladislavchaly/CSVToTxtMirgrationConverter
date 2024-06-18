<?php

require 'vendor/autoload.php';

use League\Csv\Reader;
use League\Csv\UnavailableStream;

if ($argc < 3) {
    die("Usage: php src/converter.php input.csv output.txt\n");
}

$inputFile = $argv[1];
$outputFile = $argv[2];

/**
 * @throws UnavailableStream
 * @throws \League\Csv\Exception
 */
function convert($csvFile, $outputTxt): void
{
    $reader = Reader::createFromPath($csvFile, 'r');
    $reader->setHeaderOffset(0);

    $outputHandle = fopen($outputTxt, 'w');
    fwrite($outputHandle, "SET @carrierUuid = '2ea1990d-1029-4815-87b4-6cd44dcf26e9';
            SET @bahasaLanguageUuid = (SELECT uuid FROM languages WHERE code = 'id');
            SET @englishLanguageUuid = (SELECT uuid FROM languages WHERE code = 'en');
            
            INSERT INTO translations (uuid, language_id, carrier_id, translation_key, translation_value) VALUES \n");

    $uniqFilter = [];
    foreach ($reader->getRecords() as $record) {
        if (in_array($record['KEY'], $uniqFilter)) {
            continue;
        }

        if ($record['KEY'] && $record['ID']) {
            $arValue = addslashes($record['ID']);
            fwrite($outputHandle, "                        (UUID(), @bahasaLanguageUuid, @carrierUuid, '{$record['KEY']}', '{$arValue}'),\n");
            $uniqFilter[] = $record['KEY'];
        }
        if ($record['KEY'] && $record['EN']) {
            $enValue = addslashes($record['EN']);
            fwrite($outputHandle, "                        (UUID(), @englishLanguageUuid, @carrierUuid, '{$record['KEY']}', '{$enValue}'),\n");
        }
    }

    fclose($outputHandle);
}


convert($inputFile, $outputFile)

?>