<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionFilterRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(TransactionFilterRequest $request)
    {
        $filters = $request->validated();
        $user = $request->user();

        $query = Transaction::query()
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->orderByDesc('created_at');

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $this->success($query->paginate(10));
    }

    public function latest(Request $request)
    {
        $user = $request->user();
        $items = Transaction::query()
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return $this->success($items);
    }
}
