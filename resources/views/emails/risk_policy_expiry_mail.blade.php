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
			I trust this email finds you well. As part of our ongoing commitment to maintaining strong partnerships, we would like to remind you about the upcoming expiration of your Risk Policy.
			</td>
		</tr>
		<tr>
			<td width="100%" height="25"></td>
		</tr>
        <tr>
			<td class="title">
            Renewal Information:
			</td>
		</tr>

		<tr>
			<td width="100%" height="10"></td>
		</tr>

        <tr>
            <td class="paragraph">
			Your current trade licence is set to expire on {{$document->expiry_date}}. To ensure continuity in our collaboration and to avoid any disruption of services, update your risk policy details in your profile.
			</td>
		</tr>
        <tr>
			<td width="100%" height="25"></td>
		</tr>

        <tr>
            <td class="paragraph">
            Thank you for choosing Lazim. We're confident that you'll find great value in our platform, and we look forward to serving you.
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
