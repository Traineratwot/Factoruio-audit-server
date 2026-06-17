<?php

namespace App\Helpers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;

class FileUploadController extends \Livewire\Features\SupportFileUploads\FileUploadController
{
    public function handle()
    {
        $user = auth()->user();

        if (!$user || !$user->canAccessPanel()) {
            throw new HttpResponseException(
                response()->json(['message' => 'Only super admin can upload files.'], 422),
            );
        }

        $disk = FileUploadConfiguration::disk();
        $filePaths = $this->validateAndStore(request('files'), $disk);

        return ['paths' => $filePaths];
    }
}
