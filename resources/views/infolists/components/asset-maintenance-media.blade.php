<div>
    <h6>Asset Before</h6>
    <p>comment : {{json_decode($getRecord()->comment)->before?: 'Na'}}</p>
    @if(json_decode($getRecord()->media)->before)
    <a style="color:blue"target="_blank" href={{env('AWS_URL').'/'.json_decode($getRecord()->media)->before }} >photo</a>
    @else
    Na
    @endif
    <h6>Asset After</h6>
    <p>comment : {{json_decode($getRecord()->comment)->after?: 'Na'}}</p>
    @if(json_decode($getRecord()->media)->after)
    <a style="color:blue"target="_blank" href={{env('AWS_URL').'/'.json_decode($getRecord()->media)->after }} >photo</a>
    @else
    Na
    @endif
</div>
