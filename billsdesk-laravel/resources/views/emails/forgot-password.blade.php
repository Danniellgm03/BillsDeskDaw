@component('mail::message')
# Restablecimiento de contraseña

Has solicitado restablecer tu contraseña.

<table align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<a href="{{ $url }}"
style="background-color: #3370ff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;"
target="_blank">
Restablecer contraseña
</a>
</td>
</tr>
</table>
<br>

Si no realizaste esta solicitud, puedes ignorar este mensaje.

Gracias,
{{ config('app.name') }}
@endcomponent
