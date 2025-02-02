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
                Welcome to Lazim Gatekeeper App! We're here to make your security tasks simple and efficient.
			</td>
		</tr>
		<tr>
			<td width="100%" height="25"></td>
		</tr>
		<tr>
			<td class="title">
                Your Login Details:
			</td>
		</tr>
		<tr>
			<td width="100%" height="10"></td>
		</tr>
        <tr>
            <td class="paragraph">
                <strong>Username: </strong> {{$user->email}}
			</td>
		</tr>
        <tr>
            <td class="paragraph">
                <strong>Temporary Password: </strong> {{$password}}
                <br>(Change your password after logging in.)
			</td>
		</tr>
        <tr>
			<td width="100%" height="25"></td>
		</tr>
        <tr>
			<td class="title">
                Quick Start:
			</td>
		</tr>
        <tr>
            <td class="paragraph">
                • Download the App: <a href="{{ env('GATEKEEPER_LINK') }}">{{ env('GATEKEEPER_LINK') }}</a><br>
                • Log In: Use the details above<br>
                • Explore Features: Manage visitors, deliveries, and access points effortlessly
			</td>
		</tr>
        <tr>
			<td width="100%" height="25"></td>
		</tr>
        <tr>
            <td class="paragraph">
                Need help? Contact us anytime at info@sasfm.co or 043206789.
			</td>
		</tr>
        <tr>
			<td width="100%" height="25"></td>
		</tr>
        <tr>
            <td class="paragraph">
                We're excited to have you on board!
            </td>
		</tr>
		<tr>
			<td width="100%" height="5"></td>
		</tr>
		<tr>
			<td class="paragraph">
                - Lazim Team
			</td>
		</tr>
		<tr>
			<td width="100%" height="25"></td>
		</tr>
	@include('beautymail::templates.minty.contentEnd')

<p>We're excited to have you on board!</p>

<p>
    Regards,<br>
    <strong>-Lazim Team</strong>
</p>
@endsection
