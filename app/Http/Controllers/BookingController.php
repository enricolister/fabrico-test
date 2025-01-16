<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailQueueJob;
use App\Repositories\BookingRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{

    protected $repository;

    public function __construct(BookingRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Process and create a new booking.
     *
     * This method handles the creation of a new booking, including validation of input data,
     * checking for booking conflicts, and sending confirmation emails.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing booking details.
     *                                          Expected fields:
     *                                          - date: string (required, format: Y-m-d, must be tomorrow or later)
     *                                          - start_time: string (required, format: H:i)
     *                                          - end_time: string (required, format: H:i, must be after start_time)
     *                                          - type: string (required, one of: consultancy, assistance, commercial)
     *                                          - firstname: string (required, max 255 characters)
     *                                          - lastname: string (required, max 255 characters)
     *                                          - phone: numeric (optional, min 10 digits)
     *                                          - email: string (optional, valid email, max 255 characters)
     *                                          - address: string (optional, max 255 characters)
     *
     * @return \Illuminate\Http\JsonResponse Returns a JSON response with the booking status.
     *                                       - On success: 200 OK with success message.
     *                                       - On validation failure: 422 Unprocessable Entity with error details.
     *                                       - On booking limit reached: 406 Not Acceptable with error message.
     *                                       - On booking duration exceeded: 406 Not Acceptable with error message.
     *                                       - On booking overlap: 406 Not Acceptable with error message.
     */
    public function makeBooking(Request $request)
    {
        $sendThresholdEmail = false;
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d|after_or_equal:tomorrow',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'required|string|in:consultancy,assistance,commercial',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'phone' => 'sometimes|nullable|numeric|min:10',
            'email' => 'sometimes|nullable|string|email|max:255',
            'address' => 'sometimes|nullable|string|max:255',
        ],[
            'date.required' => 'The date field is required.',
            'date.date_format' => 'The date format must be Y-m-d.',
            'date.after_or_equal' => 'The date must be tomorrow or later.',
            'start_time.required' => 'The start time field is required.',
            'start_time.date_format' => 'The start time format must be H:i.',
            'end_time.required' => 'The end time field is required.',
            'end_time.date_format' => 'The end time format must be H:i.',
            'end_time.after' => 'The end time must be after the start time.',
            'type.required' => 'The type field is required.',
            'type.in' => 'The type must be one of consultancy, assistance, commercial.',
            'firstname.required' => 'The firstname field is required.',
            'firstname.string' => 'The firstname must be a string.',
            'firstname.max' => 'The firstname may not be greater than 255 characters.',
            'lastname.required' => 'The lastname field is required.',
            'lastname.string' => 'The lastname must be a string.',
            'lastname.max' => 'The lastname may not be greater than 255 characters.',
            'phone.numeric' => 'The phone, if present, must be numeric.',
            'phone.min' => 'The phone number, if present, must be at least 10 characters.',
            'email.email' => 'The email, if present, must be a valid email address.',
            'email.max' => 'The email, if present, may not be greater than 255 characters.',
            'address.string' => 'The address, if present, must be a string.',
            'address.max' => 'The address, if present, may not be greater than 255 characters.',
        ]);

        if ($validator->fails()) {
            $responseBody = [
                'status' => 'error',
                'message' => 'bookings fields validation failed',
                'fields' => $validator->errors()
            ];
            LogController::saveLog('booking_api','bookings',json_encode($responseBody));
            return response()->json($responseBody, 422);
        }

        $existingBookings = $this->repository->getBookingsForDate($request);

        if(env('MAX_BOOKINGS_PER_DAY',12) <= count($existingBookings)) {
            $responseBody = [
                'status' => 'error',
                'message' => 'maximum number of bookings reached for the given date'
            ];
            LogController::saveLog('booking_api','bookings',json_encode($responseBody));
            return response()->json($responseBody, 406);
        }

        if(env('NUMBER_OF_BOOKINGS_EMAIL_THRESHOLD',10) == (count($existingBookings)+1)) {
            $sendThresholdEmail = true;
        }

        $duration = $this->repository->getBookingDuration($request);
        if(env('MAX_BOOKING_DURATION',45) < $duration) {
            $responseBody = [
                'status' => 'error',
                'message' => 'booking duration exceeds maximum allowed duration of '.env('MAX_BOOKING_DURATION',45).' minutes'
            ];
            LogController::saveLog('booking_api','bookings',json_encode($responseBody));
            return response()->json($responseBody, 406);
        }

        if($this->repository->checkIfBookingOverlaps($existingBookings,$request)){
            $responseBody = [
                'status' => 'error',
                'message' => 'booking time overlaps with existing bookings'
            ];
            LogController::saveLog('booking_api','bookings',json_encode($responseBody));
            return response()->json($responseBody, 406);
        }

        if($this->repository->saveBooking($request)){
            if($sendThresholdEmail){
                // Send email notification approaching booking limit to admin
                dispatch(new SendEmailQueueJob('approaching_limit',$request->all(),env('ADMIN_EMAIL')));
            }

            // Send emails for booking confirmation to client and admin
            if($request->email){
                // Send email to client
                dispatch(new SendEmailQueueJob('confirmation_to_renter',$request->all(),$request->email));
            }
            // Send email to admin
            dispatch(new SendEmailQueueJob('confirmation_to_admin',$request->all(),env('ADMIN_EMAIL')));

            // Return success response
            return response()->json([
                'status' => 'success',
                'message' => 'Booking made successfully'
            ]);
        }
    }

    /**
     * Retrieves bookings for a specific date.
     *
     * This function retrieves all bookings for a given date from the database.
     * It validates the input date and returns a JSON response with the bookings.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the date.
     *                                          Expected fields:
     *                                          - date: string (required, format: Y-m-d)
     *
     * @return \Illuminate\Http\JsonResponse Returns a JSON response with the bookings for the given date.
     *                                       - On success: 200 OK with an array of bookings.
     *                                       - On validation failure: 422 Unprocessable Entity with error details.
     */
    public function getBookingsForDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
        ],[
            'date.required' => 'The date field is required.',
            'date.date_format' => 'The date format must be Y-m-d.'
        ]);

        if ($validator->fails()) {
            $responseBody = [
                'status' => 'error',
                'message' => 'bookings fields validation failed',
                'fields' => $validator->errors()
            ];
            LogController::saveLog('booking_api','bookings',json_encode($responseBody));
            return response()->json($responseBody, 422);
        }

        $bookings = $this->repository->getBookingsForDate($request,true);
        $resArray = [];
        foreach($bookings as $booking) {
            $resArray[] = [
                'id' => $booking->id,
                'date' => $booking->date->format('Y-m-d'),
                'start_time' => $booking->start_time->format('H:i'),
                'end_time' => $booking->end_time->format('H:i'),
                'type' => $booking->type,
                'firstname' => $booking->renter->firstname,
                'lastname' => $booking->renter->lastname,
                'phone' => $booking->renter->phone,
                'email' => $booking->renter->email,
                'address' => $booking->renter->address
            ];
        }
        return response()->json($resArray);
    }
}
