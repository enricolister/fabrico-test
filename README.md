
## About Fabrico Test

This is an API for managing reservations in a coworking system, created with Laravel framework

Laravel version: v10.48.25

### How it works

A booking, as minimum data, has a date, a start time, an end time, a type (consultancy, assistance and commercial) and the user's personal data

You can book only for the next day or for the future

Each day can have a maximum of 12 bookings and a booking can last a maximum of 45 minutes (values are configurable in .env)

Bookings can not overlap each other

Notifications: after saving a booking, an asyncronous confirmation email is sent to the customer and the administration (admin email configurable in .env). The confirmation email contains the user's name and details of the booking.

When 10 bookings are reached, the system sends an asyncronous email alert to the administration reporting the limit almost reached

### Endpoints

• POST /api/bookings: Create a booking and save the data to the database with all necessary data validations

• GET /api/bookings: Retrieve all bookings given a date

• AUTH ROUTES to register an user to make bookings, login, logout and refresh auth token: see complete documentation

## For the complete documentation, created with dedoc/scramble, visit root web route ("/")

### Error Handling

Errors occurring during booking process are saved in the log file storage/logs/booking_api_errors.log

Errors about client authorization are saved in the log file storage/logs/auth_errors.log

Errors about asyncronous jobs failing are saved in the log file storage/logs/jobs_errors.log

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
