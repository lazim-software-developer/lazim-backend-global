@extends('emails.layouts.email')

@section('content')
<h2 style="color: #2b6cb0; margin: 0 0 20px 0;">Guest Registration Request</h2>

<p>Dear {{ $user->first_name }},</p>

<p>We are pleased to inform you that your guest registration request has been successfully submitted.</p>

<div class="title">Ticket Details:</div>
<p>
    <strong>Ticket Number:</strong> {{ $ticket_number }}<br>
    <strong>Building:</strong> {{ $building }}<br>
    <strong>Flat:</strong> {{ $flat }}
</p>

<p>Thank you for choosing Lazim for your guest registration needs.</p>

<p>We are committed to ensuring a smooth and efficient process and look forward to serving you.</p>

<p>Regards,</p>

@if(isset($property_manager_logo) && $property_manager_logo)
<p>
    <img src="{{ $property_manager_logo }}" alt="Property Manager" style="max-width: 150px; height: auto;">
</p>
@endif

<p>{{ $property_manager_name }}</p>
@endsection
