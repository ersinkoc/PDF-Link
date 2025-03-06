<?php
/**
 * PDF Link - Installation Script
 * 
 * This script handles the installation process for the PDF Link system.
 * It checks system requirements, extracts vendor files, and verifies directory permissions.
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session start
session_start();

// Define installation steps
$steps = [
    1 => 'System Requirements Check',
    2 => 'Directory Permissions',
    3 => 'Extract Vendor Files',
    4 => 'Installation Complete'
];

// Current step
$current_step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Header
function display_header($current_step, $steps) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PDF Link - Installation</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 0;
                padding: 20px;
                background-color: #f5f5f5;
                color: #333;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                background-color: #fff;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            h1 {
                color: #2c3e50;
                margin-bottom: 20px;
            }
            .progress {
                display: flex;
                margin-bottom: 30px;
                background-color: #eee;
                border-radius: 5px;
                overflow: hidden;
            }
            .progress-step {
                flex: 1;
                text-align: center;
                padding: 10px 0;
                font-size: 14px;
                background-color: #ddd;
                position: relative;
            }
            .progress-step.active {
                background-color: #3498db;
                color: white;
            }
            .progress-step.completed {
                background-color: #2ecc71;
                color: white;
            }
            .alert {
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 4px;
            }
            .alert-success {
                background-color: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .alert-danger {
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .alert-warning {
                background-color: #fff3cd;
                color: #856404;
                border: 1px solid #ffeeba;
            }
            .alert-info {
                background-color: #d1ecf1;
                color: #0c5460;
                border: 1px solid #bee5eb;
            }
            .btn {
                display: inline-block;
                padding: 10px 20px;
                background-color: #3498db;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                border: none;
                cursor: pointer;
                font-size: 16px;
            }
            .btn:hover {
                background-color: #2980b9;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            table, th, td {
                border: 1px solid #ddd;
            }
            th, td {
                padding: 12px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            .loader {
                border: 5px solid #f3f3f3;
                border-top: 5px solid #3498db;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                animation: spin 2s linear infinite;
                margin: 20px auto;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            #progress-container {
                width: 100%;
                background-color: #f1f1f1;
                border-radius: 5px;
                margin: 20px 0;
            }
            #progress-bar {
                width: 0%;
                height: 30px;
                background-color: #4CAF50;
                text-align: center;
                line-height: 30px;
                color: white;
                border-radius: 5px;
                transition: width 0.3s;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>PDF Link - Installation</h1>
            
            <div class="progress">
                <?php foreach ($steps as $step_num => $step_name): ?>
                    <div class="progress-step <?php 
                        if ($step_num < $current_step) echo 'completed';
                        else if ($step_num == $current_step) echo 'active';
                    ?>">
                        <?php echo $step_num . '. ' . $step_name; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <h2><?php echo $steps[$current_step]; ?></h2>
    <?php
}

// Footer
function display_footer() {
    ?>
        </div>
    </body>
    </html>
    <?php
}

// Display header
display_header($current_step, $steps);

// Process current step
switch ($current_step) {
    case 1:
        // System Requirements Check
        $requirements = [
            'PHP Version' => [
                'required' => '8.0.0',
                'current' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '8.0.0', '>=')
            ],
            'SQLite3' => [
                'required' => 'Enabled',
                'current' => class_exists('SQLite3') ? 'Enabled' : 'Disabled',
                'status' => class_exists('SQLite3')
            ],
            'PDO SQLite' => [
                'required' => 'Enabled',
                'current' => in_array('sqlite', PDO::getAvailableDrivers()) ? 'Enabled' : 'Disabled',
                'status' => in_array('sqlite', PDO::getAvailableDrivers())
            ],
            'GD Library' => [
                'required' => 'Enabled',
                'current' => extension_loaded('gd') ? 'Enabled' : 'Disabled',
                'status' => extension_loaded('gd')
            ],
            'ZIP Extension' => [
                'required' => 'Enabled',
                'current' => extension_loaded('zip') ? 'Enabled' : 'Disabled',
                'status' => extension_loaded('zip')
            ],
            'JSON Extension' => [
                'required' => 'Enabled',
                'current' => extension_loaded('json') ? 'Enabled' : 'Disabled',
                'status' => extension_loaded('json')
            ],
            'FileInfo Extension' => [
                'required' => 'Enabled',
                'current' => extension_loaded('fileinfo') ? 'Enabled' : 'Disabled',
                'status' => extension_loaded('fileinfo')
            ]
        ];
        
        $all_requirements_met = true;
        
        echo '<table>';
        echo '<tr><th>Requirement</th><th>Required</th><th>Current</th><th>Status</th></tr>';
        
        foreach ($requirements as $name => $requirement) {
            $status_class = $requirement['status'] ? 'success' : 'danger';
            $status_text = $requirement['status'] ? 'Passed' : 'Failed';
            
            echo '<tr>';
            echo '<td>' . $name . '</td>';
            echo '<td>' . $requirement['required'] . '</td>';
            echo '<td>' . $requirement['current'] . '</td>';
            echo '<td class="alert-' . $status_class . '">' . $status_text . '</td>';
            echo '</tr>';
            
            if (!$requirement['status']) {
                $all_requirements_met = false;
            }
        }
        
        echo '</table>';
        
        if ($all_requirements_met) {
            echo '<div class="alert alert-success">All system requirements are met!</div>';
            echo '<a href="install.php?step=2" class="btn">Continue to Next Step</a>';
        } else {
            echo '<div class="alert alert-danger">Some system requirements are not met. Please fix the issues before continuing.</div>';
        }
        break;
        
    case 2:
        // Directory Permissions Check
        $directories = [
            '../database',
            '../uploads',
            '../backups',
            '../cache',
            '../logs'
        ];
        
        $all_permissions_correct = true;
        
        echo '<table>';
        echo '<tr><th>Directory</th><th>Required Permission</th><th>Status</th></tr>';
        
        foreach ($directories as $directory) {
            $is_writable = is_writable($directory);
            $status_class = $is_writable ? 'success' : 'danger';
            $status_text = $is_writable ? 'Writable' : 'Not Writable';
            
            echo '<tr>';
            echo '<td>' . $directory . '</td>';
            echo '<td>Writable</td>';
            echo '<td class="alert-' . $status_class . '">' . $status_text . '</td>';
            echo '</tr>';
            
            if (!$is_writable) {
                $all_permissions_correct = false;
            }
        }
        
        echo '</table>';
        
        if ($all_permissions_correct) {
            echo '<div class="alert alert-success">All directory permissions are correct!</div>';
            echo '<a href="install.php?step=3" class="btn">Continue to Next Step</a>';
        } else {
            echo '<div class="alert alert-danger">Some directories are not writable. Please fix the permissions before continuing.</div>';
            echo '<div class="alert alert-warning">
                <p>To fix permissions, you can use the following commands:</p>
                <code>chmod 755 database uploads backups cache logs</code>
            </div>';
        }
        break;
        
    case 3:
        // Extract Vendor Files
        $vendor_zip = 'vendor.zip';
        $extract_path = '../vendor/';
        
        if (!file_exists($vendor_zip)) {
            echo '<div class="alert alert-danger">Vendor ZIP file not found. Please make sure the file exists in the installation directory.</div>';
            break;
        }
        
        // Show extraction process with JavaScript
        ?>
        <div class="alert alert-info">
            <p>Extracting vendor files. This process may take several minutes depending on your server speed.</p>
            <p>Please do not close this window or navigate away during the extraction process.</p>
        </div>
        
        <div id="progress-container">
            <div id="progress-bar">0%</div>
        </div>
        
        <div class="loader" id="loader"></div>
        <div id="status-message">Preparing to extract files...</div>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var progressBar = document.getElementById('progress-bar');
                var statusMessage = document.getElementById('status-message');
                var progress = 0;
                
                // Simulate extraction progress
                var interval = setInterval(function() {
                    if (progress < 90) {
                        progress += Math.random() * 5;
                        progressBar.style.width = progress + '%';
                        progressBar.innerHTML = Math.round(progress) + '%';
                        
                        if (progress < 20) {
                            statusMessage.innerHTML = "Preparing files...";
                        } else if (progress < 40) {
                            statusMessage.innerHTML = "Extracting vendor packages...";
                        } else if (progress < 60) {
                            statusMessage.innerHTML = "Setting up dependencies...";
                        } else if (progress < 80) {
                            statusMessage.innerHTML = "Finalizing extraction...";
                        }
                    } else {
                        clearInterval(interval);
                        // Actually perform the extraction
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', 'install.php?step=3&action=extract', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onload = function() {
                            if (this.status == 200) {
                                progressBar.style.width = '100%';
                                progressBar.innerHTML = '100%';
                                statusMessage.innerHTML = "Extraction complete!";
                                document.getElementById('loader').style.display = 'none';
                                
                                // Show success message and continue button
                                var container = document.querySelector('.container');
                                var successDiv = document.createElement('div');
                                successDiv.className = 'alert alert-success';
                                successDiv.innerHTML = 'Vendor files extracted successfully!';
                                container.appendChild(successDiv);
                                
                                var continueBtn = document.createElement('a');
                                continueBtn.href = 'install.php?step=4';
                                continueBtn.className = 'btn';
                                continueBtn.innerHTML = 'Continue to Next Step';
                                container.appendChild(continueBtn);
                            } else {
                                statusMessage.innerHTML = "Error during extraction. Please try again.";
                                var errorDiv = document.createElement('div');
                                errorDiv.className = 'alert alert-danger';
                                errorDiv.innerHTML = 'Failed to extract vendor files: ' + this.responseText;
                                document.querySelector('.container').appendChild(errorDiv);
                            }
                        };
                        xhr.send('extract=1');
                    }
                }, 500);
            });
        </script>
        <?php
        
        // Handle actual extraction via AJAX
        if (isset($_GET['action']) && $_GET['action'] === 'extract') {
            // Perform the actual extraction
            $zip = new ZipArchive;
            if ($zip->open($vendor_zip) === TRUE) {
                $zip->extractTo($extract_path);
                $zip->close();
                echo "success";
            } else {
                http_response_code(500);
                echo "Failed to open ZIP file";
            }
            exit;
        }
        break;
        
    case 4:
        // Installation Complete
        ?>
        <div class="alert alert-success">
            <h3>Installation Complete!</h3>
            <p>PDF Link has been successfully installed on your server.</p>
        </div>
        
        <div class="alert alert-warning">
            <h4>Important Security Steps:</h4>
            <ul>
                <li>Delete the setup directory for security reasons</li>
                <li>Set proper file permissions</li>
                <li>Configure your web server for optimal security</li>
            </ul>
        </div>
        
        <a href="../index.php" class="btn">Go to Homepage</a>
        <a href="../admin/index.php" class="btn">Go to Admin Panel</a>
        <?php
        break;
}

// Display footer
display_footer();
?>
