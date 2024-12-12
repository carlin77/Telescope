#include <stdio.h>
#include <stdlib.h>
#include <pthread.h>
#include <unistd.h> // Para usar sleep()

void *record_video(void *arg) {
    
    const char *command = "ffmpeg -y -f v4l2 -i /dev/video0 -ss 3 -frames:v 1 -q:v 2 img/img.png";

    // Executa o comando do sistema para iniciar a gravação
    int ret = system(command);
    if (ret == -1) {
        perror("Erro ao executar o comando de gravação");
        pthread_exit(NULL);
    }

    pthread_exit(NULL);
}

void *configure_camera(void *arg) {
    sleep(1);

    // Recebe os valores de exposição e ganho do argumento
    int *config = (int *)arg;  
    int exposure_time = config[0];
    int brightness = config[1];
    int contrast = config[2];
    int gain = config[3];
    int saturation = config[4];

    // Monta os comandos para alterar os controles da câmera
    char command[256];
    int ret;

    snprintf(command, sizeof(command), "v4l2-ctl -d /dev/video0 --set-ctrl=exposure_time_absolute=%d", exposure_time);
    ret = system(command);
    if (ret == -1) {
        perror("Erro ao configurar tempo de exposição");
        pthread_exit(NULL);
    }

    snprintf(command, sizeof(command), "v4l2-ctl -d /dev/video0 --set-ctrl=brightness=%d", brightness);
    ret = system(command);
    if (ret == -1) {
        perror("Erro ao configurar brilho");
        pthread_exit(NULL);
    }

    snprintf(command, sizeof(command), "v4l2-ctl -d /dev/video0 --set-ctrl=contrast=%d", contrast);
    ret = system(command);
    if (ret == -1) {
        perror("Erro ao configurar contraste");
        pthread_exit(NULL);
    }

    snprintf(command, sizeof(command), "v4l2-ctl -d /dev/video0 --set-ctrl=gain=%d", gain);
    ret = system(command);
    if (ret == -1) {
        perror("Erro ao configurar ganho");
        pthread_exit(NULL);
    }

    snprintf(command, sizeof(command), "v4l2-ctl -d /dev/video0 --set-ctrl=saturation=%d", saturation);
    ret = system(command);
    if (ret == -1) {
        perror("Erro ao configurar saturação");
        pthread_exit(NULL);
    }

    pthread_exit(NULL);
}

int main(int argc, char *argv[]) {
    if (argc != 6) {
        fprintf(stderr, "Uso: %s <tempo_de_exposicao> <brilho> <contraste> <ganho> <saturacao>\n", argv[0]);
        return 1;
    }

    int exposure_time = atoi(argv[1]);
    int brightness = atoi(argv[2]);
    int contrast = atoi(argv[3]);
    int gain = atoi(argv[4]);
    int saturation = atoi(argv[5]);

    // Validação dos valores de entrada
    if (exposure_time <= 0 || brightness < 0 || contrast < 0 || gain < 0 || saturation < 0) {
        fprintf(stderr, "Erro: Os valores devem ser positivos e o tempo de exposição maior que 0.\n");
        return 1;
    }

    int config[5] = {exposure_time, brightness, contrast, gain, saturation};

    pthread_t thread1, thread2;

    // Cria a primeira thread para gravar o vídeo
    if (pthread_create(&thread1, NULL, record_video, NULL) != 0) {
        perror("Erro ao criar a thread de gravação");
        return 1;
    }

    // Cria a segunda thread para configurar a câmera
    if (pthread_create(&thread2, NULL, configure_camera, config) != 0) {
        perror("Erro ao criar a thread de configuração");
        return 1;
    }

    // Aguarda as threads terminarem
    pthread_join(thread1, NULL);
    pthread_join(thread2, NULL);

    return 0;
}
