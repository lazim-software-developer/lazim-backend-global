@extends('emails.layouts.email')

@section('content')
<h2 style="color: #2b6cb0; margin: 0 0 20px 0;">Risk Policy Certificate Expiry Reminder</h2>

<p>Dear Facility Management Team,</p>

<p>This is a friendly reminder that your Risk Policy Certificate is set to expire on {{$expiry_date}}.</p>

<div class="title">Next Steps:</div>
<p>To avoid any disruptions to your services, please update your renewed Risk Policy Certificate details in your profile.</p>

<p>For assistance, reach out to us at +971501362428 / 043954525.</p>

<p>Thank you for your prompt attention to this matter.</p>

<p>
    Regards,<br>
    <strong>-Lazim Team</strong>
</p>
@endsection
