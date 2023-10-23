<div>
            {{$getRecord()->complaintable->name ? $getRecord()->complaintable->name : $getRecord()->complaintable->user->first_name }}
</div>
