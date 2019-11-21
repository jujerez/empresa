<?php

function comprobarValoresLogin(&$args, $pdo, &$errores)
{
    if (!empty($errores) || empty($_POST)) {
        return;
    }

    extract($args);

    if (isset($args['login'])) {
        if ($login === '') {
            $errores['login'] = 'El nombre de usuario es obligatorio.';
        } elseif (mb_strlen($login) > 255) {
            $errores['login'] = 'El nombre de usuario no puede tener más de 255 caracteres.';
        } else {
            // Comprobar si el usuario existe
            // $sent = $pdo->prepare('SELECT COUNT(*)
            //                          FROM departamentos
            //                         WHERE num_dep = :num_dep');
            // $sent->execute(['num_dep' => $num_dep]);
            // if ($sent->fetchColumn() > 0) {
            //     $errores['num_dep'] = 'Ese número de departamento ya existe.';
            // }
        }
    }

    if (isset($args['password'])) {
        if ($dnombre === '') {
            $errores['password'] = 'La contraseña es obligatoria.';
        } else {
            // Comprobar contraseña
        }
    }
}
