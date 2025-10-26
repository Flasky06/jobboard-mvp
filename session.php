<?php
/**
 * Debug Session Script
 * Place this file in your /pages or root directory
 * Access it to see what's in your session
 */

session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Debug</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        background: #f5f5f5;
    }

    .debug-box {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    h1 {
        color: #333;
    }

    h2 {
        color: #666;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
    }

    pre {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        overflow-x: auto;
    }

    .label {
        font-weight: bold;
        color: #007bff;
    }

    .value {
        color: #28a745;
    }

    .missing {
        color: #dc3545;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    td {
        padding: 8px;
        border-bottom: 1px solid #ddd;
    }

    td:first-child {
        font-weight: bold;
        width: 200px;
    }
    </style>
</head>

<body>
    <div class="debug-box">
        <h1>🔍 Session Debug Information</h1>

        <h2>Session Status</h2>
        <table>
            <tr>
                <td>Session Started:</td>
                <td class="<?php echo (session_status() === PHP_SESSION_ACTIVE) ? 'value' : 'missing'; ?>">
                    <?php echo (session_status() === PHP_SESSION_ACTIVE) ? 'Yes ✓' : 'No ✗'; ?>
                </td>
            </tr>
            <tr>
                <td>Session ID:</td>
                <td><?php echo session_id(); ?></td>
            </tr>
        </table>

        <h2>Authentication Status</h2>
        <table>
            <tr>
                <td>Is Logged In:</td>
                <td
                    class="<?php echo isset($_SESSION['user_uuid']) || isset($_SESSION['user_id']) ? 'value' : 'missing'; ?>">
                    <?php echo isset($_SESSION['user_uuid']) || isset($_SESSION['user_id']) ? 'Yes ✓' : 'No ✗'; ?>
                </td>
            </tr>
            <tr>
                <td>User UUID:</td>
                <td>
                    <?php if (isset($_SESSION['user_uuid'])): ?>
                    <span class="value"><?php echo htmlspecialchars($_SESSION['user_uuid']); ?></span>
                    <?php else: ?>
                    <span class="missing">Not set</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>User ID:</td>
                <td>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="value"><?php echo htmlspecialchars($_SESSION['user_id']); ?></span>
                    <?php else: ?>
                    <span class="missing">Not set</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>UUID (alternate):</td>
                <td>
                    <?php if (isset($_SESSION['uuid'])): ?>
                    <span class="value"><?php echo htmlspecialchars($_SESSION['uuid']); ?></span>
                    <?php else: ?>
                    <span class="missing">Not set</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>Role:</td>
                <td>
                    <?php if (isset($_SESSION['role'])): ?>
                    <span class="value"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                    <?php else: ?>
                    <span class="missing">Not set</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>Email:</td>
                <td>
                    <?php if (isset($_SESSION['email'])): ?>
                    <span class="value"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                    <?php else: ?>
                    <span class="missing">Not set</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <h2>Full Session Data</h2>
        <pre><?php print_r($_SESSION); ?></pre>

        <h2>Recommendations</h2>
        <div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; border-radius: 4px;">
            <?php if (empty($_SESSION)): ?>
            <p><strong>⚠️ Session is empty!</strong></p>
            <p>You need to log in. Go to your login page.</p>
            <?php elseif (!isset($_SESSION['user_uuid']) && !isset($_SESSION['user_id'])): ?>
            <p><strong>⚠️ No user identifier found!</strong></p>
            <p>Your login script should set either <code>$_SESSION['user_uuid']</code> or
                <code>$_SESSION['user_id']</code>
            </p>
            <p>Check your login/authentication code.</p>
            <?php else: ?>
            <p><strong>✓ Session looks good!</strong></p>
            <p>User identifier:
                <code><?php echo $_SESSION['user_uuid'] ?? $_SESSION['user_id'] ?? 'unknown'; ?></code>
            </p>
            <p>Role: <code><?php echo $_SESSION['role'] ?? 'not set'; ?></code></p>
            <?php endif; ?>
        </div>

        <div style="margin-top: 20px;">
            <a href="/"
                style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">Go
                to Home</a>
            <?php if (!empty($_SESSION)): ?>
            <a href="/auth/logout.php"
                style="display: inline-block; padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px; margin-left: 10px;">Logout</a>
            <?php else: ?>
            <a href="/auth/login.php"
                style="display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; margin-left: 10px;">Login</a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>