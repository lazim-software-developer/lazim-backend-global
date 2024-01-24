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
                Thank you for registering with Lazim! To complete your registration, please verify your email using the OTP provided below.
            </td>
        </tr>
        <tr>
            <td width="100%" height="25"></td>
        </tr>
        <tr>
            <td class="title">
                Your OTP:
            </td>
        </tr>

        <tr>
            <td width="100%" height="10"></td>
        </tr>

        @if($data['emailOtp'])
            <tr>
                <td class="paragraph">
                    <strong>Email OTP: </strong> {{$data['emailOtp']}}
                </td>
            </tr>
        @endif

        
        <!-- @if($data['phoneOtp'])
            <tr>
                <td class="paragraph">
                    <strong>Mobile OTP: </strong> {{$data['phoneOtp']}}
                </td>
            </tr>
        @endif -->
        <tr>
            <td width="100%" height="25"></td>
        </tr>

        <tr>
            <td class="paragraph">
                Please enter these OTP in the verification fields to verify your email.
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
