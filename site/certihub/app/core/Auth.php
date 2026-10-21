<?php
declare(strict_types=1);
class Auth {
    private static PDO $db;
    public static function boot(PDO $db): void { self::$db=$db; }
    public static function check(): bool { return isset($_SESSION['user_id']); }
    public static function user(): ?array {
        if (!self::check()) return null;
        static $user=null;
        if ($user) return $user;
        $stmt=self::$db->prepare('SELECT id,name,email,role,two_factor_enabled,two_factor_secret FROM users WHERE id=? AND active=1');$stmt->execute([$_SESSION['user_id']]);
        $user=$stmt->fetch() ?: null;
        return $user;
    }
    public static function login(string $email,string $password): bool {
        $stmt=self::$db->prepare('SELECT * FROM users WHERE email=? AND active=1');$stmt->execute([$email]);$row=$stmt->fetch();
        if (!$row || !password_verify($password,$row['password_hash'])) return false;
        if (!empty($row['two_factor_enabled'])) {
            $_SESSION['pending_2fa']=$row['id'];
            $_SESSION['pending_2fa_ts']=time();
            return true;
        }
        session_regenerate_id(true); $_SESSION['user_id']=$row['id']; return true;
    }
    public static function register(string $name,string $email,string $password): bool {
        try {$stmt=self::$db->prepare('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,"eleve")');$stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);return true;}catch(PDOException $e){return false;}
    }
    public static function verifyTotp(string $secret, string $code, int $window=1): bool {
        if (!preg_match('/^[0-9]{6}$/', $code)) return false;
        $secret = strtoupper(trim($secret));
        $alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $bits='';
        for($i=0;$i<strlen($secret);$i++){ $v=strpos($alphabet,$secret[$i]); if($v===false) return false; $bits.=str_pad(decbin($v),5,'0',STR_PAD_LEFT); }
        $bin=''; for($i=0;$i+7<=strlen($bits);$i+=8){ $bin.=chr(bindec(substr($bits,$i,8))); }
        $counter=intdiv(time(),30);
        for($off=-$window;$off<=$window;$off++){
            $msg=pack('N2',0,$counter+$off); $hash=hash_hmac('sha1',$msg,$bin,true); $idx=ord($hash[19])&15;
            $num=((ord($hash[$idx])&127)<<24)|((ord($hash[$idx+1])&255)<<16)|((ord($hash[$idx+2])&255)<<8)|(ord($hash[$idx+3])&255);
            if(hash_equals(str_pad((string)($num%1000000),6,'0',STR_PAD_LEFT),$code)) return true;
        }
        return false;
    }
    public static function logout(): void { $_SESSION=[]; if(ini_get('session.use_cookies')){ $p=session_get_cookie_params(); setcookie(session_name(),'',['expires'=>time()-42000,'path'=>$p['path'],'domain'=>$p['domain'],'secure'=>$p['secure'],'httponly'=>$p['httponly'],'samesite'=>$p['samesite']??'Lax']); } session_destroy(); }
}
