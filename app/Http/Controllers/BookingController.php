<?php

namespace App\Http\Controllers;

use App\Enum\BookingStatusEnum;
use App\Events\BookingCreated;
use App\Excel\BookingsExport;
use App\Exceptions\SendBookingException;
use App\Mail\NewBookingNotification;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class BookingController extends Controller
{

    protected $allowedSortFields = [
        'booking_date',
        'customer_name',
        'customer_email',
        'number_of_people',
        'created_at'
    ];
    public function index(Request $request)
    {
        try {

            $validatedData = $request->validate([
                'tour_id' => 'exists:tours,id',
                'hotel_id' => 'exists:hotels,id',
                'customer_name' => 'string|max:255',
                'customer_email' => 'email|max:255',
                'number_of_people' => 'integer|min:1',
                'booking_date' => 'date',
                'start_date' => 'date',
                'end_date' => 'date',
                'sort_by' => ['sometimes', Rule::in($this->allowedSortFields)],
                'sort_order' => ['sometimes', Rule::in(['asc', 'desc'])],
                'per_page' => 'integer|min:1|max:100',
            ]);





        $query = Booking::query();
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->betweenDates($request->start_date, $request->end_date);
        }elseif ($request->has('start_date')) {
            $query->where('booking_date', '>=', $request->start_date);
        }elseif ($request->has('end_date')) {
            $query->where('booking_date', '<=', $request->end_date);
        }

        if ($request->has('hotel_id')) {
            $query->hotel($request->hotel_id);
        }

        if ($request->has('tour_id')) {
            $query->tour($request->tour_id);
        }
        if ($request->has('customer_name')) {
            $customer_name = strtolower($request->customer_name);
            $query->customerName($request->$customer_name);
        }
        if ($request->has('customer_email')) {
            $customer_email = strtolower($request->customer_email);
            $query->customerEmail($request->$customer_email);
        }
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            if (in_array($sortBy, $this->allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $perPage = $request->input('per_page', 15);
            $bookings = $query->paginate($perPage);

        if ($bookings->isEmpty()) {
            return response()->json([
                'message' => 'No bookings found',
            ], 200);
        }



            return response()->json([
                'data' => $bookings->items(),
                'meta' => [
                    'current_page' => $bookings->currentPage(),
                    'total_pages' => $bookings->lastPage(),
                    'total_items' => $bookings->total(),
                    'per_page' => $bookings->perPage(),
                ]
            ], 200);
    }catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => 'Validation error',
            'errors' => $e->errors()
        ], 422);
    }
    catch (\Exception $e) {
        Log::error('Error in booking listing: ' . $e->getMessage());
        return response()->json([
            'message' => 'Error listing bookings,try again ',
        ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'tour_id' => 'required|exists:tours,id',
                'hotel_id' => 'required|exists:hotels,id',
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'number_of_people' => 'required|integer|min:1',
                'booking_date' => 'required|date',
            ]);

            DB::beginTransaction();

            $booking = Booking::create($validatedData);

            event(new BookingCreated($booking));

            DB::commit();

            return response()->json([
                'message' => 'Booking created successfully',
                'booking' => $booking
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error in booking creation: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error creating booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show(Booking $booking)
    {
        return response()->json($booking, 200);
    }

    public function update(Request $request, Booking $booking)
    {
        $validatedData = $request->validate([
            'tour_id' => 'sometimes|exists:tours,id',
            'hotel_id' => 'sometimes|exists:hotels,id',
            'customer_name' => 'sometimes|string|max:255',
            'customer_email' => 'sometimes|email|max:255',
            'number_of_people' => 'sometimes|integer|min:1',
            'booking_date' => 'sometimes|date',
        ]);

        $booking->update($validatedData);
        return response()->json($booking, 200);
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();
        return response()->json(null, 204);
    }



    public function export()
    {

        try {
            return Excel::download(new BookingsExport, 'bookings.csv', \Maatwebsite\Excel\Excel::CSV);
        } catch (\Exception $e) {
            Log::error('Error exporting bookings: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error exporting bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        try {
            $booking = Booking::findOrFail($id);

            if ($booking->booking_status === BookingStatusEnum::CANCELLED->toString()) {
                return response()->json([
                    'message' => 'Booking is already cancelled',
                ], 400);
            }

            $booking->booking_status = BookingStatusEnum::CANCELLED->value;
            $booking->save();

            Log::info("Booking {$id} has been cancelled", [
                'booking_id' => $id,
                'previous_status' => $booking->getOriginal('booking_status')
            ]);

            return response()->json([
                'message' => 'Booking successfully cancelled',
                'booking' => $booking
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error cancelling booking: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error cancelling booking',
            ], 500);
        }
    }

}
