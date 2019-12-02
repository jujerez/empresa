<?php

const TIPO_ENTERO = 0;
const TIPO_CADENA = 1;
const TIPO_PASSWORD = 2;

const REQ_GET = 'GET';
const REQ_POST = 'POST';

function comprobarParametros($par, $req, &$errores)
{
    $res = [];
    foreach ($par as $k => $v) {
        if (isset($v['def'])) {
            $res[$k] = $v['def'];
        }
    }
    $peticion = peticion($req);
    if ((es_GET($req) && !empty($peticion)) || es_POST($req)) {
        if ((es_GET($req) || es_POST($req) && !empty($peticion))
            && empty(array_diff_key($res, $peticion))
            && empty(array_diff_key($peticion, $res))) {
            $res = array_map('trim', $peticion);
        } else {
            $errores[] = 'Los parámetros recibidos no son los correctos.';
        }
    }
    return $res;
}

function mensajeError($campo, $errores)
{
    if (isset($errores[$campo])) {
        return <<<EOT
        <div class="invalid-feedback">
            {$errores[$campo]}
        </div>
        EOT;
    } else {
        return '';
    }
}

function selected($op, $o)
{
    return $op == $o ? 'selected' : '';
}

function valido($campo, $errores)
{
    $peticion = peticion();
    if (isset($errores[$campo])) {
        return 'is-invalid';
    } elseif (!empty($peticion)) {
        return 'is-valid';
    } else {
        return '';
    }
}

function dibujarFormularioIndex($args, $par, $pdo, $errores)
{ ?>
    <div class="row mt-3">
        <div class="col-4 offset-4">
            <form action="" method="get">
                <?php dibujarElementoFormulario($args, $par, $pdo, $errores) ?>
                <button type="submit" class="btn btn-primary">
                    Buscar
                </button>
                <button type="reset" class="btn btn-secondary">
                    Limpiar
                </button>
            </form>
        </div>
    </div>
    <?php
}

function token_csrf()
{
    if (isset($_SESSION['token'])) {
        $token = $_SESSION['token'];
        return <<<EOT
            <input type="hidden" name="_csrf" value="$token">
        EOT;
    }
}

function dibujarFormulario($args, $par, $accion, $pdo, $errores)
{ ?>
    <div class="row mt-3">
        <div class="col">
            <form action="" method="post">
                <?php dibujarElementoFormulario($args, $par, $pdo, $errores) ?>
                <?= token_csrf() ?>
                <button type="submit" class="btn btn-primary">
                    <?= $accion ?>
                </button>
                <a href="index.php" class="btn btn-info" role="button">
                    Volver
                </a>

            </form>
        </div>
    </div>
    <?php
}

function dibujarElementoFormulario($args, $par, $pdo, $errores)
{
    foreach ($par as $k => $v): ?>
        <?php if (isset($par[$k]['def'])): ?>
            <div class="form-group">
                <label for="<?= $k ?>"><?= $par[$k]['etiqueta'] ?></label>
                <?php if (isset($par[$k]['relacion'])): ?>
                    <?php
                    $tabla = $par[$k]['relacion']['tabla'];
                    $visualizar = $par[$k]['relacion']['visualizar'];
                    $ajena = $par[$k]['relacion']['ajena'];
                    $sent = $pdo->query("SELECT $ajena, $visualizar
                                           FROM $tabla");
                    ?>
                    <select id="<?= $k ?>" name="<?= $k ?>" class="form-control">
                        <?php foreach ($sent as $fila): ?>
                            <option value="<?= h($fila[0]) ?>"
                                    <?= selected($fila[0], $args['departamento_id']) ?>>
                                <?= h($fila[1]) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                <?php elseif ($par[$k]['tipo'] === TIPO_PASSWORD): ?>
                    <input type="password"
                           class="form-control <?= valido($k, $errores) ?>"
                           id="<?= $k ?>" name="<?= $k ?>"
                           value="">
                <?php else: ?>
                    <input type="text"
                           class="form-control <?= valido($k, $errores) ?>"
                           id="<?= $k ?>" name="<?= $k ?>"
                           value="<?= h($args[$k]) ?>">
                <?php endif ?>
                <?= mensajeError($k, $errores) ?>
            </div>
        <?php endif ?><?php
    endforeach;
}

function insertarFiltro(&$sql, &$execute, $campo, $args, $par, $errores)
{
    if (isset($par[$campo]['def']) && $args[$campo] !== '' && !isset($errores[$campo])) {
        if ($par[$campo]['tipo'] === TIPO_ENTERO) {
            $sql .= " AND $campo = :$campo";
            $execute[$campo] = $args[$campo];
        } else {
            $sql .= " AND $campo ILIKE :$campo";
            $execute[$campo] = '%' . $args[$campo] . '%';
        }
    }
}

function ejecutarConsulta($sql, $execute, $pdo)
{
    $sent = $pdo->prepare("SELECT COUNT(*) $sql");
    $sent->execute($execute);
    $count = $sent->fetchColumn();
    $sent = $pdo->prepare("SELECT * $sql");
    $sent->execute($execute);
    return [$sent, $count];
}

function dibujarTabla($sent, $count, $par, $errores)
{ ?>
    <?php if ($count == 0): ?>
        <?php alert('No se ha encontrado ninguna fila que coincida.', 'danger') ?>        <div class="row mt-3">
    <?php elseif (isset($errores[0])): ?>
        <?php alert($errores[0], 'danger') ?>
    <?php else: ?>
        <div class="row mt-4">
            <div class="col-8 offset-2">
                <table class="table">
                    <thead>
                        <?php foreach ($par as $k => $v): ?>
                            <th scope="col"><?= $par[$k]['etiqueta'] ?></th>    
                        <?php endforeach ?>
                        <th scope="col">Acciones</th>
                    </thead>
                    <tbody>
                        <?php foreach ($sent as $fila): ?>
                            <tr scope="row">
                                <?php foreach ($par as $k => $v): ?>
                                    <?php if (isset($par[$k]['relacion'])): ?>
                                        <?php $visualizar = $par[$k]['relacion']['visualizar'] ?>
                                        <td><?= $fila[$visualizar] ?></td>
                                    <?php else: ?>
                                        <td><?= h($fila[$k]) ?></td>
                                    <?php endif ?>
                                <?php endforeach ?>
                                <td>
                                    <form action="" method="post">
                                        <input type="hidden" name="id" value="<?= $fila['id'] ?>">
                                        <?= token_csrf() ?>
                                        <button type="submit" class="btn btn-sm btn-danger">Borrar</button>
                                        <a href="modificar.php?id=<?= $fila['id'] ?>" class="btn btn-sm btn-info" role="button">
                                            Modificar
                                        </a>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif;
}

function alert($mensaje = null, $severidad = null)
{
    if ($mensaje === null) {
        if (hayAvisos()) {
            $aviso = getAviso();
            $mensaje = $aviso['mensaje'];
            $severidad = $aviso['severidad'];
            quitarAvisos();
        } else {
            return;
        }
    }
    
    ?>
    <div class="row mt-3">
        <div class="col-8 offset-2">
            <div class="alert alert-<?= $severidad ?> alert-dismissible fade show" role="alert">
                <?= $mensaje ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div><?php
}

function borrarFila($pdo, $tabla, $id)
{
    $sent = $pdo->prepare("DELETE
                             FROM $tabla
                            WHERE id = :id");
    $sent->execute(['id' => $id]);
    if ($sent->rowCount() === 1) {
        aviso('Fila borrada correctamente.');
        header('Location: index.php');
    } else {
        alert('Ha ocurrido un error inesperado.', 'danger');
    }
}

function conectar()
{
    return new PDO('pgsql:host=localhost;dbname=datos', 'usuario', 'usuario');
}

function es_GET($req = null)
{
    return ($req === null) ? metodo() === 'GET' : $req === REQ_GET;
}

function es_POST($req = null)
{
    return ($req === null) ? metodo() === 'POST' : $req === REQ_POST;
}

function metodo()
{
    return $_SERVER['REQUEST_METHOD'];
}

function peticion($req = null)
{
    return es_GET($req) ? $_GET : $_POST;
}

function barra()
{ ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ml-auto justify-content-end">
                <li class="nav-item active">
                    <a class="nav-link" href="/index.php">Inicio <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="/empleados/">Empleados <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="/departamentos/">Departamentos <span class="sr-only">(current)</span></a>
                </li>
                <?php if (logueado()): ?>
                    <span class="navbar-text mr-2">
                        <?= logueado() ?>
                    </span>
                    <form class="form-inline my-2 my-lg-0" action="/usuarios/logout.php" method="post">
                        <button class="btn btn-success my-2 my-sm-0" type="submit">Logout</button>
                    </form>
                <?php else: ?>
                    <a class="nav-link" href="/usuarios/login.php">Login</a>
                <?php endif ?>
            </ul>
        </div>
    </nav>
    <?php
}

function logueado()
{
    return isset($_SESSION['login']) ? $_SESSION['login'] : false;
}

function aviso($mensaje, $severidad = 'success')
{
    $_SESSION['aviso'] = [
        'mensaje' => $mensaje,
        'severidad' => $severidad,
    ];
}

function hayAvisos()
{
    return isset($_SESSION['aviso']);
}

function getAviso()
{
    return hayAvisos() ? $_SESSION['aviso'] : [];
}

function quitarAvisos()
{
    unset($_SESSION['aviso']);
}

function h($cadena)
{
    return htmlspecialchars($cadena, ENT_QUOTES | ENT_SUBSTITUTE);
}

function logueoObligatorio()
{
    if (!logueado()) {
        aviso('Tiene que estar logueado para entrar en esa parte del programa.', 'danger');
        $_SESSION['retorno'] = $_SERVER['REQUEST_URI'];
        $_SESSION['pepe'] = 'pepe';
        header('Location: /usuarios/login.php');
        return true;
    }
    return false;
}
