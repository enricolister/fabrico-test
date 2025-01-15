<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\Renter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository
{
    public function getBookingsForDate(Request $request): Collection
    {
        $bookings = Booking::whereDate('date',$request->date)
            ->orderBy('date', 'asc')
            ->get();
        return $bookings;
    }

    public function getBookingDuration(Request $request): int
    {
        $init_time = Carbon::parse($request->start_time);
        $final_time = Carbon::parse($request->end_time);
        $duration = $init_time->diffInMinutes($final_time);
        return $duration;
    }

    public function checkIfBookingOverlaps(Collection $existingBookings, Request $request): bool
    {
        foreach ($existingBookings as $booking) {
            $oldBooking_init_time = Carbon::parse($booking->start_time);
            $oldBooking_final_time = Carbon::parse($booking->end_time);
            //$booking_duration = $booking_init_time->diffInMinutes($booking_final_time);
            $booking_start_time = Carbon::parse($request->start_time);
            $booking_end_time = Carbon::parse($request->end_time);
            if ($booking_start_time->lte($oldBooking_init_time) && $booking_end_time->gt($oldBooking_init_time) ||
                $booking_start_time->lt($oldBooking_final_time) && $booking_end_time->gte($oldBooking_final_time) ||
                $booking_start_time->gte($oldBooking_init_time) && $booking_end_time->lte($oldBooking_final_time)) {
                return true;
            }
        }
        return false;
    }

    public function saveBooking(Request $request): bool
    {
        $input = $request->all();
        $renter = null;
        if($request->email){
            // Try to find renter by email
            $renter = Renter::where('email', $request->email)->first();
            if($renter && $renter->id){
                // Renter already exists, update it
                $renter->update($input);
            }
        }
        if(!$renter || !$renter->id){
            $renter = new Renter($input);
            $renter->save();
        }
        if ($renter && $renter->id){
            $booking = new Booking([
                'renter_id' => $renter->id,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'type' => $request->type,
            ]);
            $booking->save();
            if($booking->id){
                return true;
            }
        }
        return false;
    }

}
