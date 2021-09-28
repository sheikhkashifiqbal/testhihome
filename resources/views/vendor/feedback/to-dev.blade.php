@component('mail::message')
# Received a feedback

Feedback ID: {{ $feedback->id }}

Feedback created at: {{ $feedback->created_at }}

Seller name: @if($feedback->user_id) {{$feedback->customer->first_name}} {{$feedback->customer->last_name}}@else {{$feedback->customer_name}} @endif


Seller email: @if($feedback->user_id){{$feedback->customer->email}}@else {{$feedback->customer_email}}@endif

@component('mail::panel')
{{ $feedback->body }}

@if($feedback->image)
<img src="{{ env('APP_URL') .'/'.$feedback->image }}">
@endif
@endcomponent

@endcomponent