@extends('emails.layouts.email')

@section('content')
    <p>Dear {{ $user->first_name }},</p>
    <p>We are excited to welcome you to Lazim! Your account has been successfully approved by the Property Management team, and you now have access to all our services and features.</p>
    <p><strong>Your Account Details:</strong></p>
    <p><strong>Email: </strong> {{ $user->email }}</p>
    <p><strong>Password: </strong> Use the password you created during registration</p>
    <p>To get started, simply log in to your account using the credentials you created during registration. If you encounter any issues or require assistance, please don't hesitate to contact us.</p>
    <p>We are committed to providing you with a seamless experience and ensuring your needs are met. Thank you for choosing Lazim, and we look forward to serving you.</p>
    <p>Regards,</p>
    <img src="{{ env('AWS_URL') . '/' . $pm_logo }}" alt="Owner Association Logo" style="max-width: 80px; height: 30px;">
    <p>{{ $pm_oa }}</p>
@endsection
