<?php
declare(strict_types=1);

session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'cookie_samesite' => 'Lax',
]);

require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Auth.php';

$db = Database::get();
Auth::boot($db);

$pdo = $db;
if (!isset($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
}

$route = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/', '/');
$method = $_SERVER['REQUEST_METHOD'];

function redirect_to(string $path): never {
    header('Location: /' . ltrim($path, '/'));
    exit;
}

function require_login(): void {
    if (!Auth::check()) redirect_to('connexion');
}

function require_role(array $roles): void {
    require_login();
    if (!in_array(Auth::user()['role'], $roles, true)) {
        http_response_code(403);
        render('403', ['title' => 'Accès refusé']);
        exit;
    }
}

function verify_csrf(): void {
    $token = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['_csrf'] ?? '', (string)$token)) {
        http_response_code(419);
        exit('Session expirée. Recharge la page et réessaie.');
    }
}

function render(string $view, array $data = []): void {
    global $pdo;
    extract($data);
    $viewFile = __DIR__ . '/../app/views/' . $view . '.php';
    if (!is_file($viewFile)) {
        http_response_code(500);
        echo 'Vue introuvable';
        exit;
    }
    $title = $title ?? 'CertiHub';
    include __DIR__ . '/../app/views/layout.php';
}

// Handle POST actions
if ($method === 'POST') {
    verify_csrf();
    switch ($route) {
        case 'connexion':
            $email = strtolower(trim((string)($_POST['email'] ?? '')));
            $password = (string)($_POST['password'] ?? '');
            if (Auth::login($email, $password)) {
                if (isset($_SESSION['pending_2fa'])) redirect_to('verification-2fa');
                redirect_to('tableau-de-bord');
            }
            render('login', ['title' => 'Connexion', 'error' => 'Identifiants invalides ou compte désactivé.']);
            exit;

        case 'inscription':
            $name = trim((string)($_POST['name'] ?? ''));
            $email = strtolower(trim((string)($_POST['email'] ?? '')));
            $password = (string)($_POST['password'] ?? '');
            if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10) {
                render('register', ['title' => 'Créer un compte', 'error' => 'Nom, email valide et mot de passe de 10 caractères minimum requis.']);
                exit;
            }
            if (Auth::register($name, $email, $password)) {
                Auth::login($email, $password);
                redirect_to('tableau-de-bord');
            }
            render('register', ['title' => 'Créer un compte', 'error' => 'Cet email est déjà utilisé.']);
            exit;

        case 'deconnexion':
            Auth::logout();
            redirect_to('');

        case 'verification-2fa':
            $pending=(int)($_SESSION['pending_2fa'] ?? 0);
            if (!$pending || time()-(int)($_SESSION['pending_2fa_ts']??0)>300) { unset($_SESSION['pending_2fa'],$_SESSION['pending_2fa_ts']); redirect_to('connexion'); }
            $code=preg_replace('/\D+/','',(string)($_POST['code']??''));
            $stmt=$pdo->prepare('SELECT * FROM users WHERE id=? AND active=1'); $stmt->execute([$pending]); $row=$stmt->fetch();
            if($row && Auth::verifyTotp((string)$row['two_factor_secret'],$code)){ session_regenerate_id(true); $_SESSION['user_id']=$row['id']; unset($_SESSION['pending_2fa'],$_SESSION['pending_2fa_ts']); redirect_to('tableau-de-bord'); }
            render('2fa', ['title'=>'Vérification 2FA','error'=>'Code incorrect ou expiré.']); exit;

        case 'profil/2fa':
            require_login();
            $user = Auth::user();
            $enabled = !empty($_POST['enabled']);
            $secret = strtoupper(trim((string)($_POST['secret'] ?? '')));
            if ($enabled && $secret === '') { $alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $secret=''; for($i=0;$i<24;$i++) $secret.=$alphabet[random_int(0,31)]; }
            if ($enabled && !preg_match('/^[A-Z2-7]{16,64}$/', $secret)) {
                redirect_to('profil?error=secret');
            }
            $stmt = $pdo->prepare('UPDATE users SET two_factor_enabled = ?, two_factor_secret = ? WHERE id = ?');
            $stmt->execute([$enabled ? 1 : 0, $enabled ? $secret : null, $user['id']]);
            redirect_to('profil?saved=1');

        case 'tickets/creer':
            require_login();
            $subject = trim((string)($_POST['subject'] ?? ''));
            $message = trim((string)($_POST['message'] ?? ''));
            if ($subject !== '' && $message !== '') {
                $stmt = $pdo->prepare('INSERT INTO tickets(user_id, subject, message, status, created_at) VALUES(?,?,?,?,datetime("now"))');
                $stmt->execute([Auth::user()['id'], $subject, $message, 'ouvert']);
            }
            redirect_to('support');

        case 'chat/poster':
            require_login();
            $certId = (int)($_POST['certification_id'] ?? 0);
            $body = trim((string)($_POST['body'] ?? ''));
            if ($certId > 0 && $body !== '' && (function_exists('mb_strlen') ? mb_strlen($body) : strlen($body)) <= 800) {
                $stmt = $pdo->prepare('INSERT INTO messages(certification_id,user_id,body,created_at) VALUES(?,?,?,datetime("now"))');
                $stmt->execute([$certId, Auth::user()['id'], $body]);
            }
            redirect_to('certification?id=' . $certId . '#discussion');

        case 'admin/ticket':
            require_role(['admin','support']);
            $ticketId = (int)($_POST['ticket_id'] ?? 0);
            $status = $_POST['status'] ?? 'ouvert';
            if (in_array($status, ['ouvert','en_cours','resolu'], true)) {
                $stmt = $pdo->prepare('UPDATE tickets SET status = ?, updated_at=datetime("now") WHERE id = ?');
                $stmt->execute([$status, $ticketId]);
            }
            redirect_to('admin');
    }
}

switch ($route) {
    case '':
    case 'accueil':
        $stats = [
            'certs' => (int)$pdo->query('SELECT COUNT(*) FROM certifications')->fetchColumn(),
            'domains' => (int)$pdo->query('SELECT COUNT(DISTINCT domain) FROM certifications')->fetchColumn(),
            'students' => (int)$pdo->query('SELECT COUNT(*) FROM users WHERE role = "eleve"')->fetchColumn(),
            'certified' => (int)$pdo->query('SELECT COUNT(*) FROM user_certifications WHERE status = "obtenue"')->fetchColumn(),
        ];
        $featured = $pdo->query('SELECT * FROM certifications ORDER BY featured DESC, id LIMIT 6')->fetchAll();
        render('home', compact('stats','featured'));
        break;

    case 'certifications':
        $domain = trim((string)($_GET['domaine'] ?? ''));
        $q = trim((string)($_GET['q'] ?? ''));
        $sql = 'SELECT * FROM certifications WHERE 1=1'; $params = [];
        if ($domain !== '') { $sql .= ' AND domain = ?'; $params[] = $domain; }
        if ($q !== '') { $sql .= ' AND (name LIKE ? OR vendor LIKE ? OR summary LIKE ?)'; $like = '%' . $q . '%'; array_push($params,$like,$like,$like); }
        $sql .= ' ORDER BY featured DESC, name';
        $stmt = $pdo->prepare($sql); $stmt->execute($params); $certifications = $stmt->fetchAll();
        $domains = $pdo->query('SELECT DISTINCT domain FROM certifications ORDER BY domain')->fetchAll(PDO::FETCH_COLUMN);
        render('certifications', compact('certifications','domains','domain','q'));
        break;

    case 'certification':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM certifications WHERE id = ?'); $stmt->execute([$id]); $cert = $stmt->fetch();
        if (!$cert) { http_response_code(404); render('404', ['title'=>'Certification introuvable']); exit; }
        $stmt = $pdo->prepare('SELECT m.*, u.name AS user_name, u.role FROM messages m JOIN users u ON u.id=m.user_id WHERE m.certification_id=? ORDER BY m.created_at DESC'); $stmt->execute([$id]); $messages = $stmt->fetchAll();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM user_certifications WHERE certification_id=? AND status="obtenue"'); $stmt->execute([$id]); $certifiedCount=(int)$stmt->fetchColumn();
        $labels = []; $trend = [];
        for ($i=5;$i>=0;$i--) { $labels[] = date('M', strtotime("-$i months")); $trend[] = max(8, $certifiedCount - $i*4); }
        $resources = $pdo->prepare('SELECT * FROM trainings WHERE certification_id=? ORDER BY level, title'); $resources->execute([$id]); $resources=$resources->fetchAll();
        render('certification', compact('cert','messages','certifiedCount','labels','trend','resources'));
        break;

    case 'parcours':
        $tracks = $pdo->query('SELECT * FROM tracks ORDER BY sort_order')->fetchAll();
        foreach ($tracks as &$track) {
            $stmt=$pdo->prepare('SELECT c.* FROM certifications c JOIN track_certifications tc ON tc.certification_id=c.id WHERE tc.track_id=? ORDER BY tc.position');
            $stmt->execute([$track['id']]); $track['certs']=$stmt->fetchAll();
        }
        render('tracks', compact('tracks'));
        break;

    case 'formations':
        $trainings = $pdo->query('SELECT t.*, c.name as certification_name FROM trainings t LEFT JOIN certifications c ON c.id=t.certification_id ORDER BY t.level, t.title')->fetchAll();
        render('trainings', compact('trainings'));
        break;

    case 'actualites':
        $news = $pdo->query('SELECT * FROM news ORDER BY published_at DESC')->fetchAll();
        render('news', compact('news'));
        break;

    case 'tableau-de-bord':
        require_login();
        $user = Auth::user();
        $stmt=$pdo->prepare('SELECT uc.*, c.name, c.vendor, c.domain FROM user_certifications uc JOIN certifications c ON c.id=uc.certification_id WHERE uc.user_id=? ORDER BY uc.updated_at DESC'); $stmt->execute([$user['id']]); $userCerts=$stmt->fetchAll();
        $totalCerts=count($userCerts); $obtained=count(array_filter($userCerts,fn($x)=>$x['status']==='obtenue'));
        render('dashboard', compact('user','userCerts','totalCerts','obtained'));
        break;

    case 'verification-2fa':
        if (!isset($_SESSION['pending_2fa'])) redirect_to('connexion');
        render('2fa', ['title'=>'Vérification 2FA']); break;

    case 'profil':
        require_login();
        $user=Auth::user(); render('profile', compact('user'));
        break;

    case 'support':
        require_login(); $user=Auth::user();
        $stmt=$pdo->prepare('SELECT * FROM tickets WHERE user_id=? ORDER BY created_at DESC'); $stmt->execute([$user['id']]); $tickets=$stmt->fetchAll();
        render('support', compact('tickets'));
        break;

    case 'connexion':
        render('login', ['title'=>'Connexion']); break;
    case 'inscription':
        render('register', ['title'=>'Créer un compte']); break;

    case 'admin':
        require_role(['admin','support']);
        $kpis = [
            'users'=>(int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'open_tickets'=>(int)$pdo->query('SELECT COUNT(*) FROM tickets WHERE status != "resolu"')->fetchColumn(),
            'messages'=>(int)$pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn(),
            'certifications'=>(int)$pdo->query('SELECT COUNT(*) FROM certifications')->fetchColumn(),
        ];
        $tickets=$pdo->query('SELECT t.*, u.name AS user_name, u.email FROM tickets t JOIN users u ON u.id=t.user_id ORDER BY t.created_at DESC LIMIT 12')->fetchAll();
        render('admin', compact('kpis','tickets'));
        break;

    case 'api/stats':
        header('Content-Type: application/json; charset=utf-8');
        $rows=$pdo->query('SELECT domain, COUNT(*) count FROM certifications GROUP BY domain ORDER BY count DESC')->fetchAll();
        echo json_encode($rows, JSON_UNESCAPED_UNICODE); break;

    default:
        http_response_code(404); render('404', ['title'=>'Page introuvable']);
}
