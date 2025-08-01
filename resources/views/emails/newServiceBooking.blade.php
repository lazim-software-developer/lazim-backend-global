@extends('emails.layouts.email')

@section('content')
<h2 style="color: #2b6cb0; margin: 0 0 20px 0;">New Service Booking Notification</h2>

<p>A new service booking has been created for your account.</p>

<div class="title">Service Booking Details:</div>
<p>
    <strong>Booking ID:</strong> {{ $facilityBooking->id }}<br>
    <strong>Date of Booking:</strong> {{ $facilityBooking->created_at }}<br>
    <strong>Date of Service :</strong> {{ $facilityBooking->date }}<br>
    <strong>Building Name:</strong> {{ $facilityBooking->building?->name }}<br>
    <strong>Work Type:</strong> {{ $facilityBooking->bookable?->name }}<br>
    <strong>Unit:</strong> {{ $facilityBooking->flat?->property_number }}<br>
    <strong>User Name:</strong> {{ $facilityBooking->user?->first_name . ' ' . $facilityBooking->user?->last_name }}<br>
    <strong>User Email:</strong> {{ $facilityBooking->user?->email }}<br>
    <strong>User Phone:</strong> {{ $facilityBooking->user?->phone }}<br>
</p>


<p>Regards,</p>

<p>Lazim Team</p>

@endsection
