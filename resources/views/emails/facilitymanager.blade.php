@extends('emails.layouts.email')

@section('content')
    <p>Dear {{$user->first_name}},</p>
    <p>We're thrilled to welcome you to the Lazim community! Your account has been successfully created, and you're now ready to begin using our platform.</p>
    <p><strong>Your Account Details:</strong></p>
    <ul>
        <li><strong>Email:</strong> {{$user->email}}</li>
        <li><strong>Password:</strong> {{$password}}</li>
    </ul>
    <p>To get started, please <a href="{{env('VENDOR_URL')}}/login">click here</a> to access your account. Upon logging in, you will be redirected to the document upload page, where you can submit the required documents for verification.</p>
    <p><strong>Next Steps:</strong></p>
    <ol>
        <li>Upload the necessary documents for our review.</li>
        <li>Our admin team will assess your submission and approve or provide feedback if updates are required.</li>
        <li>Once approved, you will receive a confirmation email granting full access to your account.</li>
    </ol>
    <p>We're confident that Lazim will provide you with valuable tools and support to streamline your facility management operations.</p>
    <p>If you have any questions or need assistance, please don't hesitate to contact us.</p>
    <p>Thank you for choosing Lazim. We look forward to working with you!</p>
    <p>Regards,<br>Lazim Team</p>
@endsection
