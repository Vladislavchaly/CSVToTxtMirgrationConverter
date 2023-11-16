<?php

require 'vendor/autoload.php';

use League\Csv\Reader;
use League\Csv\UnavailableStream;

if ($argc < 3) {
    die("Usage: php converter.php input.csv output.txt\n");
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
    fwrite($outputHandle, "SET @carrierUuid = '0dd2388c-53b8-11ee-8c99-0242ac120002';
            SET @arabicLanguageUuid = (SELECT uuid FROM languages WHERE code = 'ar');
            SET @englishLanguageUuid = (SELECT uuid FROM languages WHERE code = 'en');
            
            INSERT INTO translations (uuid, language_id, carrier_id, translation_key, translation_value) VALUES \n");

    $uniqFilter = [];
    foreach ($reader->getRecords() as $record) {
        if (in_array($record['KEY'], $uniqFilter)) {
            continue;
        }

        if ($record['KEY'] && $record['AR']) {
            $arValue = addslashes($record['AR']);
            fwrite($outputHandle, "                        (UUID(), @arabicLanguageUuid, @carrierUuid, '{$record['KEY']}', '{$arValue}'),\n");
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