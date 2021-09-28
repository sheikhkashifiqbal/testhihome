@extends('mail.layout')

@section('main')
<h3>Received a review for a Order</h3>
<p>Order Number:  {{$order->id}} </p>
<p>Seller Name:  {{$store->singleDescription->title}}</p>
<p>Customer Name:  {{$user->name}}</p>
<p>Order Review:  {{$order_review->review}}</p>

<h4>Products Rating</h4>
@foreach($product_rating as $product)
  <p>{{$product['product_name']}}:  {{$product['rating']}}</p>
@endforeach


@endsection
