@extends('layout.index')

@section('content')
    <h2>{{$title}}</h2>
    <p>{{$content}}</p>
    <hr>
    <h2>edit post</h2>
    <form action="/" method="POST">
        @csrf
        <input type="text" name="title" value="{{$title}}"/>
        <input type="text" name="content" value="{{$content}}"/>
        <input type="submit"/>
    </form>
    <hr/>
    <h2>new Comment</h2>
    <form action="/" method="POST">
        @csrf
        <input type="text" name="comment"/>
        <input type="submit"/>
    </form>
@endsection()
