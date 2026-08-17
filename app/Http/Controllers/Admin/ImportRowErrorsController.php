<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportRowErrorsController extends Controller
{
    /**
     * Streams the rows that failed to import as a CSV — headered by the raw
     * data each row had, plus why it wasn't imported — so staff can correct
     * the file and re-import just those rows.
     */
    public function __invoke(ImportBatch $importBatch): StreamedResponse
    {
        Gate::authorize('manage-catalog');

        $rowErrors = $importBatch->rowErrors()->orderBy('row_number')->get();

        return response()->streamDownload(function () use ($rowErrors) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['row_number', 'type', 'message', 'title', 'author', 'category', 'isbn', 'publisher']);

            foreach ($rowErrors as $rowError) {
                $data = $rowError->row_data;
                fputcsv($handle, [
                    $rowError->row_number,
                    $rowError->type->value,
                    $rowError->message,
                    $data['title'] ?? '',
                    $data['author'] ?? '',
                    $data['category'] ?? '',
                    $data['isbn'] ?? '',
                    $data['publisher'] ?? '',
                ]);
            }

            fclose($handle);
        }, "import-{$importBatch->id}-errors.csv", ['Content-Type' => 'text/csv']);
    }
}
