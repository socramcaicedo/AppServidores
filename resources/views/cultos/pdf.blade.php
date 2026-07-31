<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden del Culto - {{ $culto->nombre_culto }}</title>
    <style>
        @page {
            margin: 20mm 20mm 20mm 20mm;
            size: A4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', 'Arial', serif;
            font-size: 12px;
            line-height: 1.6;
            color: #000;
            background: #fff;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding-left: 20mm;
        }

        /* Encabezado */
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #000;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 14px;
            font-weight: normal;
            color: #333;
            margin-bottom: 15px;
        }

        .info-culto {
            text-align: left;
            font-size: 12px;
            color: #000;
            margin-bottom: 10px;
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .info-culto div {
            margin-bottom: 5px;
        }

        .info-culto strong {
            font-weight: bold;
        }

        /* Sección de información */
        .section {
            margin-bottom: 20px;
        }

        /* Nombre del culto */
        .titulo-culto {
            text-align: left;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #000;
        }

        /* Descripción */
        .descripcion {
            text-align: left;
            font-size: 12px;
            color: #333;
            margin-bottom: 15px;
            font-style: italic;
        }

        /* Mensaje pastoral */
        .mensaje-box {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ccc;
        }

        .mensaje-box p {
            font-size: 11px;
            line-height: 1.6;
            color: #333;
            margin-bottom: 8px;
            font-style: italic;
        }

        .mensaje-box .autor {
            font-size: 11px;
            color: #555;
            font-style: normal;
            text-align: left;
        }

        /* Orden del servicio */
        .orden-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: left;
            text-decoration: underline;
        }

        .asignacion-item {
            margin-bottom: 10px;
            font-size: 14px;
            line-height: 1.8;
        }

        .asignacion-rol {
            font-weight: bold;
            color: #000;
        }

        .asignacion-servidor {
            color: #333;
        }

        /* Sin servidores */
        .sin-servidores {
            text-align: center;
            padding: 20px;
            font-size: 11px;
            color: #666;
            font-style: italic;
        }

        /* Separador */
        .separador {
            margin: 20px 0;
            border-top: 1px solid #ccc;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        .footer p {
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            <h1>Iglesia Pentecostal Unida de Colombia</h1>
            <div class="info-culto">
                <div>
                    <strong>Fecha:</strong>
                    {{ $culto->fecha->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
                </div>
                <div>
                    <strong>Hora:</strong>
                    {{ $culto->fecha->format('g:i A') }}
                </div>
                <div>
                    <strong>Tipo:</strong>
                    {{ $culto->caracter_nombre }}
                </div>
            </div>
        </div>

        <!-- Nombre del culto -->
        <div class="section">
            <div class="titulo-culto">
                {{ $culto->nombre_culto }}
            </div>
            @if($culto->descripcion)
            <div class="descripcion">
                {{ $culto->descripcion }}
            </div>
            @endif
        </div>

        <div class="separador"></div>

        <!-- Orden del servicio - Servidores -->
        <div class="section">
            <div class="orden-title">
                ORDEN DEL SERVICIO
            </div>

            @if($asignacionesPorRol->count() > 0)
                @foreach($asignacionesPorRol as $rol => $asignaciones)
                    @foreach($asignaciones as $asignacion)
                    <div class="asignacion-item">
                        <span class="asignacion-rol">{{ ucfirst($rol) }}:</span>
                        <span class="asignacion-servidor">{{ $asignacion->servidor->nombre_completo }}</span>
                    </div>
                    @endforeach
                @endforeach
            @else
                <div class="sin-servidores">
                    No hay servidores asignados para este culto.
                </div>
            @endif
        </div>

        <!-- Mensaje pastoral si existe -->
        @if($culto->mensaje)
        <div class="separador"></div>
        <div class="section">
            <div class="orden-title" style="text-decoration: none;">
                MENSAJE PASTORAL
            </div>
            <div class="mensaje-box">
                <p>{{ $culto->mensaje }}</p>
                <p class="autor">
                    — {{ $culto->mensajeAutor?->nombre_completo ?? 'Pastor' }}
                </p>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Generado: {{ \Carbon\Carbon::now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY, h:mm A') }}</p>
            <p>Sistema de Gestión de Servidores - IPUC</p>
        </div>
    </div>
</body>
</html>
