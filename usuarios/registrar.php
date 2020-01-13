<?php session_start() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>Registrar</title>
</head>
<body>
    <div class="container">
        <?php
        require __DIR__ . '/../comunes/auxiliar.php';
        require __DIR__ . '/auxiliar.php';
        
        const PAR = [
            'login' => [
                'def' => '',
                'tipo' => TIPO_CADENA,
                'etiqueta' => 'Usuario',
            ],
            'password' => [
                'def' => '',
                'tipo' => TIPO_PASSWORD,
                'etiqueta' => 'Contraseña',
            ],
            'password_confirm' => [
                'def' => '',
                'tipo' => TIPO_PASSWORD,
                'etiqueta' => 'Confirmar contraseña',
            ],
            'email' => [
                'def' => '',
                'tipo' => TIPO_CADENA,
                'etiqueta' => 'Dirección de e-mail',
            ],

            'rol' => [
                'def' => '',
                'tipo' => TIPO_CADENA,
                'etiqueta' => 'Rol de Usuario',
            ]
        ];

        if (!esAdmin()) {

            if (noLogueoObligatorio() ) {
                
                return;
            }
        }


        barra();

        if (!isset($_COOKIE['aceptar'])) {
            alert('Este sitio usa cookies. <a href="/comunes/cookies.php">Estoy de acuerdo</a>', 'info');
        }

        if (hayAvisos()) {
            alert();
        }

        $errores = [];
        

        $args = comprobarParametros(PAR, REQ_POST, $errores);
        $pdo = conectar();
        comprobarValoresRegistrar($args, $pdo, $errores);

        var_dump($errores);

        if (esAdmin()) {

            if (es_POST()) {
                 $sent = $pdo->prepare('INSERT INTO usuarios (login, password, email, rol)
                                       VALUES (:login, :password, :email, :rol)');
                if (!$sent->execute([
                    'login' => $args['login'],
                    'password' => password_hash($args['password'], PASSWORD_DEFAULT),
                    'email' => $args['email'] ?: null,
                    'rol'   => $args['rol']
                ])) {
                    aviso('problemom.', 'danger');
                } elseif ($sent->rowCount() !== 1) {
                    aviso('Ha ocurrido algún problema.', 'danger');
                }
                header('Location: /index.php');
                return;
            }
        } 

        if (es_POST()) {
            $sent = $pdo->prepare('INSERT INTO usuarios (login, password, email, rol)
                                  VALUES (:login, :password, :email, :rol)');
           if (!$sent->execute([
               'login' => $args['login'],
               'password' => password_hash($args['password'], PASSWORD_DEFAULT),
               'email' => $args['email'] ?: null,
               'rol'   => 'vacio',
           ])) {
               aviso('problemita.', 'danger');
           } elseif ($sent->rowCount() !== 1) {
               aviso('Ha ocurrido algún problema.', 'danger');
           }
           header('Location: /index.php');
           return;
       } 

        dibujarFormulario($args, PAR, 'Registrar', $pdo, $errores);
        ?>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>
