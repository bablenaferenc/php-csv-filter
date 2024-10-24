<?php

if ($argc != 4) {
    echo 'Ambiguous number of parameters!';
    exit(1);
}

$dokumentumok = loadDocumentsFromCsv('document_list.csv');
$dokumentumok = filterDocuments($dokumentumok, $argv[1], $argv[2], $argv[3]);

printDocumentHeader();
printDocuments($dokumentumok, $argv[3]);

/**
 * Load documents from CSV
 *
 * @param string $filename
 * @return array List of documents
 */
function loadDocumentsFromCsv($filename): array
{
    $documents = [];
    $header = [];

    if (($handle = fopen($filename, 'r')) !== false) {
        $row = 0;

        while (($data = fgetcsv($handle, null, ';')) !== false) {
            if ($row === 0) {
                $header = $data;
            } else {
                $documents[] = parseDocumentRow($header, $data);
            }

            $row++;
        }
        fclose($handle);
    }

    return $documents;
}

/**
 * Parse document row
 *
 * @param array $header
 * @param array $data
 * @return void
 */
function parseDocumentRow($header, $data)
{
    $document = [];

    foreach ($header as $index => $key) {
        $value = json_decode($data[$index], true);
        $document[$key] = (json_last_error() === JSON_ERROR_NONE) ? $value : $data[$index];
    }

    return $document;
}

/**
 * Filter documents by type and partnerId
 *
 * @param array $documents
 * @param string $type
 * @param string $partnerId
 * @return array Filtered documents
 */
function filterDocuments($documents, $type, $partnerId): array
{
    return array_filter($documents, function ($document) use ($type, $partnerId) {
        $partner = (array)$document['partner'];

        return !empty($partner['id']) && $partner['id'] == $partnerId && $document['document_type'] == $type;
    });
}

/**
 * Calculate total price of items
 *
 * @param array $items
 * @return float
 */
function calculateTotal($items): float
{
    return array_reduce($items, function ($carry, $item) {
        return $carry + ($item['unit_price'] * $item['quantity']);
    }, 0);
}

/**
 * Print document header, as document_id, document_type, partner name, total
 *
 * @return void
 */
function printDocumentHeader()
{
    $headers = ['document_id', 'document_type', 'partner name', 'total'];

    foreach ($headers as $header) {
        echo str_pad($header, 20);
    }
    echo "\n";

    foreach ($headers as $header) {
        echo str_repeat('=', 20);
    }
    echo "\n";
}

/**
 * Print documents with total price greater than minTotal
 *
 * @param $documents
 * @param $minTotal
 * @return void
 */
function printDocuments($documents, $minTotal)
{
    foreach ($documents as $document) {
        $total = calculateTotal($document['items']);

        if ($total > $minTotal) {
            echo str_pad($document['id'], 20);
            echo str_pad($document['document_type'], 20);
            echo str_pad($document['partner']['name'], 20);
            echo str_pad($total, 20);
            echo "\n";
        }
    }
}
