@extends('beautymail::templates.minty')

@section('content')

@include('beautymail::templates.minty.contentStart')
<tr>
    <td class="paragraph">
        Dear {{$user->first_name}},
    </td>
</tr>
<tr>
    <td width="100%" height="20"></td>
</tr>
<tr>
    <td class="paragraph">
        We are excited to have you on board with Lazim! Your account has been successfully created by the Property Manager,
        and we're thrilled to welcome you to our community.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="title">
        Your Account Details:
    </td>
</tr>
<tr>
    <td width="100%" height="10"></td>
</tr>
<tr>
    <td class="paragraph">
        <strong>Email: </strong> {{$user->email}}
    </td>
</tr>
<tr>
    <td class="paragraph">
        <strong>Password: </strong> {{$password}}
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    @if(!in_array($user->role->name, ['Technician', 'Gatekeeper', 'Resident']))
    <td class="paragraph">
        To access your account and use our platform, click on
        <a href="{{ env('APP_URL') }}/app/login">this link</a>
    </td>
    @else

    @endif
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        To get started, simply log in to your account using the credentials you created during registration.
        If you encounter any issues or require assistance, please don't hesitate to contact us.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        We are committed to providing you with a seamless experience and ensuring your needs are met.
        Thank you for choosing Lazim, and we look forward to serving you.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        Regards,
    </td>
</tr>
<tr>
    <td width="100%" height="15"></td>
</tr>
<tr>
    <td>
        <img src="{{url('images/logo.png')}}" alt="Company Logo" style="max-width: 80px; height: 30px;">
    </td>
</tr>
<tr>
    <td width="100%" height="10"></td>
</tr>
<tr>
    <td class="paragraph">
        {{$pm_oa}}
    </td>
</tr>
@include('beautymail::templates.minty.contentEnd')

@stop
