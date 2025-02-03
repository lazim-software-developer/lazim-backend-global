@extends('emails.layouts.email')

@section('content')
    <p>Dear {{ $user->first_name }},</p>
    <p>Thank you for your interest in joining Lazim. We regret to inform you that your account application has not been approved at this time.</p>
    @if($remarks)
        <p><strong>Reason for Rejection:</strong><br>{{ $remarks }}</p>
    @endif
    <p><strong>Next Steps:</strong></p>
    <p>Please review the reason for rejection mentioned above and update your application as required.</p>
    <p>To update your application, please <a href="{{ env('VENDOR_URL') }}">click this link</a>.</p>
    <p>Once your updates are submitted, we will review your application again promptly.</p>
    <p>We appreciate your understanding and look forward to your revised application. Thank you for your cooperation.</p>
    <p>Regards,</p>
    @if($user->ownerAssociation && $user->ownerAssociation->first() && $user->ownerAssociation->first()->profile_photo)
        <img src="{{ env('AWS_URL') . '/' . $user->ownerAssociation->first()->profile_photo }}" alt="Owner Association Logo" style="max-width: 80px; height: 30px;">
    @endif
    <p>{{ $pm_oa }}</p>
@endsection
