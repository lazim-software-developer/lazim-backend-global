@extends('emails.layouts.email')

@section('content')
<h2 style="color: #2b6cb0; margin: 0 0 20px 0;">Complaint Request Acknowledgment</h2>

<p>Dear {{ $user->first_name }},</p>

<p>Thank you for reaching out to us. We are pleased to confirm that your complaint request has been successfully submitted.</p>

<div class="title">Ticket Details:</div>
<p>
    <strong>Ticket Number:</strong> {{ $ticket_number }}<br>
    <strong>Building:</strong> {{ $building }}<br>
    <strong>Flat:</strong> {{ $flat }}
</p>

<p>We appreciate your trust in us and are committed to addressing your concerns promptly.</p>

<p>If you have any further questions or require updates, please don't hesitate to contact our support team.</p>

@if(isset($property_manager_logo) && $property_manager_logo)
<p>
    <img src="{{ $property_manager_logo }}" alt="Property Manager" style="max-width: 150px; height: auto;">
</p>
@endif

<p>
    {{ $property_manager_name }}
</p>
@endsection
