<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $contract->title }}</title>
<style>
@page{margin:34px 38px}body{font-family:DejaVu Sans,sans-serif;color:#202936;font-size:11px;line-height:1.45}
.header{border-bottom:2px solid #f58a32;padding-bottom:14px;margin-bottom:18px}.brand{font-size:22px;font-weight:bold;color:#f58a32}
h1{margin:8px 0 3px;font-size:20px}.muted{color:#697586}.content{min-height:360px}.box{border:1px solid #d9dee6;border-radius:8px;padding:18px}
.signature{margin-top:24px;border:1px solid #ccd3dd;border-radius:8px;padding:16px}.seal{display:inline-block;padding:5px 10px;border-radius:999px;background:#e8f7ed;color:#247146;font-weight:bold}
table{width:100%;border-collapse:collapse;margin-top:10px}td{width:50%;vertical-align:top;padding:5px 8px 5px 0}.label{font-size:9px;color:#758093;text-transform:uppercase}.value{font-weight:bold;word-break:break-all}
.signature-image{width:220px;max-height:110px;object-fit:contain}.camera-image{width:160px;max-height:120px;object-fit:cover;border-radius:6px}
.hash{margin-top:14px;padding:10px;background:#f4f6f8;border-radius:6px;font-size:9px;word-break:break-all}.footer{margin-top:24px;font-size:9px;color:#7b8494;text-align:center}
</style>
</head>
<body>
<div class="header"><div class="brand">PETPAY</div><h1>{{ $contract->title }}</h1><div class="muted">UUID: {{ $contract->uuid }} · Versión {{ $contract->version }}</div></div>
<div class="content"><div class="box">
@if ($contract->content_html){!! $contract->content_html !!}@else
<p>El presente documento formaliza la aceptación del contrato <strong>{{ $contract->title }}</strong> por parte del representante registrado.</p>
<p>La firma queda vinculada al UUID, versión, fecha, dirección IP y huella criptográfica de esta constancia.</p>
@if ($contract->notes)<p><strong>Notas:</strong> {{ $contract->notes }}</p>@endif
@endif
</div></div>
<div class="signature"><span class="seal">DOCUMENTO FIRMADO</span>
<table>
<tr><td><div class="label">Firmante</div><div class="value">{{ $contract->representative_name }}</div></td><td><div class="label">Cargo</div><div class="value">{{ $contract->representative_position ?: 'No indicado' }}</div></td></tr>
<tr><td><div class="label">Método</div><div class="value">{{ ucfirst(str_replace('_', ' ', $contract->signature_method)) }}</div></td><td><div class="label">Fecha</div><div class="value">{{ $contract->signed_at?->format('d/m/Y H:i:s') }}</div></td></tr>
<tr><td><div class="label">IP</div><div class="value">{{ $contract->signed_ip }}</div></td><td><div class="label">RFC certificado</div><div class="value">{{ $contract->certificate_rfc ?: 'No aplica' }}</div></td></tr>
@if ($contract->certificate_serial)<tr><td colspan="2"><div class="label">Serie del certificado</div><div class="value">{{ $contract->certificate_serial }}</div></td></tr>@endif
</table>
@if ($signatureImage)<div style="margin-top:14px"><div class="label">Firma</div><img src="{{ $signatureImage }}" class="signature-image"></div>@endif
@if ($cameraEvidence)<div style="margin-top:14px"><div class="label">Evidencia de cámara</div><img src="{{ $cameraEvidence }}" class="camera-image"></div>@endif
<div class="hash"><strong>Huella SHA-256:</strong><br>{{ $contract->content_hash }}</div>
</div>
<div class="footer">La contraseña y la llave privada de e.firma no se almacenan.</div>
</body>
</html>
