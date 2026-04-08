<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function balance(Request $request)
    {
        $wallet = $request->user()->wallet;

        return $this->success([
            'balance' => number_format((float) $wallet->balance, 2, '.', ''),
        ]);
    }
}
