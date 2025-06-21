<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->required()
                ->live(),
                
            Forms\Components\TextInput::make('total_amount')
                ->numeric()
                ->required()
                ->disabled()
                ->prefix('₹')
                ->formatStateUsing(fn ($state) => number_format($state, 2)),
                
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'processing' => 'Processing', 
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled'
                ]),
                
            Forms\Components\Repeater::make('items')
                ->relationship()
                ->defaultItems(1)
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->relationship('product', 'name')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            $product = \App\Models\Product::find($state);
                            if ($product) {
                                $set('price_at_purchase', $product->price);
                                $set('quantity', $get('quantity', 1));
                                self::updateTotalAmount($set, $get);
                            }
                        }),
                        
                    Forms\Components\TextInput::make('quantity')
                        ->numeric()
                        ->default(1)
                        ->required()
                        ->minValue(1)
                        ->rules(['integer', 'min:1'])
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            if ($state < 1) $set('quantity', 1);
                            self::updateTotalAmount($set, $get);
                        }),
                        
                    Forms\Components\TextInput::make('price_at_purchase')
                        ->numeric()
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->prefix('₹')
                        ->formatStateUsing(fn ($state) => number_format($state, 2)),
                ])
                ->live()
                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                    self::updateTotalAmount($set, $get);
                })
                ->columns(3)
                ->minItems(1)
            ]);
    }

    protected static function updateTotalAmount(Forms\Set $set, Forms\Get $get): void
    {
        $items = $get('items');
        $total = 0;
        
        if (is_array($items)) {
            foreach ($items as $item) {
                if (is_array($item)) {  // Additional check for array items
                    $total += ($item['quantity'] ?? 1) * ($item['price_at_purchase'] ?? 0);
                }
            }
        }
        
        $set('total_amount', $total);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('INR'),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled'
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
