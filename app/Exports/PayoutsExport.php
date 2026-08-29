<?php

namespace App\Exports;

use App\Models\StorePayout;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PayoutsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $storeId;

    public function __construct($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return StorePayout::where('store_id', $this->storeId)->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Payout ID',
            'Date',
            'Amount',
            'Status',
            'Transaction Reference'
        ];
    }

    public function map($payout): array
    {
        return [
            $payout->payout_id,
            \App\Helpers\DateHelper::format($payout->created_at, true),
            $payout->amount,
            strtoupper($payout->status),
            $payout->transaction_id ?? 'N/A'
        ];
    }
}
