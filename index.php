<?php
// Dynamic CORS Headers to support cross-origin API calls from notepad.vibhu.pro or local tests
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
} else {
    header("Access-Control-Allow-Origin: *");
}

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}

$file = 'note.txt';
$message = '';

// Check if it's an API request (expects JSON or sends JSON)
$is_api = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
          (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
          (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read raw JSON input from fetch body
    $raw_input = file_get_contents('php://input');
    $input = json_decode($raw_input, true);
    
    $content = $input['content'] ?? $_POST['content'] ?? '';
    file_put_contents($file, $content);
    
    if ($is_api || isset($input['content'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Note saved successfully',
            'time' => date('H:i:s')
        ]);
        exit;
    }
    
    $message = 'Note saved successfully at ' . date('H:i:s');
}

$content = file_exists($file) ? file_get_contents($file) : '';

if ($is_api) {
    header('Content-Type: application/json');
    echo json_encode([
        'content' => $content
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Notepad</title>
    <style>
        :root {
            --bg-color: #0f172a;
            --container-bg: #1e293b;
            --text-color: #f8fafc;
            --border-color: #334155;
            --accent-color: #3b82f6;
            --accent-hover: #2563eb;
            --success-color: #10b981;
        }

        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        .container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            max-width: 900px;
            margin: 0 auto;
            background: var(--container-bg);
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }

        header {
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }

        h1 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status {
            font-size: 0.875rem;
            color: var(--success-color);
            opacity: <?php echo empty($message) ? '0' : '1'; ?>;
            transition: opacity 0.3s;
        }

        form {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        textarea {
            flex-grow: 1;
            width: 100%;
            border: none;
            padding: 1.5rem;
            font-size: 1rem;
            line-height: 1.6;
            resize: none;
            outline: none;
            box-sizing: border-box;
            background-color: transparent;
            color: var(--text-color);
            font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
        }

        textarea::placeholder {
            color: #64748b;
        }

        .toolbar {
            padding: 1rem 1.5rem;
            background-color: rgba(15, 23, 42, 0.5);
            text-align: right;
            border-top: 1px solid var(--border-color);
        }

        button {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        button:hover {
            background-color: var(--accent-hover);
            transform: translateY(-1px);
        }

        button:active {
            transform: translateY(0);
        }
        
        /* Auto-hide status message after 3 seconds */
        .status.fade-out {
            animation: fadeOut 3s forwards;
        }
        
        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                PHP Notepad
            </h1>
            <?php if (!empty($message)): ?>
                <span class="status fade-out"><?php echo $message; ?></span>
            <?php endif; ?>
        </header>
        <form method="POST">
            <textarea name="content" placeholder="Start typing your notes here..." spellcheck="false" autofocus><?php echo htmlspecialchars($content); ?></textarea>
            <div class="toolbar">
                <button type="submit">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Save Note
                </button>
            </div>
        </form>
    </div>
</body>
</html>
