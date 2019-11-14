<?php

function comprobarParametros($par, &$errores)
{
    $res = [];
    foreach ($par as $k => $v) {
        if (isset($v['def'])) {
            $res[$k] = $v['def'];
        }
    }
    $peticion = peticion();
    if ((es_GET() && !empty($peticion)) || es_POST()) {
        if ((es_GET() || es_POST() && !empty($peticion))
            && empty(array_diff_key($res, $peticion))
            && empty(array_diff_key($peticion, $res))) {
            $res = array_map('trim', $peticion);
        } else {
            $errores[] = 'Los parámetros recibidos no son los correctos.';
        }
    }
    return $res;
}

function comprobarValoresIndex($args, &$errores)
{
    if (!empty($errores)) {
        return;
    }

    extract($args);

    if (isset($args['num_dep']) && $num_dep !== '') {
        if (!ctype_digit($num_dep)) {
            $errores['num_dep'] = 'El número de departamento debe ser un número entero positivo.';
        } elseif (mb_strlen($num_dep) > 2) {
            $errores['num_dep'] = 'El número no puede tener más de dos dígitos.';
        }
    }

    if (isset($args['dnombre']) && $dnombre !== '') {
        if (mb_strlen($dnombre) > 255) {
            $errores['dnombre'] = 'El nombre del departamento no puede tener más de 255 caracteres.';
        }
    }

    if (isset($args['localidad']) && $localidad !== '') {
        if (mb_strlen($localidad) > 255) {
            $errores['localidad'] = 'La localidad no puede tener más de 255 caracteres.';
        }
    }
}

function comprobarValoresInsertar(&$args, $pdo, &$errores)
{
    if (!empty($errores) || empty($_POST)) {
        return;
    }

    extract($args);

    if (isset($args['num_dep'])) {
        if ($num_dep === '') {
            $errores['num_dep'] = 'El número de departamento es obligatorio.';
        } elseif (!ctype_digit($num_dep)) {
            $errores['num_dep'] = 'El número de departamento debe ser un número entero positivo.';
        } elseif (mb_strlen($num_dep) > 2) {
            $errores['num_dep'] = 'El número no puede tener más de dos dígitos.';
        } else {
            $sent = $pdo->prepare('SELECT COUNT(*)
                                     FROM departamentos
                                    WHERE num_dep = :num_dep');
            $sent->execute(['num_dep' => $num_dep]);
            if ($sent->fetchColumn() > 0) {
                $errores['num_dep'] = 'Ese número de departamento ya existe.';
            }
        }
    }

    if (isset($args['dnombre'])) {
        if ($dnombre === '') {
            $errores['dnombre'] = 'El nombre del departamento es obligatorio.';
        } elseif (mb_strlen($dnombre) > 255) {
            $errores['dnombre'] = 'El nombre del departamento no puede tener más de 255 caracteres.';
        }
    }

    if (isset($args['localidad'])) {
        if ($localidad !== '') {
            if (mb_strlen($localidad) > 255) {
                $errores['localidad'] = 'La localidad no puede tener más de 255 caracteres.';
            }
        } else {
            $args['localidad'] = null;
        }
    }
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

function dibujarFormularioIndex($args, $par, $errores)
{ ?>
    <div class="row mt-3">
        <div class="col-4 offset-4">
            <form action="" method="get">
                <?php dibujarElementoFormulario($args, $par, $errores) ?>
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

function dibujarFormularioInsertar($args, $par, $errores)
{ ?>
    <div class="row mt-3">
        <div class="col">
            <form action="" method="post">
                <?php dibujarElementoFormulario($args, $par, $errores) ?>
                <button type="submit" class="btn btn-primary">
                    Insertar
                </button>
            </form>
        </div>
    </div>
    <?php
}

function dibujarElementoFormulario($args, $par, $errores)
{
    foreach ($par as $k => $v): ?>
        <?php if (isset($par[$k]['def'])): ?>
            <div class="form-group">
                <label for="<?= $k ?>"><?= $par[$k]['etiqueta'] ?></label>
                <input type="text"
                       class="form-control <?= valido($k, $errores) ?>"
                       id="<?= $k ?>" name="<?= $k ?>"
                       value="<?= $args[$k] ?>">
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
                                    <td><?= $fila[$k] ?></td>
                                <?php endforeach ?>
                                <td>
                                    <form action="" method="post">
                                        <input type="hidden" name="op" value="borrar">
                                        <input type="hidden" name="id" value="<?= $fila['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Borrar</button>
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

function alert($mensaje, $tipo)
{ ?>
    <div class="row mt-3">
        <div class="col-8 offset-2">
            <div class="alert alert-<?= $tipo ?> alert-dismissible fade show" role="alert">
                <?= $mensaje ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div><?php
}

function borrarFila($pdo, $id)
{
    $sent = $pdo->prepare('DELETE
                                FROM departamentos
                            WHERE id = :id');
    $sent->execute(['id' => $id]);
    if ($sent->rowCount() === 1) {
        alert('Fila borrada con éxito.', 'success');
    } else {
        alert('Ha ocurrido un error inesperado.', 'danger');
    }
}

function conectar()
{
    return new PDO('pgsql:host=localhost;dbname=datos', 'usuario', 'usuario');
}

function es_GET()
{
    return metodo() === 'GET';
}

function es_POST()
{
    return metodo() === 'POST';
}

function metodo()
{
    return $_SERVER['REQUEST_METHOD'];
}

function peticion()
{
    return metodo() === 'GET' ? $_GET : $_POST;
}
