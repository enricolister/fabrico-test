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
    /**
     * Retrieves bookings for a specific date.
     *
     * This function fetches all bookings from the database for the date
     * specified in the request, ordered by date in ascending order.
     *
     * @param Request $request The HTTP request object containing the date parameter.
     *
     * @return Collection A collection of Booking models for the specified date.
     */
    public function getBookingsForDate(Request $request,bool $withRenter = false): Collection
    {
        $query = Booking::whereDate('date',$request->date)
            ->orderBy('date', 'asc');
        $query->when($withRenter, function ($query) {
            return $query->with('renter');
        });
        return $query->get();
    }

    /**
     * Calculate the duration of a booking in minutes.
     *
     * This function takes a request object containing start and end times,
     * parses them into Carbon instances, and calculates the difference in minutes.
     *
     * @param Request $request The HTTP request object containing start_time and end_time parameters.
     *
     * @return int The duration of the booking in minutes.
     */
    public function getBookingDuration(Request $request): int
    {
        $init_time = Carbon::parse($request->start_time);
        $final_time = Carbon::parse($request->end_time);
        $duration = $init_time->diffInMinutes($final_time);
        return $duration;
    }

    /**
     * Check if a new booking overlaps with existing bookings.
     *
     * This function compares the start and end times of a new booking request
     * against a collection of existing bookings to determine if there's any overlap.
     *
     * @param Collection $existingBookings A collection of existing Booking models to check against.
     * @param Request $request The HTTP request object containing the new booking's start_time and end_time.
     *
     * @return bool Returns true if the new booking overlaps with any existing booking, false otherwise.
     */
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

    /**
     * Save a new booking and associated renter information.
     *
     * This function processes a booking request, creating or updating a renter
     * based on the provided email, and then creates a new booking associated
     * with that renter.
     *
     * @param Request $request The HTTP request object containing booking and renter information.
     *                         Expected to contain:
     *                         - date: The date of the booking
     *                         - start_time: The start time of the booking
     *                         - end_time: The end time of the booking
     *                         - type: The type of booking
     *                         - Other renter information fields
     *                         - firstname: The renter's first name
     *                         - lastname: The renter's last name
     *                         - address: The renter's address (optional)
     *                         - email: The renter's email address (optional)
     *                         - phone: The renter's phone number (optional)
     *
     * @return bool Returns true if the booking was successfully saved, false otherwise.
     */
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
