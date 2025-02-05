@extends('emails.layouts.email')

@section('content')
    <p>Dear {{ $user->first_name }},</p>
    <p>We are excited to have you on board with Lazim!</p>
    <p>Your account has been successfully created by the Property Manager, and we’re thrilled to welcome you to our community.</p>
    <p><strong>Your Account Details:</strong></p>
    <ul>
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>Login Link:</strong> <a href="{{ env('RESIDENT_URL') }}/login">Click here to login</a></li>
    </ul>
    <p>To get started, simply log in to your account using the credentials you created during registration.</p>
    <p>If you encounter any issues or require assistance, please don’t hesitate to contact us.</p>
    <p>We are committed to providing you with a seamless experience and ensuring your needs are met.</p>
    <p>Thank you for choosing Lazim, and we look forward to serving you.</p>
    <p>Regards,</p>
    <img src="{{ env('AWS_URL') . '/' . $pm_logo }}" alt="Owner Association Logo" style="max-width: 80px; height: 30px;">
    <p>{{ $pm_oa }}</p>
@endsection
