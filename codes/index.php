<?php
// Lida com a requisição AJAX para verificar o arquivo
if (isset($_GET['check_image'])) {
    $image_path = 'img/img.png'; // Caminho relativo ao script PHP

    if (file_exists($image_path)) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
    exit; // Encerra o script após retornar a resposta
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrbitalView</title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/container.css">
    <link rel="stylesheet" href="css/btn.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

</head>
<body>
    
    <!--Menu Horizontal-->
    <header>
        <div class="menu">
            <div class="logo">OrbitalView</div>
        </div>
    </header>

    <!--Organizando em 3 colunas-->
    <div class="container">
        <div class="edge-column"></div>

        <div class="middle-column">

            <div class="video-container">
                <img id="webcam-stream" src="/TCC/telecop/video_stream" alt="Webcam Stream">
            </div>

            <div class="btnBox" id="btn-box">
                <div class="btnRow">
                    <button class="btn clip1" id="left-up" onmousedown="directionCmd('02','4','8')" onmouseup="stopCmd('01')"><i class="fas fa-arrow-right"></i></button>

                    <button class="btn" onmousedown="directionCmd('02','1','1')" onmouseup="stopCmd('01')"><i class="fas fa-arrow-up"></i></button>

                    <button class="btn clip1" id="right-up" onmousedown="directionCmd('02','4','1')" onmouseup="stopCmd('01')"><i class="fas fa-arrow-right"></i></button>
                </div>



                <div class="btnRow">
                    <button class="btn left btnSpace" onmousedown="directionCmd('02','2','4')" onmouseup="stopCmd('01')"><i class="fas fa-arrow-left"></i></btuton>
                    <button class="btn right btnSpace" onmousedown="directionCmd('02','2','1')" onmouseup="stopCmd('01')"><i class="fas fa-arrow-right"></i></button>
                </div>
                <div class="btnRow">
                    <button class="btn clip1" id="left-down" onmousedown="directionCmd('02','4','4')" onmouseup="stopCmd('01')"><i class="fas fa-arrow-right"></i></button>

                    <button class="btn" onmousedown="directionCmd('02','1','4')" onmouseup="stopCmd('01')"><i class="fas fa-arrow-down"></i></button>

                    <button class="btn clip1" id="right-down" onmousedown="directionCmd('02','4','2')" onmouseup="stopCmd('01')"><i class="fas fa-arrow-right"></i></button>
                </div>

            </div>

            <script>
            function directionCmd(cmd, motor, dir) {
                var xhttp = new XMLHttpRequest();
                xhttp.open("GET", "direction.php?cmd=" + cmd + "&motor=" + motor + "&dir=" + dir, true);
                xhttp.send();
            }

            function stopCmd(cmd) {
                var xhttp = new XMLHttpRequest();
                xhttp.open("GET", "direction.php?cmd=" + cmd, true);
                xhttp.send();
            }
            </script>


        </div>

        <div class="edge-column">
            <div class="config-box">
                <div class="title-box">CONFIGURATION</div>
                <div class="line"></div>

                <h1 class="sub-title">Automatic Mode</h1>
                <div class="line sub-line"></div>
                <div class="form" id="view-start">
                    <label for="oi">Latitude:</label>
                    <select name="signal" id="signal" class="sendBox mini">
                        <option value="1">+</option>
                        <option value="0">-</option>
                    </select>
                    <input type="text" id="deg" name ="deg" placeholder="graus" class="sendBox mini">
                    <input type="text" id="min" name ="min" placeholder="minutos" class="sendBox mini">
                    <input type="text" id="sec" name ="sec" placeholder="segundos" class="sendBox mini">

                    <label for="oi">Tempo:</label>
                    <input type="text" id="time_hour" name ="time_hour" placeholder="horas" class="sendBox mini">
                    <input type="text" id="time_min" name ="time_min" placeholder="minutos" class="sendBox mini"> <br>
                    <button class="view" onclick="automatiCmd('04')">Start</button>
                </div>

                <div class="form" id="stop-com" style="display:none;">
                    <button class="view" onclick="control_mode()" id="btn-stop">Stop</button>
                </div>

                <script>
                    
                function control_mode(){
                    document.getElementById('btn-box').style.display = "block";
                    document.getElementById('view-start').style.display = "block";  
                    document.getElementById('stop-com').style.display = "none";  

                }
                function automatiCmd(cmd) {
                    document.getElementById('btn-box').style.display = "none";
                    document.getElementById('view-start').style.display = "none"; 
                    document.getElementById('stop-com').style.display = "block"; 

                    var signal = document.getElementById('signal').value;
                    var deg = document.getElementById('deg').value;
                    var min = document.getElementById('min').value;
                    var sec = document.getElementById('sec').value;
                    var T_hour = document.getElementById('time_hour').value;
                    var T_min = document.getElementById('time_min').value;

                    
                    // Criar a requisição para enviar os dados via GET
                    var xhttp = new XMLHttpRequest();
                    var url = "automatic.php?cmd=" + cmd
                    + "&signal=" + encodeURIComponent(signal) + "&deg=" + encodeURIComponent(deg) + 
                            "&min=" + encodeURIComponent(min) + 
                            "&sec=" + encodeURIComponent(sec) + 
                            "&T_hour=" + encodeURIComponent(T_hour) + 
                            "&T_min=" + encodeURIComponent(T_min);
                    xhttp.open("GET", url, true);
                    xhttp.send();

                }
                </script>
                

                <h1 class="sub-title">Camera</h1>
                <div class="line sub-line"></div>
                <div class="form">
                    <label for="exposure">Exposure time:</label>
                    <input type="text" id="exposure" name="exposure" placeholder="Insira o tempo de exposição" class="sendBox big">
                    
                    <label for="brightness">Brightness:</label>
                    <input type="text" id="brightness" name="brightness" placeholder="Insira o brilho" class="sendBox big">
                    
                    <label for="contrast">Contrast:</label>
                    <input type="text" id="contrast" name="contrast" placeholder="Insira o contraste" class="sendBox big">
                    
                    <label for="gain">Gain:</label>
                    <input type="text" id="gain" name="gain" placeholder="Insira o ganho" class="sendBox big">
                    
                    <label for="saturation">Saturation:</label>
                    <input type="text" id="saturation" name="saturation" placeholder="Insira a saturação" class="sendBox big">
                    <br>
                    <button id="wait-button" class="view" onmousedown="printRequest()">Print</button>
                </div>

                <script>
                    document.getElementById('wait-button').addEventListener('click', function () {
                        const container = document.getElementById('webcam-stream');

                        // Função para verificar a existência da imagem
                        function checkImageExists() {
                            fetch('?check_image=1') // Requisição para o mesmo arquivo
                                .then(response => response.json())
                                .then(data => {
                                    if (data.exists) {
                                        container.src = 'img/img.png';
                                    } else {
                                        setTimeout(checkImageExists, 1000); 
                                    }
                                })
                                .catch(err => console.error('Erro ao verificar imagem:', err));
                        }

                        // Inicia a verificação
                        checkImageExists();
                    });
                </script>

                <script>
                function printRequest() {
                    var exposure = document.getElementById('exposure').value;
                    var brightness = document.getElementById('brightness').value;
                    var contrast = document.getElementById('contrast').value;
                    var gain = document.getElementById('gain').value;
                    var saturation = document.getElementById('saturation').value;
                    
                    // Criar a requisição para enviar os dados via GET
                    var xhttp = new XMLHttpRequest();
                    var url = "print.php?exposure=" + encodeURIComponent(exposure) + 
                            "&brightness=" + encodeURIComponent(brightness) + 
                            "&contrast=" + encodeURIComponent(contrast) + 
                            "&gain=" + encodeURIComponent(gain) + 
                            "&saturation=" + encodeURIComponent(saturation);
                            
                    xhttp.open("GET", url, true);
                    xhttp.send();

                }
                </script>
                
            </div>
            
        </div>
    </div>

</body>