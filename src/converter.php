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

    foreach ($reader->getRecords() as $record) {
        if ($record['AR']) {
            $arValue = addslashes($record['AR']);
            fwrite($outputHandle, "(UUID(), @arabicLanguageUuid, @carrierUuid, '{$record['KEY']}', '{$arValue}'),\n");
        }
        if ($record['EN']) {
            $enValue = addslashes($record['EN']);
            fwrite($outputHandle, "(UUID(), @englishLanguageUuid, @carrierUuid, '{$record['KEY']}', '{$enValue}'),\n");
        }
    }

    fclose($outputHandle);
}


convert($inputFile, $outputFile)

?>