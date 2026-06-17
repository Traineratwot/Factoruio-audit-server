<?php

namespace App\Http\Resources;

use App\Models\Mod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/** @mixin Mod */
class ModResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $reports = ReportResource::collection($this->whenLoaded('reports'));
        if (is_array($reports) || $reports instanceof Collection) {
            $reports = collect($reports)->mapWithKeys(fn(ReportResource $item) => [
                $item->mod_version => $item
            ])->toArray();
        }else{
            $reports = [];
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'owner' => $this->owner,
            'latest_version' => $this->latest_version,
            'category' => $this->category,
            'title' => $this->title,
            'summary' => $this->summary,
            'downloads_count' => $this->downloads_count,
            'popularity' => $this->popularity,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'reports_count' => $this->reports_count,
            'report_url' => route('report.mod', [
                'mod' => $this->name
            ]),
            'score' => (float)(isset($reports[$this->latest_version]) ? $reports[$this->latest_version]->score ?? 0 : 0),
            'reports' => $reports,
        ];
    }
}
