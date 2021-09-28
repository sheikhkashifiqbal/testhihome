@extends('mail.layout')

@section('main')
<h3>Customer has rated the order</h3>
<p>Order Number:  {{$order->id}} </p>
<p>Customer Name:  {{$user->name}}</p>
<p>Order Review:  {{$order_review->review}}</p>

<h4>Products Rating</h4>
@foreach($product_rating as $product)
  <p>{{$product['product_name']}}:  {{$product['rating']}}</p>
@endforeach


@endsection
