<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ImportUploadController extends Controller
{
    /**
     * Handles the wizard's file-upload step as a plain form POST rather than
     * a Livewire-bound file input — Livewire v4.3.5's reactive upload
     * manager has a client-side bug with this exact "select file, submit
     * form" flow (crashes inside its internal $wire.upload() proxy before
     * the request is ever sent). A classic multipart form has no such
     * dependency and is simpler for a one-shot upload anyway.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage-catalog');

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:20480'],
        ], attributes: ['file' => __('ملف إكسل')]);

        $file = $validated['file'];
        $path = $file->store('imports', 'local');

        $batch = ImportBatch::create([
            'user_id' => $request->user()->id,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $path,
        ]);

        return redirect()->route('admin.import.show', $batch);
    }
}
