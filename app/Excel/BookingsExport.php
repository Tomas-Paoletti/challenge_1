<?php

namespace App\Excel;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BookingsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function collection()
    {
        return Booking::with(['tour', 'hotel'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Customer Name',
            'Customer Email',
            'Number of People',
            'Booking Date',
            'Tour Name',
            'Tour Price',
            'Hotel Name',
            'Hotel Address',
            'Booking Status',
            'Created At'
        ];
    }

    public function map($booking): array
    {
        return [
            $booking->id,
            str_replace(',', ' ', $booking->customer_name),
            $booking->customer_email,
            $booking->number_of_people,
            $booking->booking_date,
            str_replace(',', ' ', $booking->tour->name),
            $booking->tour->price,
            str_replace(',', ' ', $booking->hotel->name),
            str_replace(',', ' ', $booking->hotel->address),
            $booking->booking_status,
            $booking->created_at->format('Y-m-d H:i:s')
        ];
    }
}
