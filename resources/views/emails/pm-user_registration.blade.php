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
        We're excited to welcome you to Lazim! Your account has been successfully created, and you're now ready to manage and oversee property-related operations seamlessly using our platform.
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
    <td class="paragraph">
        To access your account and use our platform, click on <a href="{{ env('APP_URL') }}/app/login">this link</a>.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="title">
        Key Features of Your Account:
    </td>
</tr>
<tr>
    <td class="paragraph">
        <ul style="list-style-type: disc; margin-left: 20px;">
            <li>Tenant and Lease Management</li>
            <li>Payment and Billing Management</li>
            <li>Maintenance and Service Requests</li>
            <li>Vendor and Subcontractor Management</li>
            <li>Communication Tools</li>
            <li>Document Management</li>
            <li>Role-Based Access</li>
            <li>Inspection and Compliance</li>
            <li>Community Engagement Features</li>
        </ul>
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        If you have any questions or need assistance while navigating the platform, our support team is here to help.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        Thank you for choosing Lazim as your trusted partner. We're committed to helping you streamline your property management tasks and ensure a seamless experience.
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
    <td class="paragraph">
        Lazim Team
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
@include('beautymail::templates.minty.contentEnd')

@stop
