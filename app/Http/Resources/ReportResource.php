<?php

namespace App\Http\Resources;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Report */
class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mod' => $this->mod->toArray(),
            'mod_version' => $this->mod_version,
            'score' => $this->score,
            'raw' => $this->raw,
            'sha1' => $this->sha1,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'mod_id' => $this->mod_id,
            'link' => $this->getUrl(),
        ];
    }
}
