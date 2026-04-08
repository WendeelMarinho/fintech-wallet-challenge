<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Services\TransferService;
use DomainException;

class TransferController extends Controller
{
    public function __construct(private TransferService $transferService) {}

    public function transfer(TransferRequest $request)
    {
        try {
            $this->transferService->transfer(
                $request->user(),
                $request->validated('email'),
                (float) $request->validated('amount')
            );
        } catch (DomainException $e) {
            return $this->error($e->getMessage());
        }

        return $this->success([], 'Transferência realizada.');
    }
}
