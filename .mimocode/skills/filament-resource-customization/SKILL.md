---
name: Filament Resource Customization
description: >
  Apply consistent changes across Filament v5 resources: add delete/truncate
  actions, add custom action buttons (fetch full info, run audit, create report,
  rerun audit), unify icons/language, add table filters, and batch actions.
  Use when the user asks to style, clean up, or add actions to Filament resources.
---

# Filament Resource Customization

## When to use

- User asks to clean up / unify Filament admin resources
- User wants to add delete/bulk delete/truncate actions
- User wants to add custom actions or batch actions
- User asks to change icons, language, or style across resources

## Key files to read first

```
app/Filament/Resources/
  Authors/AuthorResource.php
  Mods/ModResource.php
  ModVersions/ModVersionResource.php
  Reports/ReportResource.php
```

Each resource typically has:
- `Resource.php` — main resource class (icon, label, navigation)
- `Pages/ListX.php` — list page with table, record actions, bulk actions
- `Tables/XTable.php` — table columns, filters, actions
- `Schemas/XInfolist.php` — view page infolist
- `Schemas/XForm.php` — form (if editable)
- `RelationManagers/*.php` — related resource managers

## Common patterns

### Standard record actions

All resources include `DeleteAction` and `DeleteBulkAction`. Each resource
also has a `TruncateAction` as a header action on the List page.

```php
// In Tables/XTable.php
->recordActions([
    ViewAction::make()->iconButton(),
    // ... custom actions ...
    DeleteAction::make()->iconButton(),
], RecordActionsPosition::BeforeColumns)
->toolbarActions([
    // ... custom bulk actions ...
    DeleteBulkAction::make(),
])
```

```php
// In Pages/ListX.php — header actions
Action::make('truncateTable')
    ->label('Truncate Table')
    ->icon('heroicon-o-trash')
    ->color('danger')
    ->requiresConfirmation()
    ->modalHeading('Truncate Table')
    ->modalDescription('Are you sure you want to truncate the X table? ...')
    ->modalSubmitActionLabel('Truncate')
    ->action(function (): void {
        Model::query()->truncate();
        Notification::make()->title('X table truncated')->success()->send();
    }),
```

### Action button style

Use `->iconButton()` for table row actions to keep them compact. Position
record actions before columns with `RecordActionsPosition::BeforeColumns`.

```php
use Filament\Tables\Actions\Position\RecordActionsPosition;

->recordActionsPosition(RecordActionsPosition::BeforeColumns)
->recordActions([
    Action::make('fetchFullInfo')
        ->icon('heroicon-o-arrow-down-tray')
        ->iconButton()
        ->action(fn (Mod $record) => ...),
])
```

### Icons per resource

Each resource should have a distinct `Heroicon`:
- Authors: `Heroicon::OutlinedUserGroup`
- Mods: `Heroicon::OutlinedPuzzlePiece`
- ModVersions: `Heroicon::OutlinedTag`
- Reports: `Heroicon::OutlinedDocumentText`

### Language

Admin is in English. All labels, column headers, filter labels, action labels,
notification messages must be in English.

### Table filters

Use `TernaryFilter` for boolean fields (e.g., has full info, has errors).
Use `SelectFilter` for enum-like fields (category).

```php
use Filament\Tables\Filters\TernaryFilter;

TernaryFilter::make('has_full_info')
    ->label('Full Info')
    ->boolean()
    ->trueLabel('Has full info')
    ->falseLabel('No full info')
    ->nullable(),
```

### Batch actions

Common batch actions for this project:
- Fetch Full Info (dispatches FetchFullInfoJob for selected mods)
- Run Audit (dispatches AuditJob for selected mods)
- Delete (built-in `DeleteBulkAction`)

```php
BulkAction::make('fetchFullInfo')
    ->label('Fetch Full Info')
    ->icon('heroicon-o-arrow-down-tray')
    ->action(fn ($records) => ...)
    ->deselectRecordsAfterCompletion(),

DeleteBulkAction::make(),
```

### Mod-specific actions

**Create Report** — form with version selection dropdown:
```php
Action::make('createReport')
    ->iconButton()
    ->icon('heroicon-o-document-plus')
    ->tooltip('Create Report')
    ->color('success')
    ->form([
        Select::make('version')
            ->label('Version')
            ->options(function (Mod $record): array {
                $versions = $record->versions;
                if ($versions->isEmpty()) {
                    $record->fetchFullInfo();
                    $versions = $record->versions;
                }
                return $versions->pluck('version', 'version')->toArray();
            })
            ->required(),
    ])
    ->action(function (Mod $record, array $data): void {
        AuditJob::dispatch($record->id, $data['version']);
        Notification::make()->title('Report creation dispatched')->success()->send();
    }),
```

### Report-specific actions

**Rerun Audit** — re-dispatches audit for the report's mod/version:
```php
Action::make('rerun')
    ->iconButton()
    ->icon('heroicon-o-arrow-path')
    ->tooltip('Rerun Audit')
    ->color('warning')
    ->requiresConfirmation()
    ->modalHeading('Rerun Audit')
    ->modalDescription('Re-run the audit for this mod version.')
    ->action(function (Report $record): void {
        AuditJob::dispatch($record->mod_id, $record->mod_version);
        Notification::make()->title('Audit rerun dispatched')->success()->send();
    }),
```

## Checklist

1. Read all resource files under `app/Filament/Resources/`
2. Ensure `DeleteAction` and `DeleteBulkAction` are present in all table classes
3. Add `TruncateAction` header action to all List pages
4. Add/verify custom actions per resource (Create Report for Mods, Rerun for Reports)
5. Ensure icons are unique per resource
6. Ensure all text is in English
7. Add table filters where useful
8. Add batch actions where useful
9. Verify with `composer lint:check` and `composer types:check`
