<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Staff & Reporters';

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Newsroom Team Member')->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Select::make('role')
                        ->options([
                            'admin' => 'Admin (Full Access)',
                            'editor' => 'Editor (Publish & Review)',
                            'reporter' => 'Reporter (Drafts & Media)',
                            'producer' => 'Producer (Media & Curation)',
                        ])
                        ->required()
                        ->default('reporter'),
                    TextInput::make('password')
                        ->password()
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->maxLength(255)
                        ->helperText('Leave empty to keep existing password when editing.'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'admin' => 'danger',
                        'editor' => 'warning',
                        'reporter' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->label('Joined'),
            ])
            ->filters([
                SelectFilter::make('role')->options([
                    'admin' => 'Admin',
                    'editor' => 'Editor',
                    'reporter' => 'Reporter',
                    'producer' => 'Producer',
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}

