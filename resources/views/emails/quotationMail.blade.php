@extends('beautymail::templates.minty')

@section('content')

@include('beautymail::templates.minty.contentStart')
<tr>
    <td class="paragraph">
        Dear User,
    </td>
</tr>
<tr>
    <td width="100%" height="20"></td>
</tr>
<tr>
    <td class="paragraph">
        You have a new quotation!
    </td>
</tr>
@if($quotation->first_name)
<tr>
    <td class="paragraph">
        <strong>First Name:</strong> {{$quotation->first_name}}
    </td>
</tr>
@endif
@if($quotation->last_name)
<tr>
    <td class="paragraph">
        <strong>Last Name:</strong> {{$quotation->last_name}}
    </td>
</tr>
@endif
@if($quotation->company_name)
<tr>
    <td class="paragraph">
        <strong>Company Name:</strong> {{$quotation->company_name}}
    </td>
</tr>
@endif
@if($quotation->email)
<tr>
    <td class="paragraph">
        <strong>Email:</strong> {{$quotation->email}}
    </td>
</tr>
@endif
@if($quotation->phone)
<tr>
    <td class="paragraph">
        <strong>Phone:</strong> {{$quotation->phone}}
    </td>
</tr>
@endif
@if($quotation->address)
<tr>
    <td class="paragraph">
        <strong>Address:</strong> {{$quotation->address}}
    </td>
</tr>
@endif
@if($quotation->state)
<tr>
    <td class="paragraph">
        <strong>State:</strong> {{$quotation->state}}
    </td>
</tr>
@endif
@if($quotation->number_of_communities)
<tr>
    <td class="paragraph">
        <strong>Number of Communities:</strong> {{$quotation->number_of_communities}}
    </td>
</tr>
@endif
@if($quotation->number_of_units)
<tr>
    <td class="paragraph">
        <strong>Number of Units:</strong> {{$quotation->number_of_units}}
    </td>
</tr>
@endif
@if($quotation->message)
<tr>
    <td class="paragraph">
        <strong>Message:</strong> {{$quotation->message}}
    </td>
</tr>
@endif
@if($quotation->features && !empty($quotation->features))
<tr>
    <td class="paragraph">
        <strong>Features:</strong>
        <ul>
            @foreach($quotation->features as $feature)
                <li>{{ $feature }}</li>
            @endforeach
        </ul>
    </td>
</tr>
@endif
@if($quotation->onboarding_assistance && !empty($quotation->onboarding_assistance))
<tr>
    <td class="paragraph">
        <strong>Onboarding:</strong>
        <ul>
            @foreach($quotation->onboarding_assistance as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    </td>
</tr>
@endif
@if($quotation->support && !empty($quotation->support))
<tr>
    <td class="paragraph">
        <strong>Support:</strong>
        <ul>
            @foreach($quotation->support as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    </td>
</tr>
@endif
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