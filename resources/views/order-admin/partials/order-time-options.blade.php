@if (!$period)
<option value="" selected>Select Order Time</option>
@endif
@foreach ($allowed_times as $time)
<option value="{{ $time }}" @selected($period===$time)>{{ $time }}</option>
@endforeach
