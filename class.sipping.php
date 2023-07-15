<?php
        class sipPing {
                public $version = "1.0.0";
                public $dest_addr;
                public $dest_port;
                public $src_ip;
                public $src_host;
                public $src_port;
                public $udp;
                public $timeout;
                public $from_tag;
                public $call_id;
                public $options;
​
                public function __construct($dest_addr, $dest_port = 5060, $src_ip = '', $src_port = null, $udp = true, $timeout = 1) {
                        $this->dest_addr = $dest_addr;
                        $this->dest_port = $dest_port;
​
​
                        if ($src_port == null) {
                                $this->src_port = rand(1024, 65535);
                        }
                        else {
                                $this->src_port = $src_port;
                        }
​
                        if (strlen($src_ip) > 0) {
                                $src_host = $src_ip;
                        }
                        else {
                                $src_host = "dummy.com";
                        }
​
                        $this->src_host = $src_host;
​
                        $this->from_tag = bin2hex(random_bytes(4));
                        $this->call_id = rand(1000000000, 9999999999);
​
                        $this->options = "OPTIONS sip:nobody@{$dest_addr}:{$dest_port} SIP/2.0\r\nVia: SIP/2.0/UDP {$src_host}:{$src_port};branch=%%branch%%;rport;alias\r\nFrom: sip:sipping@{$src_host}:{$src_port};tag={from_tag}\r\nTo: sip:nobody@{$dest_addr}:{$dest_port}\r\nCall-ID: {$this->call_id}@{$src_host}\r\nCSeq: 1 OPTIONS\r\nContact: sip:sipping@{$src_host}:{$src_port}\r\nContent-Length: 0\r\nMax-Forwards: 70\r\nUser-Agent: sipping.php {$this->version}\r\nAccept: text/plain\r\n\r\n";
​
                }
​
                public function ping_once() {
                        $timeout = False;
                        $branch = "z9hG4bK.".bin2hex(random_bytes(4));
                        $sip_options = str_replace("%%branch%%", $branch, $this->options);
​
                        if (!$this->udp) {
                                $s = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                                socket_set_option($s, SOL_SOCKET, SO_REUSEADDR, 1);
                        }
                        else {
                                $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
                        }
​
                        socket_bind($s, $this->src_addr, $this->src_port);
                        socket_set_option($s, SOL_SOCKET, SO_RCVTIMEO, array("sec" => $this->timeout));
​
                        $startTime = time();
​
                        try {
                                socket_connect($s, $this->dest_addr, $this->dest_port);
                                socket_send($s, $sip_options, strlen($sip_options));
                                $response = socket_read($s, 65536);
                        }
                        catch (Exception $ex) {
                                return null;
                        }
​
                        $endTime = time();
​
                        try {
                                socket_shutdown($s);
                                socket_close($s);
                        }
                        catch (Exception $ex) {}
​
                        return ($endTime - $startTime);
                }
​
                public function ping($count, $delay = 0) {
                        $results = array();
                        for ($i = 0; $i < $count; $i++) {
                                $i++;
                                $results[] = $this->ping_once();
                                sleep($delay);
                        }
                        return $results;
                }
        }
?>
