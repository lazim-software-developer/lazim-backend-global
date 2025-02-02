@extends('emails.layouts.email')

@section('content')
<h2 style="color: #2b6cb0; margin: 0 0 20px 0;">Payment Link Request</h2>

<p>Dear {{ $user->first_name }},</p>

<p>You have received a payment request.</p>

<div class="title">Cheque Details:</div>
<p>
    <strong>Cheque Number:</strong> {{ $rentalCheque->cheque_number }}<br>
    <strong>Amount:</strong> {{ $rentalCheque->amount }}<br>
    <strong>Due Date of Cheque:</strong> {{ $rentalCheque->due_date }}
</p>

<p>Please approve this request by providing the payment link for the cheque in the admin panel at your earliest convenience.</p>

<p>For any questions, feel free to reach out to us at +971 501362428 / 043954525.</p>

<p>
    Regards,<br>
    {{ $requestedBy }} {{ $rentalCheque?->rentalDetail?->flat?->property_number }}<br>
    {{ $rentalCheque?->rentalDetail?->flat?->building?->name }}
</p>
@endsection
