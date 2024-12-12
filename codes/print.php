<?php

// Verificar se todos os parâmetros esperados estão presentes
if (isset($_GET['exposure'], $_GET['brightness'], $_GET['contrast'], $_GET['gain'], $_GET['saturation'])) {
    // Obter os valores dos parâmetros
    $exposure = escapeshellarg($_GET['exposure']);
    $brightness = escapeshellarg($_GET['brightness']);
    $contrast = escapeshellarg($_GET['contrast']);
    $gain = escapeshellarg($_GET['gain']);
    $saturation = escapeshellarg($_GET['saturation']);

    // Montar o comando para executar o programa com os parâmetros
    $command = "./photo $exposure $brightness $contrast $gain $saturation";

    // Executa o comando
    exec($command, $output, $return_var);

    // Para depuração, você pode exibir a saída e o status do retorno
    echo "Saída: " . implode("\n", $output) . "<br>";
    echo "Status do retorno: " . $return_var;
} else {
    echo "Erro: Parâmetros insuficientes fornecidos.";
}
?>
