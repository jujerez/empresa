<?php

function comprobarParametros($par, &$errores)
{
    $res = [];
    foreach ($par as $k => $v) {
        $res[$k] = $v['def'];
    }
    if (!empty($_GET)) {
        if (empty(array_diff_key($par, $_GET)) &&
            empty(array_diff_key($_GET, $par))) {
            $res = array_map('trim', $_GET);
        } else {
            $errores[] = 'Los parámetros recibidos no son los correctos.';
        }
    }
    return $res;
}

function comprobarValores($args, &$errores)
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

function comprobarErrores($errores)
{
    if (empty($_GET) || !empty($errores)) {
        throw new Exception();
    }
}

function selected($op, $o)
{
    return $op == $o ? 'selected' : '';
}

function valido($campo, $errores)
{
    if (isset($errores[$campo])) {
        return 'is-invalid';
    } elseif (!empty($_GET)) {
        return 'is-valid';
    } else {
        return '';
    }
}

function dibujarFormulario($args, $errores)
{
    extract($args);
    ?>
    <div class="row mt-3">
        <div class="col-4 offset-4">
            <form action="" method="get">
                <div class="form-group">
                    <label for="num_dep">Número:</label>
                    <input type="text"
                           class="form-control <?= valido('num_dep', $errores) ?>"
                           id="num_dep" name="num_dep"
                           value="<?= $num_dep ?>">
                    <?= mensajeError('num_dep', $errores) ?>
                </div>
                <div class="form-group">
                    <label for="dnombre">Nombre:</label>
                    <input type="text"
                           class="form-control <?= valido('dnombre', $errores) ?>"
                           id="dnombre" name="dnombre"
                           value="<?= $dnombre ?>">
                    <?= mensajeError('dnombre', $errores) ?>
                </div>
                <div class="form-group">
                    <label for="localidad">Localidad:</label>
                    <input type="text"
                           class="form-control <?= valido('localidad', $errores) ?>"
                           id="localidad" name="localidad"
                           value="<?= $localidad ?>">
                    <?= mensajeError('localidad', $localidad) ?>
                </div>
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

function insertarFiltro(&$sql, &$execute, $campo, $args, $par, $errores)
{
    if ($args[$campo] !== '' && !isset($errores[$campo])) {
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

function dibujarTabla($sent, $count, $errores)
{ ?>
    <?php if ($count == 0): ?>
        <div class="row mt-3">
            <div class="col-8 offset-2">
                <div class="alert alert-danger" role="alert">
                    No se ha encontrado ninguna fila que coincida.
                </div>
            </div>
        </div>
    <?php elseif (isset($errores[0])): ?>
        <div class="row mt-3">
            <div class="col-8 offset-2">
                <div class="alert alert-danger" role="alert">
                    <?= $errores[0] ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row mt-4">
            <div class="col-8 offset-2">
                <table class="table">
                    <thead>
                        <th scope="col">Número</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Localidad</th>
                    </thead>
                    <tbody>
                        <?php foreach ($sent as $fila): ?>
                            <tr scope="row">
                                <td><?= $fila['num_dep'] ?></td>
                                <td><?= $fila['dnombre'] ?></td>
                                <td><?= $fila['localidad'] ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif;
}
