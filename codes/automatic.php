<?php
 
if (isset($_GET['cmd'])) {
    $pidFile = '/fifo/teste/PID.txt'; 
    $file = '/fifo/teste/fifo';
    $cmd = $_GET['cmd'];

    if($cmd=="04"){
        echo "Que legal.\n";
        $signal = $_GET['signal'];
        $deg = $_GET['deg'];
        $min = $_GET['min'];
        $sec = $_GET['sec'];
        $T_hour = $_GET['T_hour'];
        $T_min = $_GET['T_min'];

        $message = $cmd . $signal . $deg . $min . $sec . $T_hour . $T_min; // Montando a mensagem
    } elseif ($cmd=="01") {
        $message = $cmd;
    }
    
    
    // Verificar se o FIFO existe, senão criar
    if (!file_exists($file)) {
        if (!posix_mkfifo($file, 0777)) {
            die("Erro ao criar o Named Pipe.\n");
        }
    }

    // Abrir o FIFO para escrita e enviar o comando do motor
    $pipe = fopen($file, 'w');
    if ($pipe) {
        fwrite($pipe, $message);
        fclose($pipe);  // Fechar o FIFO após a escrita
    } else {
        echo "Erro ao abrir o pipe para escrita.\n";
    }


    // Verificar se o arquivo de PID existe
    if (file_exists($pidFile)) {
        // Ler o PID do arquivo
        $pid = file_get_contents($pidFile);

        // Verificar se o PID é válido
        if (is_numeric($pid)) {
            // Monta o comando para enviar o sinal SIGHUP ao processo
            $command = "/usr/bin/sudo kill -HUP " . escapeshellarg($pid);

            // Executa o comando
            exec($command, $output, $return_var);

            // Verifica o resultado
            if ($return_var === 0) {
                echo "Sinal HUP enviado com sucesso para o processo com PID $pid.\n";
            } else {
                echo "Falha ao enviar o sinal para o processo com PID $pid. Código de retorno: $return_var\n";
            }
        } else {
            echo "PID inválido no arquivo $pidFile.\n";
        }
    } else {
        echo "Arquivo $pidFile não encontrado.\n";
    }

}
?>
