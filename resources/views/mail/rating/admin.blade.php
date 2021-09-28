@extends('mail.layout')

@section('main')
<h3>Received a review for a seller</h3>
<p>Customer Name: {{$customer['first_name'] . ' ' . $customer['last_name']}} </p>
<p>Store Name: {{$seller['name']}} </p>
<p>rating: {{$rate}} </p>
<p>review: {{$review}} </p>

@endsection
