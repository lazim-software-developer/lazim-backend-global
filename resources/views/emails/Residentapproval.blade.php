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
                We are excited to welcome you to Lazim! Your account has been successfully approved by the Property Management team, and you now have access to all our services and features.
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
                <strong>Password: </strong> Use the password you created during registration
            </td>
        </tr>
        <tr>
            <td width="100%" height="20"></td>
        </tr>
        <tr>
            <td class="paragraph">
                To get started, simply log in to your account using the credentials you created during registration. If you encounter any issues or require assistance, please don't hesitate to contact us.
            </td>
        </tr>
        <tr>
            <td class="paragraph">
                We are committed to providing you with a seamless experience and ensuring your needs are met. Thank you for choosing Lazim, and we look forward to serving you.
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
                <img src="{{url('images/logo.png')}}" alt="Lazim" style="max-width: 100px; height: 50px;">
            </td>
        </tr>
        <tr>
            <td width="100%" height="10"></td>
        </tr>
        <tr>
            <td class="paragraph">
                {{auth()->user()->first_name}}
            </td>
        </tr>
        <tr>
            <td width="100%" height="25"></td>
        </tr>
    @include('beautymail::templates.minty.contentEnd')

@stop
