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
                We noticed a request to reset your password for your Lazim account. Here's your OTP to proceed:
            </td>
        </tr>
        <tr>
            <td width="100%" height="25"></td>
        </tr>
        <tr>
            <td class="title">
                OTP: {{$otp}}
            </td>
        </tr>
        <tr>
            <td width="100%" height="25"></td>
        </tr>
        <tr>
            <td class="paragraph">
                If you did not request this, please ignore this email.
            </td>
        </tr>
        <tr>
            <td width="100%" height="25"></td>
        </tr>
        <tr>
            <td class="paragraph">
                Warm regards,
            </td>
        </tr>
        <tr>
            <td width="100%" height="5"></td>
        </tr>
        <tr>
            <td class="paragraph">
                Lazim team
            </td>
        </tr>
        <tr>
            <td width="100%" height="25"></td>
        </tr>
    @include('beautymail::templates.minty.contentEnd')

@stop
