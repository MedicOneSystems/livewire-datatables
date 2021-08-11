<?php

namespace Mediconesystems\LivewireDatatables\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

 class FileExportController
 {
     public function handle($filename)
     {
         $response = Response::make(Storage::disk(config('livewire-datatables.file_export.disk') ?: config('filesystems.default'))
            ->get('datatables/' . $filename), 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'X-Vapor-Base64-Encode' => 'True',
            ]);

         Storage::disk(config('livewire-datatables.file_export.disk') ?: config('filesystems.default'))
        ->delete('datatables/' . $filename);

         return $response;
     }
 }
