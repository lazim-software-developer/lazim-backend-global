@extends('emails.layouts.email')

@section('content')
<h2 style="color: #2b6cb0; margin: 0 0 20px 0;">{{ ucfirst($type) }} Request</h2>

<p>Dear {{ $user->first_name }},</p>

<p>We are pleased to confirm that your {{ $type }} request has been successfully submitted.</p>

<div class="title">@if($type == 'move-in') Request Details: @else Ticket Details: @endif</div>
<p>
    <strong>@if($type == 'move-in')Ticket Number:@else Ticket Number:@endif</strong> {{ $ticket_number }}<br>
    <strong>Building:</strong> {{ $building }}<br>
    <strong>Flat:</strong> {{ $flat }}<br>
    @if($type == 'move-in')
    <strong>Move-In Date:</strong> {{ $moving_date }}<br>
    <strong>Move-In Time:</strong> {{ $moving_time }}
    @else
    <strong>Request Type:</strong> Move-Out<br>
    <strong>Moving Date:</strong> {{ $moving_date }}<br>
    <strong>Moving Time:</strong> {{ $moving_time }}
    @endif
</p>

@if($type == 'move-in')
<p>We are excited to welcome you and are committed to ensuring a seamless move-in experience.</p>
<p>If you have any questions or require assistance, feel free to contact us.</p>
<p>Thank you for choosing Lazim.</p>
@else
<p>Thank you for choosing us.</p>
<p>We appreciate your cooperation and are here to ensure a smooth process for your move-out.</p>
<p>If you need any further assistance, please feel free to reach out to us.</p>
@endif

<p>Regards,</p>

@if(isset($property_manager_logo) && $property_manager_logo)
<p>
    <img src="{{ $property_manager_logo }}" alt="Property Manager" style="max-width: 150px; height: auto;">
</p>
@endif

<p>{{ $property_manager_name }}</p>
@endsection
