<div class="mt-8 flex lg:flex-shrink-0 lg:mt-0">
    @foreach ($buttons as $button => $route)

    @php

    // get route params
    $params = [];
    if(isset($route[1])){
    foreach ($route[1] as $val) {
    array_push($params,$result->{$val});
    }
    }else{
    $params = $result->id;
    }
    @endphp

    @include('datatables::buttons.'.$button,[
    'route_name' => $route[0],
    'route_params' => $params
    ])
    @endforeach
</div>