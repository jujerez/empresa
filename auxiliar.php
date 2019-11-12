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
    extract($args);

    if ($num_dep !== '') {
        if (!ctype_digit($num_dep)) {
            $errores['num_dep'] = 'El número de departamento debe ser un número entero positivo.';
        } elseif (mb_strlen($num_dep) > 2) {
            $errores['num_dep'] = 'El número no puede tener más de dos dígitos.';
        }
    }

    if ($dnombre !== '') {
        if (mb_strlen($dnombre) > 255) {
            $errores['dnombre'] = 'El nombre del departamento no puede tener más de 255 caracteres.';
        }
    }

    if ($localidad !== '') {
        if (mb_strlen($localidad) > 255) {
            $errores['localidad'] = 'La localidad no puede tener más de 255 caracteres.';
        }
    }

    comprobarErrores($errores);
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
