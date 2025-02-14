@extends('emails.layouts.email')

@section('content')
<h2 style="color: #2b6cb0; margin: 0 0 20px 0;">Access Card Request</h2>

<p>Dear {{ $user->first_name }},</p>

<p>We are pleased to confirm that your access card request has been successfully submitted.</p>

<div class="title">Ticket Details:</div>
<p>
    <strong>Ticket Number:</strong> {{ $ticket_number }}<br>
    <strong>Building:</strong> {{ $building }}<br>
    <strong>Flat:</strong> {{ $flat }}<br>
    <strong>Request Type:</strong> Access Card<br>
    <strong>Card Type:</strong> {{ $card_type }}
</p>

<p>Thank you for choosing Lazim.</p>

<p>We are committed to ensuring a seamless process and look forward to assisting you further.</p>

<p>Regards,</p>

@if(isset($property_manager_logo) && $property_manager_logo)
<p>
    <img src="{{ $property_manager_logo }}" alt="Property Manager" style="max-width: 150px; height: auto;">
</p>
@endif

<p>{{ $property_manager_name }}</p>
@endsection
