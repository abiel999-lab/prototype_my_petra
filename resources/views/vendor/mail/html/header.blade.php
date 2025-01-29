@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://login.petra.ac.id/images/logo-ukp.png" class="logo" alt="Petra Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
