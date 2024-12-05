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
        We are delighted to inform you that your account has been successfully approved by the Property Management team.
    </td>
</tr>
<tr>
    <td width="100%" height="20"></td>
</tr>
<tr>
    <td class="paragraph">
        Welcome to Lazim!
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="title">
        Account Details:
    </td>
</tr>
<tr>
    <td width="100%" height="10"></td>
</tr>
<tr>
    <td class="paragraph">
        <strong>Account Name:</strong> {{$user->first_name}}<br>
        <strong>Email Address:</strong> {{$user->email}}<br>
        <strong>Password:</strong> {{$password}}<br>
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        You can access our platform to manage tasks, monitor property requests, and streamline your operations efficiently.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        If you have any questions or require assistance to get started, our support team is available at 043206789.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        Thank you for partnering with Lazim. We are excited to work together in delivering exceptional property management services.
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
    <td width="100%" height="10"></td>
</tr>
<tr>
    <td>
        <img src="{{url('images/logo.png')}}" alt="Lazim" style="max-width: 150px; height: auto;">
    </td>
</tr>
<tr>
    <td width="100%" height="10"></td>
</tr>
<tr>
    <td class="paragraph">
        {{auth()->user()?->first_name}}
    </td>
</tr>

@include('beautymail::templates.minty.contentEnd')

@stop
