@extends('mail.layout')

@section('main')
<h3>"{{$name}}" License will expire on {{$expiry_date}}. </h3>
<p>Email: {{$email}}</p>
<p>Phone: {{$phone}}</p>

@endsection
