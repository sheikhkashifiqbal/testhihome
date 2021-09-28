@extends('mail.layout')

@section('main')
<h3>Your License will expire on {{$expiry_date}}. </h3>
<p>Please login to your account in HiHome Seller Application and update your license.</p>

@endsection
