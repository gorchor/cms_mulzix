<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>En construcción</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            background: url('{{ asset("upload/slider_1774649154.webp") }}') no-repeat center center/cover;
            position: relative;
            color: #fff;
        }

        /* Overlay oscuro */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.65);
        }

        .container {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }

        .content {
            max-width: 600px;
        }

        h1 {
            font-size: 48px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        p {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .badge {
            display: inline-block;
            padding: 10px 20px;
            border: 1px solid #fff;
            border-radius: 50px;
            font-size: 14px;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }

        .footer {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 14px;
            opacity: 0.7;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 32px;
            }
            p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="content">
        <div class="badge">PRÓXIMAMENTE</div>
        <h1>Estamos construyendo algo increíble 🚀</h1>
        <p>
            Nuestro sitio web está en proceso de mejora.  
            Muy pronto tendrás una experiencia totalmente renovada.
        </p>
    </div>
</div>

<div class="footer">
    © {{ date('Y') }} - Todos los derechos reservados
</div>

</body>
</html>