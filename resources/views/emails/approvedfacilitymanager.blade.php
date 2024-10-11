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
        Congratulations! Your account for Lazim has been approved. We're excited to have you on board as a facility manager.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="title">
        Your Login Credentials:
    </td>
</tr>
<tr>
    <td width="100%" height="10"></td>
</tr>
<tr>
    <td class="paragraph">
        <strong>Email:</strong> {{$user->email}}<br>
        <strong>Password:</strong> {{$password}}
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        To access your account, please click on <a href="https://lazim-vendor-git-feat-property-management-zysktech.vercel.app/login">this link</a>.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        For security reasons, we recommend changing your password after your first login.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        If you have any questions or need assistance, please don't hesitate to contact our support team.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        Welcome aboard!
    </td>
</tr>
<tr>
    <td width="100%" height="5"></td>
</tr>
<tr>
    <td class="paragraph">
        Best regards,<br>
        The Lazim Team
    </td>
</tr>

@include('beautymail::templates.minty.contentEnd')

@stop
