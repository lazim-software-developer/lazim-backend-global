@extends('emails.layouts.email')

@section('content')
<h2 style="color: #2b6cb0; margin: 0 0 20px 0;">Important: Trade License Expiry Reminder</h2>

<p>Dear Team,</p>

<p>This is a reminder that your Trade License is set to expire on {{$vendor->tl_expiry->format('Y-m-d')}}.</p>

<div class="title">Trade License Details:</div>
@php
    use Carbon\Carbon;
    $endDate = Carbon::parse($vendor->tl_expiry);
    $remainingDays = $endDate->diffInDays(Carbon::now());
@endphp
<p>
    <strong>Days Remaining:</strong> {{$remainingDays}}<br>
    <strong>Trade License Number:</strong> {{$vendor->tl_number}}
</p>

<div class="title">Action Required:</div>
<p>To ensure uninterrupted services, please update your trade license details in your profile immediately.</p>

<p>For assistance, feel free to contact us at +971 507098272 / 043206789.</p>

<p>Thank you for your prompt attention to this matter.</p>

<p>
    Regards,<br>
    <strong>-Lazim Team</strong>
</p>
@endsection
