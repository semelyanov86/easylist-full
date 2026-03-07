<?php

declare(strict_types=1);

namespace App\Actions\ShoppingList;

use App\Models\ShoppingList;
use Illuminate\Support\Facades\Mail;

final readonly class SendShoppingListEmailAction
{
    /**
     * Отправить список покупок по email.
     */
    public function execute(ShoppingList $shoppingList, string $email): void
    {
        $shoppingList->load('items');

        $items = $shoppingList->items
            ->map(fn ($item): string => "- {$item->name} ({$item->quantity} {$item->quantity_type})")
            ->implode("\n");

        Mail::raw(
            "Список покупок: {$shoppingList->name}\n\n{$items}",
            function (\Illuminate\Mail\Message $message) use ($email, $shoppingList): void {
                $message->to($email)
                    ->subject("Список покупок: {$shoppingList->name}");
            },
        );
    }
}
