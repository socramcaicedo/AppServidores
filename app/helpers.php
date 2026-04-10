<?php

if (!function_exists('accionColor')) {
    function accionColor(string $accion): string
    {
        $accion = strtolower($accion);
        if (str_contains($accion, 'cre') || str_contains($accion, 'regist'))  return 'accion-crear';
        if (str_contains($accion, 'edit') || str_contains($accion, 'actual')) return 'accion-editar';
        if (str_contains($accion, 'elim') || str_contains($accion, 'cancel')) return 'accion-eliminar';
        if (str_contains($accion, 'login') || str_contains($accion, 'sesion')) return 'accion-login';
        return 'accion-default';
    }
}