@props(['url'])
{{-- AISC Madrid header for every HTML email. PNG logo: Gmail and Outlook do not render SVG. --}}
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('images/logos/aisc-logo-color.png') }}" class="logo" alt="AISC Madrid" width="75" height="75">
<br>
<span class="header-name">AISC Madrid</span>
</a>
</td>
</tr>
