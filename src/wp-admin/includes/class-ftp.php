<?php

declare (strict_types=1);
/**
 * PemFTP - An Ftp implementation in pure PHP
 *
 * @package PemFTP
 * @since 2.5.0
 *
 * @version 1.0
 * @copyright Alexey Dotsenko
 * @author Alexey Dotsenko
 * @link https://www.phpclasses.org/package/1743-PHP-FTP-client-in-pure-PHP.html
 * @license LGPL https://opensource.org/licenses/lgpl-license.html
 */
/**
 * Defines the newline characters, if not defined already.
 *
 * This can be redefined.
 *
 * @since 2.5.0
 * @var string
 */
if (!defined('CRLF')) {
    define('CRLF', "\r\n");
}
/**
 * Sets whatever to autodetect ASCII mode.
 *
 * This can be redefined.
 *
 * @since 2.5.0
 * @var int
 */
if (!defined('FTP_AUTOASCII')) {
    define('FTP_AUTOASCII', -1);
}
/**
 *
 * This can be redefined.
 * @since 2.5.0
 * @var int
 */
if (!defined('FTP_BINARY')) {
    define('FTP_BINARY', 1);
}
/**
 *
 * This can be redefined.
 * @since 2.5.0
 * @var int
 */
if (!defined('FTP_ASCII')) {
    define('FTP_ASCII', 0);
}
/**
 * Whether to force FTP.
 *
 * This can be redefined.
 *
 * @since 2.5.0
 * @var bool
 */
if (!defined('FTP_FORCE')) {
    define('FTP_FORCE', true);
}
/**
 * @since 2.5.0
 * @var string
 */
define('FTP_OS_Unix', 'u');
/**
 * @since 2.5.0
 * @var string
 */
define('FTP_OS_Windows', 'w');
/**
 * @since 2.5.0
 * @var string
 */
define('FTP_OS_Mac', 'm');
/**
 * PemFTP base class
 *
 */
class ftp_base
{
    /* Public variables */
    public $local_echo;
    public $Verbose;
    /**
     * @var 'u'|'w'|'m'
     */
    public $OS_local;
    /**
     * @var 'u'
     */
    public $OS_remote;
    /* Private variables */
    public $_lastaction;
    public $_errors;
    public $_type;
    public $_umask;
    public $_timeout;
    public $_passive;
    public $_host;
    public $_fullhost;
    public $_port;
    public $_datahost;
    public $_dataport;
    public $_ftp_control_sock;
    public $_ftp_data_sock;
    public $_ftp_temp_sock;
    /**
     * @var int
     */
    public $_ftp_buff_size;
    /**
     * @var 'anonymous'
     */
    public $_login;
    /**
     * @var 'anon@ftp.com'
     */
    public $_password;
    /**
     * @var false
     */
    public $_connected;
    /**
     * @var false
     */
    public $_ready;
    /**
     * @var int
     */
    public $_code;
    /**
     * @var ''
     */
    public $_message;
    /**
     * @var false
     */
    public $_can_restore;
    /**
     * @var bool
     */
    public $_port_available;
    public $_curtype;
    public $_features;
    /**
     * @var array{}
     */
    public $_error_array;
    public $authorized_transfer_mode;
    public $os_full_name;
    public $_eol_code;
    public $auto_ascii_ext;
    /* Constructor */
    public function __construct($port_mode = false, $verb = false, $le = false)
    {
        $this->local_echo = $le;
        $this->Verbose = $verb;
        $this->_lastaction = null;
        $this->_error_array = [];
        $this->_eol_code = [FTP_OS_Unix => "\n", FTP_OS_Mac => "\r", FTP_OS_Windows => "\r\n"];
        $this->authorized_transfer_mode = [FTP_AUTOASCII, FTP_ASCII, FTP_BINARY];
        $this->os_full_name = [FTP_OS_Unix => 'UNIX', FTP_OS_Windows => 'WINDOWS', FTP_OS_Mac => 'MACOS'];
        $this->auto_ascii_ext = ['ASP', 'BAT', 'C', 'CPP', 'CSS', 'CSV', 'JS', 'H', 'HTM', 'HTML', 'SHTML', 'INI', 'LOG', 'PHP3', 'PHTML', 'PL', 'PERL', 'SH', 'SQL', 'TXT'];
        $this->_port_available = $port_mode == true;
        $this->send_msg('Staring FTP client class' . ($this->_port_available ? '' : ' without PORT mode support'));
        $this->_connected = false;
        $this->_ready = false;
        $this->_can_restore = false;
        $this->_code = 0;
        $this->_message = '';
        $this->_ftp_buff_size = 4096;
        $this->_curtype = null;
        $this->set_umask(022);
        $this->set_type(FTP_AUTOASCII);
        $this->set_timeout(30);
        $this->Passive(!$this->_port_available);
        $this->_login = 'anonymous';
        $this->_password = 'anon@ftp.com';
        $this->_features = [];
        $this->OS_local = FTP_OS_Unix;
        $this->OS_remote = FTP_OS_Unix;
        $this->features = [];
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->OS_local = FTP_OS_Windows;
        } elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'MAC') {
            $this->OS_local = FTP_OS_Mac;
        }
    }
    // <!-- --------------------------------------------------------------------------------------- -->
    // <!--       Public functions                                                                  -->
    // <!-- --------------------------------------------------------------------------------------- -->
    public function parselisting($line)
    {
        $is_windows = $this->OS_remote == FTP_OS_Windows;
        if ($is_windows && preg_match('/([0-9]{2})-([0-9]{2})-([0-9]{2}) +([0-9]{2}):([0-9]{2})(AM|PM) +([0-9]+|<DIR>) +(.+)/', $line, $lucifer)) {
            $b = [];
            if ($lucifer[3] < 70) {
                $lucifer[3] += 2000;
            } else {
                $lucifer[3] += 1900;
            }
            // 4digit year fix
            $b['isdir'] = $lucifer[7] == '<DIR>';
            if ($b['isdir']) {
                $b['type'] = 'd';
            } else {
                $b['type'] = 'f';
            }
            $b['size'] = $lucifer[7];
            $b['month'] = $lucifer[1];
            $b['day'] = $lucifer[2];
            $b['year'] = $lucifer[3];
            $b['hour'] = $lucifer[4];
            $b['minute'] = $lucifer[5];
            $b['time'] = @mktime($lucifer[4] + (strcasecmp($lucifer[6], 'PM') == 0 ? 12 : 0), $lucifer[5], 0, $lucifer[1], $lucifer[2], $lucifer[3]);
            $b['am/pm'] = $lucifer[6];
            $b['name'] = $lucifer[8];
        } elseif (!$is_windows && $lucifer = preg_split('/[ ]/', $line, 9, PREG_SPLIT_NO_EMPTY)) {
            //echo $line."\n";
            $lcount = count($lucifer);
            if ($lcount < 8) {
                return '';
            }
            $b = [];
            $b['isdir'] = $lucifer[0][0] === 'd';
            $b['islink'] = $lucifer[0][0] === 'l';
            if ($b['isdir']) {
                $b['type'] = 'd';
            } elseif ($b['islink']) {
                $b['type'] = 'l';
            } else {
                $b['type'] = 'f';
            }
            $b['perms'] = $lucifer[0];
            $b['number'] = $lucifer[1];
            $b['owner'] = $lucifer[2];
            $b['group'] = $lucifer[3];
            $b['size'] = $lucifer[4];
            if ($lcount == 8) {
                sscanf($lucifer[5], '%d-%d-%d', $b['year'], $b['month'], $b['day']);
                sscanf($lucifer[6], '%d:%d', $b['hour'], $b['minute']);
                $b['time'] = @mktime($b['hour'], $b['minute'], 0, $b['month'], $b['day'], $b['year']);
                $b['name'] = $lucifer[7];
            } else {
                $b['month'] = $lucifer[5];
                $b['day'] = $lucifer[6];
                if (preg_match('/([0-9]{2}):([0-9]{2})/', $lucifer[7], $l2)) {
                    $b['year'] = gmdate('Y');
                    $b['hour'] = $l2[1];
                    $b['minute'] = $l2[2];
                } else {
                    $b['year'] = $lucifer[7];
                    $b['hour'] = 0;
                    $b['minute'] = 0;
                }
                $b['time'] = strtotime(sprintf('%d %s %d %02d:%02d', $b['day'], $b['month'], $b['year'], $b['hour'], $b['minute']));
                $b['name'] = $lucifer[8];
            }
        }
        return $b;
    }
    public function send_msg(string $message = '', $crlf = true): bool
    {
        if ($this->Verbose) {
            echo $message . ($crlf ? CRLF : '');
            flush();
        }
        return true;
    }
    public function set_type($mode = FTP_AUTOASCII): bool
    {
        if (!in_array($mode, $this->authorized_transfer_mode)) {
            $this->send_msg('Wrong type');
            return false;
        }
        $this->_type = $mode;
        $this->send_msg('Transfer type: ' . ($this->_type == FTP_BINARY ? 'binary' : ($this->_type == FTP_ASCII ? 'ASCII' : 'auto ASCII')));
        return true;
    }
    public function _settype($mode = FTP_ASCII): bool
    {
        if ($this->_ready) {
            if ($mode == FTP_BINARY) {
                if ($this->_curtype != FTP_BINARY) {
                    if (!$this->_exec('TYPE I', 'SetType')) {
                        return false;
                    }
                    $this->_curtype = FTP_BINARY;
                }
            } elseif ($this->_curtype != FTP_ASCII) {
                if (!$this->_exec('TYPE A', 'SetType')) {
                    return false;
                }
                $this->_curtype = FTP_ASCII;
            }
        } else {
            return false;
        }
        return true;
    }
    public function Passive($pasv = null): bool
    {
        if (is_null($pasv)) {
            $this->_passive = !$this->_passive;
        } else {
            $this->_passive = $pasv;
        }
        if (!$this->_port_available and !$this->_passive) {
            $this->send_msg('Only passive connections available!');
            $this->_passive = true;
            return false;
        }
        $this->send_msg('Passive mode ' . ($this->_passive ? 'on' : 'off'));
        return true;
    }
    public function set_server(string $host, $port = 21, $reconnect = true): bool
    {
        if (!is_long($port)) {
            $this->verbose = true;
            $this->send_msg('Incorrect port syntax');
            return false;
        }
        $ip = @gethostbyname($host);
        $dns = @gethostbyaddr($host);
        if (!$ip) {
            $ip = $host;
        }
        if (!$dns) {
            $dns = $host;
        }
        // Validate the IPAddress PHP4 returns -1 for invalid, PHP5 false
        // -1 === "255.255.255.255" which is the broadcast address which is also going to be invalid
        $ipaslong = ip2long($ip);
        if ($ipaslong == false || $ipaslong === -1) {
            $this->send_msg('Wrong host name/address "' . $host . '"');
            return false;
        }
        $this->_host = $ip;
        $this->_fullhost = $dns;
        $this->_port = $port;
        $this->_dataport = $port - 1;
        $this->send_msg('Host "' . $this->_fullhost . '(' . $this->_host . '):' . $this->_port . '"');
        if ($reconnect) {
            if ($this->_connected) {
                $this->send_msg('Reconnecting');
                if (!$this->quit(FTP_FORCE)) {
                    return false;
                }
                if (!$this->connect()) {
                    return false;
                }
            }
        }
        return true;
    }
    public function set_umask($umask = 022): bool
    {
        $this->_umask = $umask;
        umask($this->_umask);
        $this->send_msg('UMASK 0' . decoct($this->_umask));
        return true;
    }
    public function set_timeout($timeout = 30): bool
    {
        $this->_timeout = $timeout;
        $this->send_msg('Timeout ' . $this->_timeout);
        if (!$this->_connected) {
            return true;
        }
        if (!$this->_settimeout($this->_ftp_control_sock)) {
            return false;
        }
        return true;
    }
    public function connect($server = null): bool
    {
        if (!empty($server)) {
            if (!$this->set_server($server)) {
                return false;
            }
        }
        if ($this->_ready) {
            return true;
        }
        $this->send_msg('Local OS : ' . $this->os_full_name[$this->OS_local]);
        if (!$this->_ftp_control_sock = $this->_connect($this->_host, $this->_port)) {
            $this->send_msg('Error : Cannot connect to remote host "' . $this->_fullhost . ' :' . $this->_port . '"');
            return false;
        }
        $this->send_msg('Connected to remote host "' . $this->_fullhost . ':' . $this->_port . '". Waiting for greeting.');
        do {
            if (!$this->_readmsg()) {
                return false;
            }
            if (!$this->_check_code()) {
                return false;
            }
            $this->_lastaction = time();
        } while ($this->_code < 200);
        $this->_ready = true;
        $syst = $this->systype();
        if (!$syst) {
            $this->send_msg('Cannot detect remote OS');
        } else {
            if (preg_match('/win|dos|novell/i', $syst[0])) {
                $this->OS_remote = FTP_OS_Windows;
            } elseif (preg_match('/os/i', $syst[0])) {
                $this->OS_remote = FTP_OS_Mac;
            } elseif (preg_match('/(li|u)nix/i', $syst[0])) {
                $this->OS_remote = FTP_OS_Unix;
            } else {
                $this->OS_remote = FTP_OS_Mac;
            }
            $this->send_msg('Remote OS: ' . $this->os_full_name[$this->OS_remote]);
        }
        if (!$this->features()) {
            $this->send_msg('Cannot get features list. All supported - disabled');
        } else {
            $this->send_msg('Supported features: ' . implode(', ', array_keys($this->_features)));
        }
        return true;
    }
    public function quit($force = false): bool
    {
        if ($this->_ready) {
            if (!$this->_exec('QUIT') and !$force) {
                return false;
            }
            if (!$this->_check_code() and !$force) {
                return false;
            }
            $this->_ready = false;
            $this->send_msg('Session finished');
        }
        $this->_quit();
        return true;
    }
    public function login($user = null, $pass = null): bool
    {
        if (!is_null($user)) {
            $this->_login = $user;
        } else {
            $this->_login = 'anonymous';
        }
        if (!is_null($pass)) {
            $this->_password = $pass;
        } else {
            $this->_password = 'anon@anon.com';
        }
        if (!$this->_exec('USER ' . $this->_login, 'login')) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        if ($this->_code != 230) {
            if (!$this->_exec(($this->_code == 331 ? 'PASS ' : 'ACCT ') . $this->_password, 'login')) {
                return false;
            }
            if (!$this->_check_code()) {
                return false;
            }
        }
        $this->send_msg('Authentication succeeded');
        if (empty($this->_features)) {
            if (!$this->features()) {
                $this->send_msg('Cannot get features list. All supported - disabled');
            } else {
                $this->send_msg('Supported features: ' . implode(', ', array_keys($this->_features)));
            }
        }
        return true;
    }
    public function pwd()
    {
        if (!$this->_exec('PWD', 'pwd')) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        return preg_replace('/^[0-9]{3} "(.+)".*$/s', '\1', $this->_message);
    }
    public function cdup(): bool
    {
        if (!$this->_exec('CDUP', 'cdup')) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        return true;
    }
    public function chdir(string $pathname): bool
    {
        if (!$this->_exec('CWD ' . $pathname, 'chdir')) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        return true;
    }
    public function rmdir(string $pathname): bool
    {
        if (!$this->_exec('RMD ' . $pathname, 'rmdir')) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        return true;
    }
    public function mkdir(string $pathname): bool
    {
        if (!$this->_exec('MKD ' . $pathname, 'mkdir')) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        return true;
    }
    public function rename(string $from, string $to): bool
    {
        if (!$this->_exec('RNFR ' . $from, 'rename')) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        if ($this->_code == 350) {
            if (!$this->_exec('RNTO ' . $to, 'rename')) {
                return false;
            }
            if (!$this->_check_code()) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }
    public function filesize(string $pathname)
    {
        if (!isset($this->_features['SIZE'])) {
            $this->push_error('filesize', 'not supported by server');
            return false;
        }
        if (!$this->_exec('SIZE ' . $pathname, 'filesize')) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        return preg_replace('/^[0-9]{3} ([0-9]+).*$/s', '\1', $this->_message);
    }
    public function abort(): bool
    {
        if (!$this->_exec('ABOR', 'abort')) {
            return false;
        }
        if (!$this->_check_code()) {
            if ($this->_code != 426) {
                return false;
            }
            if (!$this->_readmsg('abort')) {
                return false;
            }
            if (!$this->_check_code()) {
                return false;
            }
        }
        return true;
    }
    public function mdtm(string $pathname)
    {
        if (!isset($this->_features['MDTM'])) {
            $this->push_error('mdtm', 'not supported by server');
            return false;
        }
        if (!$this->_exec('MDTM ' . $pathname, 'mdtm')) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        $mdtm = preg_replace('/^[0-9]{3} ([0-9]+).*$/s', '\1', $this->_message);
        $date = sscanf($mdtm, '%4d%2d%2d%2d%2d%2d');
        return mktime($date[3], $date[4], $date[5], $date[1], $date[2], $date[0]);
    }
    public function systype()
    {
        if (!$this->_exec('SYST', 'systype')) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        $DATA = explode(' ', $this->_message);
        return [$DATA[1], $DATA[3]];
    }
    public function delete(string $pathname): bool
    {
        if (!$this->_exec('DELE ' . $pathname, 'delete')) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        return true;
    }
    public function site(string $command, $fnction = 'site'): bool
    {
        if (!$this->_exec('SITE ' . $command, $fnction)) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        return true;
    }
    public function chmod($pathname, $mode): bool
    {
        if (!$this->site(sprintf('CHMOD %o %s', $mode, $pathname), 'chmod')) {
            return false;
        }
        return true;
    }
    public function restore(string $from): bool
    {
        if (!isset($this->_features['REST'])) {
            $this->push_error('restore', 'not supported by server');
            return false;
        }
        if ($this->_curtype != FTP_BINARY) {
            $this->push_error('restore', 'cannot restore in ASCII mode');
            return false;
        }
        if (!$this->_exec('REST ' . $from, 'restore')) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        return true;
    }
    public function features(): bool
    {
        if (!$this->_exec('FEAT', 'features')) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        $f = preg_split('/[' . CRLF . ']+/', preg_replace('/[0-9]{3}[ -].*[' . CRLF . ']+/', '', $this->_message), -1, PREG_SPLIT_NO_EMPTY);
        $this->_features = [];
        foreach ($f as $v) {
            $v = explode(' ', trim($v));
            $this->_features[array_shift($v)] = $v;
        }
        return true;
    }
    public function rawlist(?string $pathname = '', ?string $arg = '')
    {
        return $this->_list(($arg ? ' ' . $arg : '') . ($pathname ? ' ' . $pathname : ''), 'LIST', 'rawlist');
    }
    public function nlist(?string $pathname = '', ?string $arg = '')
    {
        return $this->_list(($arg ? ' ' . $arg : '') . ($pathname ? ' ' . $pathname : ''), 'NLST', 'nlist');
    }
    public function is_exists($pathname)
    {
        return $this->file_exists($pathname);
    }
    public function file_exists(string $pathname)
    {
        $exists = true;
        if (!$this->_exec('RNFR ' . $pathname, 'rename')) {
            $exists = false;
        } else {
            if (!$this->_check_code()) {
                $exists = false;
            }
            $this->abort();
        }
        if ($exists) {
            $this->send_msg('Remote file ' . $pathname . ' exists');
        } else {
            $this->send_msg('Remote file ' . $pathname . ' does not exist');
        }
        return $exists;
    }
    public function fget($fp, string $remotefile, $rest = 0)
    {
        if ($this->_can_restore and $rest != 0) {
            fseek($fp, $rest);
        }
        $pi = pathinfo($remotefile);
        if ($this->_type == FTP_ASCII or $this->_type == FTP_AUTOASCII and in_array(strtoupper($pi['extension']), $this->auto_ascii_ext)) {
            $mode = FTP_ASCII;
        } else {
            $mode = FTP_BINARY;
        }
        if (!$this->_data_prepare($mode)) {
            return false;
        }
        if ($this->_can_restore and $rest != 0) {
            $this->restore($rest);
        }
        if (!$this->_exec('RETR ' . $remotefile, 'get')) {
            $this->_data_close();
            return false;
        }
        if (!$this->_check_code()) {
            $this->_data_close();
            return false;
        }
        $out = $this->_data_read($mode, $fp);
        $this->_data_close();
        if (!$this->_readmsg()) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        return $out;
    }
    public function get(string $remotefile, $localfile = null, $rest = 0)
    {
        if (is_null($localfile)) {
            $localfile = $remotefile;
        }
        if (@file_exists($localfile)) {
            $this->send_msg('Warning : local file will be overwritten');
        }
        $fp = @fopen($localfile, 'w');
        if (!$fp) {
            $this->push_error('get', 'cannot open local file', 'Cannot create "' . $localfile . '"');
            return false;
        }
        if ($this->_can_restore and $rest != 0) {
            fseek($fp, $rest);
        }
        $pi = pathinfo($remotefile);
        if ($this->_type == FTP_ASCII or $this->_type == FTP_AUTOASCII and in_array(strtoupper($pi['extension']), $this->auto_ascii_ext)) {
            $mode = FTP_ASCII;
        } else {
            $mode = FTP_BINARY;
        }
        if (!$this->_data_prepare($mode)) {
            fclose($fp);
            return false;
        }
        if ($this->_can_restore and $rest != 0) {
            $this->restore($rest);
        }
        if (!$this->_exec('RETR ' . $remotefile, 'get')) {
            $this->_data_close();
            fclose($fp);
            return false;
        }
        if (!$this->_check_code()) {
            $this->_data_close();
            fclose($fp);
            return false;
        }
        $out = $this->_data_read($mode, $fp);
        fclose($fp);
        $this->_data_close();
        if (!$this->_readmsg()) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        return $out;
    }
    public function fput(string $remotefile, $fp, $rest = 0)
    {
        if ($this->_can_restore and $rest != 0) {
            fseek($fp, $rest);
        }
        $pi = pathinfo($remotefile);
        if ($this->_type == FTP_ASCII or $this->_type == FTP_AUTOASCII and in_array(strtoupper($pi['extension']), $this->auto_ascii_ext)) {
            $mode = FTP_ASCII;
        } else {
            $mode = FTP_BINARY;
        }
        if (!$this->_data_prepare($mode)) {
            return false;
        }
        if ($this->_can_restore and $rest != 0) {
            $this->restore($rest);
        }
        if (!$this->_exec('STOR ' . $remotefile, 'put')) {
            $this->_data_close();
            return false;
        }
        if (!$this->_check_code()) {
            $this->_data_close();
            return false;
        }
        $ret = $this->_data_write($mode, $fp);
        $this->_data_close();
        if (!$this->_readmsg()) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        return $ret;
    }
    public function put(string $localfile, $remotefile = null, $rest = 0)
    {
        if (is_null($remotefile)) {
            $remotefile = $localfile;
        }
        if (!file_exists($localfile)) {
            $this->push_error('put', 'cannot open local file', 'No such file or directory "' . $localfile . '"');
            return false;
        }
        $fp = @fopen($localfile, 'r');
        if (!$fp) {
            $this->push_error('put', 'cannot open local file', 'Cannot read file "' . $localfile . '"');
            return false;
        }
        if ($this->_can_restore and $rest != 0) {
            fseek($fp, $rest);
        }
        $pi = pathinfo($localfile);
        if ($this->_type == FTP_ASCII or $this->_type == FTP_AUTOASCII and in_array(strtoupper($pi['extension']), $this->auto_ascii_ext)) {
            $mode = FTP_ASCII;
        } else {
            $mode = FTP_BINARY;
        }
        if (!$this->_data_prepare($mode)) {
            fclose($fp);
            return false;
        }
        if ($this->_can_restore and $rest != 0) {
            $this->restore($rest);
        }
        if (!$this->_exec('STOR ' . $remotefile, 'put')) {
            $this->_data_close();
            fclose($fp);
            return false;
        }
        if (!$this->_check_code()) {
            $this->_data_close();
            fclose($fp);
            return false;
        }
        $ret = $this->_data_write($mode, $fp);
        fclose($fp);
        $this->_data_close();
        if (!$this->_readmsg()) {
            return false;
        }
        if (!$this->_check_code()) {
            return false;
        }
        return $ret;
    }
    public function mput($local = '.', $remote = null, $continious = false)
    {
        $local = realpath($local);
        if (!@file_exists($local)) {
            $this->push_error('mput', 'cannot open local folder', 'Cannot stat folder "' . $local . '"');
            return false;
        }
        if (!is_dir($local)) {
            return $this->put($local, $remote);
        }
        if (empty($remote)) {
            $remote = '.';
        } elseif (!$this->file_exists($remote) and !$this->mkdir($remote)) {
            return false;
        }
        if ($handle = opendir($local)) {
            $list = [];
            while (false !== $file = readdir($handle)) {
                if ($file != '.' && $file != '..') {
                    $list[] = $file;
                }
            }
            closedir($handle);
        } else {
            $this->push_error('mput', 'cannot open local folder', 'Cannot read folder "' . $local . '"');
            return false;
        }
        if (empty($list)) {
            return true;
        }
        $ret = true;
        foreach ($list as $el) {
            if (is_dir($local . '/' . $el)) {
                $t = $this->mput($local . '/' . $el, $remote . '/' . $el);
            } else {
                $t = $this->put($local . '/' . $el, $remote . '/' . $el);
            }
            if (!$t) {
                $ret = false;
                if (!$continious) {
                    break;
                }
            }
        }
        return $ret;
    }
    public function mget(string $remote, string $local = '.', $continious = false)
    {
        $list = $this->rawlist($remote, '-lA');
        if ($list === false) {
            $this->push_error('mget', 'cannot read remote folder list', 'Cannot read remote folder "' . $remote . '" contents');
            return false;
        }
        if (empty($list)) {
            return true;
        }
        if (!@file_exists($local)) {
            if (!@mkdir($local)) {
                $this->push_error('mget', 'cannot create local folder', 'Cannot create folder "' . $local . '"');
                return false;
            }
        }
        foreach ($list as $k => $v) {
            $list[$k] = $this->parselisting($v);
            if (!$list[$k] or $list[$k]['name'] == '.' or $list[$k]['name'] == '..') {
                unset($list[$k]);
            }
        }
        $ret = true;
        foreach ($list as $el) {
            if ($el['type'] == 'd') {
                if (!$this->mget($remote . '/' . $el['name'], $local . '/' . $el['name'], $continious)) {
                    $this->push_error('mget', 'cannot copy folder', 'Cannot copy remote folder "' . $remote . '/' . $el['name'] . '" to local "' . $local . '/' . $el['name'] . '"');
                    $ret = false;
                    if (!$continious) {
                        break;
                    }
                }
            } else if (!$this->get($remote . '/' . $el['name'], $local . '/' . $el['name'])) {
                $this->push_error('mget', 'cannot copy file', 'Cannot copy remote file "' . $remote . '/' . $el['name'] . '" to local "' . $local . '/' . $el['name'] . '"');
                $ret = false;
                if (!$continious) {
                    break;
                }
            }
            @chmod($local . '/' . $el['name'], $el['perms']);
            $t = strtotime($el['date']);
            if ($t !== -1 and $t !== false) {
                @touch($local . '/' . $el['name'], $t);
            }
        }
        return $ret;
    }
    public function mdel(string $remote, $continious = false)
    {
        $list = $this->rawlist($remote, '-la');
        if ($list === false) {
            $this->push_error('mdel', 'cannot read remote folder list', 'Cannot read remote folder "' . $remote . '" contents');
            return false;
        }
        foreach ($list as $k => $v) {
            $list[$k] = $this->parselisting($v);
            if (!$list[$k] or $list[$k]['name'] == '.' or $list[$k]['name'] == '..') {
                unset($list[$k]);
            }
        }
        $ret = true;
        foreach ($list as $el) {
            if (empty($el)) {
                continue;
            }
            if ($el['type'] == 'd') {
                if (!$this->mdel($remote . '/' . $el['name'], $continious)) {
                    $ret = false;
                    if (!$continious) {
                        break;
                    }
                }
            } else if (!$this->delete($remote . '/' . $el['name'])) {
                $this->push_error('mdel', 'cannot delete file', 'Cannot delete remote file "' . $remote . '/' . $el['name'] . '"');
                $ret = false;
                if (!$continious) {
                    break;
                }
            }
        }
        if (!$this->rmdir($remote)) {
            $this->push_error('mdel', 'cannot delete folder', 'Cannot delete remote folder "' . $remote . '/' . $el['name'] . '"');
            $ret = false;
        }
        return $ret;
    }
    public function mmkdir($dir, $mode = 0777)
    {
        if (empty($dir)) {
            return false;
        }
        if ($this->is_exists($dir) or $dir == '/') {
            return true;
        }
        if (!$this->mmkdir(dirname($dir), $mode)) {
            return false;
        }
        $r = $this->mkdir($dir);
        $this->chmod($dir, $mode);
        return $r;
    }
    public function glob($pattern, $handle = null)
    {
        $path = $output = null;
        if (PHP_OS == 'WIN32') {
            $slash = '\\';
        } else {
            $slash = '/';
        }
        $lastpos = strrpos($pattern, $slash);
        if (!($lastpos === false)) {
            $path = substr($pattern, 0, -$lastpos - 1);
            $pattern = substr($pattern, $lastpos);
        } else {
            $path = getcwd();
        }
        if (is_array($handle) and !empty($handle)) {
            foreach ($handle as $dir) {
                if ($this->glob_pattern_match($pattern, $dir)) {
                    $output[] = $dir;
                }
            }
        } else {
            $handle = @opendir($path);
            if ($handle === false) {
                return false;
            }
            while ($dir = readdir($handle)) {
                if ($this->glob_pattern_match($pattern, $dir)) {
                    $output[] = $dir;
                }
            }
            closedir($handle);
        }
        if (is_array($output)) {
            return $output;
        }
        return false;
    }
    public function glob_pattern_match($pattern, $subject)
    {
        $out = null;
        $chunks = explode(';', $pattern);
        foreach ($chunks as $pattern) {
            $escape = ['$', '^', '.', '{', '}', '(', ')', '[', ']', '|'];
            while (str_contains($pattern, '**')) {
                $pattern = str_replace('**', '*', $pattern);
            }
            foreach ($escape as $probe) {
                $pattern = str_replace($probe, "\\{$probe}", $pattern);
            }
            $pattern = str_replace('?*', '*', str_replace('*?', '*', str_replace('*', '.*', str_replace('?', '.{1,1}', $pattern))));
            $out[] = $pattern;
        }
        if (count($out) == 1) {
            return $this->glob_regexp("^{$out[0]}\$", $subject);
        }
        foreach ($out as $tester) {
            // TODO: This should probably be glob_regexp(), but needs tests.
            if ($this->my_regexp("^{$tester}\$", $subject)) {
                return true;
            }
        }
        return false;
    }
    public function glob_regexp($pattern, $subject)
    {
        $sensitive = PHP_OS != 'WIN32';
        return $sensitive ? preg_match('/' . preg_quote($pattern, '/') . '/', $subject) : preg_match('/' . preg_quote($pattern, '/') . '/i', $subject);
    }
    public function dirlist(string $remote)
    {
        $list = $this->rawlist($remote, '-la');
        if ($list === false) {
            $this->push_error('dirlist', 'cannot read remote folder list', 'Cannot read remote folder "' . $remote . '" contents');
            return false;
        }
        $dirlist = [];
        foreach ($list as $v) {
            $entry = $this->parselisting($v);
            if (empty($entry)) {
                continue;
            }
            if ($entry['name'] == '.' or $entry['name'] == '..') {
                continue;
            }
            $dirlist[$entry['name']] = $entry;
        }
        return $dirlist;
    }
    // <!-- --------------------------------------------------------------------------------------- -->
    // <!--       Private functions                                                                 -->
    // <!-- --------------------------------------------------------------------------------------- -->
    public function _check_code(): bool
    {
        return $this->_code < 400 and $this->_code > 0;
    }
    public function _list(string $arg = '', string $cmd = 'LIST', $fnction = '_list')
    {
        if (!$this->_data_prepare()) {
            return false;
        }
        if (!$this->_exec($cmd . $arg, $fnction)) {
            $this->_data_close();
            return false;
        }
        if (!$this->_check_code()) {
            $this->_data_close();
            return false;
        }
        $out = '';
        if ($this->_code < 200) {
            $out = $this->_data_read();
            $this->_data_close();
            if (!$this->_readmsg()) {
                return false;
            }
            if (!$this->_check_code()) {
                return false;
            }
            if ($out === false) {
                return false;
            }
            $out = preg_split('/[' . CRLF . ']+/', $out, -1, PREG_SPLIT_NO_EMPTY);
            //			$this->SendMSG(implode($this->_eol_code[$this->OS_local], $out));
        }
        return $out;
    }
    // <!-- --------------------------------------------------------------------------------------- -->
    // <!-- Partie : gestion des erreurs                                                            -->
    // <!-- --------------------------------------------------------------------------------------- -->
    // Gnre une erreur pour traitement externe  la classe
    public function push_error(string $fctname, string $msg, $desc = false): int
    {
        $error = [];
        $error['time'] = time();
        $error['fctname'] = $fctname;
        $error['msg'] = $msg;
        $error['desc'] = $desc;
        if ($desc) {
            $tmp = ' (' . $desc . ')';
        } else {
            $tmp = '';
        }
        $this->send_msg($fctname . ': ' . $msg . $tmp);
        return array_push($this->_error_array, $error);
    }
    // Rcupre une erreur externe
    public function pop_error()
    {
        if (count($this->_error_array)) {
            return array_pop($this->_error_array);
        }
        return false;
    }
}
$mod_sockets = extension_loaded('sockets');
if (!$mod_sockets && function_exists('dl') && is_callable('dl')) {
    $prefix = PHP_SHLIB_SUFFIX == 'dll' ? 'php_' : '';
    @dl($prefix . 'sockets.' . PHP_SHLIB_SUFFIX);
    // phpcs:ignore PHPCompatibility.FunctionUse.RemovedFunctions.dlDeprecated
    $mod_sockets = extension_loaded('sockets');
}
require_once __DIR__ . '/class-ftp-' . ($mod_sockets ? 'sockets' : 'pure') . '.php';
if ($mod_sockets) {
    class ftp extends ftp_sockets
    {
    }
} else {
    class ftp extends ftp_pure
    {
    }
}