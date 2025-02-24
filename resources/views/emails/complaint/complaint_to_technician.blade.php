@extends('emails.layouts.email')

@section('content')
<h2 style="color: #2b6cb0; margin: 0 0 20px 0;">New Task Assignment</h2>

<p>Dear {{ $user->first_name }},</p>

<p>A new task has been assigned to you.</p>

<div class="title">Task Details:</div>
<p>
    <strong>Ticket Number:</strong> {{ $ticket_number }}<br>
    <strong>Building:</strong> {{ $building }}<br>
    <strong>Flat:</strong> {{ $flat }}<br>
    <strong>Task Details:</strong> {{ $description }}
</p>

<p>Kindly address this task at your earliest convenience. Your prompt attention and resolution are greatly appreciated.</p>

<p>Best regards,</p>

@if(isset($property_manager_logo) && $property_manager_logo)
<p>
    <img src="{{ $property_manager_logo }}" alt="Property Manager" style="max-width: 150px; height: auto;">
</p>
@endif

<p>{{ $property_manager_name }}</p>
@endsection
