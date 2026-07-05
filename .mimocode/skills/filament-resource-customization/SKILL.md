---
name: Filament Resource Customization
description: >
  Apply consistent changes across Filament v5 resources: remove CRUD actions,
  add custom action buttons (fetch full info, run audit), unify icons/language,
  add table filters, and batch actions. Use when the user asks to style, clean
  up, or add actions to Filament resources.
---

# Filament Resource Customization

## When to use

- User asks to clean up / unify Filament admin resources
- User wants read-only resources (no create/edit/delete)
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

### Remove CRUD actions (read-only admin)

This project's admin is strictly read-only. Remove EditAction, DeleteAction,
CreateAction, and DeleteBulkAction from all resources. Keep only custom actions
like "Fetch Full Info" or "Run Audit".

```php
// In ListX page
public function table(Table $table): Table
{
    return $table
        ->recordActions([
            // Custom actions only, no EditAction/DeleteAction
        ])
        ->toolbarActions([
            // Custom bulk actions only, no DeleteBulkAction
        ]);
}
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

```php
Action::make('fetchFullInfo')
    ->label('Fetch Full Info')
    ->icon('heroicon-o-arrow-down-tray')
    ->action(fn (Collection $records) => ...)
    ->deselectRecordsAfterCompletion(),
```

## Checklist

1. Read all resource files under `app/Filament/Resources/`
2. Identify which resources have CRUD actions that should be removed
3. Add/verify custom actions per resource
4. Ensure icons are unique per resource
5. Ensure all text is in English
6. Add table filters where useful
7. Add batch actions where useful
8. Verify with `composer types:check` (PHPStan level 7)
