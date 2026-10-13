<?php
/**
 * Aventaris — procesador del formulario de cotización
 *
 * Recibe el POST del formulario de index.html, valida los datos y los
 * envía por correo. Responde en JSON si la petición viene por fetch (JS)
 * o redirige de vuelta a index.html si el navegador no tiene JS.
 */

declare(strict_types=1);

// ===================== CONFIGURACIÓN =====================
const DESTINATARIO = 'contacto@aventaris.cl';       // casilla que recibe las cotizaciones
const REMITENTE    = 'no-reply@aventaris.cl';       // debe existir en el mismo dominio del hosting
const ASUNTO       = 'Nueva solicitud de cotización - Sitio web Aventaris';
const ESPERA_SEG   = 30;                      // tiempo mínimo entre envíos por visitante
// =========================================================

date_default_timezone_set('America/Santiago');
session_start();

function es_ajax(): bool
{
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
}

function responder(bool $ok, string $mensaje, int $codigo = 200): void
{
    if (es_ajax()) {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'mensaje' => $mensaje], JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: index.html?estado=' . ($ok ? 'ok' : 'error') . '#cotizar', true, 303);
    }
    exit;
}

/** Limpia un campo de una línea (sin saltos para evitar inyección de cabeceras). */
function campo(string $clave, int $max): string
{
    $valor = trim((string) ($_POST[$clave] ?? ''));
    $valor = preg_replace('/[\r\n\t]+/', ' ', $valor) ?? '';
    return mb_substr($valor, 0, $max);
}

/** Limpia un campo de texto largo (permite saltos de línea). */
function campo_largo(string $clave, int $max): string
{
    $valor = trim((string) ($_POST[$clave] ?? ''));
    $valor = str_replace("\r\n", "\n", $valor);
    return mb_substr($valor, 0, $max);
}

// --- Solo se aceptan envíos POST; si alguien abre index.php, vuelve a la landing ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html', true, 302);
    exit;
}

// --- Anti-spam 1: campo trampa. Si viene lleno, es un bot: fingimos éxito y no enviamos ---
if (!empty($_POST['sitio_web'])) {
    responder(true, '¡Solicitud enviada con éxito! Te contactaremos en breve.');
}

// --- Anti-spam 2: límite de frecuencia por sesión ---
$ultimo = $_SESSION['ultimo_envio'] ?? 0;
if (time() - $ultimo < ESPERA_SEG) {
    responder(false, 'Ya recibimos una solicitud hace un momento. Espera unos segundos antes de enviar otra.', 429);
}

// --- Datos del formulario ---
$nombre      = campo('nombre', 100);
$telefono    = campo('telefono', 20);
$origen      = campo('origen', 200);
$destino     = campo('destino', 200);
$descripcion = campo_largo('descripcion', 2000);
$servicio    = campo('servicio', 60);

const SERVICIOS = [
    'Transporte de pasajeros',
    'Servicio de turismo',
    'Transporte de carga',
    'Asesoría en licitaciones',
];

// --- Validación ---
$errores = [];
if (mb_strlen($nombre) < 2)                              $errores[] = 'nombre';
if (!preg_match('/^[0-9+\s().-]{8,20}$/', $telefono))    $errores[] = 'teléfono';
if (mb_strlen($origen) < 3)                              $errores[] = 'origen';
if (!in_array($servicio, SERVICIOS, true))               $errores[] = 'tipo de servicio';
if (mb_strlen($descripcion) < 3)                         $errores[] = 'detalle de la solicitud';

if ($errores) {
    responder(false, 'Revisa estos campos: ' . implode(', ', $errores) . '.', 422);
}

// --- Armado del correo ---
$cuerpo = "Nueva solicitud de cotización desde el sitio web\n"
        . "==================================================\n\n"
        . "Nombre:     {$nombre}\n"
        . "Teléfono:   {$telefono}\n"
        . "Servicio:   {$servicio}\n"
        . "Origen:     {$origen}\n"
        . 'Destino:    ' . ($destino !== '' ? $destino : '(no indicado)') . "\n\n"
        . "Detalle:\n{$descripcion}\n\n"
        . "--------------------------------------------------\n"
        . 'Fecha: ' . date('d-m-Y H:i') . "\n"
        . 'IP:    ' . ($_SERVER['REMOTE_ADDR'] ?? 'desconocida') . "\n";

$cabeceras = implode("\r\n", [
    'From: Aventaris Web <' . REMITENTE . '>',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'X-Mailer: PHP/' . phpversion(),
]);

$asunto = '=?UTF-8?B?' . base64_encode(ASUNTO) . '?=';

$enviado = mail(DESTINATARIO, $asunto, $cuerpo, $cabeceras, '-f' . REMITENTE);

if ($enviado) {
    $_SESSION['ultimo_envio'] = time();
    responder(true, '¡Solicitud enviada con éxito! El equipo de Aventaris se comunicará contigo en breve.');
}

responder(false, 'No pudimos enviar tu solicitud. Escríbenos por WhatsApp o llámanos al +56 9 7900 8964.', 500);