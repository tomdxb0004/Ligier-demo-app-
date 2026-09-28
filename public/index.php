<?php
/**
 * Dealer Portal (demo) — a neutral B2B dealer-portal mock that shows how the
 * SimplyBoost agent is embedded ONLY for logged-in users (the equivalent of
 * Laravel's Blade `@auth` around the embed snippet), and how the chat is reset
 * on logout so a shared workshop computer never shows one user's chat to the next.
 *
 * All data is fictional. Configuration comes from environment variables; the app
 * fails closed (503) when a required variable is missing.
 */

declare(strict_types=1);

const REQUIRED_ENV = ['DEMO_PASSWORD', 'BASIC_AUTH_USER', 'BASIC_AUTH_PASSWORD', 'SIMPLYBOOST_BOT_ID'];
const DEFAULT_WIDGET_URL = 'https://get.simplyboost.io/widget.js';

function env_value(string $name): string
{
    $value = getenv($name);
    return $value === false ? '' : trim($value);
}

function send_security_headers(): void
{
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('X-Robots-Tag: noindex, nofollow');
}

function is_https(): bool
{
    return ($_SERVER['HTTPS'] ?? '') === 'on'
        || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

function fail(int $status, string $message): void
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $message;
}

function redirect(string $location): void
{
    header('Location: ' . $location, true, 303);
}

send_security_headers();

$missing = array_values(array_filter(REQUIRED_ENV, fn($name) => env_value($name) === ''));
if ($missing) {
    error_log('[dealer-portal-demo] missing required env vars: ' . implode(', ', $missing));
    fail(503, 'Demo is not configured.');
    return;
}

if ((parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/') === '/healthz') {
    fail(200, 'ok');
    return;
}

// Gate the whole demo behind HTTP basic auth so it is never publicly browsable.
$basicUser = $_SERVER['PHP_AUTH_USER'] ?? '';
$basicPass = $_SERVER['PHP_AUTH_PW'] ?? '';
if (!hash_equals(env_value('BASIC_AUTH_USER'), $basicUser) || !hash_equals(env_value('BASIC_AUTH_PASSWORD'), $basicPass)) {
    header('WWW-Authenticate: Basic realm="Dealer Portal demo", charset="UTF-8"');
    fail(401, 'Authentication required.');
    return;
}

// Built-in server router: let it serve real files under public/ (CSS) itself.
$requestedFile = realpath(__DIR__ . (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
if (PHP_SAPI === 'cli-server' && $requestedFile !== false && is_file($requestedFile)
    && str_starts_with($requestedFile, __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR)) {
    return false;
}

session_name('dealer_portal_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => is_https(),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

$demoPassword = env_value('DEMO_PASSWORD');
$users = [
    'admin@demo.test'  => ['name' => 'Eva', 'role' => 'admin'],
    'dealer@demo.test' => ['name' => 'Sanne', 'role' => 'dealer'],
];

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrfValid = fn(): bool => hash_equals($_SESSION['csrf'], (string) ($_POST['_token'] ?? ''));

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($path === '/login' && $method === 'POST') {
    if (!$csrfValid()) {
        fail(419, 'Page expired. Go back and try again.');
        return;
    }
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    if (isset($users[$email]) && hash_equals($demoPassword, $password)) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['email' => $email] + $users[$email];
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        redirect('/admin');
        return;
    }
    redirect('/login?failed=1');
    return;
}

if ($path === '/logout' && $method === 'POST') {
    if (!$csrfValid()) {
        fail(419, 'Page expired. Go back and try again.');
        return;
    }
    $_SESSION = [];
    session_destroy();
    redirect('/login');
    return;
}

$user = $_SESSION['user'] ?? null;
if (!$user && $path !== '/login') {
    redirect('/login');
    return;
}
if ($user && $path !== '/admin') {
    redirect('/admin');
    return;
}

$widgetUrl = env_value('SIMPLYBOOST_WIDGET_URL') ?: DEFAULT_WIDGET_URL;
$botId = env_value('SIMPLYBOOST_BOT_ID');
$csrf = $_SESSION['csrf'];

$e = fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
// Same escaping as Blade's @json: safe to print inside a <script> block.
$json = fn($value): string => json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

$menu = ['Support', 'Formulieren', 'Garanties', 'Onderdeel bestellingen', 'Voertuig bestellingen', 'Aanbod',
         'Gebruikers', 'Logs', 'Mededelingen', 'Financiering', 'Intranet'];
$warrantyRequests = [
    ['id' => 10, 'status' => 'Nieuw', 'user' => 'Sanne', 'dealer' => 'Autohuis Voorbeeld', 'vin' => 'DEMOVIN0000000001', 'date' => '25/09/2026 - 14:45'],
];

require __DIR__ . '/../views/layout.php';
