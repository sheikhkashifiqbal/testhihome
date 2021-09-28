@extends('mail.layout')

@section('main')
<h3>Congratulation! you have got a new review.</h3>
<p>Customer Name: {{$customer['first_name'] . ' ' . $customer['last_name']}} </p>
<p>rating: {{$rate}} </p>
<p>review: {{$review}} </p>

@endsection
