@extends('layout.index')

@section('content')
    @foreach($response as $key => $post)
        <h2>{{$titles[$key]}}</h2>
        <p>{{$post->head_trace_id}}</p>
        <hr/>
    @endforeach

    {{$response->links()}}

    <hr>
    <h2>new Post</h2>
    <form action="/" method="POST">
        @csrf
        <input type="text" name="title"/>
        <input type="text" name="content"/> <input type="submit">
    </form>
@endsection()
