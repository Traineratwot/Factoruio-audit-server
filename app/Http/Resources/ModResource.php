<?php

namespace App\Http\Resources;

use App\Models\Mod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

/** @mixin Mod */
class ModResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $reports = $this->whenLoaded('reports');
        if ($reports && ! ($reports instanceof MissingValue)) {
            $reports = ReportResource::collection($reports);
            $reports = collect($reports)->mapWithKeys(fn (ReportResource $item) => [
                $item->mod_version => $item,
            ])->toArray();
        } else {
            $reports = [];
        }

        return [
            'id' => str($this->id)->limit(256)->ascii(),
            'name' => str($this->name)->limit(256)->ascii(),
            'owner' => str($this->author?->name)->limit(256)->ascii(),
            'latest_version' => $this->latest_version,
            'category' => str($this->category)->limit(256)->ascii(),
            'title' => str($this->title)->limit(256)->ascii(),
            'summary' => str($this->summary)->limit(256)->ascii(),
            'downloads_count' => $this->downloads_count,
            'popularity' => $this->popularity,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'reports_count' => $this->whenLoaded('reports', fn () => $this->reports->count(), 0),
            'image' => $this->getImage(),
            'latest_report_version' => $this->when(
                $this->relationLoaded('reports'),
                fn () => $this->latest_report_version,
            ),
            'report_url' => $this->when(
                $this->relationLoaded('reports') && $this->reports->count() > 0,
                fn () => route('report.mod.version', [
                    'mod' => $this->name,
                    'version' => $this->latest_report_version,
                ]),
                fn () => route('report.mod', ['mod' => $this->name]),
            ),
            'score' => (float) (isset($reports[$this->latest_report_version]) ? $reports[$this->latest_report_version]->score ?? 0 : 0),
            'reports' => $reports,
        ];
    }
}
