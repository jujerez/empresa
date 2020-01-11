<?php session_start() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>Departamentos</title>
</head>
<body>
    <div class="container">
        <?php
        require __DIR__ . '/../comunes/auxiliar.php';
        require __DIR__ . '/auxiliar.php';

        if (logueoObligatorio()) {
            return;
        }

        barra();
        
        if (!isset($_COOKIE['aceptar'])) {
            alert('Este sitio usa cookies. <a href="/comunes/cookies.php">Estoy de acuerdo</a>', 'info');
        }
        
        $pag = recogerNumPag();
        $pdo = conectar();
        $orden = recogerOrden();
        $asc = recogerAsc();
        
        if (es_POST()) {
            if (isset($_POST['id'])) {
                $id = trim($_POST['id']);
                if (!departamentoVacio($pdo, $id)) {
                    alert('El departamento tiene empleados.', 'danger');
                } else {
                    if (isset($_SESSION['token'])) {
                        $token_sesion = $_SESSION['token'];
                        var_dump($token_sesion);
                        if (isset($_POST['_csrf'])) {
                            $token_form = $_POST['_csrf'];
                            var_dump($token_form);
                            unset($_POST['_csrf']);
                            if ($token_sesion !== $token_form) {
                                alert('Ha ocurrido un error interno en el servidor.', 'danger');
                            } else {
                                borrarFila($pdo, 'departamentos', $id);
                            }
                        } else {
                            alert('Ha ocurrido un error interno en el servidor.', 'danger');
                        }
                    }
                }
            }
        } elseif (hayAvisos()) {
            alert();
        }

        $errores = [];
        $args = comprobarParametros(PAR, REQ_GET, $errores);
        comprobarValoresIndex($args, $errores);
        dibujarFormularioIndex($args, PAR, $pdo, $errores);
        $sql = 'FROM v_departamentos WHERE true';
        $execute = [];
        foreach (PAR as $k => $v) {
            insertarFiltro($sql, $execute, $k, $args, PAR, $errores);    
        }
        $nfilas = contarConsulta($sql, $execute, $pdo);
        $sql .= " ORDER BY $orden " . sentido($asc);
        $sql .=  "LIMIT " . FPP;
        $sql .=   ' OFFSET ' . ($pag - 1) * FPP;
        $sent = ejecutarConsulta($sql, $execute, $pdo);
        dibujarTabla($sent, $nfilas, PAR, $orden, $asc, $errores );
        $npags = ceil($nfilas / FPP);
        ?>
        <div class="row">
            <div class="col text-center">
                <a href="/departamentos/insertar.php" class="btn btn-info" role="button">
                    Insertar
                </a>
            </div>
        </div>
        <?php paginador($pag, $npags) ?>
    </div>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>
