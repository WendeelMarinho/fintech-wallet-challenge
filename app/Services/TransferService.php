<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use DomainException;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function transfer(User $sender, string $receiverEmail, float $amount): void
    {
        if ($amount <= 0) {
            throw new DomainException('Valor deve ser maior que zero.');
        }

        $receiver = User::where('email', $receiverEmail)->first();
        if (! $receiver) {
            throw new DomainException('Usuário destinatário não encontrado.');
        }

        if ($receiver->id === $sender->id) {
            throw new DomainException('Não é permitido transferir para si mesmo.');
        }

        DB::transaction(function () use ($sender, $receiver, $amount) {
            $senderWallet = Wallet::where('user_id', $sender->id)->lockForUpdate()->firstOrFail();
            $receiverWallet = Wallet::where('user_id', $receiver->id)->lockForUpdate()->firstOrFail();

            if ((float) $senderWallet->balance < $amount) {
                throw new DomainException('Saldo insuficiente.');
            }

            $senderWallet->balance = (float) $senderWallet->balance - $amount;
            $receiverWallet->balance = (float) $receiverWallet->balance + $amount;
            $senderWallet->save();
            $receiverWallet->save();

            Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'type' => 'debit',
            ]);

            Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'type' => 'credit',
            ]);
        });
    }
}
