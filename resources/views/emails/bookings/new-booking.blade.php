{{-- resources/views/emails/bookings/new-booking.blade.php --}}
    <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmación de Reserva de Tour</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
        }
        .booking-details {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .tour-details {
            background: #f9f9f9;
            margin: 20px 0;
            padding: 20px;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>¡Reserva Confirmada!</h1>
</div>

<div class="booking-details">
    <h2>Detalles de la Reserva</h2>
    <p><strong>Nombre:</strong> {{ $booking->customer_name }}</p>
    <p><strong>Email:</strong> {{ $booking->customer_email }}</p>
    <p><strong>Número de Personas:</strong> {{ $booking->number_of_people }}</p>
    <p><strong>Fecha de Reserva:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y H:i') }}</p>
</div>

<div class="tour-details">
    <h2>Detalles del Tour</h2>
    <p><strong>Tour:</strong> {{ $tour->name }}</p>
    @if($hotel)
        <p><strong>Hotel:</strong> {{ $hotel->name }}</p>
    @endif
</div>

<div class="footer">
    <p>Gracias por elegir nuestros servicios</p>
    <p>Si tienes alguna pregunta, no dudes en contactarnos</p>
</div>
</body>
</html>
