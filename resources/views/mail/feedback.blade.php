@extends('mail.layout')

@section('main')
<h3>Received a feedback </h3>
<p>Feedback ID: {{$id}} </p>
<p>Name: {{$customer_name}} </p>
<p>Email: {{$customer_email}} </p>
<p>Phone: {{$customer_phone}} </p>
<p>Feedback created at: {{$created_at}} </p>
@endsection
