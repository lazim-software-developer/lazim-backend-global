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
        We're thrilled to welcome you to the Lazim community! Your account has been successfully created, and you're now ready to begin using our platform.
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
        <strong>● Email: </strong> {{$user->email}}
    </td>
</tr>
<tr>
    <td class="paragraph">
        <strong>● Password: </strong> {{$password}}
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        To get started, please <a href="{{env('VENDOR_URL')}}/login">click here</a> to access your account. Upon logging in, you will be redirected to the document upload page, where you can submit the required documents for verification.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="title">
        Next Steps:
    </td>
</tr>
<tr>
    <td class="paragraph">
        1. Upload the necessary documents for our review.<br>
        2. Our admin team will assess your submission and approve or provide feedback if updates are required.<br>
        3. Once approved, you will receive a confirmation email granting full access to your account.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        We're confident that Lazim will provide you with valuable tools and support to streamline your facility management operations.
    </td>
</tr>
<tr>
    <td width="100%" height="20"></td>
</tr>
<tr>
    <td class="paragraph">
        If you have any questions or need assistance, please don't hesitate to contact us.
    </td>
</tr>
<tr>
    <td width="100%" height="20"></td>
</tr>
<tr>
    <td class="paragraph">
        Thank you for choosing Lazim. We look forward to working with you!
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        Regards,<br>
        Lazim Team
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
@include('beautymail::templates.minty.contentEnd')

@stop
