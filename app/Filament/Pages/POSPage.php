<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class POSPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $slug = 'pos-page';
    protected static ?string $navigationLabel = 'POS';
    protected static string $view = 'filament.pages.p-o-s-page';
  
}
