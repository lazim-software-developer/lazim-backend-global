@extends('emails.layouts.email')

@section('content')
    <p>Dear {{ $user->first_name }},</p>
    <p>We regret to inform you that your account application with Lazim is not approved.</p>
    @if($record->remarks)
        <p><strong>Rejection Reason:</strong><br>Remark: {{ $record->remarks }}</p>
    @endif
    <p>To proceed with the approval of your account, kindly upload the required valid documents again.</p>
    <p><a href="{{ env('RESIDENT_DOCUMENT_PAGE') . '/' . encrypt($record->id) . '/' . $role }}">Click here to upload your documents.</a></p>
    <p>If you have any questions or need further assistance, please do not hesitate to contact our support team.</p>
    <p>We appreciate your understanding and look forward to your updated submission.</p>
    <p>Regards,</p>
    <img src="{{ env('AWS_URL') . '/' . $pm_logo }}" alt="Owner Association Logo" style="max-width: 80px; height: 30px;">
    {{-- @if($user->ownerAssociation && $user->ownerAssociation->first() && $user->ownerAssociation->first()->profile_photo)
        <img src="{{ env('AWS_URL') . '/' . $user->ownerAssociation->first()->profile_photo }}" alt="Owner Association Logo" style="max-width: 80px; height: 30px;">
    @endif --}}
    <p>{{ $pm_oa }}</p>
@endsection
