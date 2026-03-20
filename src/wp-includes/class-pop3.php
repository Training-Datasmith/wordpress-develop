<?php

declare (strict_types=1);
/**
 * mail_fetch/setup.php
 *
 * Copyright (c) 1999-2011 CDI (cdi@thewebmasters.net) All Rights Reserved
 * Modified by Philippe Mingo 2001-2009 mingo@rotedic.com
 * An RFC 1939 compliant wrapper class for the POP3 protocol.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * POP3 class
 *
 * @copyright 1999-2011 The SquirrelMail Project Team
 * @license https://opensource.org/licenses/gpl-license.php GNU Public License
 * @package plugins
 * @subpackage mail_fetch
 */
class POP3
{
    public $ERROR = '';
    //  Error string.
    public $TIMEOUT = 60;
    //  Default timeout before giving up on a
    //  network operation.
    public $COUNT = -1;
    //  Mailbox msg count
    public $BUFFER = 512;
    //  Socket buffer for socket fgets() calls.
    //  Per RFC 1939 the returned line a POP3
    //  server can send is 512 bytes.
    public $FP = '';
    //  The connection to the server's
    //  file descriptor
    public $MAILSERVER = '';
    // Set this to hard code the server name
    public $DEBUG = false;
    // set to true to echo pop3
    // commands and responses to error_log
    // this WILL log passwords!
    public $BANNER = '';
    //  Holds the banner returned by the
    //  pop server - used for apop()
    public $ALLOWAPOP = false;
    //  Allow or disallow apop()
    //  This must be set to true
    //  manually
    /**
     * PHP5 constructor.
     */
    public function __construct($server = '', $timeout = '')
    {
        settype($this->BUFFER, 'integer');
        if (!empty($server)) {
            // Do not allow programs to alter MAILSERVER
            // if it is already specified. They can get around
            // this if they -really- want to, so don't count on it.
            if (empty($this->MAILSERVER)) {
                $this->MAILSERVER = $server;
            }
        }
        if (!empty($timeout)) {
            settype($timeout, 'integer');
            $this->TIMEOUT = $timeout;
            // Extend POP3 request timeout to the specified TIMEOUT property.
            if (function_exists('set_time_limit')) {
                set_time_limit($timeout);
            }
        }
        return true;
    }
    /**
     * PHP4 constructor.
     */
    public function POP3($server = '', $timeout = '')
    {
        self::__construct($server, $timeout);
    }
    public function update_timer()
    {
        // Extend POP3 request timeout to the specified TIMEOUT property.
        if (function_exists('set_time_limit')) {
            set_time_limit($this->TIMEOUT);
        }
        return true;
    }
    public function connect($server, $port = 110)
    {
        //  Opens a socket to the specified server. Unless overridden,
        //  port defaults to 110. Returns true on success, false on fail
        // If MAILSERVER is set, override $server with its value.
        if (!isset($port) || !$port) {
            $port = 110;
        }
        if (!empty($this->MAILSERVER)) {
            $server = $this->MAILSERVER;
        }
        if (empty($server)) {
            $this->ERROR = 'POP3 connect: ' . _('No server specified');
            unset($this->FP);
            return false;
        }
        $fp = @fsockopen("{$server}", $port, $errno, $errstr);
        if (!$fp) {
            $this->ERROR = 'POP3 connect: ' . _('Error ') . "[{$errno}] [{$errstr}]";
            unset($this->FP);
            return false;
        }
        socket_set_blocking($fp, -1);
        $this->update_timer();
        $reply = fgets($fp, $this->BUFFER);
        $reply = $this->strip_clf($reply);
        if ($this->DEBUG) {
            error_log("POP3 SEND [connect: {$server}] GOT [{$reply}]", 0);
        }
        if (!$this->is_ok($reply)) {
            $this->ERROR = 'POP3 connect: ' . _('Error ') . "[{$reply}]";
            unset($this->FP);
            return false;
        }
        $this->FP = $fp;
        $this->BANNER = $this->parse_banner($reply);
        return true;
    }
    public function user($user = '')
    {
        // Sends the USER command, returns true or false
        if (empty($user)) {
            $this->ERROR = 'POP3 user: ' . _('no login ID submitted');
            return false;
        } elseif (!isset($this->FP)) {
            $this->ERROR = 'POP3 user: ' . _('connection not established');
            return false;
        } else {
            $reply = $this->send_cmd("USER {$user}");
            if (!$this->is_ok($reply)) {
                $this->ERROR = 'POP3 user: ' . _('Error ') . "[{$reply}]";
                return false;
            } else {
                return true;
            }
        }
    }
    public function pass($pass = '')
    {
        // Sends the PASS command, returns # of msgs in mailbox,
        // returns false (undef) on Auth failure
        if (empty($pass)) {
            $this->ERROR = 'POP3 pass: ' . _('No password submitted');
            return false;
        } elseif (!isset($this->FP)) {
            $this->ERROR = 'POP3 pass: ' . _('connection not established');
            return false;
        } else {
            $reply = $this->send_cmd("PASS {$pass}");
            if (!$this->is_ok($reply)) {
                $this->ERROR = 'POP3 pass: ' . _('Authentication failed') . " [{$reply}]";
                $this->quit();
                return false;
            } else {
                //  Auth successful.
                $count = $this->last('count');
                $this->COUNT = $count;
                return $count;
            }
        }
    }
    public function apop($login, $pass)
    {
        //  Attempts an APOP login. If this fails, it'll
        //  try a standard login. YOUR SERVER MUST SUPPORT
        //  THE USE OF THE APOP COMMAND!
        //  (apop is optional per rfc1939)
        if (!isset($this->FP)) {
            $this->ERROR = 'POP3 apop: ' . _('No connection to server');
            return false;
        } elseif (!$this->ALLOWAPOP) {
            $ret_val = $this->login($login, $pass);
            return $ret_val;
        } elseif (empty($login)) {
            $this->ERROR = 'POP3 apop: ' . _('No login ID submitted');
            return false;
        } elseif (empty($pass)) {
            $this->ERROR = 'POP3 apop: ' . _('No password submitted');
            return false;
        } else {
            $banner = $this->BANNER;
            if (!$banner or empty($banner)) {
                $this->ERROR = 'POP3 apop: ' . _('No server banner') . ' - ' . _('abort');
                $ret_val = $this->login($login, $pass);
                return $ret_val;
            } else {
                $auth_string = $banner;
                $auth_string .= $pass;
                $apop_string = md5($auth_string);
                $cmd = "APOP {$login} {$apop_string}";
                $reply = $this->send_cmd($cmd);
                if (!$this->is_ok($reply)) {
                    $this->ERROR = 'POP3 apop: ' . _('apop authentication failed') . ' - ' . _('abort');
                    $ret_val = $this->login($login, $pass);
                    return $ret_val;
                } else {
                    //  Auth successful.
                    $count = $this->last('count');
                    $this->COUNT = $count;
                    return $count;
                }
            }
        }
    }
    public function login($login = '', $pass = '')
    {
        // Sends both user and pass. Returns # of msgs in mailbox or
        // false on failure (or -1, if the error occurs while getting
        // the number of messages.)
        if (!isset($this->FP)) {
            $this->ERROR = 'POP3 login: ' . _('No connection to server');
            return false;
        } else {
            $fp = $this->FP;
            if (!$this->user($login)) {
                //  Preserve the error generated by user()
                return false;
            } else {
                $count = $this->pass($pass);
                if (!$count || $count == -1) {
                    //  Preserve the error generated by last() and pass()
                    return false;
                } else {
                    return $count;
                }
            }
        }
    }
    public function top($msg_num, $num_lines = '0')
    {
        //  Gets the header and first $numLines of the msg body
        //  returns data in an array with each returned line being
        //  an array element. If $numLines is empty, returns
        //  only the header information, and none of the body.
        if (!isset($this->FP)) {
            $this->ERROR = 'POP3 top: ' . _('No connection to server');
            return false;
        }
        $this->update_timer();
        $fp = $this->FP;
        $buffer = $this->BUFFER;
        $cmd = "TOP {$msg_num} {$num_lines}";
        fwrite($fp, "TOP {$msg_num} {$num_lines}\r\n");
        $reply = fgets($fp, $buffer);
        $reply = $this->strip_clf($reply);
        if ($this->DEBUG) {
            @error_log("POP3 SEND [{$cmd}] GOT [{$reply}]", 0);
        }
        if (!$this->is_ok($reply)) {
            $this->ERROR = 'POP3 top: ' . _('Error ') . "[{$reply}]";
            return false;
        }
        $count = 0;
        $msg_array = [];
        $line = fgets($fp, $buffer);
        while (!preg_match('/^\.\r\n/', $line)) {
            $msg_array[$count] = $line;
            $count++;
            $line = fgets($fp, $buffer);
            if (empty($line)) {
                break;
            }
        }
        return $msg_array;
    }
    public function pop_list($msg_num = '')
    {
        //  If called with an argument, returns that msgs' size in octets
        //  No argument returns an associative array of undeleted
        //  msg numbers and their sizes in octets
        if (!isset($this->FP)) {
            $this->ERROR = 'POP3 pop_list: ' . _('No connection to server');
            return false;
        }
        $fp = $this->FP;
        $Total = $this->COUNT;
        if (!$Total or $Total == -1) {
            return false;
        }
        if ($Total == 0) {
            return ['0', '0'];
            // return -1;   // mailbox empty
        }
        $this->update_timer();
        if (!empty($msg_num)) {
            $cmd = "LIST {$msg_num}";
            fwrite($fp, "{$cmd}\r\n");
            $reply = fgets($fp, $this->BUFFER);
            $reply = $this->strip_clf($reply);
            if ($this->DEBUG) {
                @error_log("POP3 SEND [{$cmd}] GOT [{$reply}]", 0);
            }
            if (!$this->is_ok($reply)) {
                $this->ERROR = 'POP3 pop_list: ' . _('Error ') . "[{$reply}]";
                return false;
            }
            list($junk, $num, $size) = preg_split('/\s+/', $reply);
            return $size;
        }
        $cmd = 'LIST';
        $reply = $this->send_cmd($cmd);
        if (!$this->is_ok($reply)) {
            $reply = $this->strip_clf($reply);
            $this->ERROR = 'POP3 pop_list: ' . _('Error ') . "[{$reply}]";
            return false;
        }
        $msg_array = [];
        $msg_array[0] = $Total;
        for ($msg_c = 1; $msg_c <= $Total; $msg_c++) {
            if ($msg_c > $Total) {
                break;
            }
            $line = fgets($fp, $this->BUFFER);
            $line = $this->strip_clf($line);
            if (strpos($line, '.') === 0) {
                $this->ERROR = 'POP3 pop_list: ' . _('Premature end of list');
                return false;
            }
            list($this_msg, $msg_size) = preg_split('/\s+/', $line);
            settype($this_msg, 'integer');
            if ($this_msg != $msg_c) {
                $msg_array[$msg_c] = 'deleted';
            } else {
                $msg_array[$msg_c] = $msg_size;
            }
        }
        return $msg_array;
    }
    public function get($msg_num)
    {
        //  Retrieve the specified msg number. Returns an array
        //  where each line of the msg is an array element.
        if (!isset($this->FP)) {
            $this->ERROR = 'POP3 get: ' . _('No connection to server');
            return false;
        }
        $this->update_timer();
        $fp = $this->FP;
        $buffer = $this->BUFFER;
        $cmd = "RETR {$msg_num}";
        $reply = $this->send_cmd($cmd);
        if (!$this->is_ok($reply)) {
            $this->ERROR = 'POP3 get: ' . _('Error ') . "[{$reply}]";
            return false;
        }
        $count = 0;
        $msg_array = [];
        $line = fgets($fp, $buffer);
        while (!preg_match('/^\.\r\n/', $line)) {
            if ($line[0] == '.') {
                $line = substr($line, 1);
            }
            $msg_array[$count] = $line;
            $count++;
            $line = fgets($fp, $buffer);
            if (empty($line)) {
                break;
            }
        }
        return $msg_array;
    }
    public function last($type = 'count')
    {
        //  Returns the highest msg number in the mailbox.
        //  returns -1 on error, 0+ on success, if type != count
        //  results in a popstat() call (2 element array returned)
        $last = -1;
        if (!isset($this->FP)) {
            $this->ERROR = 'POP3 last: ' . _('No connection to server');
            return $last;
        }
        $reply = $this->send_cmd('STAT');
        if (!$this->is_ok($reply)) {
            $this->ERROR = 'POP3 last: ' . _('Error ') . "[{$reply}]";
            return $last;
        }
        $Vars = preg_split('/\s+/', $reply);
        $count = $Vars[1];
        $size = $Vars[2];
        settype($count, 'integer');
        settype($size, 'integer');
        if ($type != 'count') {
            return [$count, $size];
        }
        return $count;
    }
    public function reset()
    {
        //  Resets the status of the remote server. This includes
        //  resetting the status of ALL msgs to not be deleted.
        //  This method automatically closes the connection to the server.
        if (!isset($this->FP)) {
            $this->ERROR = 'POP3 reset: ' . _('No connection to server');
            return false;
        }
        $reply = $this->send_cmd('RSET');
        if (!$this->is_ok($reply)) {
            //  The POP3 RSET command -never- gives a -ERR
            //  response - if it ever does, something truly
            //  wild is going on.
            $this->ERROR = 'POP3 reset: ' . _('Error ') . "[{$reply}]";
            @error_log("POP3 reset: ERROR [{$reply}]", 0);
        }
        $this->quit();
        return true;
    }
    public function send_cmd($cmd = '')
    {
        //  Sends a user defined command string to the
        //  POP server and returns the results. Useful for
        //  non-compliant or custom POP servers.
        //  Do NOT include the \r\n as part of your command
        //  string - it will be appended automatically.
        //  The return value is a standard fgets() call, which
        //  will read up to $this->BUFFER bytes of data, until it
        //  encounters a new line, or EOF, whichever happens first.
        //  This method works best if $cmd responds with only
        //  one line of data.
        if (!isset($this->FP)) {
            $this->ERROR = 'POP3 send_cmd: ' . _('No connection to server');
            return false;
        }
        if (empty($cmd)) {
            $this->ERROR = 'POP3 send_cmd: ' . _('Empty command string');
            return '';
        }
        $fp = $this->FP;
        $buffer = $this->BUFFER;
        $this->update_timer();
        fwrite($fp, "{$cmd}\r\n");
        $reply = fgets($fp, $buffer);
        $reply = $this->strip_clf($reply);
        if ($this->DEBUG) {
            @error_log("POP3 SEND [{$cmd}] GOT [{$reply}]", 0);
        }
        return $reply;
    }
    public function quit()
    {
        //  Closes the connection to the POP3 server, deleting
        //  any msgs marked as deleted.
        if (!isset($this->FP)) {
            $this->ERROR = 'POP3 quit: ' . _('connection does not exist');
            return false;
        }
        $fp = $this->FP;
        $cmd = 'QUIT';
        fwrite($fp, "{$cmd}\r\n");
        $reply = fgets($fp, $this->BUFFER);
        $reply = $this->strip_clf($reply);
        if ($this->DEBUG) {
            @error_log("POP3 SEND [{$cmd}] GOT [{$reply}]", 0);
        }
        fclose($fp);
        unset($this->FP);
        return true;
    }
    public function popstat()
    {
        //  Returns an array of 2 elements. The number of undeleted
        //  msgs in the mailbox, and the size of the mbox in octets.
        $pop_array = $this->last('array');
        if ($pop_array == -1) {
            return false;
        }
        if (!$pop_array or empty($pop_array)) {
            return false;
        }
        return $pop_array;
    }
    public function uidl($msg_num = '')
    {
        //  Returns the UIDL of the msg specified. If called with
        //  no arguments, returns an associative array where each
        //  undeleted msg num is a key, and the msg's uidl is the element
        //  Array element 0 will contain the total number of msgs
        if (!isset($this->FP)) {
            $this->ERROR = 'POP3 uidl: ' . _('No connection to server');
            return false;
        }
        $fp = $this->FP;
        $buffer = $this->BUFFER;
        if (!empty($msg_num)) {
            $cmd = "UIDL {$msg_num}";
            $reply = $this->send_cmd($cmd);
            if (!$this->is_ok($reply)) {
                $this->ERROR = 'POP3 uidl: ' . _('Error ') . "[{$reply}]";
                return false;
            }
            list($ok, $num, $my_uidl) = preg_split('/\s+/', $reply);
            return $my_uidl;
        } else {
            $this->update_timer();
            $uidl_array = [];
            $Total = $this->COUNT;
            $uidl_array[0] = $Total;
            if ($Total < 1) {
                return $uidl_array;
            }
            $cmd = 'UIDL';
            fwrite($fp, "UIDL\r\n");
            $reply = fgets($fp, $buffer);
            $reply = $this->strip_clf($reply);
            if ($this->DEBUG) {
                @error_log("POP3 SEND [{$cmd}] GOT [{$reply}]", 0);
            }
            if (!$this->is_ok($reply)) {
                $this->ERROR = 'POP3 uidl: ' . _('Error ') . "[{$reply}]";
                return false;
            }
            $line = '';
            $count = 1;
            $line = fgets($fp, $buffer);
            while (!preg_match('/^\.\r\n/', $line)) {
                list($msg, $msg_uidl) = preg_split('/\s+/', $line);
                $msg_uidl = $this->strip_clf($msg_uidl);
                if ($count == $msg) {
                    $uidl_array[$msg] = $msg_uidl;
                } else {
                    $uidl_array[$count] = 'deleted';
                }
                $count++;
                $line = fgets($fp, $buffer);
            }
        }
        return $uidl_array;
    }
    public function delete($msg_num = '')
    {
        //  Flags a specified msg as deleted. The msg will not
        //  be deleted until a quit() method is called.
        if (!isset($this->FP)) {
            $this->ERROR = 'POP3 delete: ' . _('No connection to server');
            return false;
        }
        if (empty($msg_num)) {
            $this->ERROR = 'POP3 delete: ' . _('No msg number submitted');
            return false;
        }
        $reply = $this->send_cmd("DELE {$msg_num}");
        if (!$this->is_ok($reply)) {
            $this->ERROR = 'POP3 delete: ' . _('Command failed ') . "[{$reply}]";
            return false;
        }
        return true;
    }
    //  *********************************************************
    //  The following methods are internal to the class.
    public function is_ok($cmd = '')
    {
        //  Return true or false on +OK or -ERR
        if (empty($cmd)) {
            return false;
        } else {
            return stripos($cmd, '+OK') !== false;
        }
    }
    public function strip_clf($text = '')
    {
        // Strips \r\n from server responses
        if (empty($text)) {
            return $text;
        } else {
            $stripped = str_replace(["\r", "\n"], '', $text);
            return $stripped;
        }
    }
    public function parse_banner($server_text)
    {
        $outside = true;
        $banner = '';
        $length = strlen($server_text);
        for ($count = 0; $count < $length; $count++) {
            $digit = substr($server_text, $count, 1);
            if (!empty($digit)) {
                if (!$outside && $digit != '<' && $digit != '>') {
                    $banner .= $digit;
                }
                if ($digit == '<') {
                    $outside = false;
                }
                if ($digit == '>') {
                    $outside = true;
                }
            }
        }
        $banner = $this->strip_clf($banner);
        // Just in case
        return "<{$banner}>";
    }
}
// End class
// For php4 compatibility
if (!function_exists('stripos')) {
    function stripos($haystack, $needle)
    {
        return strpos($haystack, stristr($haystack, $needle));
    }
}