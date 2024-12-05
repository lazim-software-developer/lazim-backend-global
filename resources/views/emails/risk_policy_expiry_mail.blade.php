@extends('beautymail::templates.minty')

@section('content')

	@include('beautymail::templates.minty.contentStart')
		<tr>
			<td class="paragraph">
                Dear Facility Management Team,
            </td>
		</tr>
		<tr>
			<td width="100%" height="20"></td>
		</tr>
		<tr>
			<td class="paragraph">
                This is a friendly reminder that your Risk Policy Certificate is set to expire on {{$expiry_date}}.
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
			<td width="100%" height="10"></td>
		</tr>

        <tr>
            <td class="paragraph">
                To avoid any disruptions to your services, please update your renewed Risk Policy Certificate details in your profile.
			</td>
		</tr>
        <tr>
			<td width="100%" height="25"></td>
		</tr>

        <tr>
            <td class="paragraph">
                For assistance, reach out to us at +971 501362428 / 043954525.
			</td>
		</tr>
        <tr>
			<td width="100%" height="25"></td>
		</tr>

        <tr>
            <td class="paragraph">
                Thank you for your prompt attention to this matter.
			</td>
		</tr>
        <tr>
			<td width="100%" height="5"></td>
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
