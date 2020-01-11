<?php session_start() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>Empleados</title>
</head>
<body>
    <div class="container">
        <?php
        require __DIR__ . '/../comunes/auxiliar.php';
        require __DIR__ . '/auxiliar.php';

        $_csrf = (isset($_POST['_csrf'])) ? $_POST['_csrf'] : null;
        unset($_POST['_csrf']);
        barra();

        if (!isset($_COOKIE['aceptar'])) {
            alert('Este sitio usa cookies. <a href="/comunes/cookies.php">Estoy de acuerdo</a>', 'info');
        }

      
        $usuario = $_SESSION['login']; // El usuario es unique, por lo que obtendremos su id a partir de este
        $pdo = conectar();
        $id = getId($pdo,$usuario);

        if (es_POST()) {
           if (isset($_POST['pass']) && isset($_POST['pass2'])) {
               if ($_POST['pass']===$_POST['pass2']) {

                    $sent = $pdo->prepare('UPDATE usuarios
                                            SET password = :password
                                                
                                            WHERE id = :id');
                    
                    $sent->execute(['password' => password_hash($_POST['pass'], PASSWORD_DEFAULT)
                                , 'id' => $id
                    ]);
                    aviso('Fila modificada correctamente.');
                    header('Location: logout.php');
                    return;

               } else {
                   echo "Las contraseñas no coinciden";
                   aviso('Las contraseñas no coinciden', 'danger');
               }
           } 
        }
        ?>

        <form action="" method="POST">
       
        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="pass" value="" class="form-control" >
        </div>

        <div class="form-group">
            <label> Repetir Contraseña</label>
            <input type="password" name="pass2" value="" class="form-control">
        </div>
        
        <button type="submit" class="btn btn-primary">Submit</button>
        </form>

    </div>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>
