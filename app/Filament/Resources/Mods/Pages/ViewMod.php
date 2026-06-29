<?php

namespace App\Filament\Resources\Mods\Pages;

use App\Facades\FactorioService;
use App\Filament\Resources\Mods\ModResource;
use App\Models\Mod;
use App\Models\ModVersion;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewMod extends ViewRecord
{
    protected static string $resource = ModResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('fetchFullInfo')
                ->label('Загрузить полную информацию')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn (Mod $record) => $this->fetchFullInfo($record)),
        ];
    }

    protected function fetchFullInfo(Mod $record): void
    {
        $data = FactorioService::modFull($record->name);
        if ($data === null) {
            Notification::make()
                ->title('Ошибка')
                ->body('Не удалось загрузить информацию о моде')
                ->danger()
                ->send();

            return;
        }

        $latestRelease = $data['releases'][0] ?? null;

        $record->update([
            'thumbnail' => $data['thumbnail'] ?? null,
            'description' => $data['description'] ?? null,
            'homepage' => $data['homepage'] ?? null,
            'license' => $data['license'] ?? null,
            'tags' => $data['tags'] ?? null,
            'images' => $data['images'] ?? null,
            'releases' => $data['releases'] ?? null,
            'changelog' => $data['changelog'] ?? null,
            'score' => $data['score'] ?? null,
            'factorio_version' => $latestRelease['info_json']['factorio_version'] ?? null,
            'latest_release_date' => $latestRelease['released_at'] ?? null,
        ]);

        $this->syncVersions($record, $data['releases'] ?? []);

        Notification::make()
            ->title('Готово')
            ->body('Полная информация загружена')
            ->success()
            ->send();

        $this->refreshFormData([
            'thumbnail',
            'description',
            'homepage',
            'license',
            'tags',
            'images',
            'releases',
            'changelog',
            'score',
            'factorio_version',
            'latest_release_date',
        ]);
    }

    protected function syncVersions(Mod $record, array $releases): void
    {
        foreach ($releases as $release) {
            ModVersion::updateOrCreate(
                [
                    'mod_id' => $record->id,
                    'version' => $release['version'],
                ],
                [
                    'file_name' => $release['file_name'],
                    'download_url' => $release['download_url'],
                    'sha1' => $release['sha1'],
                    'factorio_version' => $release['info_json']['factorio_version'],
                    'dependencies' => $release['info_json']['dependencies'],
                    'released_at' => $release['released_at'],
                ]
            );
        }
    }
}
